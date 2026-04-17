<?php
    $settings = parse_ini_file(__DIR__ . "/../settings.env", true);
    $database_settings = $settings['mysql'];

    $con = mysqli_connect(
        $database_settings['HOSTNAME'],
        $database_settings['USERNAME'],
        $database_settings['PASSWORD'],
        $database_settings['DATABASE']
    );
?>