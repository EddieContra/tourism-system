<?php
require_once __DIR__ . '/includes/bootstrap.php';

$packages = $dbh->query('SELECT * FROM tbltourpackages ORDER BY Creationdate DESC LIMIT 6')->fetchAll();
$types = $dbh->query("SELECT DISTINCT PackageType FROM tbltourpackages WHERE PackageType <> '' ORDER BY PackageType")->fetchAll(PDO::FETCH_COLUMN);

$gallery = [
    ['twiga', 'Giraffes on the open savannah', 'tall'],
    ['zebra', 'Zebras crossing a dusty track', 'wide'],
    ['smb', 'A lioness resting in an acacia tree', ''],
    ['leopard', 'Leopard overlooking the plains', ''],
    ['chui', 'Wildebeest at a watering hole', ''],
    ['kibo', 'Kibo crater on Mount Kilimanjaro', ''],
    ['fm', 'A pride of lions in the grass', 'wide'],
    ['america1', 'Aerial view of a volcanic crater lake', 'wide'],
];

$pageDescription = 'Book curated safaris, Kilimanjaro treks and cultural tours across northern Tanzania with Five Star Tours, an Arusha-based tour operator.';
$activeNav = 'home';
$navOverlay = true;
$preloadImage = 'assets/img/hero.jpg';
require __DIR__ . '/includes/header.php';
?>

<section class="hero" style="background-image: url('assets/img/hero.jpg')">
  <div class="container">
    <span class="eyebrow"><i class="bi bi-geo-alt-fill"></i> Arusha · Northern Tanzania</span>
    <h1 class="mt-3 mb-4">Safaris you’ll remember for a lifetime</h1>
    <p class="lead mb-4">From the Serengeti migration to the summit of Kilimanjaro — hand-crafted journeys with local guides, comfortable lodges and secure online booking.</p>

    <form class="hero-search mt-4" action="packages.php" method="get" role="search">
      <div class="row g-0 align-items-center">
        <div class="col-md-6 field">
          <i class="bi bi-search" aria-hidden="true"></i>
          <label for="hero-q" class="visually-hidden">Destination or activity</label>
          <input id="hero-q" class="form-control" type="search" name="q" placeholder="Where do you want to go?">
        </div>
        <div class="col-md-3 field">
          <i class="bi bi-compass" aria-hidden="true"></i>
          <label for="hero-type" class="visually-hidden">Trip type</label>
          <select id="hero-type" class="form-select" name="type">
            <option value="">Any trip type</option>
            <?php foreach ($types as $type): ?>
              <option value="<?= e($type) ?>"><?= e($type) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3 p-1">
          <button class="btn btn-accent w-100 py-3" type="submit">Find tours</button>
        </div>
      </div>
    </form>
  </div>
</section>

<section class="feature-strip" aria-label="Why travel with us">
  <div class="container">
    <div class="row g-3">
      <?php foreach ([
          ['person-badge', 'Licensed local guides', 'Born and raised in Arusha.'],
          ['shield-check', 'Secure booking', 'Book online in a few clicks.'],
          ['calendar2-x', 'Flexible cancellation', 'Cancel up to 24 hours before.'],
          ['headset', 'Support when you need it', 'Real people, every day.'],
      ] as [$icon, $title, $text]): ?>
        <div class="col-6 col-lg-3">
          <div class="feature-card">
            <span class="icon-badge"><i class="bi bi-<?= $icon ?>"></i></span>
            <div><h3><?= $title ?></h3><p><?= $text ?></p></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" id="packages">
  <div class="container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 section-heading mw-100">
      <div style="max-width: 640px">
        <span class="eyebrow">Popular packages</span>
        <h2>Our most-loved adventures</h2>
        <p>Every trip includes park fees, an experienced driver-guide and hand-picked accommodation.</p>
      </div>
      <a href="packages.php" class="btn btn-outline-primary flex-shrink-0">View all packages <i class="bi bi-arrow-right ms-1"></i></a>
    </div>

    <?php if ($packages): ?>
      <div class="row g-4">
        <?php foreach ($packages as $pkg): ?>
          <div class="col-md-6 col-lg-4 reveal"><?php require __DIR__ . '/includes/partials/package_card.php'; ?></div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state card-surface"><p class="mb-0">New packages are coming soon. Please check back shortly.</p></div>
    <?php endif; ?>
  </div>
</section>

