<?php
// Simple tester to set ANORRLSECURITY cookie for debugging login.
// Usage: /test_set_cookie.php?token=<security_key>

// load settings
$settingsPath = __DIR__ . '/settings.json';
if (file_exists($settingsPath)) {
    $cfg = json_decode(file_get_contents($settingsPath));
    if ($cfg !== null) define('CONFIG', $cfg);
}

$token = $_GET['token'] ?? null;
if (!$token) {
    echo "Provide ?token=...\n";
    exit;
}

$domain = null;
if(defined('CONFIG') && isset(CONFIG->domain)) {
    $domain = CONFIG->domain;
} elseif (!empty($_SERVER['HTTP_HOST'])) {
    $domain = $_SERVER['HTTP_HOST'];
} elseif (!empty($_SERVER['SERVER_NAME'])) {
    $domain = $_SERVER['SERVER_NAME'];
}

if ($domain !== null && ($domain === 'localhost' || strpos($domain, ':') !== false)) {
    setcookie("ANORRLSECURITY", $token, time() + (460800* 30), "/", $domain);
} else {
    setcookie("ANORRLSECURITY", $token, time() + (460800* 30), "/", $domain ? ".".$domain : "");
}

header('Location: /');
exit;
