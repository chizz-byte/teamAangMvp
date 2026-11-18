<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/User.php';

if (is_logged_in()) redirect(url('index.php'));

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $studentNumber = trim($_POST['student_number'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($studentNumber === '') $errors[] = 'Student number is required.';
    if ($fullName === '') $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, '@slu.edu.ph')) {
        $errors[] = 'A valid SLU institutional email (@slu.edu.ph) is required.';
    }
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (User::studentNumberExists($studentNumber)) $errors[] = 'That student number is already registered.';
    if ($email !== '' && User::emailExists($email)) $errors[] = 'That email is already registered.';

    if (!$errors) {
        $id = User::create($studentNumber, $fullName, $email, $password);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        clear_old();
        flash('success', 'Account created! Welcome to the SLU Marketplace.');
        redirect(url('index.php'));
    }
    flash_errors($errors);
    set_old(['student_number' => $studentNumber, 'full_name' => $fullName, 'email' => $email]);
    redirect(url('register.php'));
}

$page_title = 'Register';
require __DIR__ . '/includes/header.php';
$errs = flash('errors');
?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h4 class="mb-1 text-center">Create account</h4>
        <p class="text-muted text-center mb-4">SLU students only · @slu.edu.ph email required</p>
        <?php if ($errs): ?>
          <div class="alert alert-danger"><?php foreach ((array)$errs as $err) echo e($err) . '<br>'; ?></div>
        <?php endif; ?>
        <form method="post">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label">Student Number</label>
            <input name="student_number" class="form-control" required value="<?= old('student_number') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input name="full_name" class="form-control" required value="<?= old('full_name') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Institutional Email</label>
            <input type="email" name="email" class="form-control" required
                   placeholder="you@slu.edu.ph" value="<?= old('email') ?>">
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required minlength="6">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="confirm_password" class="form-control" required minlength="6">
            </div>
          </div>
          <button class="btn btn-primary w-100">Register</button>
        </form>
        <p class="text-center mt-3 mb-0">Already registered? <a href="<?= url('login.php') ?>">Login</a></p>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
