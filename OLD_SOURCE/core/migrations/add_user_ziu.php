<?php
require_once __DIR__.'/../connection.php';
$con->query('ALTER TABLE `users` ADD `user_ziu` INT NOT NULL DEFAULT 100;');
if($con->error){
    echo "Error: ".$con->error;}
else{echo "Column added successfully";}
?>
