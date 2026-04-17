<?php
$_SERVER['DOCUMENT_ROOT'] = __DIR__;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/core/classes/user.php";
require_once __DIR__ . "/core/classes/renderer.php";
require_once __DIR__ . "/core/classes/asset.php";
require_once __DIR__ . "/core/classes/assettype.php";
$data = TheFuckingRenderer::RenderPlayer(1);
var_dump($data);
?>