<section class="section bg-sand" id="about">
  <div class="container">
    <div class="row g-4 g-lg-5 align-items-center">
      <div class="col-lg-5 reveal">
        <div class="about-media">
          <img src="assets/img/about.jpg" alt="A group of travellers celebrating at the summit of Mount Kilimanjaro" loading="lazy">
          <div class="stamp"><strong>Based in Arusha</strong>The safari capital of Tanzania</div>
        </div>
      </div>
      <div class="col-lg-7 reveal">
        <span class="eyebrow">About us</span>
        <h2 class="display-6 mt-2 mb-4">Local experts in northern Tanzania’s wild places</h2>
        <p class="text-muted">Five Star Tours is a tour company based in Arusha, dedicated to showcasing the region’s natural beauty and rich cultural heritage. We offer a diverse range of carefully crafted packages — from exhilarating safaris and cultural immersions to luxurious retreats.</p>
        <p class="text-muted mb-4">Every journey is tailored to our travellers’ interests, and as advocates for sustainable tourism we prioritise conservation and community engagement in everything we do.</p>
        <ul class="check-list mb-4">
          <li><i class="bi bi-check-circle-fill"></i><span>Small groups and private departures with experienced driver-guides</span></li>
          <li><i class="bi bi-check-circle-fill"></i><span>Hand-picked lodges and tented camps inside or near the parks</span></li>
          <li><i class="bi bi-check-circle-fill"></i><span>Transparent pricing — park fees and transfers included</span></li>
        </ul>
        <a href="#contact" class="btn btn-primary">Plan your trip with us</a>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-heading mx-auto text-center">
      <span class="eyebrow">How it works</span>
      <h2>Booking your safari is easy</h2>
    </div>
    <div class="row g-4">
      <?php foreach ([
          ['Choose a package', 'Browse our tours and pick the adventure that fits your dates, budget and travel style.'],
          ['Request your dates', 'Create a free account, choose your travel dates and the number of travellers.'],
          ['Get confirmed & go', 'Our team confirms availability, and you can track or cancel bookings from your account.'],
      ] as $i => [$title, $text]): ?>
        <div class="col-md-4 reveal">
          <div class="step">
            <span class="num">0<?= $i + 1 ?></span>
            <h3><?= $title ?></h3>
            <p><?= $text ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="quote-band" style="background-image: url('assets/img/cta.jpg')">
  <div class="container">
    <blockquote>“The world is a book, and those who do not travel read only a page.”</blockquote>
    <p class="mb-4 text-white-50">— Saint Augustine</p>
    <a href="packages.php" class="btn btn-accent btn-lg">Start your journey</a>
  </div>
</section>

<section class="section" id="gallery">
  <div class="container">
    <div class="section-heading mx-auto text-center">
      <span class="eyebrow">Gallery</span>
      <h2>Moments from the wild</h2>
      <p>A glimpse of the wildlife and landscapes waiting for you in and around Arusha.</p>
    </div>
    <div class="gallery-grid">
      <?php foreach ($gallery as [$file, $alt, $size]): ?>
        <button type="button" class="gallery-item <?= $size ?> reveal" data-full="assets/img/gallery/<?= $file ?>.jpg" aria-label="Open photo: <?= e($alt) ?>">
          <img src="assets/img/gallery/thumbs/<?= $file ?>.jpg" alt="<?= e($alt) ?>" loading="lazy" decoding="async" width="800" height="533">
        </button>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<div class="modal fade lightbox" id="lightbox" tabindex="-1" aria-label="Photo viewer" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content position-relative">
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      <img src="" alt="" class="w-100">
      <button type="button" class="lightbox-nav prev" aria-label="Previous photo"><i class="bi bi-chevron-left"></i></button>
      <button type="button" class="lightbox-nav next" aria-label="Next photo"><i class="bi bi-chevron-right"></i></button>
      <p class="lightbox-caption text-center text-white mt-3 mb-0"></p>
    </div>
  </div>
</div>

<section class="section bg-sand" id="contact">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-7">
        <div class="card-surface h-100">
          <span class="eyebrow">Contact us</span>
          <h2 class="mt-2 mb-2">Let’s plan your trip</h2>
          <p class="text-muted mb-4">Questions about a package or a custom itinerary? Send us a message and we’ll get back to you within one business day.</p>
          <form action="contact.php" method="post" class="needs-validation" novalidate>
            <?= csrf_field() ?>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="c-name">Full name</label>
                <input class="form-control" id="c-name" name="name" value="<?= old('name') ?>" required maxlength="100" autocomplete="name">
                <div class="invalid-feedback">Please enter your name.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="c-email">Email</label>
                <input class="form-control" id="c-email" type="email" name="email" value="<?= old('email') ?>" required maxlength="150" autocomplete="email">
                <div class="invalid-feedback">Please enter a valid email address.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="c-phone">Phone <span class="text-muted fw-normal">(optional)</span></label>
                <input class="form-control" id="c-phone" type="tel" name="phone" value="<?= old('phone') ?>" maxlength="20" autocomplete="tel">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="c-subject">Subject</label>
                <input class="form-control" id="c-subject" name="subject" value="<?= old('subject') ?>" required maxlength="150">
                <div class="invalid-feedback">Please add a subject.</div>
              </div>
              <div class="col-12">
                <label class="form-label" for="c-message">Message</label>
                <textarea class="form-control" id="c-message" name="message" rows="5" required minlength="10" maxlength="2000"><?= old('message') ?></textarea>
                <div class="invalid-feedback">Please write a message (at least 10 characters).</div>
              </div>
              <div class="col-12">
                <button class="btn btn-primary btn-lg" type="submit"><i class="bi bi-send me-2"></i>Send message</button>
              </div>
            </div>
          </form>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="contact-card">
          <h3>Visit our office</h3>
          <p class="text-white-50 mb-0">Drop by for a coffee and let’s talk safari.</p>
          <div class="item"><i class="bi bi-geo-alt"></i><div><small>Address</small><?= e(COMPANY_ADDRESS) ?></div></div>
          <div class="item"><i class="bi bi-telephone"></i><div><small>Phone</small><a href="tel:<?= e(preg_replace('/\s+/', '', COMPANY_PHONE)) ?>"><?= e(COMPANY_PHONE) ?></a></div></div>
          <div class="item"><i class="bi bi-envelope"></i><div><small>Email</small><a href="mailto:<?= e(COMPANY_EMAIL) ?>"><?= e(COMPANY_EMAIL) ?></a></div></div>
          <div class="item"><i class="bi bi-clock"></i><div><small>Opening hours</small>Mon – Sat, 8:00 – 18:00 EAT</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php forget_input(); require __DIR__ . '/includes/footer.php'; ?>
