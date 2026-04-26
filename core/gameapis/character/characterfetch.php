<?php 
    ob_start();
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
header("Content-Type: text/plain"); 
    // dont cache this shit!
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    if(isset($_GET['assetId'])): 
    $host = $_SERVER['HTTP_HOST'] ?? 'zomium.xyz';
?>
http://<?= $host ?>/Asset/BodyColors.ashx?clothing;http://<?= $host ?>/asset/?id=<?= $_GET['assetId'] ?>
<?php 
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
else: 

require_once $_SERVER['DOCUMENT_ROOT']."/core/utilities/userutils.php";

$userId = intval($_GET['userId']) ?? 1;

$user = User::FromID($userId);

if($user == null) {
    $user = User::FromID(1);
    $userId = 1;
}

// clean leading whitespace
ob_clean();
die($user->GetCharacterAppearance());
endif ?>
