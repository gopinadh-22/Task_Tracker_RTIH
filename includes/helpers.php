<?php
// Small helpers used by every page.
// The only statuses the app accepts (must match the database ENUM).
const TASK_STATUSES = ['Pending', 'In Progress', 'Completed'];


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Escape text before printing it in HTML (XSS protection).
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// TEMPORARY: replaced by the logged-in user's id in Level 3.
function current_user_id(): int
{
    return 1;
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