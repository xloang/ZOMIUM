<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/userutils.php';
$user = UserUtils::RetrieveUser();
if($user == null) { die(header('Location: /login')); }
?>
<!DOCTYPE html>
<html>
    <head>
        <?php
        $page_title = 'Installed - Zomium';
        include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php';
        ?>
    </head>
    <body>
        <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/header.php'; ?>
        <main class="app-main">
            <div class="container">
                <div class="card border-0">
                    <div class="card-body p-5 text-center">
                        <div class="section-title mb-3">Launcher</div>
                        <h1 class="h2 mb-3">Thanks for installing</h1>
                        <p class="text-secondary mb-4">The client is ready. If Windows Defender gets dramatic, that is between you and Windows Defender.</p>
                        <a class="btn btn-primary" href="/download">Back to Downloads</a>
                    </div>
                </div>
            </div>
        </main>
        <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/footer.php'; ?>
    </body>
</html>
