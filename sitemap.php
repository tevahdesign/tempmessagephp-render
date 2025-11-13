<?php
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '300');
ini_set('output_buffering', 'off');

header("Content-Type: application/xml; charset=utf-8");

$domain = "https://tempmessage.com/";
$keywordsFile = __DIR__ . '/keywords.txt';

// If file doesn't exist, show base URL only
if (!file_exists($keywordsFile)) {
    echo "<?xml version='1.0' encoding='UTF-8'?>\n";
    echo "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>\n";
    echo "<url><loc>{$domain}</loc></url>\n";
    echo "</urlset>";
    exit;
}

// Read file line-by-line (very fast, no heavy loading)
$handle = fopen($keywordsFile, "r");

echo "<?xml version='1.0' encoding='UTF-8'?>\n";
echo "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>\n";

$today = date('Y-m-d');

if ($handle) {
    while (($kw = fgets($handle)) !== false) {
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
    fclose($handle);
}

echo "</urlset>";
?>
