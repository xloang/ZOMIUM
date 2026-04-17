<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/userutils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/classes/comment.php';

function IsRewrite() {
    if(!empty($_SERVER['IIS_WasUrlRewritten'])) return true;
    else if(array_key_exists('HTTP_MOD_REWRITE',$_SERVER)) return true;
    else if(array_key_exists('REDIRECT_URL', $_SERVER)) return true;
    else return false;
}
if(!IsRewrite()) { die(header('Location: /my/home')); }
if(!isset($_GET['id'])) { die(header('Location: /my/home')); }
$get_user = User::FromID(intval($_GET['id']));
if($get_user == null) { die(header('Location: /my/home')); }
if($get_user->id == 1) { die(require $_SERVER['DOCUMENT_ROOT'].'/core/venturing.html'); }
if(isset($_GET['page'])) {
    if(intval($_GET['page']) == 1) {
        die(include($_SERVER['DOCUMENT_ROOT'].'/users/api/friends.php'));
    } else {
        header('Content-Type: application/json');
        die('{}');
    }
}
$user = UserUtils::RetrieveUser($get_user);
if($user == null) { die(header('Location: /login')); }
$header_data = $get_user;
$people = $get_user->GetFriends();
$title = $get_user->name."'s Friends";
require $_SERVER['DOCUMENT_ROOT'].'/core/ui/user_list_page.php';
renderUserListPage($title, $people, $get_user, $user);

