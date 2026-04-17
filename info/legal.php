<?php
$page_title = 'Legal - Zomium';
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
                        <div class="section-title mb-2">Legal</div>
                        <h1 class="display-5 fw-bold mb-3">TODO</h1>
                        <p class="lead mb-0"> we are not affiliated with any trademarks or copyrights.</p>
                    </div>
                </div>
            </div>
        </section>
        <div class="row g-4">
            <div class="col-md-6 col-xl-3"><a class="catalog-card" href="/about"><div class="card legal-section-card"><div class="card-body"><h2 class="h5 mb-2">About Us</h2><p class="text-secondary mb-0">Who we are and what the site is trying to build.</p></div></div></a></div>
            <div class="col-md-6 col-xl-3"><a class="catalog-card" href="/terms"><div class="card legal-section-card"><div class="card-body"><h2 class="h5 mb-2">Terms</h2><p class="text-secondary mb-0">Rules around account use, user content, and moderation.</p></div></div></a></div>
            <div class="col-md-6 col-xl-3"><a class="catalog-card" href="/privacy"><div class="card legal-section-card"><div class="card-body"><h2 class="h5 mb-2">Privacy Policy</h2><p class="text-secondary mb-0">What data is stored and how it is used to run the service.</p></div></div></a></div>
            <div class="col-md-6 col-xl-3"><a class="catalog-card" href="/report"><div class="card legal-section-card"><div class="card-body"><h2 class="h5 mb-2">Support</h2><p class="text-secondary mb-0">Contact and reporting entry points already available on the site.</p></div></div></a></div>
        </div>
    </div>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/footer.php'; ?>
</body>
</html>
