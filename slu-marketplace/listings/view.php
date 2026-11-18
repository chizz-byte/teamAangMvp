<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/Message.php';

$id = (int)($_GET['id'] ?? 0);
$listing = $id ? Listing::findById($id) : null;
if (!$listing || $listing['status'] === 'removed') {
    flash('errors', ['Listing not found or has been removed.']);
    redirect(url('index.php'));
}

$me = current_user();
$isOwner = $me && (int)$me['user_id'] === (int)$listing['seller_id'];
$sellerStats = User::ratingStats((int)$listing['seller_id']);
$tx = Transaction::activeForListing($id);
$myTxWithSeller = null;
if ($me && !$isOwner) {
    $myTxWithSeller = Transaction::forListingPair($id, (int)$me['user_id'], (int)$listing['seller_id']);
}

// Owner delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    require_login();
    csrf_verify();
    if ($me && $isOwner) {
        Listing::delete($id);
        flash('success', 'Listing deleted.');
        redirect(url('listings/mine.php'));
    }
    redirect(url('listings/view.php?id=' . $id));
}

// Report submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report'])) {
    require_login();
    csrf_verify();
    if ($isOwner) { flash('errors', ['You cannot report your own listing.']); redirect(url('listings/view.php?id=' . $id)); }
    $reason = trim($_POST['reason'] ?? '');
    if ($reason === '') {
        flash('errors', ['Please provide a reason for the report.']);
    } elseif (Report::alreadyReported($id, (int)$me['user_id'])) {
        flash('errors', ['You have already reported this listing.']);
    } else {
        Report::create($id, (int)$me['user_id'], $reason);
        flash('success', 'Report submitted. Our admins will review it.');
    }
    redirect(url('listings/view.php?id=' . $id));
}

// Review submission (completed transaction participant)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review'])) {
    require_login();
    csrf_verify();
    $txId = (int)($_POST['transaction_id'] ?? 0);
    $t = Transaction::findById($txId);
    $ok = false;
    if ($t && $t['status'] === 'completed'
        && ((int)$t['buyer_id'] === (int)$me['user_id'] || (int)$t['seller_id'] === (int)$me['user_id'])) {
        $reviewedId = (int)$t['buyer_id'] === (int)$me['user_id'] ? (int)$t['seller_id'] : (int)$t['buyer_id'];
        $rating = (int)($_POST['rating'] ?? 0);
        $ok = Review::submit($txId, (int)$me['user_id'], $reviewedId, $rating, trim($_POST['feedback'] ?? '') ?: null);
    }
    flash($ok ? 'success' : 'errors', $ok ? 'Thank you for your feedback!' : 'Could not submit review (already reviewed or not allowed).');
    redirect(url('listings/view.php?id=' . $id));
}

