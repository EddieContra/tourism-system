<?php
/**
 * Application bootstrap.
 *
 * Every page (public and admin) includes this file first. It loads the
 * environment configuration, configures error handling and the session,
 * sends security headers, opens the database connection ($dbh) and
 * enforces CSRF protection on every POST request.
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

require_once __DIR__ . '/env.php';
load_env(APP_ROOT . '/.env');

define('APP_NAME', env('APP_NAME', 'Five Star Tours'));
// Without an explicit APP_ENV, only treat requests to this machine as local,
// so a deploy that forgets its .env never shows demo or development helpers.
$requestHost = trim(strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'localhost')), '[]');
define('APP_ENV', env('APP_ENV') ?: (in_array($requestHost, ['localhost', '127.0.0.1', '::1'], true) || PHP_SAPI === 'cli' ? 'local' : 'production'));
unset($requestHost);
define('APP_DEBUG', filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN));

// Company details shown on the public site.
define('COMPANY_ADDRESS', env('COMPANY_ADDRESS', '414 Meru Street, Sanawari, Arusha'));
define('COMPANY_PHONE', env('COMPANY_PHONE', '+255 745 225 985'));
define('COMPANY_EMAIL', env('COMPANY_EMAIL', 'info@fivestartours.test'));

// Database credentials. Defaults match a stock XAMPP install so the project
// still runs there without a .env file; Docker and production override them.
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'tourism'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

// ---------------------------------------------------------------------------
// Error handling: log everything, only display errors when debugging.
// ---------------------------------------------------------------------------
error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');

date_default_timezone_set(env('APP_TIMEZONE', 'Africa/Dar_es_Salaam'));

require_once __DIR__ . '/helpers.php';

// Unexpected errors: log the details and show a friendly page instead of a blank 500.
set_exception_handler(function (Throwable $e): void {
    error_log('Uncaught ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
    }
    $detail = APP_DEBUG ? ' (' . $e->getMessage() . ')' : '';
    echo render_error_page('Something went wrong', 'An unexpected error occurred and our team has been notified. Please try again in a moment.' . $detail);
});

// ---------------------------------------------------------------------------
// Session: hardened cookie, started once for the whole request.
// ---------------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_name('fst_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

// ---------------------------------------------------------------------------
// Security headers.
// ---------------------------------------------------------------------------
if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data: blob:; style-src 'self' 'unsafe-inline'; script-src 'self'; font-src 'self'; connect-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");
    if (is_https()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// ---------------------------------------------------------------------------
// Database connection.
// ---------------------------------------------------------------------------
try {
    $dbh = new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME),
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
    // Keep MySQL timestamps in the same time zone as PHP.
    $dbh->exec("SET time_zone = '" . date('P') . "'");
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(503);
    $detail = APP_DEBUG ? $e->getMessage() : 'Please try again in a few minutes.';
    exit(render_error_page('Service unavailable', 'We could not connect to the database. ' . $detail));
}

// ---------------------------------------------------------------------------
// CSRF protection for every state-changing request.
// ---------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !csrf_valid($_POST['_token'] ?? null)) {
    http_response_code(403);
    exit(render_error_page('Page expired', 'Your session expired or the form was submitted twice. Please go back, refresh the page and try again.'));
}
