<?php
	require_once $_SERVER['DOCUMENT_ROOT']."/core/utilities/userutils.php";

	$user = User::FromID(1);

	if(isset($_GET['userid'])) {
		$user = User::FromID(intval($_GET['userid']));
	}

	if($user == null) {
		$user = User::FromID(1);
	}

	if($user->setprofilepicture) {
		die(json_encode([
			"Final" => true,
			"Url" => "http://zomium.xyz/thumbs/profile?id=".$user->id."&nocompress",
			"RetryUrl" => "http://zomium.xyz/thumbs/profile?id=".$user->id."&nocompress",
		]));
	} else {
		die(json_encode([
			"Final" => true,
			"Url" => "http://zomium.xyz/thumbs/headshot?id=".$user->id."&nocompress",
			"RetryUrl" => "http://zomium.xyz/thumbs/headshot?id=".$user->id."&nocompress",
		]));
	}

?>