<?php
    
	$domain = $GLOBALS['__config']->domain;
	
    if(SESSION) {
		$user = $GLOBALS['__session']->user;
        exit("http://$domain/Login/Negotiate.ashx?suggest=".base64_encode($user->security_key));
    } else {
        die(http_response_code(401));
    }
?>