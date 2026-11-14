<?php
require __DIR__ . '/../includes/helpers.php';
require_login();
require __DIR__ . '/../includes/db.php';

$pageTitle = 'My Tasks';

// Search text from the URL (?q=...), trimmed and limited in length
$q = mb_substr(trim($_GET['q'] ?? ''), 0, 100);

// Status filter from the URL (?status=...), accepted only if it's a known status
$statusFilter = $_GET['status'] ?? '';
if (!in_array($statusFilter, TASK_STATUSES, true)) {
    $statusFilter = '';
}

// Build the query from fixed pieces; user input only goes into $params
$where  = ['user_id = ?'];
$params = [current_user_id()];

if ($q !== '') {
    // Escape LIKE wildcards so a typed % or _ is searched as normal text
    $where[]  = 'title LIKE ?';
    $params[] = '%' . addcslashes($q, '%_\\') . '%';
}

if ($statusFilter !== '') {
    $where[]  = 'status = ?';
    $params[] = $statusFilter;
}

$sql = 'SELECT id, title, description, status, created_at
        FROM task_table
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY created_at DESC, id DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();


require __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-3">My Tasks</h1>
<form method="get" action="index.php" class="row g-2 mb-3">
    <div class="col-sm-6 col-md-5">
        <input type="text" name="q" class="form-control"
               placeholder="Search by title" value="<?= e($q) ?>" maxlength="100">
    </div>
    <div class="col-sm-4 col-md-3">
        <select name="status" class="form-select">
            <option value="">All statuses</option>
            <?php foreach (TASK_STATUSES as $option): ?>
                <option value="<?= e($option) ?>" <?= $statusFilter === $option ? 'selected' : '' ?>>
                    <?= e($option) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Apply</button>
        <?php if ($q !== '' || $statusFilter !== ''): ?>
            <a href="index.php" class="btn btn-secondary">Clear</a>
        <?php endif; ?>
    </div>
</form>
<a href="task_create.php" class="btn btn-primary mb-3">Add Task</a>

<?php if (count($tasks) === 0): ?>
    <div class="alert alert-info">
    <?= ($q !== '' || $statusFilter !== '') ? 'No tasks match your search or filter.' : 'No tasks yet.' ?>
    </div>
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= (int) $task['id'] ?></td>
                    <td><?= htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($task['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <span class="badge <?= status_badge_class($task['status']) ?>">
                            <?= e($task['status']) ?>
                        </span>
                    <form method="post" action="task_status.php" class="mt-1">
                        <input type="hidden" name="id" value="<?= (int) $task['id'] ?>">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <?php foreach (TASK_STATUSES as $option): ?>
                                <option value="<?= e($option) ?>" <?= $task['status'] === $option ? 'selected' : '' ?>>
                                    <?= e($option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <noscript><button type="submit" class="btn btn-sm btn-primary mt-1">Save</button></noscript>
                    </form>
                    </td>
                    <td><?= htmlspecialchars($task['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                    <a href="task_edit.php?id=<?= (int) $task['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                    <form method="post" action="task_delete.php" class="d-inline"
                        onsubmit="return confirm('Delete this task? This cannot be undone.');">
                        <input type="hidden" name="id" value="<?= (int) $task['id'] ?>">
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

