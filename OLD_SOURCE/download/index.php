<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/userutils.php';
$user = UserUtils::RetrieveUser();

$admin = UserUtils::RetrieveUser(); // TODO: improve this page and make it public soon
if ($admin == null || !$admin->IsAdmin()) {
    die(header('Location: /login'));
}

if($user == null) {
    die(header('Location: /login'));
}

$randomclientsplashes = [
    'Now with 100% viruses!',
    'Download RIGHT NOW!',
    'Better than anything else',
    '0.00$, Awesome deal!'
];

$randomclientsplash = $randomclientsplashes[array_rand($randomclientsplashes)];
?>
<!DOCTYPE html>
<html>
    <head>
        <?php
        $page_title = 'Download - Zomium';
        include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php';
        ?>
    </head>
    <body>
        <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/header.php'; ?>
        <main class="app-main">
            <div class="container">
                <div class="hero-panel p-4 p-lg-5 mb-4">
                    <div class="section-title mb-3">Client Downloads</div>
                    <h1 class="display-6 fw-bold mb-3"><?= htmlspecialchars($randomclientsplash, ENT_QUOTES, 'UTF-8') ?></h1>
                    <p class="lead text-secondary mb-0">Windows-first launchers for the Zomium client and studio. Wine may work on Linux. Mac builds are not ready yet.</p>
                </div>
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card border-0 h-100">
                            <div class="card-body p-4">
                                <div class="section-title mb-2">Clients</div>
                                <h2 class="h4 mb-4">Player launchers</h2>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <a class="card border-0 h-100 text-center lift-on-hover" href="2016/ANORRLPlayerLauncher.exe">
                                            <div class="card-body p-4">
                                                <img class="img-fluid mb-3" src="/images/download/2016client.png" alt="2016 client">
                                                <div class="fw-semibold">2016 Client</div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-sm-6">
                                        <a class="card border-0 h-100 text-center lift-on-hover" href="2013/ANORRL2013PlayerLauncher.exe">
                                            <div class="card-body p-4">
                                                <img class="img-fluid mb-3" src="/images/download/2013client.png" alt="2013 client">
                                                <div class="fw-semibold">2013 Client</div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card border-0 h-100">
                            <div class="card-body p-4">
                                <div class="section-title mb-2">Studio</div>
                                <h2 class="h4 mb-4">Builder launchers</h2>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <a class="card border-0 h-100 text-center lift-on-hover" href="2016/ANORRLStudioLauncher.exe">
                                            <div class="card-body p-4">
                                                <img class="img-fluid mb-3" src="/images/download/2016studio.png" alt="2016 studio">
                                                <div class="fw-semibold">2016 Studio</div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-sm-6">
                                        <a class="card border-0 h-100 text-center lift-on-hover" href="2013/ANORRL2013StudioLauncher.exe">
                                            <div class="card-body p-4">
                                                <img class="img-fluid mb-3" src="/images/download/2013studio.png" alt="2013 studio">
                                                <div class="fw-semibold">2013 Studio</div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/footer.php'; ?>
    </body>
</html>
