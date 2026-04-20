<?php
session_start();
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/userutils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/assetuploader.php';
$user = UserUtils::RetrieveUser();

if ($user == null) {
    die(header('Location: /'));
}


$type = 'none';
if(isset($_GET['type'])) {
    $type = trim(strtolower($_GET['type']));
}

$validtypes = ['faces','shirts','tshirts','pants','audio','decals','models','gears','meshes','images','lua','hats','animations'];
$types = ['faces' => AssetType::FACE,'shirts' => AssetType::SHIRT,'tshirts' => AssetType::TSHIRT,'pants' => AssetType::PANTS,'audio' => AssetType::AUDIO,'decals' => AssetType::DECAL,'models' => AssetType::MODEL,'gears' => AssetType::GEAR,'meshes' => AssetType::MESH,'images' => AssetType::IMAGE,'lua' => AssetType::LUA,'hats' => AssetType::HAT,'animations' => AssetType::ANIMATION];

if(count($_POST) != 0) {
    if(in_array($type, $validtypes)) {
        if(isset($_POST['ANORRL$CreateAsset$Name']) && isset($_POST['ANORRL$CreateAsset$Description']) && isset($_FILES['ZOMIUM$CreateAsset$File']) && isset($_POST['ANORRL$CreateAsset$Year'])) {
            $name = trim($_POST['ANORRL$CreateAsset$Name']);
            $description = trim($_POST['ANORRL$CreateAsset$Description']);
            $public = isset($_POST['ANORRL$CreateAsset$Public']);
            $comments_enabled = isset($_POST['ANORRL$CreateAsset$CommentsEnabled']);
            $on_sale = isset($_POST['ANORRL$CreateAsset$OnSale']);
            $int_year = intval($_POST['ANORRL$CreateAsset$Year']);
            if($int_year < 0 || $int_year > 3) { $int_year = 0; }
            $year = AssetYear::index($int_year);
            $result = AssetUploader::UploadAsset($_FILES['ZOMIUM$CreateAsset$File'], $types[$type], $name, $description, $year, $public, $on_sale, $comments_enabled);
            if(isset($result)) {
                if($result['error']) {
                    $_SESSION['ANORRL$CreateAsset$Error'] = true;
                    $_SESSION['ANORRL$CreateAsset$Result'] = $result['reason'];
                } else {
                    $_SESSION['ANORRL$CreateAsset$Error'] = false;
                    $_SESSION['ANORRL$CreateAsset$Result'] = $result['id'];
                }
                die(header('Location: /create/'.$type));
            }
        }
    } else {
        die('Not valid type...');
    }
}

