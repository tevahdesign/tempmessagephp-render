<?php
// MUST BE FIRST — no whitespace before this line!
ob_start();
session_start();

// ===========================================
// ✅ JavaScript Verification Endpoint
// URL: /?verify_token=XXXX
// ===========================================
if (isset($_GET['verify_token'])) {
    if (!empty($_GET['verify_token'])) {
        $_SESSION['verified'] = true;
        echo "ok";
        exit;
    }
}

// =========================================================
// 🛡 ALWAYS ALLOW SEARCH ENGINE BOTS (Google safe)
// =========================================================
$userAgent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
$allowedBots = ['googlebot','bingbot','duckduckbot','yandexbot','adsbot-google'];
$isBot = false;
foreach ($allowedBots as $bot) {
    if (strpos($userAgent, $bot) !== false) { 
        $isBot = true; 
        break; 
    }
}

// Function to get real IP (used both in SG block + Tier1 block)
function getClientIP() {
    foreach (['HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ipList = explode(',', $_SERVER[$key]);
            return trim($ipList[0]);
        }
    }
    return '0.0.0.0';
}

// =========================================================
// 🚫 BLOCK SINGAPORE TRAFFIC (non-bot + non-verified)
// =========================================================
if (!$isBot && empty($_SESSION['verified'])) {

    $ip = getClientIP();
    $cacheFile = sys_get_temp_dir() . "/geo_{$ip}_sg.json";

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 86400) {
        $data = json_decode(file_get_contents($cacheFile), true);
    } else {
        $resp = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,countryCode");
        $data = $resp ? json_decode($resp, true) : null;
        if ($data) file_put_contents($cacheFile, json_encode($data));
    }

    if (($data['countryCode'] ?? null) === 'SG') {
        http_response_code(403);
        echo "<h1 style='text-align:center;margin-top:20vh;font-family:sans-serif;color:#444;'>Access Restricted</h1>
        <p style='text-align:center;font-family:sans-serif;'>Sorry, TempMessage.com is not available in your region.</p>";
        exit;
    }
}

// =========================================================
// 🌍 TIER-1 ONLY TRAFFIC BOOSTER (Google ALWAYS allowed)
// =========================================================
if (!$isBot) {

    $ip = getClientIP();
    $cacheFile = sys_get_temp_dir() . "/geo_{$ip}_tier1.json";

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 1800) { 
        $geo = json_decode(file_get_contents($cacheFile), true);
    } else {
        $resp = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,countryCode");
        $geo = $resp ? json_decode($resp, true) : null;
        if ($geo) file_put_contents($cacheFile, json_encode($geo));
    }

    // Allowed FULL Tier-1 countries
    $tier1 = [
        'US','GB','CA','AU','NZ','DE','FR','NL',
        'SE','CH','NO','DK','FI','BE','AT','IE'
    ];

    // ❌ Non-Tier1 humans → blocked
    // ✔ Bots → allowed full content
    if (!in_array($geo['countryCode'] ?? 'XX', $tier1)) {

        if ($isBot) {
            // Bot sees full content
        } else {
            // Human non-Tier1 blocked
            http_response_code(403);
            echo "<h1>Access Restricted</h1>";
            exit;
        }
    }
}

// =========================================================
// 🧩 Slug converter
// =========================================================
function makeSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

// =========================================================
// 🌐 Programmatic SEO Keyword System
// =========================================================
$domain = "https://tempmessage.com/";
$keywordsFile = __DIR__ . '/keywords.txt';

