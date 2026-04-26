<?php
require_once __DIR__.'/../connection.php';
$res = $con->query('SHOW COLUMNS FROM `users`');
while($row = $res->fetch_assoc()){
    echo $row['Field']." | Null: ".$row['Null']." | Default: ".$row['Default']."\n";
}
?>
