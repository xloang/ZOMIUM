<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $page_title = '500 - Zomium';
    include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php';
    ?>
</head>
<body>
    <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/header.php'; ?>
    <main class="app-main">
        <div class="container">
            <div class="card border-0">
                <div class="card-body p-5 text-center">
                    <img src="/images/icons/img-alert-transparent.png" alt="Error" style="max-width:160px" class="mb-4">
                    <div class="section-title mb-3">500</div>
                    <h1 class="h2 mb-3">Internal error</h1>
                    <p class="text-secondary mb-4">A server-side failure occurred. Refreshing repeatedly will not help. Tell grace to fix it.</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button class="btn btn-outline-light" onclick="window.history.back();">Back</button>
                        <a class="btn btn-primary" href="/my/home">Home</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/footer.php'; ?>
</body>
</html>