$keywordsList = file_exists($keywordsFile)
    ? file($keywordsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
    : [];

// =========================================================
// 📌 Extract slug from URL path
// =========================================================
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uriParts = explode('/', $uri);
$pathSlug = isset($uriParts[0]) ? $uriParts[0] : "";

// =========================================================
// 🌐 Keyword Selection
// =========================================================
if ($pathSlug !== "" && $pathSlug !== "index.php") {

    $slug = $pathSlug;
    $keyword = str_replace('-', ' ', $slug);

} else {

    if (!empty($keywordsList)) {
        $daySeed = date('Ymd');
        srand(crc32($daySeed));
        $keyword = $keywordsList[array_rand($keywordsList)];
    } else {
        $keyword = "Temporary Message Creator";
    }

    $slug = makeSlug($keyword);
}

$keyword = htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8');

// =========================================================
// 📌 Canonical Tag Logic
// =========================================================
if ($pathSlug !== "" && $pathSlug !== "index.php") {
    $canonical = $domain . $slug . '/';
} else {
    $canonical = $domain;
}

// =========================================================
// 🧠 UNIQUE CONTENT GENERATOR (Spintax Engine)
// =========================================================
function spinx($text) {
    return preg_replace_callback('/\{([^{}]+)\}/', function($m) {
        $parts = explode('|', $m[1]);
        return $parts[array_rand($parts)];
    }, $text);
}

function megaUnique($keyword) {
    $templates = [
        "{Using|With|Through} $keyword you {stay private|avoid spam|protect your identity|hide your inbox}.",
        "$keyword {autodeletes all emails|stores nothing|removes messages instantly|keeps no logs}.",
        "{Perfect|Ideal|Great|Useful} for {signups|OTP attempts|anonymous browsing|quick verification}.",
        "{No registration needed|Instant access|Zero setup|Completely anonymous} when using $keyword.",
        "$keyword is {fast|instant|quick|lightning-fast} and {secure|safe|private|encrypted}.",
        "{Temporary email|Disposable inbox|One-time email} with $keyword helps you stay {hidden|protected|off the radar}.",
        "{You can use|People rely on|Millions prefer} $keyword for {privacy|quick tasks|secure usage}.",
    ];

    shuffle($templates);
    $slice = array_slice($templates, 0, rand(3, 6));
    return spinx(implode(" ", $slice));
}

function buildFAQ($keyword) {
    return [
        [
            "q" => spinx("Is {it safe|it secure|it trusted} to use $keyword?"),
            "a" => spinx("$keyword is completely {safe|secure|private}. All emails are deleted {automatically|instantly|after use}.")
        ],
        [
            "q" => spinx("Can I use $keyword for {signups|verification|OTP}?"),
            "a" => spinx("Some platforms accept $keyword for OTP, while others {block temp mail|restrict disposable addresses}.")
        ],
        [
            "q" => spinx("How long do messages stay in $keyword?"),
            "a" => spinx("Messages remain {until refreshed|until session expires|for a short duration}.")
        ],
        [
            "q" => spinx("Do I need an account to use $keyword?"),
            "a" => spinx("No account is needed. $keyword is {anonymous|instant|registration-free}.")
        ]
    ];
}

$paragraph1 = megaUnique($keyword);
$paragraph2 = megaUnique($keyword);
$paragraph3 = megaUnique($keyword);
$faqList = buildFAQ($keyword);

// =========================================================
// 📝 Meta Tags
// =========================================================
$metaDescription = spinx(
    "{Get|Generate|Create} a {temporary email|disposable inbox|secure one-time email} with $keyword. ".
    "Fast, private, and fully anonymous."
);

$title = "$keyword — Free Temporary Email Service";

?>
<?php ob_end_flush(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Secure Temporary Message Tool – High RPM Version</title>

<!-- YOUR ADSENSE SCRIPT -->
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2885050972904135"
crossorigin="anonymous"></script>

<!-- SPEED BOOST -->
<link rel="preconnect" href="https://googleads.g.doubleclick.net">
<link rel="preconnect" href="https://tpc.googlesyndication.com">

<style>
body{font-family:Arial;margin:0;background:#f7f7f7;line-height:1.7;}
.wrapper{max-width:850px;margin:auto;padding:15px;background:white;}
h1,h2,h3{font-weight:bold;}
.ad-box{min-height:300px;margin:20px 0;background:#f1f1f1;border-radius:12px;}

#sticky-ad{
position:fixed;bottom:0;left:0;right:0;
background:white;padding:6px;
box-shadow:0 -3px 10px rgba(0,0,0,0.25);
z-index:9999;
}
</style>
</head>

<body>

<div class="wrapper">

    <h1><?php echo $keyword; ?></h1>
    <p>This tool lets you generate and manage secure temporary messages without storing personal information. Perfect for privacy, verification, and short-term communication.</p>

    <!-- ⭐ HEADER AD -->
    <div id="ad-header" class="ad-box"></div>

    <h2>Why People Use Temporary Messaging?</h2>
    <p>Temporary messaging tools have become essential for users who want to protect their identity online. Whether you're signing up for a new service, testing a platform, or avoiding spam, these one-time messages offer a fast and secure way to receive important information without revealing your personal contact details.</p>

    <!-- ⭐ IN-ARTICLE AD -->
    <div id="ad-inarticle" class="ad-box"></div>

    <h2>Top Benefits of Using a Temporary Message Tool</h2>
    <ul>
        <li><b>Privacy Protection:</b> Avoid exposing your real email or phone number.</li>
        <li><b>Spam Control:</b> Block unwanted newsletters, marketing emails, and promotions.</li>
        <li><b>Instant Use:</b> No sign-up required, messages appear automatically.</li>
        <li><b>Perfect for Testing:</b> Ideal for developers, QA testers, and automation workflows.</li>
    </ul>

    <h2>High-Value Topics (Why This Page Gets High RPM)</h2>
    <p>
    Google advertisers pay high CPC for:
    <br><br>
    🔥 <b>VPN services</b> – Up to $15 CPC (US, UK, CA)  
    <br>
    🔥 <b>Web hosting & cloud</b> – Up to $12 CPC  
    <br>
    🔥 <b>AI productivity tools</b> – Up to $8 CPC  
    <br>
    🔥 <b>Cybersecurity</b> – Up to $10 CPC  
    <br>
    🔥 <b>Finance & insurance</b> – Up to $20 CPC  
    <br><br>
    This content is written using these categories, so Google shows premium ads.
    </p>

    <!-- ⭐ MULTIPLEX AD -->
    <div id="ad-multiplex" class="ad-box"></div>

    <h2>How Secure Temporary Messages Work</h2>
    <p>Temporary messaging uses random, disposable identifiers that exist only for a short period. Messages are delivered instantly and deleted automatically after expiration, ensuring complete data protection. This technology is frequently used in cybersecurity and privacy tools.</p>

    <h3>Use Cases</h3>
    <ul>
        <li>Two-factor authentication testing</li>
        <li>Verifying new accounts safely</li>
        <li>Accessing geo-blocked platforms</li>
        <li>Bypassing spam-heavy websites</li>
        <li>Testing API systems and automation flows</li>
    </ul>

    <h2>Why You Should Always Use a VPN with Temporary Messaging</h2>
    <p>Privacy experts recommend using a <b>secure VPN service</b> while sending temporary messages. A VPN hides your IP address, encrypts your traffic, and protects you from tracking. Premium VPN companies pay VERY high CPC to appear next to content like this, which is why this section provides extremely high RPM.</p>

    <p><b>Recommended high-value topics:</b></p>
    <ul>
        <li>Best VPN for privacy</li>
        <li>Best cloud hosting providers</li>
        <li>Top AI tools for automation</li>
        <li>Affordable VPS and dedicated servers</li>
        <li>Best cybersecurity practices</li>
    </ul>

    <h2>Final Thoughts</h2>
    <p>Temporary messaging is an essential privacy tool today. Combine it with cybersecurity practices, VPNs, and secure browsing tools to stay safe online. This page layout is optimized to give you the <b>maximum possible RPM</b> using Google-approved, high-value content.</p>

</div>

<!-- ⭐ STICKY BOTTOM AD -->
<div id="sticky-ad">
    <ins class="adsbygoogle"
         style="display:block"
         data-ad-client="ca-pub-2885050972904135"
         data-ad-slot="3533469790"
         data-ad-format="auto"
         data-full-width-responsive="true"></ins>
</div>
<script>(adsbygoogle=window.adsbygoogle||[]).push({});</script>

<!-- ⭐ GEO + LAZY LOAD + PARALLEL QUEUE + SAFE REFRESH -->
<script>
window.adsbygoogle = window.adsbygoogle || [];

const TIER1 = ["US","GB","CA","AU","DE","SG","NL","FR","SE","DK","NO","NZ","CH","AE"];

/* load ad with lazy load */
function loadAd(id, slot, format="auto") {
    let el = document.getElementById(id);

    el.innerHTML = `
      <ins class="adsbygoogle"
           style="display:block"
           data-ad-client="ca-pub-2885050972904135"
           data-ad-slot="${slot}"
           data-ad-format="${format}"
           data-full-width-responsive="true"></ins>
    `;

    let obs = new IntersectionObserver(e=>{
        e.forEach(x=>{
            if(x.isIntersecting){
                adsbygoogle.push({});
                obs.unobserve(x.target);
            }
        });
    });
    obs.observe(el);
}

/* GEO targeting */
fetch("https://ipapi.co/country/")
.then(r=>r.text())
.then(code=>{
    code = code.trim();

    if(TIER1.includes(code)){
        loadAd("ad-header",     "3533469790", "auto");
        loadAd("ad-inarticle",  "3533469790", "inarticle");
        loadAd("ad-multiplex",  "3533469790", "autorelaxed");
    } else {
        loadAd("ad-header",     "3533469790");
        loadAd("ad-inarticle",  "3533469790", "auto");
        loadAd("ad-multiplex",  "3533469790", "autorelaxed");
    }
});

/* Safe Ad Refresh (55 sec) */
setInterval(()=>{
    document.querySelectorAll(".adsbygoogle").forEach(ad=>{
        let r = ad.getBoundingClientRect();
        if(r.top < innerHeight && r.bottom > 0){
            adsbygoogle.push({});
        }
    });
}, 55000);
</script>

</body>
    </html>
