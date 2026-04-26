<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/userutils.php';
$user = UserUtils::RetrieveUser();

if($user == null) {
    die(header('Location: /login'));
}

$randomcatalogsplash = 'Catalog';
?>
<!DOCTYPE html>
<html>
<head>
    <?php
    $page_title = 'Catalog - Zomium';
    $page_scripts = ['/js/catalog.js?t=1771413807'];
    include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php';
    ?>
    <style>
        body {
            background: #0f0f10 !important;
            background-image: none !important;
        }

        .catalog-shell {
            color: #f2f4f8;
        }

        .catalog-title {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 1.5rem;
        }

        .catalog-panel,
        .catalog-search,
        .asset-card,
        .asset-thumb-container,
        .catalog-chip,
        #Paginator .form-control {
            background: #18181b !important;
            border: 1px solid rgba(255,255,255,.08) !important;
        }

        .catalog-panel {
            border-radius: 0;
            overflow: hidden;
            background: #18181b !important;
        }

        .filter-title {
            color: #f1f3f6;
            font-size: 1rem;
            font-weight: 700;
            text-transform: lowercase;
            padding: 1rem 1.1rem;
            border-bottom: 1px solid rgba(255,255,255,.06);
            background: #17171a;
            text-align: left;
        }

        .filter-list {
            list-style: none;
            padding: 1rem;
            margin: 0;
            background: #18181b;
        }

        .filter-list li {
            display: block;
            width: 100%;
            padding: .78rem .95rem;
            margin-bottom: .45rem;
            border-radius: .2rem;
            background: linear-gradient(180deg, #161617 0%, #161617 100%);
            color: #f3f5f8;
            cursor: pointer;
            transition: background-color .15s ease, border-color .15s ease, color .15s ease;
            text-transform: lowercase;
            text-align: center;
        }

        .filter-list li.active,
        .filter-list li[selected] {
            background: linear-gradient(180deg, #4d8fe8 0%, #4d8fe8 100%);
        }

        .filter-list li:hover {
            color: #fff;
            background: linear-gradient(180deg, #4d8fe8 0%, #4d8fe8 100%);
            border-color: rgba(255,255,255,.08);
            text-decoration: none;
        }

        .catalog-search {
            border-radius: .35rem;
            overflow: hidden;
        }

        .catalog-search .form-control,
        .catalog-search .btn {
            background: transparent !important;
            border: 0 !important;
            color: #eef2f6 !important;
            box-shadow: none !important;
        }

        .catalog-search .form-control::placeholder {
            color: #8f97a3;
        }

        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.25rem;
        }

        .asset-card {
            border-radius: .35rem;
            overflow: hidden;
            min-height: 100%;
            box-shadow: none !important;
        }

        .asset-card-link {
            display: flex;
            flex-direction: column;
            min-height: 100%;
            color: inherit;
            text-decoration: none;
        }

        .asset-thumb-container {
            position: relative;
            aspect-ratio: 1 / 1;
            overflow: hidden;
            border-bottom: 1px solid rgba(255,255,255,.08);
            padding: 1.35rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .asset-thumb-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
            background: #111;
        }

        .asset-card .card-body {
            background: #18181b !important;
        }

        .asset-card a,
        .asset-card .text-secondary,
        .asset-card .text-muted,
        .catalog-chip,
        .catalog-item-title {
            color: #eef2f7 !important;
        }

        .asset-card a:hover:not(.btn) {
            text-decoration: underline !important;
            text-decoration-color: #6fb7ff !important;
            text-decoration-thickness: 2px;
            text-underline-offset: .28rem;
        }

        .catalog-flag {
            position: absolute;
            top: .55rem;
            right: .55rem;
            background: #2b2d33;
            color: #fff;
            font-size: .72rem;
            font-weight: 700;
            padding: .16rem .45rem;
            border-radius: .2rem;
            line-height: 1.2;
        }

        .catalog-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .2rem .5rem;
            border-radius: .3rem;
            font-size: .82rem;
            border: 1px solid rgba(255,255,255,.08);
        }

        .catalog-item-title {
            font-weight: 700;
            font-size: .98rem;
        }

        #StatusText {
            color: #c5cbd5;
        }

        #NoAssets {
            background: #17171a;
            border: 1px solid rgba(255,255,255,.08);
            border-radius: .45rem;
        }
    </style>
