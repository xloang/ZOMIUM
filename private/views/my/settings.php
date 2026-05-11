<?php
use anorrl\Page;
	use anorrl\User;
	use anorrl\Database;

    $page = new Page("Settings", "my/settings.php");
	$page->loadHeader(); 
    $page->addStylesheet("/css/new/main.css?v=3");
	$page->addScript("/js/main.js?t=1776250887");
?>


<h1>Settings</h1>
<hr>
<h3>This page is W.I.P this will be updated soon</h3>