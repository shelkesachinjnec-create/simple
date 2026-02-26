<?php
// ============================================================
// Simple Scooter Showroom - Configuration
// ============================================================

define('APP_NAME', 'Simple Scooters');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/simple-scooter/public');
define('APP_ROOT', dirname(__DIR__));
define('APP_ENV', 'development'); // production | development

// Database
define('DB_HOST', 'localhost');
define('DB_PORT', '3307');
define('DB_NAME', 'simple_scooter');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Session
define('SESSION_NAME', 'simple_session');
define('SESSION_LIFETIME', 7200); // 2 hours

// Security
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes in seconds
define('CSRF_TOKEN_NAME', '_csrf_token');

// File Uploads
define('UPLOAD_DIR', APP_ROOT . '/public/uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ALLOWED_DOC_TYPES', ['application/pdf', 'image/jpeg', 'image/png']);

// Pagination
define('DEFAULT_PAGE_SIZE', 20);

// Currency
define('CURRENCY_SYMBOL', '₹');
define('CURRENCY_CODE', 'INR');

// Time
define('TIMEZONE', 'Asia/Kolkata');
date_default_timezone_set(TIMEZONE);

// Paths
define('VIEWS_PATH', APP_ROOT . '/app/Views/');
define('LOGS_PATH', APP_ROOT . '/storage/logs/');
