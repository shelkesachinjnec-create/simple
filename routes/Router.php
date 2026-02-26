<?php

class Router {
    private array $routes = [];

    public function get(string $pattern, string $controller, string $method): void {
        $this->routes[] = ['GET', $pattern, $controller, $method];
    }

    public function post(string $pattern, string $controller, string $method): void {
        $this->routes[] = ['POST', $pattern, $controller, $method];
    }

    public function dispatch(string $uri, string $httpMethod): void {
        $uri = trim(parse_url($uri, PHP_URL_PATH), '/');
        // Remove the base path from the uri
        $basePath = trim(parse_url(APP_URL, PHP_URL_PATH), '/');
        if ($basePath && strpos($uri, $basePath) === 0) {
            $uri = trim(substr($uri, strlen($basePath)), '/');
        }

        foreach ($this->routes as [$routeMethod, $pattern, $controllerName, $action]) {
            if ($routeMethod !== $httpMethod) continue;
            $regex = preg_replace('/\{(\w+)\}/', '([^/]+)', $pattern);
            if (preg_match('#^' . $regex . '$#', $uri, $matches)) {
                array_shift($matches);
                $controller = new $controllerName();
                // Cast int params
                $params = array_map(function($m) { return ctype_digit($m) ? (int)$m : $m; }, $matches);
                call_user_func_array([$controller, $action], $params);
                return;
            }
        }
        http_response_code(404);
        include VIEWS_PATH . '404.php';
    }
}
