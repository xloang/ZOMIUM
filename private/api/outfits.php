<?php

	header("Content-Type: application/json");

	if(SESSION) {
		$user = $GLOBALS['__session']->user;
		if(isset($_POST['create'])) {
			
		}
		else {

		}


	}
	else {
		die(json_encode(
			[
				"error" => true,
				"reason" => "User is not authorised!"
			]
		));
	}

?>