$page_title = $listing['title'];
require __DIR__ . '/../includes/header.php';
?>
<div class="row">
  <div class="col-lg-6 mb-4">
    <div class="card">
      <?php if ($listing['image_path']): ?>
        <img src="<?= url(e($listing['image_path'])) ?>" class="card-img-top" alt="<?= e($listing['title']) ?>">
      <?php else: ?>
        <div class="bg-light d-flex align-items-center justify-content-center" style="height:320px">
          <i class="bi bi-book fs-1 text-secondary"></i>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="d-flex justify-content-between align-items-start mb-2">
      <h3 class="mb-0"><?= e($listing['title']) ?></h3>
      <span class="badge fs-6 bg-<?= $listing['status']==='available'?'success':($listing['status']==='reserved'?'warning text-dark':($listing['status']==='sold'?'secondary':'danger')) ?> align-self-start">
        <?= ucfirst($listing['status']) ?>
      </span>
    </div>
    <div class="text-primary fs-3 fw-bold mb-3"><?= money($listing['price']) ?></div>

    <table class="table table-sm">
      <tr><th style="width:40%">Category</th><td><?= e($listing['category']) ?></td></tr>
      <tr><th>Condition</th><td><?= e($listing['condition']) ?></td></tr>
      <?php if ($listing['course_code']): ?><tr><th>Course Code</th><td><?= e($listing['course_code']) ?></td></tr><?php endif; ?>
      <?php if ($listing['department']): ?><tr><th>Department</th><td><?= e($listing['department']) ?></td></tr><?php endif; ?>
      <?php if ($listing['edition']): ?><tr><th>Edition</th><td><?= e($listing['edition']) ?></td></tr><?php endif; ?>
      <tr><th>Seller</th><td>
        <?= e($listing['seller_name']) ?>
        <span class="text-warning"><?= star_rating($sellerStats['avg']) ?></span>
        <small class="text-muted">(<?= $sellerStats['count'] ?> review<?= $sellerStats['count']==1?'':'s' ?>)</small>
      </td></tr>
      <tr><th>Listed</th><td><?= date('M j, Y g:i A', strtotime($listing['created_at'])) ?></td></tr>
    </table>

    <h6>Description</h6>
    <p><?= nl2br(e($listing['description'] ?? 'No description provided.')) ?></p>

    <div class="d-flex flex-wrap gap-2 mt-4">
      <?php if ($me && !$isOwner && in_array($listing['status'], ['available','reserved'], true)): ?>
        <a href="<?= url('messages/view.php?listing_id=' . $id . '&peer_id=' . (int)$listing['seller_id']) ?>"
           class="btn btn-primary"><i class="bi bi-chat-dots"></i> Chat with Seller</a>
      <?php elseif (!$me && in_array($listing['status'], ['available','reserved'], true)): ?>
        <a href="<?= url('login.php') ?>" class="btn btn-primary"><i class="bi bi-chat-dots"></i> Login to Chat</a>
      <?php endif; ?>

      <?php if ($me && !$isOwner && $listing['status'] === 'available' && !$tx): ?>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#txModal">
          <i class="bi bi-handshake"></i> Start Transaction
        </button>
      <?php endif; ?>

      <?php if ($me && $isOwner): ?>
        <a href="<?= url('listings/edit.php?id=' . $id) ?>" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
        <form method="post" class="d-inline" onsubmit="return confirm('Delete this listing?')">
          <?= csrf_field() ?>
          <input type="hidden" name="delete" value="1">
          <button class="btn btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
        </form>
      <?php endif; ?>

      <?php if ($me && !$isOwner): ?>
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#reportModal">
          <i class="bi bi-flag"></i> Report
        </button>
      <?php endif; ?>
    </div>

    <?php // Transaction panel for participants
    $panelTx = $tx ?: $myTxWithSeller;
    if ($panelTx && $me && in_array($panelTx['status'], ['pending','completed'], true)
        && ((int)$panelTx['buyer_id'] === (int)$me['user_id'] || (int)$panelTx['seller_id'] === (int)$me['user_id'])): ?>
      <div class="card mt-4">
        <div class="card-body">
          <h6 class="card-title"><i class="bi bi-arrow-left-right"></i> Transaction
            <span class="badge bg-<?= $panelTx['status']==='completed'?'success':'warning text-dark' ?>"><?= ucfirst($panelTx['status']) ?></span>
          </h6>
          <p class="mb-2 small">
            Buyer: <strong><?= e($panelTx['buyer_name']) ?></strong> ·
            Seller: <strong><?= e($panelTx['seller_name']) ?></strong><br>
            Agreed price: <strong><?= $panelTx['agreed_price'] ? money($panelTx['agreed_price']) : 'Not set — negotiate via chat' ?></strong> ·
            Meetup: <?= e($panelTx['meetup_location'] ?? 'To be arranged') ?>
          </p>
          <?php if ($panelTx['status'] === 'pending'): ?>
            <div class="d-flex flex-wrap gap-2" id="txActions">
              <button class="btn btn-sm btn-outline-primary" onclick="txUpdate(<?= (int)$panelTx['transaction_id'] ?>)">Update Price/Meetup</button>
              <button class="btn btn-sm btn-success" onclick="txComplete(<?= (int)$panelTx['transaction_id'] ?>)">Mark Completed</button>
              <button class="btn btn-sm btn-outline-danger" onclick="txCancel(<?= (int)$panelTx['transaction_id'] ?>)">Cancel</button>
            </div>
          <?php elseif ($panelTx['status'] === 'completed'): ?>
            <p class="text-success small mb-1"><i class="bi bi-check-circle"></i> Transaction completed on
              <?= $panelTx['completed_at'] ? date('M j, Y', strtotime($panelTx['completed_at'])) : '' ?>.</p>
            <?php
              $peer = (int)$panelTx['buyer_id'] === (int)$me['user_id'] ? (int)$panelTx['seller_id'] : (int)$panelTx['buyer_id'];
              $peerName = (int)$panelTx['buyer_id'] === (int)$me['user_id'] ? $panelTx['seller_name'] : $panelTx['buyer_name'];
              $already = Review::hasReviewed((int)$panelTx['transaction_id'], (int)$me['user_id']);
            ?>
            <?php if (!$already): ?>
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#reviewBox">
                <i class="bi bi-star"></i> Rate <?= e($peerName) ?>
              </button>
              <div class="collapse mt-2" id="reviewBox">
                <form method="post" class="border rounded p-3">
                  <?= csrf_field() ?>
                  <input type="hidden" name="review" value="1">
                  <input type="hidden" name="transaction_id" value="<?= (int)$panelTx['transaction_id'] ?>">
                  <div class="mb-2">
                    <label class="form-label">Rating (1-5)</label>
                    <select name="rating" class="form-select form-select-sm" style="width:auto" required>
                      <?php for ($i=5;$i>=1;$i--): ?><option value="<?= $i ?>"><?= $i ?> ★</option><?php endfor; ?>
                    </select>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Feedback (optional)</label>
                    <textarea name="feedback" class="form-control form-control-sm" rows="2"></textarea>
                  </div>
                  <button class="btn btn-sm btn-primary">Submit Review</button>
                </form>
              </div>
            <?php else: ?>
              <span class="text-muted small">Review submitted. Thank you!</span>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <?php // Seller reviews
    $reviews = Review::forUser((int)$listing['seller_id'], 5);
    if ($reviews): ?>
      <div class="mt-4">
        <h6>Recent Reviews for Seller</h6>
        <?php foreach ($reviews as $r): ?>
          <div class="border rounded p-2 mb-2 small">
            <span class="text-warning"><?= star_rating((float)$r['rating']) ?></span>
            <strong><?= e($r['reviewer_name']) ?></strong>
            <span class="text-muted">· <?= time_ago($r['created_at']) ?></span>
            <?php if ($r['feedback']): ?><div><?= e($r['feedback']) ?></div><?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php // Start Transaction modal
