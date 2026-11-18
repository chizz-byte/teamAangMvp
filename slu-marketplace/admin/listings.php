<?php
require_once __DIR__ . '/../middleware/admin.php';
require_once __DIR__ . '/../models/Listing.php';

$statusFilter = $_GET['status'] ?? '';
if ($statusFilter && !in_array($statusFilter, Listing::STATUSES, true)) $statusFilter = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $lid = (int)($_POST['listing_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($lid) {
        if ($action === 'remove') Listing::setStatus($lid, 'removed');
        elseif ($action === 'restore') Listing::setStatus($lid, 'available');
    }
    flash('success', 'Listing updated.');
    redirect(url('admin/listings.php' . ($statusFilter ? '?status=' . urlencode($statusFilter) : '')));
}

$where = $statusFilter ? 'WHERE l.status = ' . db()->quote($statusFilter) : '';
$listings = db()->query(
    "SELECT l.*, u.full_name AS seller_name, u.email AS seller_email
     FROM listings l JOIN users u ON u.user_id = l.seller_id $where
     ORDER BY l.created_at DESC LIMIT 300"
)->fetchAll();

$page_title = 'Manage Listings';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Listings (<?= count($listings) ?>)</h3>
  <form method="get" class="d-flex gap-2">
    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
      <option value="">All statuses</option>
      <?php foreach (Listing::STATUSES as $s): ?>
        <option value="<?= $s ?>" <?= $statusFilter===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</div>
<div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead><tr><th></th><th>Title</th><th>Seller</th><th>Price</th><th>Category</th><th>Status</th><th>Listed</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($listings as $l): ?>
      <tr>
        <td style="width:56px">
          <?php if ($l['image_path']): ?>
            <img src="<?= url(e($l['image_path'])) ?>" class="rounded" style="width:44px;height:44px;object-fit:cover">
          <?php endif; ?>
        </td>
        <td>
          <a href="<?= url('listings/view.php?id=' . (int)$l['listing_id']) ?>"><?= e($l['title']) ?></a>
          <?php if ($l['course_code']): ?><div class="small text-muted"><?= e($l['course_code']) ?></div><?php endif; ?>
        </td>
        <td class="small"><?= e($l['seller_name']) ?><br><span class="text-muted"><?= e($l['seller_email']) ?></span></td>
        <td><?= money($l['price']) ?></td>
        <td class="small"><?= e($l['category']) ?></td>
        <td><span class="badge bg-<?= $l['status']==='available'?'success':($l['status']==='reserved'?'warning text-dark':($l['status']==='sold'?'secondary':'danger')) ?>">
          <?= ucfirst($l['status']) ?></span></td>
        <td class="small text-muted"><?= date('M j, Y', strtotime($l['created_at'])) ?></td>
        <td class="text-nowrap">
          <?php if ($l['status'] === 'removed'): ?>
            <form method="post" class="d-inline"><?= csrf_field() ?>
              <input type="hidden" name="listing_id" value="<?= (int)$l['listing_id'] ?>">
              <input type="hidden" name="action" value="restore">
              <button class="btn btn-sm btn-outline-success">Restore</button>
            </form>
          <?php else: ?>
            <form method="post" class="d-inline" onsubmit="return confirm('Remove this listing?')"><?= csrf_field() ?>
              <input type="hidden" name="listing_id" value="<?= (int)$l['listing_id'] ?>">
              <input type="hidden" name="action" value="remove">
              <button class="btn btn-sm btn-outline-danger">Remove</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
