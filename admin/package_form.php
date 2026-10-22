<?php
require_once __DIR__ . '/includes/auth.php';
$admin = require_admin();

$id = (int) ($_GET['id'] ?? 0);
$pkg = null;
if ($id) {
    $stmt = $dbh->prepare('SELECT * FROM tbltourpackages WHERE PackageId = ?');
    $stmt->execute([$id]);
    $pkg = $stmt->fetch();
    if (!$pkg) {
        flash('danger', 'Package not found.');
        redirect('manage_packages.php');
    }
}

$fields = ['packagename', 'packagetype', 'packagelocation', 'packageduration', 'packageprice', 'packagefeatures', 'packagedetails'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [];
    foreach ($fields as $field) {
        $data[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    $errors = [];
    if ($data['packagename'] === '' || mb_strlen($data['packagename']) > 200) {
        $errors[] = 'Package name is required (max 200 characters).';
    }
    if ($data['packagetype'] === '' || mb_strlen($data['packagetype']) > 150) {
        $errors[] = 'Package type is required.';
    }
    if ($data['packagelocation'] === '' || mb_strlen($data['packagelocation']) > 150) {
        $errors[] = 'Location is required.';
    }
    if (mb_strlen($data['packageduration']) > 50) {
        $errors[] = 'Duration must be 50 characters or fewer.';
    }
    if (!ctype_digit($data['packageprice']) || (int) $data['packageprice'] < 1) {
        $errors[] = 'Price must be a whole number greater than zero.';
    }
    if ($data['packagefeatures'] === '') {
        $errors[] = 'Please list at least one highlight.';
    }
    if (mb_strlen($data['packagedetails']) < 20) {
        $errors[] = 'Please write a description of at least 20 characters.';
    }

    $hasUpload = ($_FILES['packageimage']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    if (!$pkg && !$hasUpload) {
        $errors[] = 'Please upload a cover image.';
    }

    $image = $pkg->PackageImage ?? null;
    if (!$errors && $hasUpload) {
        try {
            $image = store_uploaded_image($_FILES['packageimage'], package_upload_dir());
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if ($errors) {
        remember_input($fields);
        flash('danger', implode(' ', $errors));
        redirect('package_form.php' . ($id ? '?id=' . $id : ''));
    }

    $params = [
        $data['packagename'], $data['packagetype'], $data['packagelocation'], $data['packageduration'] ?: null,
        (int) $data['packageprice'], $data['packagefeatures'], $data['packagedetails'], $image,
    ];

    if ($pkg) {
        $params[] = $id;
        $dbh->prepare('UPDATE tbltourpackages SET PackageName = ?, PackageType = ?, PackageLocation = ?, PackageDuration = ?, PackagePrice = ?, PackageFetures = ?, PackageDetails = ?, PackageImage = ? WHERE PackageId = ?')
            ->execute($params);
        // Remove the replaced image file.
        if ($hasUpload && $pkg->PackageImage && $pkg->PackageImage !== $image) {
            @unlink(package_upload_dir() . '/' . basename($pkg->PackageImage));
        }
        flash('success', 'Package updated.');
    } else {
        $dbh->prepare('INSERT INTO tbltourpackages (PackageName, PackageType, PackageLocation, PackageDuration, PackagePrice, PackageFetures, PackageDetails, PackageImage) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute($params);
        flash('success', 'Package created and published on the website.');
    }
    forget_input();
    redirect('manage_packages.php');
}

/** Old input after a failed submit, otherwise the stored value. */
$value = fn (string $field, ?string $stored) => isset($_SESSION['_old'][$field]) ? old($field) : e($stored);

$pageTitle = $pkg ? 'Edit package' : 'New package';
$activeNav = 'packages';
require __DIR__ . '/includes/layout_start.php';
?>
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="manage_packages.php">Packages</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= $pkg ? e($pkg->PackageName) : 'New package' ?></li>
  </ol>
</nav>

<form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
  <?= csrf_field() ?>
  <div class="row g-4">
    <div class="col-xl-8">
      <section class="panel">
        <div class="panel-header"><h2>Package details</h2></div>
        <div class="panel-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label" for="packagename">Package name</label>
              <input class="form-control" id="packagename" name="packagename" value="<?= $value('packagename', $pkg->PackageName ?? '') ?>" required maxlength="200">
              <div class="invalid-feedback">Package name is required.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="packagetype">Type</label>
              <input class="form-control" id="packagetype" name="packagetype" value="<?= $value('packagetype', $pkg->PackageType ?? '') ?>" required maxlength="150" placeholder="e.g. Safari, Adventure" list="type-suggestions">
              <datalist id="type-suggestions"><option value="Safari"><option value="Adventure"><option value="Discovery"><option value="Exploration"><option value="Expedition"><option value="Summit Expedition"><option value="Cultural"></datalist>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="packagelocation">Location</label>
              <input class="form-control" id="packagelocation" name="packagelocation" value="<?= $value('packagelocation', $pkg->PackageLocation ?? '') ?>" required maxlength="150" placeholder="e.g. Arusha, Tanzania">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="packageduration">Duration</label>
              <input class="form-control" id="packageduration" name="packageduration" value="<?= $value('packageduration', $pkg->PackageDuration ?? '') ?>" maxlength="50" placeholder="e.g. 3 days / 2 nights">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="packageprice">Price per person (USD)</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input class="form-control" type="number" id="packageprice" name="packageprice" value="<?= $value('packageprice', isset($pkg) ? (string) $pkg->PackagePrice : '') ?>" required min="1" step="1">
              </div>
            </div>
            <div class="col-12">
              <label class="form-label" for="packagefeatures">Highlights &amp; inclusions</label>
              <textarea class="form-control" id="packagefeatures" name="packagefeatures" rows="3" required><?= $value('packagefeatures', $pkg->PackageFetures ?? '') ?></textarea>
              <div class="form-text">Separate items with commas — each one is shown as a checklist item on the website.</div>
            </div>
            <div class="col-12">
              <label class="form-label" for="packagedetails">Description</label>
              <textarea class="form-control" id="packagedetails" name="packagedetails" rows="9" required minlength="20"><?= $value('packagedetails', $pkg->PackageDetails ?? '') ?></textarea>
              <div class="form-text">Leave a blank line between paragraphs.</div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <div class="col-xl-4">
      <section class="panel mb-4">
        <div class="panel-header"><h2>Cover image</h2></div>
        <div class="panel-body">
          <img id="image-preview" class="w-100 rounded-3 mb-3<?= $pkg ? '' : ' d-none' ?>" style="aspect-ratio: 4/3; object-fit: cover" src="<?= $pkg ? e(package_image_url($pkg->PackageImage)) : '' ?>" alt="Cover image preview">
          <label class="form-label" for="packageimage"><?= $pkg ? 'Replace image' : 'Upload image' ?></label>
          <input class="form-control" type="file" id="packageimage" name="packageimage" accept="image/jpeg,image/png,image/webp,image/gif" data-preview="#image-preview"<?= $pkg ? '' : ' required' ?>>
          <div class="form-text">JPG, PNG, WebP or GIF up to 5 MB. Landscape photos (4:3) look best.</div>
        </div>
      </section>
      <div class="d-grid gap-2">
        <button class="btn btn-primary btn-lg" type="submit"><?= $pkg ? 'Save changes' : 'Publish package' ?></button>
        <a class="btn btn-light" href="manage_packages.php">Cancel</a>
      </div>
    </div>
  </div>
</form>
<?php forget_input(); require __DIR__ . '/includes/layout_end.php'; ?>
