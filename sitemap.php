<?php
/** XML sitemap for search engines (also served as /sitemap.xml via .htaccess). */
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/xml; charset=UTF-8');

$urls = [
    ['loc' => app_url('index.php'), 'lastmod' => null],
    ['loc' => app_url('packages.php'), 'lastmod' => null],
];
foreach ($dbh->query('SELECT PackageId, COALESCE(UpdationDate, Creationdate) AS changed FROM tbltourpackages ORDER BY PackageId') as $pkg) {
    $urls[] = [
        'loc'     => app_url('package_details.php?pkgid=' . (int) $pkg->PackageId),
        'lastmod' => $pkg->changed ? date('Y-m-d', strtotime($pkg->changed)) : null,
    ];
}

echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', "\n";
foreach ($urls as $url) {
    echo '  <url><loc>', e($url['loc']), '</loc>', $url['lastmod'] ? '<lastmod>' . $url['lastmod'] . '</lastmod>' : '', "</url>\n";
}
echo "</urlset>\n";
