<?php
// Usage: /test_force_login.php?token=<security_key>
// Sets session token and cookie server-side to emulate a login.

session_start();

$settingsPath = __DIR__ . '/settings.json';
if (file_exists($settingsPath)) {
    $cfg = json_decode(file_get_contents($settingsPath));
    if ($cfg !== null && !defined('CONFIG')) define('CONFIG', $cfg);
}

$token = $_GET['token'] ?? null;
if (!$token) {
    echo "Provide ?token=...\n";
    exit;
}

// set session
$_SESSION['SESSION_TOKEN_YAA'] = $token;

// set cookie using same logic as UserUtils
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

@file_put_contents(__DIR__ . '/private/logs/login.log', date('c') . " FORCE_LOGIN token=" . substr($token,0,16) . "... (len=".strlen($token).")" . PHP_EOL, FILE_APPEND);

header('Location: /');
exit;
