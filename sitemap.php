<?php
header("Content-Type: application/xml; charset=utf-8");

$domain = "https://tempmessage.com/";
$keywordsFile = __DIR__ . '/keywords.txt';

if (!file_exists($keywordsFile)) {
    echo "<?xml version='1.0' encoding='UTF-8'?>\n";
    echo "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>\n";
    echo "<url><loc>{$domain}</loc></url>\n";
    echo "</urlset>";
    exit;
}

$keywords = file($keywordsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

echo "<?xml version='1.0' encoding='UTF-8'?>\n";
echo "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>\n";

$today = date('Y-m-d');
foreach ($keywords as $kw) {
    $kw = trim($kw);
    if ($kw === '') continue;
    $url = htmlspecialchars($domain . '?q=' . urlencode($kw), ENT_QUOTES, 'UTF-8');
    echo "  <url>\n";
    echo "    <loc>{$url}</loc>\n";
    echo "    <lastmod>{$today}</lastmod>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>0.8</priority>\n";
    echo "  </url>\n";
}
echo "</urlset>";
?>
