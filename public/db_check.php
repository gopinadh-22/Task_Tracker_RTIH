<?php
require __DIR__ . '/../includes/db.php';

$users = db()->query('SELECT COUNT(*) AS total FROM users_table')->fetch();
$tasks = db()->query('SELECT COUNT(*) AS total FROM task_table')->fetch();

echo 'Connected OK. Users: ' . $users['total'] . ', Tasks: ' . $tasks['total'];