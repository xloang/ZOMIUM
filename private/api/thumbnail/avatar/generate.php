<?php
	use anorrl\User;
	use anorrl\utilities\Thumbnail;
	
	header("Content-Type: application/json");

	if(!isset($_GET['for']))
		die(http_response_code(500));

	$user = User::FromID(intval($_GET['for']));

	if(!$user)
		die(http_response_code(500));

	$generated_shit = Thumbnail::Generate3D($user);

	if(!$generated_shit)
		die(http_response_code(500));

	exit(json_encode($generated_shit));
?>