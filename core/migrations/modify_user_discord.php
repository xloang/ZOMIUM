<?php
require_once __DIR__.'/../connection.php';
$sql = "ALTER TABLE `users` MODIFY `user_discord` VARCHAR(256) NULL DEFAULT NULL;";
if($con->query($sql)===TRUE){
    echo "Column modified successfully";
} else {
    echo "Error: ".$con->error;
}
?>
