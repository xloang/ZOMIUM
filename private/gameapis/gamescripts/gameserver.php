<?php
	use anorrl\Script;

	header("Content-Type: text/plain");

	$sc = new Script("gameserver");
	die($sc->sign());
?>
