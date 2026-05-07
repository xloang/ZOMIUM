<?php
	if (!defined('CONFIG')) {
		$possible = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'settings.json';
		if (file_exists($possible)) {
			$cfg = json_decode(file_get_contents($possible));
			if ($cfg !== null) define('CONFIG', $cfg);
		}
	}

	$host = $user = $pass = $db = null;
	if (defined('CONFIG') && isset(CONFIG->database)) {
		$host = CONFIG->database->hostname ?? null;
		$user = CONFIG->database->username ?? null;
		$pass = CONFIG->database->password ?? null;
		$db   = CONFIG->database->name ?? null;
	}

	$host = $host ?? getenv('DB_HOST') ?: '127.0.0.1';
	$user = $user ?? getenv('DB_USER') ?: 'root';
	$pass = $pass ?? getenv('DB_PASS') ?: '';
	$db   = $db   ?? getenv('DB_NAME') ?: null;

	try {
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		$con = mysqli_connect($host, $user, $pass, $db);
	} catch (mysqli_sql_exception $ex) {
		error_log('MySQL connection error: ' . $ex->getMessage());
		die('Database connection error. Check server logs.');
	}
?>
