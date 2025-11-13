<?php
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '300');
ini_set('output_buffering', 'off');

header("Content-Type: application/xml; charset=utf-8");

$domain = "https://tempmessage.com/";
$keywordsFile = __DIR__ . '/keywords.txt';

// Slug function (same as index.php)
function makeSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

// If no keywords → only homepage in sitemap
if (!file_exists($keywordsFile)) {
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    echo "  <url>\n";
    echo "    <loc>{$domain}</loc>\n";
    echo "    <lastmod>".date("Y-m-d")."</lastmod>\n";
    echo "  </url>\n";
    echo "</urlset>";
    exit;
}

// Begin sitemap
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

$today = date('Y-m-d');
$lines = file($keywordsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Add homepage
echo "  <url>\n";
echo "    <loc>{$domain}</loc>\n";
echo "    <lastmod>{$today}</lastmod>\n";
echo "    <changefreq>daily</changefreq>\n";
echo "    <priority>1.0</priority>\n";
echo "  </url>\n";

// Add keyword pages with clean slugs
foreach ($lines as $kw) {
    $kw = trim($kw);
    if ($kw === '') continue;

    $slug = makeSlug($kw);
    $url = $domain . $slug . "/";

    echo "  <url>\n";
    echo "    <loc>{$url}</loc>\n";
    echo "    <lastmod>{$today}</lastmod>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>0.8</priority>\n";
    echo "  </url>\n";
}

// Close sitemap
echo "</urlset>";
?>
