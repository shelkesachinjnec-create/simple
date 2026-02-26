<?php

abstract class BaseController {
    protected function view(string $view, array $data = []): void {
        extract($data);
        $user = Auth::user();
        $flash_success = getFlash('success');
        $flash_error   = getFlash('error');
        $flash_info    = getFlash('info');
        $viewFile = VIEWS_PATH . $view . '.php';
        if (!file_exists($viewFile)) {
            die("View not found: $view");
        }
        include VIEWS_PATH . 'layout/header.php';
        include $viewFile;
        include VIEWS_PATH . 'layout/footer.php';
    }

    protected function viewPartial(string $view, array $data = []): void {
        extract($data);
        $viewFile = VIEWS_PATH . $view . '.php';
        if (file_exists($viewFile)) include $viewFile;
    }

    protected function redirect(string $url): void {
        redirect($url);
    }

    protected function json(array $data, int $status = 200): void {
        jsonResponse($data, $status);
    }

    protected function requireAuth(string $minRole = 'operator'): void {
        Auth::require($minRole);
    }

    protected function post(string $key, $default = ''): string {
        return sanitize($_POST[$key] ?? $default);
    }

    protected function get(string $key, $default = ''): string {
        return sanitize($_GET[$key] ?? $default);
    }

    protected function verifyCsrf(): void {
        $token = $_POST[CSRF_TOKEN_NAME] ?? '';
        if (!Auth::verifyCsrfToken($token)) {
            if (isAjax()) {
                $this->json(['success' => false, 'message' => 'CSRF validation failed'], 403);
            }
            die('CSRF token mismatch.');
        }
    }
}