if ($me && !$isOwner && $listing['status'] === 'available' && !$tx): ?>
<div class="modal fade" id="txModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form onsubmit="return txPropose(event, <?= $id ?>)">
        <div class="modal-header"><h5 class="modal-title">Start Transaction</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <p class="small text-muted">Arrange an on-campus meetup. Payment is handled directly
          (cash on meetup, GCash, or other e-wallets).</p>
          <div class="mb-3">
            <label class="form-label">Agreed Price (₱, optional — can negotiate in chat)</label>
            <input type="number" step="0.01" min="1" name="agreed_price" class="form-control" placeholder="<?= money($listing['price']) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Meetup Location</label>
            <select name="meetup_location" class="form-select">
              <option value="">Decide later</option>
              <option>Main Campus</option>
              <option>Maryheights Campus</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-success">Reserve Item</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php // Report modal
if ($me && !$isOwner): ?>
<div class="modal fade" id="reportModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="report" value="1">
        <div class="modal-header"><h5 class="modal-title">Report Listing</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <label class="form-label">Reason</label>
          <textarea name="reason" class="form-control" rows="3" required
                    placeholder="e.g. scam, wrong item, inappropriate content"></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-danger">Submit Report</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
const CSRF = '<?= csrf_token() ?>';
const API = '<?= url('api.php') ?>';
async function txPropose(ev, listingId) {
  ev.preventDefault();
  const fd = new FormData(ev.target);
  fd.append('action', 'tx_propose'); fd.append('listing_id', listingId); fd.append('csrf', CSRF);
  const r = await fetch(API, {method:'POST', body: fd});
  const j = await r.json();
  if (j.ok) location.reload(); else alert(j.error || 'Failed');
  return false;
}
async function txUpdate(id) {
  const price = prompt('Agreed price (leave blank to keep):');
  const loc = prompt('Meetup location: Main Campus or Maryheights Campus (leave blank to keep):');
  const fd = new FormData();
  fd.append('action','tx_update'); fd.append('transaction_id', id); fd.append('csrf', CSRF);
  if (price !== null && price !== '') fd.append('agreed_price', price);
  if (loc) fd.append('meetup_location', loc);
  const r = await fetch(API, {method:'POST', body: fd});
  const j = await r.json();
  if (j.ok) location.reload(); else alert(j.error || 'Failed');
}
async function txComplete(id) {
  if (!confirm('Mark this transaction as completed? The listing will be marked Sold.')) return;
  const fd = new FormData();
  fd.append('action','tx_complete'); fd.append('transaction_id', id); fd.append('csrf', CSRF);
  const r = await fetch(API, {method:'POST', body: fd});
  const j = await r.json();
  if (j.ok) location.reload(); else alert(j.error || 'Failed');
}
async function txCancel(id) {
  if (!confirm('Cancel this transaction?')) return;
  const fd = new FormData();
  fd.append('action','tx_cancel'); fd.append('transaction_id', id); fd.append('csrf', CSRF);
  const r = await fetch(API, {method:'POST', body: fd});
  const j = await r.json();
  if (j.ok) location.reload(); else alert(j.error || 'Failed');
}
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
