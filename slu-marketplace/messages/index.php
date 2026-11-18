<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Message.php';

$convos = Message::conversations((int)current_user()['user_id']);
$page_title = 'Messages';
require __DIR__ . '/../includes/header.php';
?>
<h3 class="mb-3">Messages</h3>
<?php if (!$convos): ?>
  <div class="text-center text-muted py-5">
    <i class="bi bi-chat-square-text fs-1 d-block mb-2"></i>
    No conversations yet. Open a listing and tap "Chat with Seller" to start one.
  </div>
<?php else: ?>
  <div class="list-group">
    <?php foreach ($convos as $c): ?>
      <a href="<?= url('messages/view.php?listing_id=' . (int)$c['listing_id'] . '&peer_id=' . (int)$c['peer_id']) ?>"
         class="list-group-item list-group-item-action d-flex align-items-center gap-3">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
             style="width:44px;height:44px;flex-shrink:0">
          <?= strtoupper(substr($c['peer_name'], 0, 1)) ?>
        </div>
        <div class="flex-grow-1 overflow-hidden">
          <div class="d-flex justify-content-between">
            <strong><?= e($c['peer_name']) ?></strong>
            <small class="text-muted"><?= time_ago($c['last_at']) ?></small>
          </div>
          <div class="text-muted small text-truncate"><?= e($c['listing_title']) ?></div>
          <div class="small text-truncate"><?= e($c['last_msg']) ?></div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
