<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/utilities/userutils.php';
$user = UserUtils::RetrieveUser();



if (session_status() != PHP_SESSION_ACTIVE) {
    session_start();
}

$istoomany = count(UserUtils::GetAllUsers()) > 100;

if (isset($_POST['ANORRL$Key$Submit']) && !$istoomany) {
    $accesskey = trim($_POST['ANORRL$Key$Value'] ?? '');

    UserUtils::StorePendingRegistrationKey($accesskey);
    header('Location: /register');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<?php
$page_title = 'Key - Zomium';
include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/head.php';
?>

<style>
body {
    background: #0f1115 !important;
}

/* ANA */
.keys-wrapper {
    min-height: calc(100vh - 120px);
    display: flex;
    align-items: center;
}

/* SOL BLOK */
.keys-left {
    padding: 3rem;
    max-width: 420px;
}

.keys-left img {
    width: 260px;
    margin-bottom: 1.2rem;
}

.keys-left h1 {
    color: #e6edf3;
    font-size: 2rem;
    font-weight: 700;
}

.keys-left p {
    color: #9da5b0;
    margin-bottom: 20px;
}

/* FORM */


.keys-submit {
    width: 100%;
    height: 50px;
    border: none;
    background: #38a169;
    color: white;
    font-weight: 600;
    transition: 0.15s;
}

.keys-submit:hover {
    background: #2f855a;
}

/* ERROR */
.keys-error {
    background: #2a1a1a;
    color: #ffb4b4;
    padding: 10px;
    margin-bottom: 12px;
    border: 1px solid #5a2a2a;
}

/* ALT LINK */
.keys-links {
    margin-top: 12px;
    color: #9da5b0;
    font-size: 14px;
}

.keys-links a {
    color: #7eb3ff;
    text-decoration: none;
}
</style>

</head>
<body>

<?php if (!$istoomany): ?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/header.php'; ?>

<div class="keys-wrapper">

    <div class="keys-left">
        <img src="/images/ZoniumBIG2.png" alt="Zomium">

        <h1>W.I.P THIS DOESNT WORKS FOR NOW</h1>
        <p>
            type your access key that we give you here.
        </p>

        <?php if (isset($_SESSION['key_error'])): ?>
            <div class="keys-error">
                <?= htmlspecialchars($_SESSION['key_error'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

       <form method="POST">
    <input class="btn btn-success btn-lg mb-2" type="password" name="ANORRL$Key$Value" placeholder="Access key" required>  
    <button class="keys-submit" type="submit" name="ANORRL$Key$Submit">
        Enter
    </button>
</form>

        <div class="keys-links">
            Already have an account? <a href="/login">Login</a>
        </div>
    </div>

</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/footer.php'; ?>

<?php else: ?>

<?php endif; ?>

</body>
</html>

<?php unset($_SESSION['key_error']); ?>