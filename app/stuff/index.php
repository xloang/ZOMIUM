<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/userutils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/classes/user.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/utilutils.php';

$user = UserUtils::RetrieveUser();

if ($user == null || !$user->IsAdmin()) {
    http_response_code(404);
    require $_SERVER['DOCUMENT_ROOT'] . '/core/error/404.php';
    exit;
}

// this page is not gonna be used it just private

?>