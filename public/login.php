<?php
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/db.php';

// Already logged in? Go to the dashboard.
if (is_logged_in()) {
    redirect('index.php');
}

$pageTitle = 'Login';
$error     = '';
$email     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = db()->prepare(
            'SELECT id, name, password_hash, role FROM users_table WHERE email = ?'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // A fixed hash, used only so a wrong email takes as long to check as a wrong password
        $dummyHash = '$2y$12$tAe2SgmzMumoonTpp4hFRO8UjcrrsU2f05eQaOY30goEMO3eA/ZZi';
        $hash      = $user['password_hash'] ?? $dummyHash;

        if (password_verify($password, $hash) && $user) {
            session_regenerate_id(true);
            unset($_SESSION['csrf_token']);
            $_SESSION['user_id']   = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            flash_set('success', 'Welcome back, ' . $user['name'] . '.');
            redirect('index.php');
        }

        $error = 'Invalid email or password.';
    }
}

require __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-3">Login</h1>

<?php if ($error !== ''): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" class="card card-body" style="max-width: 480px;">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" id="email" name="email" maxlength="255" required
               class="form-control" value="<?= e($email) ?>" autocomplete="email">
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" id="password" name="password" required
               class="form-control" autocomplete="current-password">
    </div>

    <div>
        <button type="submit" class="btn btn-primary">Login</button>
        <a href="register.php" class="btn btn-link">Create an account</a>
    </div>
</form>

