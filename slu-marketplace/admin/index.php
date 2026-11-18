<?php
require_once __DIR__ . '/../middleware/admin.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/Message.php';

$lc = Listing::counts();
$users = User::count();
$pendingReports = Report::pendingCount();
$txs = db()->query('SELECT status, COUNT(*) c FROM transactions GROUP BY status')->fetchAll();
$txCounts = array_column($txs, 'c', 'status');
$reviews = (int)db()->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
$messages = (int)db()->query('SELECT COUNT(*) FROM messages')->fetchColumn();
$latestUsers = db()->query('SELECT * FROM users ORDER BY created_at DESC LIMIT 5')->fetchAll();
$latestListings = db()->query(
    'SELECT l.*, u.full_name AS seller_name FROM listings l JOIN users u ON u.user_id=l.seller_id
     ORDER BY l.created_at DESC LIMIT 5'
)->fetchAll();

$page_title = 'Admin Dashboard';
require __DIR__ . '/../includes/header.php';
?>
<h3 class="mb-4">Admin Dashboard</h3>
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3"><div class="card text-center p-3"><div class="fs-3 fw-bold"><?= $users ?></div><div class="text-muted small">Users</div></div></div>
  <div class="col-6 col-md-3"><div class="card text-center p-3"><div class="fs-3 fw-bold"><?= $lc['total'] ?></div><div class="text-muted small">Listings</div></div></div>
  <div class="col-6 col-md-3"><div class="card text-center p-3"><div class="fs-3 fw-bold text-success"><?= $lc['available'] ?></div><div class="text-muted small">Available</div></div></div>
  <div class="col-6 col-md-3"><div class="card text-center p-3"><div class="fs-3 fw-bold text-secondary"><?= $lc['sold'] ?></div><div class="text-muted small">Sold</div></div></div>
  <div class="col-6 col-md-3"><div class="card text-center p-3"><div class="fs-3 fw-bold text-warning"><?= $lc['reserved'] ?></div><div class="text-muted small">Reserved</div></div></div>
  <div class="col-6 col-md-3"><div class="card text-center p-3"><div class="fs-3 fw-bold"><?= $txCounts['pending'] ?? 0 ?></div><div class="text-muted small">Pending Transactions</div></div></div>
  <div class="col-6 col-md-3"><div class="card text-center p-3"><div class="fs-3 fw-bold"><?= $txCounts['completed'] ?? 0 ?></div><div class="text-muted small">Completed Transactions</div></div></div>
  <div class="col-6 col-md-3"><div class="card text-center p-3"><div class="fs-3 fw-bold text-danger"><?= $pendingReports ?></div><div class="text-muted small">Pending Reports</div></div></div>
  <div class="col-6 col-md-3"><div class="card text-center p-3"><div class="fs-3 fw-bold"><?= $reviews ?></div><div class="text-muted small">Reviews</div></div></div>
  <div class="col-6 col-md-3"><div class="card text-center p-3"><div class="fs-3 fw-bold"><?= $messages ?></div><div class="text-muted small">Messages</div></div></div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header fw-bold">Newest Users</div>
      <ul class="list-group list-group-flush">
        <?php foreach ($latestUsers as $u): ?>
          <li class="list-group-item d-flex justify-content-between">
            <span><?= e($u['full_name']) ?> <small class="text-muted"><?= e($u['student_number']) ?></small></span>
            <span class="badge bg-<?= $u['role']==='admin'?'danger':'primary' ?>"><?= e($u['role']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card">
      <div class="card-header fw-bold">Newest Listings</div>
      <ul class="list-group list-group-flush">
        <?php foreach ($latestListings as $l): ?>
          <li class="list-group-item d-flex justify-content-between">
            <span class="text-truncate"><?= e($l['title']) ?></span>
            <span class="badge bg-secondary ms-2"><?= ucfirst($l['status']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="card-footer"><a href="<?= url('admin/listings.php') ?>">Manage listings →</a></div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
