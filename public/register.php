<?php
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/db.php';

$pageTitle = 'Register';
$errors    = [];
$name      = '';
$email     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name     = trim($_POST['name'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($name === '') {
        $errors['name'] = 'Name is required.';
    } elseif (mb_strlen($name) > 100) {
        $errors['name'] = 'Name must be 100 characters or fewer.';
    }

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (mb_strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    } elseif (strlen($password) > 72) {
        $errors['password'] = 'Password must be 72 characters or fewer.';
    }

    if ($password !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // Email must not already be registered
    if (!isset($errors['email'])) {
        $stmt = db()->prepare('SELECT id FROM users_table WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'This email is already registered.';
        }
    }

    if (!$errors) {
        try {
            $stmt = db()->prepare(
                'INSERT INTO users_table (name, email, password_hash, role) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                'user',
            ]);

            flash_set('success', 'Account created. Please log in.');
            redirect('login.php');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors['email'] = 'This email is already registered.';
            } else {
                error_log('Registration failed: ' . $e->getMessage());
                $errors['general'] = 'Something went wrong. Please try again.';
            }
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-3">Create an account</h1>

<?php if (isset($errors['general'])): ?>
    <div class="alert alert-danger"><?= e($errors['general']) ?></div>
<?php endif; ?>

<form method="post" class="card card-body" style="max-width: 480px;">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" id="name" name="name" maxlength="100" required
               class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
               value="<?= e($name) ?>" autocomplete="name">
        <?php if (isset($errors['name'])): ?>
            <div class="invalid-feedback"><?= e($errors['name']) ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" id="email" name="email" maxlength="255" required
               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
               value="<?= e($email) ?>" autocomplete="email">
        <?php if (isset($errors['email'])): ?>
            <div class="invalid-feedback"><?= e($errors['email']) ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" id="password" name="password" minlength="8" maxlength="72" required
               class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
               autocomplete="new-password">
        <div class="form-text">At least 8 characters.</div>
        <?php if (isset($errors['password'])): ?>
            <div class="invalid-feedback"><?= e($errors['password']) ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm password</label>
        <input type="password" id="confirm_password" name="confirm_password" required
               class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
               autocomplete="new-password">
        <?php if (isset($errors['confirm_password'])): ?>
            <div class="invalid-feedback"><?= e($errors['confirm_password']) ?></div>
        <?php endif; ?>
    </div>

    <div>
        <button type="submit" class="btn btn-primary">Register</button>
        <a href="login.php" class="btn btn-link">Already have an account?</a>
    </div>
</form>

