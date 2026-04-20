<?php
$page_title = 'Privacy Policy - Zomium';
?>
<!DOCTYPE html>
<html>
<head>
    <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php'; ?>
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/header.php'; ?>
<main class="app-main">
    <div class="container py-4">
        <section class="legal-banner mb-4">
            <div class="container py-5">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="section-title mb-2">Privacy Policy</div>
                        <h1 class="display-5 fw-bold mb-3">What data the site stores and why.</h1>
                        <p class="lead mb-0">This page explains the practical data we keep to run accounts, sessions, uploads, moderation, and core site features.</p>
                    </div>
                </div>
            </div>
        </section>
        <div class="row g-4">
            <div class="col-lg-4"><div class="card legal-section-card"><div class="card-body"><h2 class="h4 mb-3">Account Data</h2><p>We store usernames, authentication-related data, and profile information needed to operate the site.</p></div></div></div>
            <div class="col-lg-4"><div class="card legal-section-card"><div class="card-body"><h2 class="h4 mb-3">Usage Data</h2><p>Basic activity such as posts, uploads, and session state may be logged to keep the platform functional and moderate abuse.</p></div></div></div>
            <div class="col-lg-4"><div class="card legal-section-card"><div class="card-body"><h2 class="h4 mb-3">Media Uploads</h2><p>Images and videos uploaded by users are stored on the server so they can be shown in the gallery and other site features.</p></div></div></div>
        </div>
    </div>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/footer.php'; ?>
</body>
</html>
