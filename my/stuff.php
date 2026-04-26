<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/userutils.php';
$user = UserUtils::RetrieveUser();

$admin = UserUtils::RetrieveUser(); // TODO: why bro why 
if ($admin == null || !$admin->IsAdmin()) {
    die(header('Location: /login'));
}


if($user == null) {
    die(header('Location: /login'));
}
?>
<!DOCTYPE html>
<html>
    <head>
        <?php
        $page_title = 'Your Stuff - Zomium';
        $page_scripts = ['/js/stuff.js?t=1771413807'];
        include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php';
        ?>
    </head>
    <body>
        <div class="Asset card border-0 text-center h-100" template>
            <a id="NameAndThumbs" href="javascript:void(0)">
                <img class="img-fluid mb-3" src="" alt="Asset thumb">
                <div id="Pricing" class="mb-3"></div>
                <span class="d-block fw-semibold">AssetName</span>
            </a>
            <a id="Creator" class="small d-block mt-2" href="javascript:void(0)"><span>AssetCreator</span></a>
        </div>
        <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/header.php'; ?>
        <main class="app-main">
            <div class="container">
                <div class="hero-panel p-4 p-lg-5 mb-4">
                    <h1 class="display-6 fw-bold mb-3"><marquee behavior="alternate" scrollamount="15">Inventory</marquee></h1>
                </div>
                <div id="StuffContainer" class="row g-4">
                    <div class="col-lg-3">
                        <div id="StuffNavigation" class="card border-0 h-100">
                            <div class="card-body p-4">
                                <div id="CreateArea" class="d-flex flex-wrap gap-2 mb-4">
                                    <a class="btn btn-primary btn-sm" href="/create/">Create</a>
                                    <a class="btn btn-outline-light btn-sm" href="/catalog">Shop</a>
                                </div>
                                <ul class="list-group list-group-flush">
                                    <!-- <li class="list-group-item" data_category="8"><a href="javascript:void(0)">Hats</a></li>
                                    <li class="list-group-item" data_category="18"><a href="javascript:void(0)">Faces</a></li> -->
                                    <li class="list-group-item" data_category="11"><a href="javascript:void(0)">Shirts</a></li>
                                    <li class="list-group-item" data_category="2"><a href="javascript:void(0)">T-Shirts</a></li>
                                    <li class="list-group-item" data_category="12"><a href="javascript:void(0)">Pants</a></li>
                                    <li class="list-group-item" data_category="3"><a href="javascript:void(0)">Audio</a></li>
                                    <li class="list-group-item" data_category="13"><a href="javascript:void(0)">Decals</a></li>
                                    <!--<li class="list-group-item" data_category="10"><a href="javascript:void(0)">Models</a></li> -->
                                    <li class="list-group-item" data_category="9"><a href="javascript:void(0)">Places</a></li>
                                   <!-- <li class="list-group-item" data_category="19"><a href="javascript:void(0)">Gears</a></li> -->
                                    <li class="list-group-item" data_category="21"><a href="javascript:void(0)">Badges</a></li>
                                    <li class="list-group-item" data_category="34"><a href="javascript:void(0)">Gamepasses</a></li>
                                  <!-- TODO: <li class="list-group-item" data_category="32"><a href="javascript:void(0)">Packages</a></li> -->
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9">
                        <div id="AssetsContainer" class="card border-0">
                            <div class="card-body p-4">
                                <div id="StatusText" class="mb-4">
                                    <b id="Loading" style="display:none">Loading assets...</b>
                                    <div id="NoAssets" class="empty-state" style="display:none">You have no <span id="AssetType"></span>.</div>
                                </div>
                                <table class="table table-borderless create-grid mb-0" hidden></table>
                                <div id="Paginator" class="d-flex flex-wrap align-items-center gap-2 mt-4" style="display:none">
                                    <a class="btn btn-outline-light btn-sm" href="javascript:ANORRL.Stuff.DeadvancePager()" id="PrevPager">Previous</a>
                                    <input class="form-control form-control-sm" maxlength="4" id="NumberPutter" style="width:84px;">
                                    <span class="text-secondary">of <span id="Pages">1</span></span>
                                    <a class="btn btn-outline-light btn-sm" href="javascript:ANORRL.Stuff.AdvancePager()" id="NextPager">Next</a>
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
