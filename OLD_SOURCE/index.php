<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/utilities/userutils.php';
$user = UserUtils::RetrieveUser();

$messages = [
    "welcome to zomium.",
    "hello",
    "Z-Z-ZOMIUM",
    "zonium",
    "this is work in progress so im still working on it",
    "this website is for old versions of a minecraft server called zomium",
    "android clients soon",
    "join discord",
    "zomium is free"
];

$randomMessage = $messages[array_rand($messages)];

if ($user != null) {
    die(header('Location: /my/home'));
}
?>
<!DOCTYPE html>
<html>
<head>
<?php
$page_title = 'Zomium';
include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/head.php';
?>
<style>
body {
    background: #0d1117 !important;
}


.landing-shell {
    position: relative;
    min-height: calc(100vh - 140px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4rem 1.25rem 5rem;
    overflow: hidden;
    z-index: 0;
}


.landing-bg {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(180deg, rgba(9,15,25,.6), rgba(9,15,25,.85)),
        url('/s/img/xmas_small.jpg') center/cover no-repeat;
    filter: blur(6px);
    transform: scale(1.05);
    z-index: 0;
}


.landing-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,.1), rgba(0,0,0,.4));
    z-index: 1;
}

.landing-content {
    position: relative;
    z-index: 2; 
    width: min(100%, 1120px);
    text-align: center;
}

.landing-logo {
    width: min(92vw, 760px);
    margin-bottom: 2rem;
}

/* hold on wait */
.landing-message {
    display: block;
    width: 100%;
    margin: 0 auto;
    padding: 1.2rem;
    color: #f4f6f8;
    font-size: 1.4rem;
    font-weight: 700;
    text-align: center;
    background: rgba(0,0,0,0.5); 
    border-radius: 1px;
    backdrop-filter: blur(6px);
}


.landing-actions {
    margin-top: 1.8rem;
}

.landing-actions .btn {
    min-width: 160px;
}
</style>
</head>

<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/header.php'; ?>

<main class="app-main p-0">
    <section class="landing-shell">

        <div class="landing-bg"></div>
        <div class="landing-overlay"></div>

        <div class="landing-content">
            <img class="landing-logo" src="/images/ZoniumBIG2.png">
            <div class="landing-message">
            <?= htmlspecialchars($randomMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>

    </section>
</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/footer.php'; ?>

</body>
</html>