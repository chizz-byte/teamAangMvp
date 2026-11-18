<?php
require_once __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Listing.php';
require_once __DIR__ . '/models/Review.php';

$me = current_user();
$stats = User::ratingStats((int)$me['user_id']);
$myListings = Listing::bySeller((int)$me['user_id']);
$available = count(array_filter($myListings, fn($l) => $l['status'] === 'available'));
$reviews = Review::forUser((int)$me['user_id'], 50);

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    csrf_verify();
    $cur = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $conf = $_POST['confirm_password'] ?? '';
    $errs = [];
    if (!password_verify($cur, $me['password_hash'])) $errs[] = 'Current password is incorrect.';
    if (strlen($new) < 6) $errs[] = 'New password must be at least 6 characters.';
    if ($new !== $conf) $errs[] = 'New passwords do not match.';
    if ($errs) {
        flash_errors($errs);
    } else {
        User::updatePassword((int)$me['user_id'], $new);
        flash('success', 'Password updated.');
    }
    redirect(url('profile.php'));
}

$page_title = 'My Profile';
require __DIR__ . '/includes/header.php';
?>
<div class="row">
  <div class="col-lg-5 mb-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4 class="mb-1"><?= e($me['full_name']) ?></h4>
        <p class="text-muted mb-3"><?= e($me['email']) ?> · Student #<?= e($me['student_number']) ?></p>
        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex justify-content-between">
            <span>Listings</span><strong><?= count($myListings) ?> (<?= $available ?> available)</strong>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            <span>Rating as seller</span>
            <strong><span class="text-warning"><?= star_rating($stats['avg']) ?></span>
              <?= $stats['avg'] > 0 ? number_format($stats['avg'], 1) : '—' ?> (<?= $stats['count'] ?>)</strong>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            <span>Member since</span><strong><?= date('M Y', strtotime($me['created_at'])) ?></strong>
          </li>
        </ul>
      </div>
    </div>

    <div class="card shadow-sm mt-3">
      <div class="card-body">
        <h6>Change Password</h6>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="change_password" value="1">
          <div class="mb-2">
            <input type="password" name="current_password" class="form-control form-control-sm" placeholder="Current password" required>
          </div>
          <div class="mb-2">
            <input type="password" name="new_password" class="form-control form-control-sm" placeholder="New password" required minlength="6">
          </div>
          <div class="mb-2">
            <input type="password" name="confirm_password" class="form-control form-control-sm" placeholder="Confirm new password" required minlength="6">
          </div>
          <button class="btn btn-sm btn-primary">Update Password</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <h5>Reviews Received</h5>
    <?php if (!$reviews): ?>
      <p class="text-muted">No reviews yet. Complete a transaction to start earning ratings.</p>
    <?php else: foreach ($reviews as $r): ?>
      <div class="card mb-2">
        <div class="card-body py-2">
          <span class="text-warning"><?= star_rating((float)$r['rating']) ?></span>
          <strong class="ms-1"><?= e($r['reviewer_name']) ?></strong>
          <span class="text-muted small ms-2"><?= time_ago($r['created_at']) ?></span>
          <?php if ($r['feedback']): ?><div class="small mt-1"><?= e($r['feedback']) ?></div><?php endif; ?>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
