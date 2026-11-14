<?php
// ---- Error handling: log everything, show nothing technical ----
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
ini_set('error_log', $logDir . '/php-error.log');

// Any error nobody caught ends up here: log the details, show a generic page.
set_exception_handler(function (Throwable $e): void {
    error_log('Uncaught ' . get_class($e) . ': ' . $e->getMessage()
        . ' in ' . $e->getFile() . ':' . $e->getLine());

    if (!headers_sent()) {
        http_response_code(500);
    }
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>Something went wrong</title>'
       . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">'
       . '</head><body class="bg-light"><div class="container py-5">'
       . '<div class="alert alert-danger"><h1 class="h4">Something went wrong</h1>'
       . '<p class="mb-2">We could not complete your request. Please try again later.</p>'
       . '<a href="index.php" class="btn btn-primary btn-sm">Back to home</a></div>'
       . '</div></body></html>';
    exit;
});
// Small helpers used by every page.
// The only statuses the app accepts (must match the database ENUM).
const TASK_STATUSES = ['Pending', 'In Progress', 'Completed'];


if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Escape text before printing it in HTML (XSS protection).
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// The id of the logged-in user (0 means nobody is logged in, which matches no rows).
function current_user_id(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

// Call at the top of every protected page.
function require_login(): void
{
    if (!is_logged_in()) {
        flash_set('danger', 'Please log in to continue.');
        redirect('login.php');
    }
}

// Store a one-time message to show on the next page.
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Read the message once, then delete it.
function flash_get(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}


// Bootstrap color class for each status badge.
function status_badge_class(string $status): string
{
    return match ($status) {
        'Completed'   => 'text-bg-success',
        'In Progress' => 'text-bg-warning',
        default       => 'text-bg-secondary',
    };
}

// True when a user has logged in.
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}


// One random token per session, created the first time it's needed.
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// The hidden field to put inside every POST form.
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

// Call at the start of every POST handler. Stops the request if the token is wrong.
function csrf_check(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    $real = $_SESSION['csrf_token'] ?? '';

    if (!is_string($sent) || $real === '' || !hash_equals($real, $sent)) {
        abort(403, 'Invalid or expired form. Please go back, reload the page, and try again.');
    }
}

// True only if the logged-in user is an admin. The role is read from the database,
// so changing a user's role takes effect immediately, even in an open session.
function is_admin(): bool
{
    static $cache = null;

    if (!is_logged_in()) {
        return false;
    }
    if ($cache !== null) {
        return $cache;
    }

    require_once __DIR__ . '/db.php';
    $stmt = db()->prepare('SELECT role FROM users_table WHERE id = ?');
    $stmt->execute([current_user_id()]);
    $cache = ($stmt->fetchColumn() === 'admin');

    return $cache;
}

// Call at the top of admin-only pages.
function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        abort(403, 'Access denied.');
    }
}

// Show a friendly error page with the normal layout, then stop.
function abort(int $code, string $message): void
{
    http_response_code($code);
    $pageTitle = 'Error ' . $code;

    require __DIR__ . '/header.php';
    echo '<div class="alert alert-danger">' . e($message) . '</div>';
    echo '<a href="index.php" class="btn btn-secondary">Back to tasks</a>';
    require __DIR__ . '/footer.php';
    exit;
}