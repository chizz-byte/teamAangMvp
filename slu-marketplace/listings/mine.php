<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Listing.php';

$items = Listing::bySeller((int)current_user()['user_id']);
$page_title = 'My Listings';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">My Listings</h3>
  <a href="<?= url('listings/create.php') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Listing</a>
</div>
<?php if (!$items): ?>
  <div class="text-center text-muted py-5">
    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
    You haven't listed anything yet. <a href="<?= url('listings/create.php') ?>">Create your first listing</a>.
  </div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr>
        <th></th><th>Title</th><th>Price</th><th>Category</th><th>Condition</th><th>Status</th><th>Listed</th><th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($items as $item): ?>
        <tr>
          <td style="width:60px">
            <?php if ($item['image_path']): ?>
              <img src="<?= url(e($item['image_path'])) ?>" class="rounded" style="width:48px;height:48px;object-fit:cover">
            <?php else: ?><i class="bi bi-book text-muted"></i><?php endif; ?>
          </td>
          <td><a href="<?= url('listings/view.php?id=' . (int)$item['listing_id']) ?>"><?= e($item['title']) ?></a></td>
          <td><?= money($item['price']) ?></td>
          <td><?= e($item['category']) ?></td>
          <td><?= e($item['condition']) ?></td>
          <td><span class="badge bg-<?= $item['status']==='available'?'success':($item['status']==='reserved'?'warning text-dark':($item['status']==='sold'?'secondary':'danger')) ?>">
            <?= ucfirst($item['status']) ?></span></td>
          <td class="small text-muted"><?= time_ago($item['created_at']) ?></td>
          <td class="text-nowrap">
            <a href="<?= url('listings/edit.php?id=' . (int)$item['listing_id']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
            <a href="<?= url('listings/view.php?id=' . (int)$item['listing_id']) ?>" class="btn btn-sm btn-outline-secondary">View</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
