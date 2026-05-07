<?php
	use anorrl\Script;

	header("Content-Type: text/plain");

	$username = "Player";
	$userid = 1;
	$userage = 0;
	
	if(SESSION) {
		$user = SESSION->user;
		$username = $user->name;
		$userid = $user->id;
		$userage = $user->getAccountAge();
	}

$sc = new Script("visit");
die($sc->sign(
	[
		"userid" => $userid,
		"username" => $username,
		"accountage" => $userage
	]));
?>
