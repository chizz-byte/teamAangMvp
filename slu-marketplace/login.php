<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) redirect(url('index.php'));

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    require_once __DIR__ . '/models/User.php';
    $user = $email !== '' ? User::findByEmail($email) : null;

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $errors[] = 'Invalid email or password.';
    } else {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['user_id'];
        flash('success', 'Welcome back, ' . $user['full_name'] . '!');
        redirect(url('index.php'));
    }
}

$page_title = 'Login';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-5 col-lg-4">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h4 class="mb-1 text-center">Welcome back</h4>
        <p class="text-muted text-center mb-4">Log in to the SLU Marketplace</p>
        <?php if ($errors): ?>
          <div class="alert alert-danger"><?php foreach ($errors as $err) echo e($err) . '<br>'; ?></div>
        <?php endif; ?>
        <form method="post">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label">SLU Email</label>
            <input type="email" name="email" class="form-control" required
                   placeholder="you@slu.edu.ph" value="<?= e($_POST['email'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button class="btn btn-primary w-100">Login</button>
        </form>
        <p class="text-center mt-3 mb-0">No account? <a href="<?= url('register.php') ?>">Register</a></p>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
