<?php
require __DIR__ . '/../includes/helpers.php';

// Logout changes state, so it only accepts POST (the button above)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    csrf_check();
    abort(405, 'Method not allowed.');
}

// 1. Empty the session data
$_SESSION = [];

// 2. Delete the session cookie in the browser
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// 3. Destroy the session on the server
session_destroy();

// 4. Start a fresh session just to carry the goodbye message
session_start();
flash_set('success', 'You have been logged out.');
redirect('login.php');