</head>
<body>
    <div class="Asset" template>
        <div class="card asset-card h-100 overflow-hidden">
            <a id="NameAndThumbs" href="javascript:void(0)" class="asset-card-link">
                <div class="asset-thumb-container">
                    <div id="FavouritesArea" class="catalog-flag"><span>0</span></div>
                    <img class="img-fluid" src="/thumbs/unavailable.jpg" alt="Asset thumb">
                </div>
                <div class="card-body p-3 flex-grow-1">
                    <div id="Pricing" class="mb-2"></div>
                    <div class="catalog-item-title text-truncate mb-1">AssetName</div>
                    <div id="Creator" class="small text-secondary text-truncate">by <span>AssetCreator</span></div>
                </div>
            </a>
        </div>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/header.php'; ?>

    <main class="app-main py-4 catalog-shell" id="AssetsContainer">
        <div class="container">
            <h1 class="catalog-title"><?= htmlspecialchars($randomcatalogsplash, ENT_QUOTES, 'UTF-8') ?></h1>

            <div class="row g-4 align-items-start">
                <div class="col-lg-3">
                    <aside class="catalog-panel mb-4">
                        <div class="filter-title">categories</div>
                        <ul class="filter-list mb-0">
                            <li data_category="0">all items</li>
                            <li data_category="8" selected>hats</li>
                            <li data_category="18">faces</li>
                            <li data_category="11">shirts</li>
                            <li data_category="2">t-shirts</li>
                            <li data_category="12">pants</li>
                            <li data_category="19">gears</li>
                            <li data_category="17">heads</li>
                        </ul>
                    </aside>

                    <aside class="catalog-panel">
                        <div class="filter-title">sort by</div>
                        <ul class="filter-list mb-0">
                            <li data_filter="1" selected>recently uploaded</li>
                            <li data_filter="5">most sold</li>
                            <li data_filter="6">most favourited</li>
                        </ul>
                    </aside>
                </div>

                <div class="col-lg-9">
                    <div class="catalog-search input-group input-group-lg mb-4">
                        <input class="form-control" id="SearchBox" name="query" type="text" placeholder="Search...">
                        <button class="btn" type="button" onclick="ANORRL.Catalog.Submit();">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>

                    <div id="StatusText" class="mb-4">
                        <div id="Loading" style="display:none" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <div class="mt-2">Searching catalog...</div>
                        </div>
                        <div id="NoAssets" class="empty-state text-center py-5" style="display:none">
                            <div class="text-center">
    <img src="/images/error.png" alt="Error" style="max-width:150px;" class="mb-3">
    <h4 class="mb-2">no items found</h4>
</div>
                            <p class="mb-0 text-secondary">try searching different item or category</p>
                        </div>
                    </div>

                    <div id="Assets" class="catalog-grid"></div>

                    <div id="Paginator" class="d-flex justify-content-center align-items-center gap-3 mt-5" style="display:none;">
                        <a class="btn btn-light shadow-sm" id="PrevPager" href="javascript:ANORRL.Catalog.PrevPage()" style="display:none; text-decoration:none;">&laquo; Back</a>
                        <div class="d-flex align-items-center gap-2">
                            <input class="form-control text-center shadow-sm" type="text" id="NumberPutter" maxlength="3" style="width:60px;">
                            <span class="text-secondary">of <span id="Counter">1</span></span>
                        </div>
                        <a class="btn btn-light shadow-sm" id="NextPager" href="javascript:ANORRL.Catalog.NextPage()" style="display:none; text-decoration:none;">Next &raquo;</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/footer.php'; ?>
</body>
</html>

