<?php
// Top part of every page. Set $pageTitle before including this file.
$pageTitle = $pageTitle ?? 'Task Tracker';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
        <a class="navbar-brand" href="index.php">Task Tracker</a>

        <div class="d-flex align-items-center gap-2">
        <?php if (is_logged_in()): ?>
            <?php if (is_admin()): ?>
                <a href="admin_tasks.php" class="btn btn-sm btn-warning">All Tasks</a>
            <?php endif; ?>
            <?php $navName = $_SESSION['user_name'] ?? ''; ?>
            <span class="rounded-circle bg-white text-primary fw-bold d-inline-flex justify-content-center align-items-center"
                  style="width: 34px; height: 34px;" title="<?= e($navName) ?>">
                <?= e(mb_strtoupper(mb_substr($navName, 0, 1))) ?>
            </span>
            <span class="text-white"><?= e($navName) ?></span>
            <span class="badge text-bg-secondary"><?= is_admin() ? 'admin' : 'user' ?></span>

            <form method="post" action="logout.php" class="m-0">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
            </form>
        <?php else: ?>
            <a href="login.php" class="btn btn-sm btn-outline-light">Login</a>
            <a href="register.php" class="btn btn-sm btn-light">Register</a>
        <?php endif; ?>
        </div>
    </div>
</nav>
<main class="container">
    
<?php $flash = flash_get(); ?>
<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>