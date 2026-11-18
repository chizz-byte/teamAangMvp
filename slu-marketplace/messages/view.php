<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/../models/User.php';

$me = current_user();
$listingId = (int)($_GET['listing_id'] ?? 0);
$peerId = (int)($_GET['peer_id'] ?? 0);
$listing = $listingId ? Listing::findById($listingId) : null;
$peer = $peerId ? User::findById($peerId) : null;

// Authorization: must be seller contacting buyer-side, buyer contacting seller, or existing thread.
$allowed = false;
if ($listing && $peer && $listing['status'] !== 'removed' && (int)$me['user_id'] !== $peerId) {
    if (Message::canAccess((int)$me['user_id'], $listingId, $peerId)) {
        $allowed = true;
    } elseif ((int)$listing['seller_id'] === $peerId && (int)$listing['seller_id'] !== (int)$me['user_id']) {
        $allowed = true; // buyer initiating to seller
    } elseif ((int)$listing['seller_id'] === (int)$me['user_id'] && Message::canAccess($peerId, $listingId, (int)$me['user_id'])) {
        $allowed = true; // seller viewing existing thread
    }
}
if (!$allowed) {
    flash('errors', ['You are not allowed to view this conversation.']);
    redirect(url('messages/index.php'));
}

$thread = Message::thread($listingId, (int)$me['user_id'], $peerId);
$page_title = 'Chat · ' . $listing['title'];
require __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <strong><?= e($peer['full_name']) ?></strong>
          <span class="text-muted small">· re: <?= e($listing['title']) ?> (<?= money($listing['price']) ?>)</span>
        </div>
        <a href="<?= url('messages/index.php') ?>" class="btn btn-sm btn-outline-secondary">All Chats</a>
      </div>
      <div class="card-body p-0">
        <div id="chatBox" class="p-3" style="height:420px;overflow-y:auto;background:#f8f9fa">
          <?php if (!$thread): ?>
            <div class="text-center text-muted py-5" id="emptyMsg">
              No messages yet. Say hello and negotiate your price!
            </div>
          <?php endif; ?>
          <?php foreach ($thread as $m): ?>
            <div class="d-flex mb-2 <?= (int)$m['sender_id'] === (int)$me['user_id'] ? 'justify-content-end' : '' ?>" data-mid="<?= (int)$m['message_id'] ?>">
              <div class="px-3 py-2 rounded <?= (int)$m['sender_id'] === (int)$me['user_id'] ? 'bg-primary text-white' : 'bg-white border' ?>"
                   style="max-width:75%">
                <div><?= nl2br(e($m['content'])) ?></div>
                <div class="small <?= (int)$m['sender_id'] === (int)$me['user_id'] ? 'text-white-50' : 'text-muted' ?>"
                     style="font-size:.7rem"><?= date('M j, g:i A', strtotime($m['created_at'])) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="border-top p-2 d-flex gap-2">
          <input type="text" id="msgInput" class="form-control" placeholder="Type a message…" maxlength="2000" autocomplete="off">
          <button class="btn btn-primary" id="sendBtn"><i class="bi bi-send"></i></button>
        </div>
      </div>
    </div>
    <p class="text-muted small mt-2">
      Tip: agree on price and meetup here (Main Campus or Maryheights Campus),
      then use "Start Transaction" / "Mark Completed" on the <a href="<?= url('listings/view.php?id=' . $listingId) ?>">listing page</a>.
    </p>
  </div>
</div>
<script>
const ME = <?= (int)$me['user_id'] ?>;
const LISTING_ID = <?= $listingId ?>;
const PEER_ID = <?= $peerId ?>;
const API = '<?= url('api.php') ?>';
const CSRF = '<?= csrf_token() ?>';
const chatBox = document.getElementById('chatBox');
let lastId = <?= $thread ? (int)end($thread)['message_id'] : 0 ?>;

function scrollBottom(){ chatBox.scrollTop = chatBox.scrollHeight; }
function bubble(m){
  const mine = m.sender_id == ME;
  const d = document.createElement('div');
  d.className = 'd-flex mb-2 ' + (mine ? 'justify-content-end' : '');
  d.dataset.mid = m.message_id;
  d.innerHTML = '<div class="px-3 py-2 rounded ' + (mine ? 'bg-primary text-white' : 'bg-white border') +
    '" style="max-width:75%"><div></div><div class="small ' + (mine ? 'text-white-50' : 'text-muted') +
    '" style="font-size:.7rem"></div></div>';
  d.querySelector('div div').textContent = m.content;
  return d;
}
async function poll(){
  try {
    const r = await fetch(`${API}?action=messages_fetch&listing_id=${LISTING_ID}&peer_id=${PEER_ID}&after=${lastId}`);
    const j = await r.json();
    if (j.ok && j.messages.length){
      document.getElementById('emptyMsg')?.remove();
      for (const m of j.messages){ chatBox.appendChild(bubble(m)); lastId = Math.max(lastId, m.message_id); }
      scrollBottom();
    }
  } catch(e){}
}
async function send(){
  const inp = document.getElementById('msgInput');
  const content = inp.value.trim();
  if (!content) return;
  inp.value = '';
  const fd = new FormData();
  fd.append('action','messages_send'); fd.append('csrf', CSRF);
  fd.append('listing_id', LISTING_ID); fd.append('peer_id', PEER_ID); fd.append('content', content);
  try {
    const r = await fetch(API, {method:'POST', body: fd});
    const j = await r.json();
    if (j.ok){ document.getElementById('emptyMsg')?.remove(); chatBox.appendChild(bubble(j.message)); lastId = j.message.message_id; scrollBottom(); }
    else alert(j.error || 'Send failed');
  } catch(e){ alert('Network error'); }
}
document.getElementById('sendBtn').onclick = send;
document.getElementById('msgInput').addEventListener('keydown', e => { if (e.key === 'Enter') send(); });
scrollBottom();
setInterval(poll, 4000);
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
