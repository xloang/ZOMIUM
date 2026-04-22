<?php
	use anorrl\Place;

	header("Content-Type: application/json");

	$place_id = intval($universeId);

	$place = Place::FromID($place_id);
	$user = $GLOBALS['__session']->user;

	if($place != null && $user != null && ($user->id == $place->creator->id || $user->isAdmin())) {
		$place->enableTeamCreate();
	}
?>