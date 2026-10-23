</main>

<footer class="site-footer">
  <div class="container">
    <div class="row g-4 g-lg-5">
      <div class="col-lg-4">
        <a class="navbar-brand mb-3" href="index.php">
          <span class="brand-mark" aria-hidden="true"><i class="bi bi-compass"></i></span>
          Five Star Tours
        </a>
        <p class="mb-0">Curated safaris, mountain treks and cultural journeys across northern Tanzania — planned by local experts based in Arusha.</p>
      </div>
      <div class="col-6 col-lg-2">
        <h2>Explore</h2>
        <ul>
          <li><a href="packages.php">All packages</a></li>
          <li><a href="index.php#about">About us</a></li>
          <li><a href="index.php#gallery">Gallery</a></li>
          <li><a href="index.php#contact">Contact</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h2>Account</h2>
        <ul>
          <?php if (user_logged_in()): ?>
            <li><a href="tour_history.php">My bookings</a></li>
            <li><a href="profile.php">Profile</a></li>
          <?php else: ?>
            <li><a href="login.php">Sign in</a></li>
            <li><a href="register.php">Create account</a></li>
          <?php endif; ?>
          <li><a href="admin/">Staff portal</a></li>
        </ul>
      </div>
      <div class="col-lg-4">
        <h2>Get in touch</h2>
        <ul>
          <li><i class="bi bi-geo-alt me-2"></i><?= e(COMPANY_ADDRESS) ?></li>
          <li><i class="bi bi-telephone me-2"></i><a href="tel:<?= e(preg_replace('/\s+/', '', COMPANY_PHONE)) ?>"><?= e(COMPANY_PHONE) ?></a></li>
          <li><i class="bi bi-envelope me-2"></i><a href="mailto:<?= e(COMPANY_EMAIL) ?>"><?= e(COMPANY_EMAIL) ?></a></li>
        </ul>
      </div>
    </div>
    <div class="bottom d-flex flex-column flex-md-row justify-content-between gap-2">
      <span>&copy; <?= date('Y') ?> Five Star Tours Company. All rights reserved.</span>
      <span>Arusha · Tanzania</span>
    </div>
  </div>
</footer>

<button class="back-to-top" type="button" aria-label="Back to top"><i class="bi bi-arrow-up"></i></button>

<script src="assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
