<?php
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/db.php';

// Only accept form submissions (POST), never plain links
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id < 1) {
    flash_set('danger', 'Invalid task.');
    redirect('index.php');
}

// Delete only if the task belongs to the current user
$stmt = db()->prepare('DELETE FROM task_table WHERE id = ? AND user_id = ?');
$stmt->execute([$id, current_user_id()]);

if ($stmt->rowCount() === 1) {
    flash_set('success', 'Task deleted successfully.');
} else {
    flash_set('danger', 'Task not found.');
}

redirect('index.php');