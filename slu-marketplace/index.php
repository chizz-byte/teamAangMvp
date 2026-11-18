<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/Listing.php';
require_once __DIR__ . '/models/User.php';

$filters = [
    'q'         => trim($_GET['q'] ?? ''),
    'category'  => $_GET['category'] ?? '',
    'department'=> $_GET['department'] ?? '',
    'condition' => $_GET['condition'] ?? '',
    'sort'      => $_GET['sort'] ?? 'newest',
];
if ($filters['category'] && !in_array($filters['category'], Listing::CATEGORIES, true)) $filters['category'] = '';
if ($filters['condition'] && !in_array($filters['condition'], Listing::CONDITIONS, true)) $filters['condition'] = '';
if ($filters['department'] && !in_array($filters['department'], Listing::DEPARTMENTS, true)) $filters['department'] = '';

$items = Listing::search($filters);

$page_title = 'Marketplace';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h3 class="mb-0">Marketplace</h3>
  <?php if (is_logged_in() && !is_admin()): ?>
    <a href="<?= url('listings/create.php') ?>" class="btn btn-primary">
      <i class="bi bi-plus-lg"></i> Sell an Item
    </a>
  <?php endif; ?>
</div>

<form class="card card-body mb-4" method="get">
  <div class="row g-2">
    <div class="col-md-4">
      <input type="search" name="q" class="form-control" placeholder="Search title, keyword, or course code…"
             value="<?= e($filters['q']) ?>">
    </div>
    <div class="col-md-2">
      <select name="category" class="form-select">
        <option value="">All categories</option>
        <?php foreach (Listing::CATEGORIES as $c): ?>
          <option value="<?= e($c) ?>" <?= $filters['category'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="department" class="form-select">
        <option value="">All departments</option>
        <?php foreach (Listing::DEPARTMENTS as $d): ?>
          <option value="<?= e($d) ?>" <?= $filters['department'] === $d ? 'selected' : '' ?>><?= e($d) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="condition" class="form-select">
        <option value="">Any condition</option>
        <?php foreach (Listing::CONDITIONS as $c): ?>
          <option value="<?= e($c) ?>" <?= $filters['condition'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="sort" class="form-select">
        <option value="newest" <?= $filters['sort']==='newest'?'selected':'' ?>>Newest</option>
        <option value="oldest" <?= $filters['sort']==='oldest'?'selected':'' ?>>Oldest</option>
        <option value="price_asc" <?= $filters['sort']==='price_asc'?'selected':'' ?>>Price: Low → High</option>
        <option value="price_desc" <?= $filters['sort']==='price_desc'?'selected':'' ?>>Price: High → Low</option>
      </select>
    </div>
    <div class="col-12">
      <button class="btn btn-outline-primary"><i class="bi bi-funnel"></i> Apply Filters</button>
      <a href="<?= url('index.php') ?>" class="btn btn-outline-secondary">Reset</a>
    </div>
  </div>
</form>

<?php if (!$items): ?>
  <div class="text-center text-muted py-5">
    <i class="bi bi-search fs-1 d-block mb-2"></i>
    No listings found. Try different keywords or filters.
  </div>
<?php else: ?>
  <p class="text-muted"><?= count($items) ?> listing(s) found</p>
  <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
    <?php foreach ($items as $item): ?>
      <div class="col">
        <div class="card h-100 listing-card">
          <a href="<?= url('listings/view.php?id=' . (int)$item['listing_id']) ?>" class="text-decoration-none text-dark">
            <?php if ($item['image_path']): ?>
              <img src="<?= url(e($item['image_path'])) ?>" class="card-img-top listing-img" alt="">
            <?php else: ?>
              <div class="card-img-top listing-img bg-light d-flex align-items-center justify-content-center">
                <i class="bi bi-book fs-1 text-secondary"></i>
              </div>
            <?php endif; ?>
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <h6 class="card-title mb-1"><?= e($item['title']) ?></h6>
                <span class="badge bg-<?= $item['status']==='available'?'success':($item['status']==='reserved'?'warning text-dark':($item['status']==='sold'?'secondary':'danger')) ?>">
                  <?= ucfirst($item['status']) ?>
                </span>
              </div>
              <div class="text-primary fw-bold mb-1"><?= money($item['price']) ?></div>
              <div class="small text-muted">
                <?= e($item['category']) ?> · <?= e($item['condition']) ?>
                <?php if ($item['course_code']): ?><br><i class="bi bi-journal-code"></i> <?= e($item['course_code']) ?><?php endif; ?>
                <?php if ($item['department']): ?><br><i class="bi bi-building"></i> <?= e($item['department']) ?><?php endif; ?>
              </div>
              <div class="small mt-2 text-muted">
                <i class="bi bi-person"></i> <?= e($item['seller_name']) ?>
              </div>
            </div>
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
