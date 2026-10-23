<?php /** Package card. Expects $pkg (row from tbltourpackages). */ ?>
<article class="package-card">
  <div class="media">
    <img src="<?= e(package_image_url($pkg->PackageImage)) ?>" alt="<?= e($pkg->PackageName) ?>" loading="lazy" width="600" height="450">
    <?php if ($pkg->PackageType): ?><span class="type-badge"><?= e($pkg->PackageType) ?></span><?php endif; ?>
  </div>
  <div class="body">
    <h3><a href="package_details.php?pkgid=<?= (int) $pkg->PackageId ?>"><?= e($pkg->PackageName) ?></a></h3>
    <div class="package-meta">
      <?php if ($pkg->PackageLocation): ?><span><i class="bi bi-geo-alt"></i><?= e($pkg->PackageLocation) ?></span><?php endif; ?>
      <?php if (!empty($pkg->PackageDuration)): ?><span><i class="bi bi-clock"></i><?= e($pkg->PackageDuration) ?></span><?php endif; ?>
    </div>
    <p class="excerpt"><?= e($pkg->PackageFetures) ?></p>
    <div class="footer">
      <div class="price"><small>From</small><strong><?= e(format_price($pkg->PackagePrice)) ?></strong></div>
      <span class="link-arrow">View details <i class="bi bi-arrow-right"></i></span>
    </div>
  </div>
</article>
