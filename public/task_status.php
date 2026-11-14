<?php
require __DIR__ . '/../includes/helpers.php';
require_login();
require __DIR__ . '/../includes/db.php';

// Only accept form submissions (POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    abort(405, 'Method not allowed.');
}
csrf_check();

$id     = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$status = $_POST['status'] ?? '';

if ($id === false || $id < 1 || !in_array($status, TASK_STATUSES, true)) {
    flash_set('danger', 'Invalid request.');
    redirect('index.php');
}

// 1. Make sure the task exists and belongs to the current user
$stmt = db()->prepare('SELECT id FROM task_table WHERE id = ? AND user_id = ?');
$stmt->execute([$id, current_user_id()]);

if (!$stmt->fetch()) {
    flash_set('danger', 'Task not found.');
    redirect('index.php');
}

// 2. Update the status (the ownership check is repeated in the query)
$stmt = db()->prepare('UPDATE task_table SET status = ? WHERE id = ? AND user_id = ?');
$stmt->execute([$status, $id, current_user_id()]);

flash_set('success', 'Status changed to ' . $status . '.');
redirect('index.php');