if($user == null) {
    die(header('Location: /'));
}
?>
<!DOCTYPE html>
<html>
    <head>
        <?php
        $page_title = 'Create - Zomium';
        $page_scripts = ['/js/create.js?t=1771701183'];
        include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php';
        ?>
    </head>
    <body>
        <div class="Asset card border-0 text-center h-100" template>
            <a id="NameAndThumbs" href="javascript:void(0)">
                <img class="img-fluid mb-3" src="" alt="Asset thumb">
                <div id="Pricing"></div>
                <span class="d-block fw-semibold">AssetName</span>
            </a>
        </div>
        <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/header.php'; ?>
        <main class="app-main">
            <div class="container">
                <div class="hero-panel p-4 p-lg-5 mb-4">
                    <h1 class="display-6 fw-bold mb-3">Upload and manage assets (Admin Only page btw)</h1>
                </div>
                <div id="StuffContainer" class="row g-4">
                    <div class="col-lg-3">
                        <div id="StuffNavigation" class="card border-0 h-100">
                            <div class="card-body p-4">
                                <h2 class="h5 mb-3">Categories</h2>
                                <ul class="list-group list-group-flush">
                                     <li class="list-group-item" data_category="11"><a href="javascript:void(0)">Shirts</a></li>
                                    <li class="list-group-item" data_category="2"><a href="javascript:void(0)">T-Shirts</a></li>
                                    <li class="list-group-item" data_category="11"><a href="javascript:void(0)">Shirts</a></li>
                                    <li class="list-group-item" data_category="2"><a href="javascript:void(0)">T-Shirts</a></li>
                                    <li class="list-group-item" data_category="12"><a href="javascript:void(0)">Pants</a></li>
                                    <li class="list-group-item" data_category="3"><a href="javascript:void(0)">Audio</a></li>
                                    <li class="list-group-item" data_category="13"><a href="javascript:void(0)">Decals</a></li>
                                    <li class="list-group-item" data_category="10"><a href="javascript:void(0)">Models</a></li>  <!-- yes. -->
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9">
                        <div id="CreationPanel" class="d-grid gap-4">
                            <div id="UploadPanel" class="card border-0">
                                <div class="card-body p-4">
                                    <h2 class="h4 mb-3">Upload <span id="TypaLabel"></span></h2>
                                    <?php if(isset($_SESSION['ANORRL$CreateAsset$Error'], $_SESSION['ANORRL$CreateAsset$Result'])): ?>
                                        <?php if($_SESSION['ANORRL$CreateAsset$Error']): ?>
                                        <div id="ErrorTime" class="alert alert-danger">Error: <span id="Message"><?= $_SESSION['ANORRL$CreateAsset$Result'] ?></span></div>
                                        <?php else: ?>
                                        <div id="SuccessTime" class="alert alert-success">Success <span id="Message"><?= 'Check it out <a href="/'.Asset::FromID($_SESSION['ANORRL$CreateAsset$Result'])->GetURLTitle().'-item?id='.$_SESSION['ANORRL$CreateAsset$Result'].'">here</a>.' ?></span></div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <form method="POST" enctype="multipart/form-data" class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Name</label>
                                            <input class="form-control" type="text" name="ANORRL$CreateAsset$Name" minlength="3" maxlength="100" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">File</label>
                                            <input class="form-control" id="files" type="file" name="ZOMIUM$CreateAsset$File" required>
                                            <div class="small mt-2" id="filename">No file chosen</div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="ANORRL$CreateAsset$Description" maxlength="1000" rows="4"></textarea>
                                        </div>
                                        <div class="col-md-4 form-check ms-2">
                                            <input class="form-check-input" name="ANORRL$CreateAsset$Public" type="checkbox" checked>
                                            <label class="form-check-label">Public</label>
                                        </div>
                                        <div class="col-md-4 form-check ms-2">
                                            <input class="form-check-input" name="ANORRL$CreateAsset$CommentsEnabled" type="checkbox" checked>
                                            <label class="form-check-label">Comments</label>
                                        </div>
                                        <div class="col-md-4 form-check ms-2">
                                            <input class="form-check-input" name="ANORRL$CreateAsset$OnSale" type="checkbox">
                                            <label class="form-check-label">On Sale</label>
                                        </div>
                                        <div class="col-md-4" id="AssetYear">
                                            <label class="form-label">For Client</label>
                                            <select class="form-select" name="ANORRL$CreateAsset$Year">
                                                <option value="0">Any</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <button class="btn btn-primary" type="submit" name="ANORRL$CreateAsset$Submit" onclick="$(this).attr('disabled', 'true'); document.forms[0].submit()">Upload</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div id="AssetsContainer" class="card border-0">
                                <div class="card-body p-4">
                                    <div id="StatusText" class="mb-4">
                                        <b id="Loading" style="display:none">Loading assets...</b>
                                        <div id="NoAssets" class="empty-state" style="display:none">You have no <span id="AssetType"></span>.</div>
                                    </div>
                                    <table class="table table-borderless create-grid mb-0" hidden></table>
                                    <div id="Paginator" class="d-flex flex-wrap align-items-center gap-2 mt-4" style="display:none">
                                        <a class="btn btn-outline-light btn-sm" href="javascript:ANORRL.Create.DeadvancePager()" id="PrevPager">Previous</a>
                                        <span class="text-secondary">Page</span>
                                        <input class="form-control form-control-sm" maxlength="4" style="width:84px;">
                                        <span class="text-secondary">of <span id="Pages">1</span></span>
                                        <a class="btn btn-outline-light btn-sm" href="javascript:ANORRL.Create.AdvancePager()" id="NextPager">Next</a>
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
<?php
unset($_SESSION['ANORRL$CreateAsset$Error']);
unset($_SESSION['ANORRL$CreateAsset$Result']);
?>
