<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/utilities/userutils.php';

if (!defined('APP_HEAD_INCLUDED')) {
    ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="/css/new/app.css?v=8">
    <script src="/js/core/jquery.js"></script>
    <script src="/js/main.js?t=1771413807"></script>
    <?php
}

if (!isset($header_data)) {
    $header_data = null;
}

$header_check_user = UserUtils::RetrieveUser($header_data);
$pendingreqscount = 0;
$header_ziu = 0;

if ($header_check_user != null) {
    $pendingreqscount = $header_check_user->GetPendingFriendRequestsCount();
    $header_check_user->ZiuDaily();
    $header_ziu = $header_check_user->GetZiu();
}
?>
<style>
.app-navbar .nav-link:hover,
.app-subnav .nav-link:hover,
.nav-scroller-inner > a.nav-link:hover {
    text-decoration: underline;
    text-decoration-color: #6fb7ff;
    text-decoration-thickness: 2px;
    text-underline-offset: .45rem;
}
</style><div class="app-shell d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark app-navbar shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="/">
                <img src="/s/img/finnobe3llogo.png" alt="Zomium" class="app-brand-logo">
                <span class="app-brand-text">Zomium</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain"
                aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="<?= $header_check_user == null ? '/' : '/my/home' ?>">Home</a></li>
                    <?php if ($header_check_user != null): ?>
                        <li class="nav-item"><a class="nav-link"
                                href="/users/<?= $header_check_user->id ?>/profile">Profile</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="/app/games.php">Games</a></li>
                    <li class="nav-item"><a class="nav-link" href="/app/catalog.php">Catalog</a></li>
                    <li class="nav-item"><a class="nav-link" href="/app/videos.php">Videos</a></li>
                    <li class="nav-item"><a class="nav-link" href="/app/forum.php">Forum</a></li>
                     <li class="nav-item"><a class="nav-link" href="/my/groups.php">Groups</a></li>
                     <a>More</a>
                     
                    
                </ul>

                <?php if ($header_check_user == null): ?>
                    <div class="d-flex flex-column flex-lg-row gap-2 my-3 my-lg-0">
                        <a class="btn btn-outline-light" href="/app/login.php">Login</a>
                        <a class="btn btn-primary" href="/app/register.php">Register</a>
                    </div>
                <?php else: ?>
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                        <h1 class="h6 mb-0 text-light d-flex align-items-center gap-1">
                            <img src="/images/ziu_16.png" style="width:16px; height:16px;">
                            <span> <?= number_format($header_ziu) ?></span>
                        </h1>
                        <li class="nav-item">
                            <a class="nav-link icon-nav-link" href="/my/friends" title="Friend requests">
                                <i class="fas fa-user-friends"></i>
                                <?php if ($pendingreqscount > 0): ?>
                                    <span
                                        class="badge rounded-pill bg-danger badge-notification"><?= $pendingreqscount ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="user-pill-avatar">
                                    <img src="/thumbs/headshot?id=<?= $header_check_user->id ?>&sxy=100"
                                        alt="<?= htmlspecialchars($header_check_user->name, ENT_QUOTES, 'UTF-8') ?>">
                                </span>
                                <span><?= htmlspecialchars($header_check_user->name, ENT_QUOTES, 'UTF-8') ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><a class="dropdown-item" href="/my/home"><i
                                            class="fas fa-columns me-2"></i>Dashboard</a></li>
                                <li><a class="dropdown-item" href="/users/<?= $header_check_user->id ?>/profile"><i
                                            class="fas fa-user me-2"></i>Profile</a></li>
                                            <?php if ($header_check_user->IsAdmin()): ?>
                                <li><a class="dropdown-item" href="/my/stuff"><i class="fas fa-box-open me-2"></i>Inventory</a>
                                </li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="/my/character"><i
                                            class="fas fa-tshirt me-2"></i>Character</a></li>
                                <li><a class="dropdown-item" href="/gallery"><i class="fas fa-images me-2"></i>Gallery</a>
                                </li>
                                <li><a class="dropdown-item" href="/videos"><i class="fas fa-video me-2"></i>Videos</a>
                                </li>
                                <li><a class="dropdown-item" href="/forum"><i class="fas fa-comments me-2"></i>Forum</a>
                                </li>
                                <?php if ($header_check_user->IsAdmin()): ?>
                                    <li><a class="dropdown-item" href="/admi"><i class="fas fa-flag me-2"></i>Admin Panel!</a>
                                    </li>   
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="/my/settings.php"><i class="fas fa-cog me-2"></i>Settings</a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="javascript:ANORRL.Logout()"><i
                                            class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php if ($header_check_user != null): ?>
        <div class="app-subnav">
            <div class="container">
                <div class="nav nav-pills nav-finobe flex-nowrap overflow-auto py-2">
                    <?php if ($header_check_user->IsAdmin()): ?>
                    <a class="nav-link" href="/create"><i class="fas fa-plus me-2"></i>Create</a>
                    <?php endif; ?>
                    <a class="nav-link" href="/my/character"><i class="fas fa-user me-2"></i>Character</a>
                    
                    
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="DisplayMobileWarning alert alert-warning rounded-0 border-0 text-center mb-0 d-none">
        Mobile support is limited on this build.
        <button class="btn btn-sm btn-dark ms-2" onclick="ANORRL.HideMobileWarning()">Continue</button>
    </div>














