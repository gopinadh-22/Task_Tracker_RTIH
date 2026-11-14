<?php
require __DIR__ . '/../includes/helpers.php';
require_login();
require __DIR__ . '/../includes/db.php';

$pageTitle = 'Edit Task';

// 1. Read and check the id from the URL
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id < 1) {
    http_response_code(404);
    exit('Task not found.');
}

// 2. Load the task, but only if it belongs to the current user
$stmt = db()->prepare(
    'SELECT id, title, description, status FROM task_table WHERE id = ? AND user_id = ?'
);
$stmt->execute([$id, current_user_id()]);
$task = $stmt->fetch();

if (!$task) {
    http_response_code(404);
    exit('Task not found.');
}

$errors      = [];
$title       = $task['title'];
$description = $task['description'] ?? '';
$status = $task['status'];

// 3. Handle the form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? '';

    if ($title === '') {
        $errors['title'] = 'Title is required.';
    } elseif (mb_strlen($title) > 150) {
        $errors['title'] = 'Title must be 150 characters or fewer.';
    }

    if (mb_strlen($description) > 1000) {
        $errors['description'] = 'Description must be 1000 characters or fewer.';
    }

    if (!in_array($status, TASK_STATUSES, true)) {
    $errors['status'] = 'Please choose a valid status.';
    }

    if (!$errors) {
        $stmt = db()->prepare(
            'UPDATE task_table SET title = ?, description = ?, status = ? WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([
            $title,
            $description === '' ? null : $description,
            $status,
            $id,
            current_user_id(),
        ]);

        flash_set('success', 'Task updated successfully.');
        redirect('index.php');
    }
}

require __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-3">Edit Task</h1>

<form method="post" class="card card-body">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" id="title" name="title"
               class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
               value="<?= e($title) ?>" maxlength="150" required>
        <?php if (isset($errors['title'])): ?>
            <div class="invalid-feedback"><?= e($errors['title']) ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea id="description" name="description" rows="4"
                  class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                  maxlength="1000"><?= e($description) ?></textarea>
        <?php if (isset($errors['description'])): ?>
            <div class="invalid-feedback"><?= e($errors['description']) ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
    <label for="status" class="form-label">Status</label>
    <select id="status" name="status"
            class="form-select <?= isset($errors['status']) ? 'is-invalid' : '' ?>">
        <?php foreach (TASK_STATUSES as $option): ?>
            <option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>>
                <?= e($option) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if (isset($errors['status'])): ?>
        <div class="invalid-feedback"><?= e($errors['status']) ?></div>
    <?php endif; ?>
    </div>
    
    <div>
        <button type="submit" class="btn btn-primary">Update Task</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>