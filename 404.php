<?php
require_once __DIR__ . '/includes/bootstrap.php';
http_response_code(404);

$pageTitle = 'Page not found';
require __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="card-surface empty-state mx-auto" style="max-width: 560px">
      <span class="icon-badge"><i class="bi bi-signpost-split"></i></span>
      <h1 class="h2">Looks like you’ve wandered off the trail</h1>
      <p class="text-muted mb-4">The page you’re looking for doesn’t exist or may have moved.</p>
      <div class="d-flex gap-2 justify-content-center flex-wrap">
        <a class="btn btn-primary" href="index.php">Back to home</a>
        <a class="btn btn-outline-primary" href="packages.php">Browse packages</a>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
