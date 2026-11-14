<?php
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/db.php';
require_admin();

$pageTitle = 'All Tasks (Admin)';

$stmt = db()->query(
    'SELECT t.id, t.title, t.status, t.created_at, u.name AS owner_name, u.email AS owner_email
     FROM task_table t
     JOIN users_table u ON u.id = t.user_id
     ORDER BY t.created_at DESC, t.id DESC'
);
$tasks = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-3">All Tasks (Admin)</h1>

<?php if (count($tasks) === 0): ?>
    <div class="alert alert-info">There are no tasks yet.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped align-middle bg-white">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Owner</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= (int) $task['id'] ?></td>
                    <td>
                        <?= e($task['owner_name']) ?><br>
                        <small class="text-muted"><?= e($task['owner_email']) ?></small>
                    </td>
                    <td><?= e($task['title']) ?></td>
                    <td>
                        <span class="badge <?= status_badge_class($task['status']) ?>">
                            <?= e($task['status']) ?>
                        </span>
                    </td>
                    <td><?= e($task['created_at']) ?></td>
                    <td>
                        <form method="post" action="task_delete.php" class="d-inline"
                              onsubmit="return confirm('Delete this task? This cannot be undone.');">
                            <input type="hidden" name="id" value="<?= (int) $task['id'] ?>">
                            <input type="hidden" name="from" value="admin">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

