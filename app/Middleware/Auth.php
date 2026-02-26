<?php

class Auth {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
            session_name(SESSION_NAME);
            session_start();
        }
    }

    public static function login(array $user): void {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email']= $user['email'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time']= time();
    }

    public static function logout(): void {
        $_SESSION = [];
        session_destroy();
    }

    public static function check(): bool {
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) return false;
        if (time() - ($_SESSION['login_time'] ?? 0) > SESSION_LIFETIME) {
            self::logout();
            return false;
        }
        return true;
    }

    public static function user(): array {
        return [
            'id'    => $_SESSION['user_id'] ?? 0,
            'name'  => $_SESSION['user_name'] ?? '',
            'role'  => $_SESSION['user_role'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
        ];
    }

    public static function id(): int {
        return $_SESSION['user_id'] ?? 0;
    }

    public static function role(): string {
        return $_SESSION['user_role'] ?? '';
    }

    public static function isSuperAdmin(): bool {
        return self::role() === 'super_admin';
    }

    public static function isAdmin(): bool {
        return in_array(self::role(), ['super_admin', 'admin']);
    }

    public static function isOperator(): bool {
        return self::check();
    }

    public static function require(string $minRole = 'operator'): void {
        if (!self::check()) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        $roles = ['operator' => 1, 'admin' => 2, 'super_admin' => 3];
        $currentLevel = $roles[self::role()] ?? 0;
        $requiredLevel = $roles[$minRole] ?? 1;
        if ($currentLevel < $requiredLevel) {
            http_response_code(403);
            die('Access denied.');
        }
    }

    public static function hasRole(string ...$roles): bool {
        return in_array(self::role(), $roles);
    }

    public static function generateCsrfToken(): string {
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    public static function verifyCsrfToken(string $token): bool {
        return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    public static function csrfField(): string {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }
}

// Helper functions
function h(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function formatCurrency(float $amount): string {
    return CURRENCY_SYMBOL . number_format($amount, 2);
}

function formatDate(string $date, string $format = 'd M Y'): string {
    return $date ? date($format, strtotime($date)) : '-';
}

function redirect(string $url): void {
    header('Location: ' . APP_URL . '/' . ltrim($url, '/'));
    exit;
}

function flash(string $key, string $message): void {
    $_SESSION['flash'][$key] = $message;
}

function getFlash(string $key): ?string {
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function sanitize(string $value): string {
    return trim(strip_tags($value));
}

function isAjax(): bool {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function uploadFile(array $file, string $folder = 'general'): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > MAX_UPLOAD_SIZE) return null;
    $allowedTypes = array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_DOC_TYPES);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowedTypes)) return null;
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $dir = UPLOAD_DIR . $folder . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
        return $folder . '/' . $filename;
    }
    return null;
}
