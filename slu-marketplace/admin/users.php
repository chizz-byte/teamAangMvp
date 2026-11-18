<?php
require_once __DIR__ . '/../middleware/admin.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Listing.php';

$users = User::all();
$page_title = 'Manage Users';
require __DIR__ . '/../includes/header.php';
?>
<h3 class="mb-3">Registered Users (<?= count($users) ?>)</h3>
<div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead><tr><th>ID</th><th>Name</th><th>Student #</th><th>Email</th><th>Role</th><th>Listings</th><th>Rating</th><th>Joined</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u):
      $stats = User::ratingStats((int)$u['user_id']);
      $cnt = db()->prepare('SELECT COUNT(*) FROM listings WHERE seller_id = ?');
      $cnt->execute([(int)$u['user_id']]);
    ?>
      <tr>
        <td><?= (int)$u['user_id'] ?></td>
        <td><?= e($u['full_name']) ?></td>
        <td><?= e($u['student_number']) ?></td>
        <td><?= e($u['email']) ?></td>
        <td><span class="badge bg-<?= $u['role']==='admin'?'danger':'primary' ?>"><?= e($u['role']) ?></span></td>
        <td><?= (int)$cnt->fetchColumn() ?></td>
        <td><?= $stats['count'] ? number_format($stats['avg'],1).'★ ('.$stats['count'].')' : '—' ?></td>
        <td class="small text-muted"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
