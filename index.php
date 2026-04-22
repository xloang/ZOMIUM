<?php
	// lifted from pixie - by parakeet

	require_once __DIR__ . "/private/bootstrap.php";

	require __DIR__ . "/vendor/autoload.php";

	use anorrl\utilities\UserUtils;
	use anorrl\Session;
	
	
	
	$session_user = UserUtils::RetrieveUser();

	if(session_status() != PHP_SESSION_ACTIVE) {
		session_start();
	}

	if($session_user != null) {
		define('SESSION', new Session($session_user));
	} else {
		define('SESSION', false);
	}

	$GLOBALS['__session'] = SESSION;
	
	require_once __DIR__ . "/router.php";

	exit();
?>
