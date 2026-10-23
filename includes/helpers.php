<?php
/**
 * Shared helper functions used by the public site and the admin panel.
 */

declare(strict_types=1);

// ---------------------------------------------------------------------------
// Output & navigation
// ---------------------------------------------------------------------------

/** Escape a value for safe output in HTML (text and attributes). */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

/** Relative prefix from the current script to the project root ('' or '../'). */
function root_prefix(): string
{
    return basename(dirname($_SERVER['SCRIPT_FILENAME'] ?? '')) === 'admin' ? '../' : '';
}

/** Public URL of an uploaded package image, usable from public and admin pages. */
function package_image_url(?string $file): string
{
    if ($file === null || $file === '') {
        return root_prefix() . 'assets/img/placeholder.jpg';
    }
    return root_prefix() . 'uploads/packages/' . rawurlencode($file);
}

/** Public URL of an admin profile photo. */
function profile_image_url(?string $file): string
{
    $file = ($file === null || $file === '') ? 'avatar.jpg' : $file;
    return root_prefix() . 'uploads/profiles/' . rawurlencode($file);
}

function format_price(int|float|string|null $amount): string
{
    return 'USD ' . number_format((float) $amount);
}

function format_date(?string $date, string $format = 'M j, Y'): string
{
    if (!$date) {
        return '—';
    }
    $ts = strtotime($date);
    return $ts ? date($format, $ts) : $date;
}

/** Render a minimal stand-alone error page (used before any layout is available). */
function render_error_page(string $title, string $message): string
{
    $t = e($title);
    $m = e($message);
    $home = e(root_prefix() . 'index.php');
    return <<<HTML
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$t}</title><style>body{font-family:system-ui,sans-serif;background:#f6f4ef;color:#1f2a24;display:grid;place-items:center;min-height:100vh;margin:0;padding:16px}
main{max-width:480px;text-align:center}h1{font-size:1.6rem;margin:0 0 .5rem}p{color:#5b665f;line-height:1.6}a{color:#1d6b4f}</style></head>
<body><main><h1>{$t}</h1><p>{$m}</p><p><a href="{$home}">Back to the homepage</a></p></main></body></html>
HTML;
}

// ---------------------------------------------------------------------------
// Flash messages & old input
// ---------------------------------------------------------------------------

/** Queue a one-time message for the next page; $link is an optional [url, label]. */
function flash(string $type, string $message, ?array $link = null): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message, 'link' => $link];
}

/** @return array<int, array{type: string, message: string, link: ?array}> */
function take_flashes(): array
{
    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $messages;
}

/** Remember submitted form values so they can be re-filled after a redirect. */
function remember_input(array $fields): void
{
    $_SESSION['_old'] = array_intersect_key($_POST, array_flip($fields));
}

function old(string $key, string $default = ''): string
{
    return e($_SESSION['_old'][$key] ?? $default);
}

function forget_input(): void
{
    unset($_SESSION['_old']);
}

// ---------------------------------------------------------------------------
// CSRF
// ---------------------------------------------------------------------------

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

/** Query-string fragment for links that change state (e.g. admin actions). */
function csrf_query(): string
{
    return '_token=' . urlencode(csrf_token());
}

function csrf_valid(?string $token): bool
{
    return is_string($token) && !empty($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
}

/** Abort unless the current GET request carries a valid CSRF token. */
function require_csrf_get(): void
{
    if (!csrf_valid($_GET['_token'] ?? null)) {
        http_response_code(403);
        exit(render_error_page('Link expired', 'This action link has expired. Please go back, refresh the page and try again.'));
    }
}

// ---------------------------------------------------------------------------
// Authentication
// ---------------------------------------------------------------------------

/**
 * Verify a password against a stored hash.
 *
 * Supports legacy unsalted MD5 hashes so existing accounts keep working;
 * $rehash is set when the caller should store an upgraded bcrypt hash.
 */
function verify_password(string $plain, ?string $stored, ?string &$rehash = null): bool
{
    $rehash = null;
    if ($stored === null || $stored === '') {
        return false;
    }

    if (preg_match('/^[a-f0-9]{32}$/i', $stored)) {
        if (!hash_equals(strtolower($stored), md5($plain))) {
            return false;
        }
        $rehash = password_hash($plain, PASSWORD_DEFAULT);
        return true;
    }

    if (!password_verify($plain, $stored)) {
        return false;
    }
    if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
        $rehash = password_hash($plain, PASSWORD_DEFAULT);
    }
    return true;
}

