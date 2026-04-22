<?php
	require_once __DIR__ . "/bootstrap.php";

	$database_config = $GLOBALS['__config']->database ?? null;
	$db_hostname = trim((string)($database_config->hostname ?? 'localhost'));
	$db_username = (string)($database_config->username ?? 'root');
	$db_password = (string)($database_config->password ?? '');
	$db_name = trim((string)($database_config->name ?? ''));
	$db_port = intval($database_config->port ?? 3306);

	if($db_name === '') {
		throw new RuntimeException("Database name is missing in settings.json.");
	}

	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$con = mysqli_init();
	mysqli_real_connect($con, $db_hostname, $db_username, $db_password, $db_name, $db_port);
	mysqli_set_charset($con, 'utf8mb4');
?>
