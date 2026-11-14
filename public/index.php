<?php
require __DIR__ . '/../includes/db.php';

$pageTitle = 'My Tasks';
$userId = 1; // TEMPORARY: replaced by the logged-in user in Level 3

$stmt = db()->prepare(
    'SELECT id, title, description, status, created_at
     FROM task_table
     WHERE user_id = ?
     ORDER BY created_at DESC, id DESC'
);
$stmt->execute([$userId]);
$tasks = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-3">My Tasks</h1>

<?php if (count($tasks) === 0): ?>
    <div class="alert alert-info">No tasks yet.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped align-middle bg-white">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= (int) $task['id'] ?></td>
                    <td><?= htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($task['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($task['status'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($task['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>