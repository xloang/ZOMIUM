<?php
	$user = $GLOBALS['__session']->user;

	if(!$user->isAdmin()) {
		die("Hey... You're not an admin I don't think...");
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin panel</title>
</head>
<body>
	<h1>SOON</h1>
</body>
</html>