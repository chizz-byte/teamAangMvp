<?php
// Expects: $page_title (string)
$page_title = $page_title ?? APP_NAME;
$user = current_user();
$flash_errors = flash('errors');
$flash_success = flash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?> · <?= e(APP_NAME) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark slu-navbar sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= url('index.php') ?>">
      <i class="bi bi-mortarboard-fill"></i> SLU Marketplace
    </a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?= url('index.php') ?>">Marketplace</a></li>
        <?php if (is_logged_in()): ?>
          <?php if (is_admin()): ?>
            <li class="nav-item"><a class="nav-link" href="<?= url('admin/index.php') ?>">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= url('admin/users.php') ?>">Users</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= url('admin/listings.php') ?>">Listings</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= url('admin/reports.php') ?>">Reports</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="<?= url('listings/create.php') ?>">Sell an Item</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= url('listings/mine.php') ?>">My Listings</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= url('messages/index.php') ?>">Messages</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= url('transactions/index.php') ?>">Transactions</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= url('profile.php') ?>">My Profile</a></li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav">
        <?php if (is_logged_in()): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle"></i> <?= e($user['full_name']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><span class="dropdown-item-text text-muted small"><?= e($user['email']) ?></span></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= url('logout.php') ?>">Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= url('login.php') ?>">Login</a></li>
          <li class="nav-item"><a class="btn btn-light btn-sm ms-2 align-self-center" href="<?= url('register.php') ?>">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container py-4" style="min-height:70vh">
<?php if ($flash_errors): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <?php foreach ((array)$flash_errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if ($flash_success): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <?php foreach ((array)$flash_success as $msg): ?><div><?= e($msg) ?></div><?php endforeach; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
