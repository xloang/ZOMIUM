<?php
function renderUserListPage($title, $people, $get_user, $user) {
?>
<!DOCTYPE html>
<html>
    <head>
        <?php
        $page_title = $title.' - Zomium';
        include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php';
        ?>
    </head>
    <body>
        <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/header.php'; ?>
        <main class="app-main">
            <div class="container">
                <div class="hero-panel p-4 p-lg-5 mb-4">
                    <div class="section-title mb-3">Connections</div>
                    <h1 class="display-6 fw-bold mb-3"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
                    <p class="text-secondary mb-0">A shared dark-theme grid for profile connection lists.</p>
                </div>
                <div class="row g-4">
                    <?php if(count($people) != 0): ?>
                        <?php foreach($people as $friendo): ?>
                        <?php $fid = $friendo->id; $profile = UserSettings::Get($user)->headshots_enabled ? 'headshot' : ($friendo->setprofilepicture ? 'profile' : 'headshot'); $status = $friendo->IsOnline() ? 'Online' : 'Offline'; ?>
                        <div class="col-md-6 col-xl-4 friend-tile">
                            <div class="card border-0 h-100">
                                <div class="card-body p-4 text-center">
                                    <a href="/users/<?= $fid ?>/profile" target="_blank"><img class="img-fluid mb-3" src="/thumbs/<?= $profile ?>?id=<?= $fid ?>&sxy=100" alt="<?= htmlspecialchars($friendo->name, ENT_QUOTES, 'UTF-8') ?>"></a>
                                    <h2 class="h5 mb-2"><?= htmlspecialchars($friendo->name, ENT_QUOTES, 'UTF-8') ?></h2>
                                    <div class="small <?= $status === 'Online' ? 'status-online' : 'status-offline' ?>"><?= $status ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <div class="col-12"><div class="empty-state">Nothing to show here yet.</div></div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
        <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/footer.php'; ?>
    </body>
</html>
<?php }
