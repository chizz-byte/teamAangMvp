<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/form.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    [$data, $errors] = validate_listing_post($_POST);
    if (!$errors) {
        $upload = handle_listing_image($_FILES['image'] ?? []);
        if ($upload['error']) {
            $errors[] = $upload['error'];
        } else {
            $data['image_path'] = $upload['path'];
            $id = Listing::create($data, (int)current_user()['user_id']);
            flash('success', 'Listing published!');
            redirect(url('listings/view.php?id=' . $id));
        }
    }
    flash_errors($errors);
    set_old($_POST);
    redirect(url('listings/create.php'));
}

$errs = flash('errors');
$page_title = 'Sell an Item';
require __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h4 class="mb-4">Create Listing</h4>
        <?php if ($errs): ?>
          <div class="alert alert-danger"><?php foreach ((array)$errs as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <?php
            $f = ['title'=>'','description'=>'','category'=>'','department'=>'','course_code'=>'','edition'=>'','price'=>'','condition'=>''];
            foreach ($_SESSION['old'] ?? [] as $k=>$v) { if (isset($f[$k])) $f[$k]=$v; }
          ?>
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
                <option value="">Select…</option>
                <?php foreach (Listing::CATEGORIES as $c): ?>
                  <option <?= $f['category']===$c?'selected':'' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Condition *</label>
              <select name="condition" class="form-select" required>
                <option value="">Select…</option>
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
              <input name="course_code" class="form-control" placeholder="e.g. ITE 28" value="<?= e($f['course_code']) ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Edition (optional)</label>
              <input name="edition" class="form-control" placeholder="e.g. 3rd Edition" value="<?= e($f['edition']) ?>">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Item Image</label>
            <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
            <div class="form-text">JPG, PNG, or WebP · max 5MB</div>
          </div>
          <button class="btn btn-primary"><i class="bi bi-megaphone"></i> Publish Listing</button>
          <a href="<?= url('index.php') ?>" class="btn btn-outline-secondary">Cancel</a>
        </form>
      </div>
    </div>
  </div>
</div>
<?php clear_old(); require __DIR__ . '/../includes/footer.php'; ?>
