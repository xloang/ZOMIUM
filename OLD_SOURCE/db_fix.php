<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/connection.php';

// Hata ayıklamayı açalım
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // 1. ZIU kolonlarını ekle (eğer yoksa DB hatası verir ama ignore edeceğiz/try catch içinde)
    try {
        $con->query("ALTER TABLE `users` ADD COLUMN `user_ziu` int(11) NOT NULL DEFAULT 0");
        echo "user_ziu added.<br>";
    } catch(Exception $e) {
        echo "user_ziu may already exist: " . $e->getMessage() . "<br>";
    }

    try {
        $con->query("ALTER TABLE `users` ADD COLUMN `user_ziu_last_daily` timestamp NULL DEFAULT NULL");
        echo "user_ziu_last_daily added.<br>";
    } catch(Exception $e) {
        echo "user_ziu_last_daily may already exist: " . $e->getMessage() . "<br>";
    }

    // 2. User ID 1'e Admin yetkisi ver (profilebadges tablosunda badge_id 1 = admin)
    // Önce var mı diye bakalım
    $res = $con->query("SELECT * FROM `profilebadges` WHERE `badge_userid` = 1 AND `badge_id` = 1");
    if($res->num_rows == 0) {
        $con->query("INSERT INTO `profilebadges`(`badge_id`, `badge_userid`, `badge_admincorecore`) VALUES (1, 1, 0)");
        echo "Admin badge given to user ID 1.<br>";
    } else {
        echo "User ID 1 is already an Admin.<br>";
    }

    echo "<b>All tasks completed!</b>";

} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage();
}
