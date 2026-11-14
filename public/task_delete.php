<?php
require __DIR__ . '/../includes/helpers.php';
require_login();
require __DIR__ . '/../includes/db.php';

// Only accept form submissions (POST), never plain links
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    abort(405, 'Method not allowed.');
}
csrf_check();

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id < 1) {
    flash_set('danger', 'Invalid task.');
    redirect('index.php');
}

if (is_admin() && ($_POST['from'] ?? '') === 'admin') {
    // Admin moderation: may delete any task
    $stmt = db()->prepare('DELETE FROM task_table WHERE id = ?');
    $stmt->execute([$id]);
    $back = 'admin_tasks.php';
} else {
    // Everyone else: only their own tasks
    $stmt = db()->prepare('DELETE FROM task_table WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, current_user_id()]);
    $back = 'index.php';
}

if ($stmt->rowCount() === 1) {
    flash_set('success', 'Task deleted successfully.');
} else {
    flash_set('danger', 'Task not found.');
}


redirect('index.php');