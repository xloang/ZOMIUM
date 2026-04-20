<?php
http_response_code(403);
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/userutils.php';
$error_user = UserUtils::RetrieveUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $page_title = '403 - Zomium';
    include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php';
    ?>
</head>
<body>
    <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/header.php'; ?>
    <main class="app-main">
        <div class="container">
            <div class="card border-0">
                <div class="card-body p-5 text-center">
                    <?php
$images = [
    "/images/error.png"
];

$randomImage = $images[array_rand($images)];
?>

<img src="<?= $randomImage ?>" alt="Error" style="max-width:180px" class="mb-4">
                    <h1 class="h2 mb-3">Access Denied</h1>
                    <p>Sorry you cant access here.</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button class="btn btn-outline-light" onclick="window.history.back();">Back</button>
                        <a class="btn btn-primary" href="<?= $error_user != null ? '/my/home' : '/' ?>">Home</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/footer.php'; ?>
</body>
</html>