/** Returns an error message, or null when the password is acceptable. */
function password_problem(string $password): ?string
{
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters long.';
    }
    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        return 'Password must contain both letters and numbers.';
    }
    return null;
}

function user_logged_in(): bool
{
    return !empty($_SESSION['login']);
}

function current_user_email(): ?string
{
    return $_SESSION['login'] ?? null;
}

function login_user(object $user): void
{
    session_regenerate_id(true);
    $_SESSION['login']  = $user->EmailId;
    $_SESSION['login2'] = $user->FullName;
}

function require_user(): void
{
    if (!user_logged_in()) {
        flash('info', 'Please sign in to continue.');
        redirect('login.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? 'index.php'));
    }
}

/** Only allow redirects back to pages on this site. */
function safe_next(?string $next, string $fallback = 'index.php'): string
{
    if ($next === null || $next === '' || str_contains($next, '\\') || preg_match('/[\x00-\x20]/', $next) || str_starts_with($next, '//')) {
        return $fallback;
    }
    $parts = parse_url($next);
    if ($parts === false || isset($parts['scheme']) || isset($parts['host'])) {
        return $fallback;
    }
    return $next;
}

// ---------------------------------------------------------------------------
// Brute-force protection
// ---------------------------------------------------------------------------

/** Throttle key for an action and account, e.g. throttle_key('login', $email). */
function throttle_key(string $scope, string $identifier): string
{
    return substr($scope . '|' . strtolower(trim($identifier)), 0, 190);
}

function client_ip(): string
{
    return substr($_SERVER['REMOTE_ADDR'] ?? 'unknown', 0, 45);
}

/**
 * True when there have been too many recent attempts for this key — either
 * from the current IP address ($max) or from anywhere ($max * 4, which slows
 * down attacks spread across many addresses).
 */
function too_many_attempts(string $key, int $max = 5, int $minutes = 15): bool
{
    global $dbh;
    $since = date('Y-m-d H:i:s', time() - $minutes * 60);

    $fromIp = $dbh->prepare('SELECT COUNT(*) FROM tbllogin_attempts WHERE Identifier = ? AND IpAddress = ? AND AttemptedAt > ?');
    $fromIp->execute([$key, client_ip(), $since]);
    if ((int) $fromIp->fetchColumn() >= $max) {
        return true;
    }

    $overall = $dbh->prepare('SELECT COUNT(*) FROM tbllogin_attempts WHERE Identifier = ? AND AttemptedAt > ?');
    $overall->execute([$key, $since]);
    return (int) $overall->fetchColumn() >= $max * 4;
}

function record_attempt(string $key): void
{
    global $dbh;
    $dbh->prepare('INSERT INTO tbllogin_attempts (Identifier, IpAddress, AttemptedAt) VALUES (?, ?, ?)')
        ->execute([$key, client_ip(), date('Y-m-d H:i:s')]);

    // Housekeeping: occasionally drop records older than a day.
    if (random_int(1, 50) === 1) {
        $dbh->prepare('DELETE FROM tbllogin_attempts WHERE AttemptedAt < ?')->execute([date('Y-m-d H:i:s', time() - 86400)]);
    }
}

function clear_attempts(string $key): void
{
    global $dbh;
    $dbh->prepare('DELETE FROM tbllogin_attempts WHERE Identifier = ? AND IpAddress = ?')->execute([$key, client_ip()]);
}

// ---------------------------------------------------------------------------
// URLs & email
// ---------------------------------------------------------------------------

/** Absolute URL to a page in the project root, e.g. app_url('reset_password.php'). */
function app_url(string $path = ''): string
{
    $base = env('APP_URL');
    if (!$base) {
        $dir  = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        $dir  = preg_replace('#/admin$#', '', $dir);
        $base = (is_https() ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $dir;
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/**
 * Send a plain-text email. Returns false when mail delivery is disabled
 * (MAIL_ENABLED=false, the default for local development); the message is
 * then written to the PHP error log instead so nothing is lost.
 */
function send_mail(string $to, string $subject, string $body): bool
{
    if (!filter_var(env('MAIL_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN)) {
        error_log("[mail disabled] To: $to | Subject: $subject\n$body");
        return false;
    }
    $headers = [
        'From' => APP_NAME . ' <' . env('MAIL_FROM', COMPANY_EMAIL) . '>',
        'Content-Type' => 'text/plain; charset=UTF-8',
    ];
    return mail($to, $subject, $body, $headers);
}

/**
 * Email a customer about their booking. $event is 'received', 'confirmed' or 'cancelled'.
 * $b needs: email, name, id, package, from, to, travellers, total.
 */
function send_booking_email(string $event, array $b): bool
{
    $intro = [
        'received'  => "Thanks for your booking request! Our team will check availability and confirm within 24 hours.",
        'confirmed' => "Great news — your booking is confirmed. We can't wait to welcome you in Tanzania!",
        'cancelled' => "Your booking has been cancelled. If this is unexpected, just reply to this email and we'll help.",
    ][$event] ?? '';

    $subject = sprintf('Booking #BK-%d %s — %s', $b['id'], $event, $b['package']);
    $body = "Hi {$b['name']},\n\n{$intro}\n\n"
        . "Booking:     #BK-{$b['id']}\n"
        . "Package:     {$b['package']}\n"
        . 'Dates:       ' . format_date($b['from']) . ' – ' . format_date($b['to']) . "\n"
        . "Travellers:  {$b['travellers']}\n"
        . 'Total:       ' . format_price($b['total']) . "\n\n"
        . 'Manage your bookings: ' . app_url('tour_history.php') . "\n\n— Five Star Tours";

    return send_mail($b['email'], $subject, $body);
}

// ---------------------------------------------------------------------------
// Uploads
// ---------------------------------------------------------------------------

/**
 * Validate and store an uploaded image under a random file name.
 *
 * Only real JPEG/PNG/WebP/GIF images up to 5 MB are accepted, so a script
 * disguised as an image can never be uploaded and executed.
 *
 * @return string the stored file name
 * @throws RuntimeException with a user-friendly message on failure
 */
function store_uploaded_image(array $file, string $targetDir): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException($file['error'] === UPLOAD_ERR_NO_FILE
            ? 'Please choose an image to upload.'
            : 'The image could not be uploaded. Please try again.');
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Images must be 5 MB or smaller.');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    // getimagesize() parses the actual image header; finfo double-checks when available.
    $info = @getimagesize($file['tmp_name']);
    $mime = $info['mime'] ?? '';
    if (class_exists('finfo')) {
        $detected = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if ($detected !== $mime) {
            $mime = '';
        }
    }
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, PNG, WebP or GIF images are allowed.');
    }

    if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true)) {
        throw new RuntimeException('Upload folder is not writable.');
    }

    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], rtrim($targetDir, '/') . '/' . $name)) {
        throw new RuntimeException('The image could not be saved. Please try again.');
    }
    return $name;
}

function package_upload_dir(): string
{
    return APP_ROOT . '/uploads/packages';
}

function profile_upload_dir(): string
{
    return APP_ROOT . '/uploads/profiles';
}
