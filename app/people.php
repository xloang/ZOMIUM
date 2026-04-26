<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/userutils.php';
$user = UserUtils::RetrieveUser();

if($user == null) {
    die(header('Location: /login'));
}

$randomvandalsplashes = ['Users'];
$randomvandalsplash = $randomvandalsplashes[array_rand($randomvandalsplashes)];
?>
<!DOCTYPE html>
<html>
    <head>
        <?php
        $page_title = 'Users - Zomium';
        $page_scripts = ['/js/people.js?t=1771933381'];
        include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php';
        ?>
    </head>
    <body>
        <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/header.php'; ?>
        <main class="app-main">
            <div class="container">
                <div class="hero-panel p-4 p-lg-5 mb-4">
                    <div class="section-title mb-3">People</div>
                    <h1 class="display-6 fw-bold mb-3"><?= htmlspecialchars($randomvandalsplash, ENT_QUOTES, 'UTF-8') ?></h1>
                    <p class="text-secondary mb-0">Search the community, inspect blurbs, and check who is currently active.</p>
                </div>
                <div id="Users" class="card border-0">
                    <div class="card-body p-4">
                        <div id="FormPanel" class="row g-2 mb-4">
                            <div class="col-sm-9"><input class="form-control" id="SearchBox" name="query" type="text" placeholder="Look for users"></div>
                            <div class="col-sm-3"><input class="btn btn-primary w-100" id="Submit" type="submit" value="Search" onclick="ANORRL.People.Submit(); return false;"></div>
                        </div>
                        <table id="UsersDataTable" class="table align-middle">
                            <thead>
                                <tr>
                                    <th width="90">Avatar</th>
                                    <th width="220">Name</th>
                                    <th>Blurb</th>
                                    <th width="200">Active</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <div id="UsersNavLinks" class="d-flex flex-wrap align-items-center gap-2 mt-3">
                            <a class="btn btn-outline-light btn-sm" id="BackPager" href="javascript:ANORRL.People.DeadvanceFeed()">Back</a>
                            <input class="form-control form-control-sm" maxlength="4" id="NumberPutter" style="width:84px;">
                            <span class="text-secondary">of <span id="Counter"></span></span>
                            <a class="btn btn-outline-light btn-sm" id="NextPager" href="javascript:ANORRL.People.AdvanceFeed()">Next</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/footer.php'; ?>
    </body>
</html>
