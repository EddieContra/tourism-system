<?php
require_once __DIR__ . '/includes/bootstrap.php';

$q    = trim((string) ($_GET['q'] ?? ''));
$type = trim((string) ($_GET['type'] ?? ''));
$sort = (string) ($_GET['sort'] ?? 'popular');

$sorts = [
    'popular'    => ['Recommended', 'Creationdate DESC'],
    'price_asc'  => ['Price: low to high', 'PackagePrice ASC'],
    'price_desc' => ['Price: high to low', 'PackagePrice DESC'],
    'name'       => ['Name A–Z', 'PackageName ASC'],
];
if (!isset($sorts[$sort])) {
    $sort = 'popular';
}

$where = [];
$params = [];
if ($q !== '') {
    $where[] = '(PackageName LIKE :q1 OR PackageLocation LIKE :q2 OR PackageFetures LIKE :q3 OR PackageDetails LIKE :q4)';
    $like = '%' . $q . '%';
    $params += [':q1' => $like, ':q2' => $like, ':q3' => $like, ':q4' => $like];
}
if ($type !== '') {
    $where[] = 'PackageType = :type';
    $params[':type'] = $type;
}

$sql = 'SELECT * FROM tbltourpackages'
    . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
    . ' ORDER BY ' . $sorts[$sort][1];
$stmt = $dbh->prepare($sql);
$stmt->execute($params);
$packages = $stmt->fetchAll();

$types = $dbh->query("SELECT DISTINCT PackageType FROM tbltourpackages WHERE PackageType <> '' ORDER BY PackageType")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Tour packages';
$pageDescription = 'Browse safaris, mountain treks and cultural tours across northern Tanzania.';
$activeNav = 'packages';
$navOverlay = true;
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero" style="background-image: url('assets/img/gallery/zebra.jpg')">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Packages</li>
      </ol>
    </nav>
    <h1>Tour packages</h1>
    <p class="lead text-white-50 mb-0">Find the Tanzania adventure that’s right for you.</p>
  </div>
</section>

<section class="section-sm">
  <div class="container">
    <form class="filter-bar mb-4" method="get" role="search">
      <div class="row g-2 align-items-end">
        <div class="col-md-5">
          <label class="form-label small" for="f-q">Search</label>
          <div class="input-icon">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input id="f-q" class="form-control" type="search" name="q" value="<?= e($q) ?>" placeholder="Destination, activity or park">
          </div>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label small" for="f-type">Trip type</label>
          <select id="f-type" class="form-select" name="type">
            <option value="">All types</option>
            <?php foreach ($types as $t): ?>
              <option value="<?= e($t) ?>"<?= $t === $type ? ' selected' : '' ?>><?= e($t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label small" for="f-sort">Sort by</label>
          <select id="f-sort" class="form-select" name="sort">
            <?php foreach ($sorts as $key => [$label]): ?>
              <option value="<?= $key ?>"<?= $key === $sort ? ' selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2 d-grid">
          <button class="btn btn-primary" type="submit">Apply</button>
        </div>
      </div>
    </form>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <p class="text-muted mb-0" aria-live="polite">
        <?= count($packages) ?> <?= count($packages) === 1 ? 'package' : 'packages' ?> found
        <?php if ($q !== ''): ?> for “<?= e($q) ?>”<?php endif; ?>
      </p>
      <?php if ($q !== '' || $type !== '' || $sort !== 'popular'): ?>
        <a href="packages.php" class="small">Clear filters</a>
      <?php endif; ?>
    </div>

    <?php if ($packages): ?>
      <div class="row g-4">
        <?php foreach ($packages as $pkg): ?>
          <div class="col-md-6 col-lg-4"><?php require __DIR__ . '/includes/partials/package_card.php'; ?></div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="card-surface empty-state">
        <span class="icon-badge"><i class="bi bi-binoculars"></i></span>
        <h2 class="h4">No packages match your search</h2>
        <p class="text-muted">Try a different keyword or trip type — or ask us for a custom itinerary.</p>
        <a href="packages.php" class="btn btn-outline-primary">See all packages</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
