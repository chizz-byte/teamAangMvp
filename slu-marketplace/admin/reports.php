<?php
require_once __DIR__ . '/../middleware/admin.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/Listing.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $rid = (int)($_POST['report_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($rid && in_array($action, ['resolve','dismiss','remove_listing'], true)) {
        if ($action === 'resolve') Report::setStatus($rid, 'resolved');
        elseif ($action === 'dismiss') Report::setStatus($rid, 'dismissed');
        else {
            $r = db()->prepare('SELECT listing_id FROM reports WHERE report_id = ?');
            $r->execute([$rid]);
            if ($lid = $r->fetchColumn()) Listing::setStatus((int)$lid, 'removed');
            Report::setStatus($rid, 'resolved');
        }
        flash('success', 'Report updated.');
    }
    redirect(url('admin/reports.php'));
}

$tab = $_GET['tab'] ?? 'pending';
$reports = $tab === 'all' ? Report::all() : Report::pending();

$page_title = 'Reports';
require __DIR__ . '/../includes/header.php';
?>
<h3 class="mb-3">Reported Listings</h3>
<ul class="nav nav-tabs mb-3">
  <li class="nav-item"><a class="nav-link <?= $tab!=='all'?'active':'' ?>" href="<?= url('admin/reports.php') ?>">Pending</a></li>
  <li class="nav-item"><a class="nav-link <?= $tab==='all'?'active':'' ?>" href="<?= url('admin/reports.php?tab=all') ?>">All Reports</a></li>
</ul>
<?php if (!$reports): ?>
  <div class="text-center text-muted py-5">No reports to show.</div>
<?php else: ?>
  <?php foreach ($reports as $r): ?>
    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex justify-content-between flex-wrap gap-2">
          <div>
            <a href="<?= url('listings/view.php?id=' . (int)$r['listing_id']) ?>" class="fw-bold"><?= e($r['listing_title']) ?></a>
            <span class="badge bg-<?= $r['status']==='pending'?'warning text-dark':($r['status']==='resolved'?'success':'secondary') ?> ms-2"><?= ucfirst($r['status']) ?></span>
            <div class="small text-muted">
              Reported by <?= e($r['reporter_name']) ?> · <?= time_ago($r['created_at']) ?>
              · Listing status: <?= ucfirst($r['listing_status'] ?? '') ?>
            </div>
            <p class="mt-2 mb-0"><?= nl2br(e($r['reason'])) ?></p>
          </div>
          <?php if ($r['status'] === 'pending'): ?>
            <div class="d-flex flex-column gap-2">
              <form method="post" onsubmit="return confirm('Remove this listing?')">
                <?= csrf_field() ?>
                <input type="hidden" name="report_id" value="<?= (int)$r['report_id'] ?>">
                <input type="hidden" name="action" value="remove_listing">
                <button class="btn btn-sm btn-danger"><i class="bi bi-slash-circle"></i> Remove Listing</button>
              </form>
              <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="report_id" value="<?= (int)$r['report_id'] ?>">
                <input type="hidden" name="action" value="resolve">
                <button class="btn btn-sm btn-outline-success">Mark Resolved</button>
              </form>
              <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="report_id" value="<?= (int)$r['report_id'] ?>">
                <input type="hidden" name="action" value="dismiss">
                <button class="btn btn-sm btn-outline-secondary">Dismiss</button>
              </form>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
