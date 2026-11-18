<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/form.php';

$id = (int)($_GET['id'] ?? $_POST['listing_id'] ?? 0);
$listing = $id ? Listing::findById($id) : null;
if (!$listing) {
    flash('errors', ['Listing not found.']);
    redirect(url('listings/mine.php'));
}
if ((int)$listing['seller_id'] !== (int)current_user()['user_id']) {
    flash('errors', ['You can only edit your own listings.']);
    redirect(url('listings/view.php?id=' . $id));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    [$data, $errors] = validate_listing_post($_POST);
    if (!$errors) {
        $upload = handle_listing_image($_FILES['image'] ?? []);
        if ($upload['error']) {
            $errors[] = $upload['error'];
        } else {
            if ($upload['path']) $data['image_path'] = $upload['path'];
            Listing::update($id, $data);
            if (isset($data['image_path'])) {
                db()->prepare('UPDATE listings SET image_path = ? WHERE listing_id = ?')
                    ->execute([$data['image_path'], $id]);
            }
            flash('success', 'Listing updated.');
            redirect(url('listings/view.php?id=' . $id));
        }
    }
    flash_errors($errors);
    set_old($_POST);
    redirect(url('listings/edit.php?id=' . $id));
}

$errs = flash('errors');
$page_title = 'Edit Listing';
require __DIR__ . '/../includes/header.php';
$f = [
    'title' => $listing['title'], 'description' => $listing['description'] ?? '',
    'category' => $listing['category'], 'department' => $listing['department'] ?? '',
    'course_code' => $listing['course_code'] ?? '', 'edition' => $listing['edition'] ?? '',
    'price' => $listing['price'], 'condition' => $listing['condition'],
];
foreach ($_SESSION['old'] ?? [] as $k => $v) { if (isset($f[$k])) $f[$k] = $v; }
?>
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h4 class="mb-4">Edit Listing</h4>
        <?php if ($errs): ?>
          <div class="alert alert-danger"><?php foreach ((array)$errs as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
        <?php endif; ?>
        <?php if ($listing['image_path']): ?>
          <p>Current image: <img src="<?= url(e($listing['image_path'])) ?>" style="height:80px" class="rounded border"></p>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <input type="hidden" name="listing_id" value="<?= $id ?>">
          <div class="mb-3">
            <label class="form-label">Item Title *</label>
            <input name="title" class="form-control" required maxlength="200" value="<?= e($f['title']) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4"><?= e($f['description']) ?></textarea>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Category *</label>
              <select name="category" class="form-select" required>
                <?php foreach (Listing::CATEGORIES as $c): ?>
                  <option <?= $f['category']===$c?'selected':'' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Condition *</label>
              <select name="condition" class="form-select" required>
                <?php foreach (Listing::CONDITIONS as $c): ?>
                  <option <?= $f['condition']===$c?'selected':'' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Price (₱) *</label>
              <input type="number" step="0.01" min="1" name="price" class="form-control" required value="<?= e($f['price']) ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Department</label>
              <select name="department" class="form-select">
                <option value="">Select…</option>
                <?php foreach (Listing::DEPARTMENTS as $d): ?>
                  <option <?= $f['department']===$d?'selected':'' ?>><?= e($d) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Course Code</label>
              <input name="course_code" class="form-control" value="<?= e($f['course_code']) ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Edition (optional)</label>
              <input name="edition" class="form-control" value="<?= e($f['edition']) ?>">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Replace Image</label>
            <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
          </div>
          <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Changes</button>
          <a href="<?= url('listings/view.php?id=' . $id) ?>" class="btn btn-outline-secondary">Cancel</a>
        </form>
      </div>
    </div>
  </div>
</div>
<?php clear_old(); require __DIR__ . '/../includes/footer.php'; ?>
