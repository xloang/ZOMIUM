<?php
	// lifted from pixie - by parakeet

	$candidates = [__DIR__ . DIRECTORY_SEPARATOR . 'settings.json', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'settings.json'];
	$settingsPath = null;
	foreach ($candidates as $p) {
		if (file_exists($p)) { $settingsPath = $p; break; }
	}
	if ($settingsPath === null) {
		die('Missing settings.json. Put a valid settings.json in the project root or in the web folder.');
	}
	$raw = file_get_contents($settingsPath);
	$decoded = json_decode($raw);
	if ($decoded === null) {
		die('Invalid settings.json: ' . json_last_error_msg());
	}
	define('CONFIG', $decoded);

	require __DIR__ . "/vendor/autoload.php";

	use anorrl\utilities\UserUtils;
	use anorrl\Session;
	
	if(isset(CONFIG->secret)) {
		if(isset($_GET[CONFIG->secret->partone]) && $_GET[CONFIG->secret->partone] == CONFIG->secret->parttwo) {
			setcookie('ANORRL$Hidden$Cookie$yaya', CONFIG->secret->token, time() + (460800* 30), "/", CONFIG->domain);
			die(header("Location: /register"));
		}
	}
	
	$session_user = UserUtils::RetrieveUser();

	if(session_status() != PHP_SESSION_ACTIVE) {
		session_start();
	}

	if($session_user != null) {
		define('SESSION', new Session($session_user));
	} else {
		define('SESSION', false);
	}
	
	require_once __DIR__ . "/router.php";

	exit();
?>