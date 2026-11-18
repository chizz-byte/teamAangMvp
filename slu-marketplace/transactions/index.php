<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Transaction.php';

$txs = Transaction::forUser((int)current_user()['user_id']);
$page_title = 'Transactions';
require __DIR__ . '/../includes/header.php';
?>
<h3 class="mb-3">My Transactions</h3>
<?php if (!$txs): ?>
  <div class="text-center text-muted py-5">
    <i class="bi bi-arrow-left-right fs-1 d-block mb-2"></i>
    No transactions yet. Browse the <a href="<?= url('index.php') ?>">marketplace</a> to get started.
  </div>
<?php else: ?>
  <div class="list-group">
    <?php foreach ($txs as $t): ?>
      <div class="list-group-item">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
          <div>
            <a href="<?= url('listings/view.php?id=' . (int)$t['listing_id']) ?>" class="fw-bold text-decoration-none">
              <?= e($t['listing_title']) ?>
            </a>
            <div class="small text-muted">
              Buyer: <?= e($t['buyer_name']) ?> · Seller: <?= e($t['seller_name']) ?>
            </div>
            <div class="small">
              Agreed: <?= $t['agreed_price'] ? money($t['agreed_price']) : '—' ?> ·
              Meetup: <?= e($t['meetup_location'] ?? '—') ?> ·
              Started <?= time_ago($t['created_at']) ?>
              <?php if ($t['completed_at']): ?> · Completed <?= date('M j, Y', strtotime($t['completed_at'])) ?><?php endif; ?>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-<?= $t['status']==='completed'?'success':($t['status']==='pending'?'warning text-dark':'secondary') ?>">
              <?= ucfirst($t['status']) ?>
            </span>
            <a href="<?= url('messages/view.php?listing_id=' . (int)$t['listing_id'] . '&peer_id=' . ((int)$t['buyer_id'] === (int)current_user()['user_id'] ? (int)$t['seller_id'] : (int)$t['buyer_id'])) ?>"
               class="btn btn-sm btn-outline-primary"><i class="bi bi-chat"></i> Chat</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
