<?php
// load settings.json as CONFIG
$settingsPath = __DIR__ . '/../settings.json';
if (!file_exists($settingsPath)) {
	echo "Missing settings.json\n";
	exit(1);
}
$cfg = json_decode(file_get_contents($settingsPath));
if ($cfg === null) {
	echo "Invalid settings.json\n";
	exit(1);
}
define('CONFIG', $cfg);

require __DIR__ . '/../private/lib/anorrl/Database.php';
require __DIR__ . '/../private/lib/anorrl/User.php';
require __DIR__ . '/../private/lib/anorrl/utilities/UserUtils.php';

// provide a username/password to test via CLI args or default
// support: php tools/test_login.php --list  OR  php tools/test_login.php username password
if (isset($argv[1]) && $argv[1] === '--list') {
	$db = anorrl\Database::singleton();
	$rows = $db->run('SELECT id, name FROM users LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
	echo "First users:\n";
	print_r($rows);
	exit(0);
}
if (isset($argv[1]) && $argv[1] === '--show' && isset($argv[2])) {
	$name = $argv[2];
	$db = anorrl\Database::singleton();
	$row = $db->run('SELECT * FROM users WHERE name = :name', [':name' => $name])->fetch(PDO::FETCH_ASSOC);
	print_r($row);
	exit(0);
}

$username = $argv[1] ?? 'admin';
$password = $argv[2] ?? 'password';

echo "Testing login for: $username\n";
$res = anorrl\utilities\UserUtils::LoginUser($username, $password);
var_export($res);
echo "\nLogs (last 50 lines):\n";
echo (file_exists(__DIR__ . '/../private/logs/login.log') ? shell_exec('powershell -command "Get-Content -Path \"' . __DIR__ . '/../private/logs/login.log\" -Tail 50 | Out-String"') : 'no log');
