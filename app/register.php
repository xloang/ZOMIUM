<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/utilities/userutils.php';
$user = UserUtils::RetrieveUser();

if ($user == null) {
    echo "<h1>YOU CANT CREATE ACCOUNT 😂😂</h1>";
    exit;
}

/*if ($user != null) {
    die(header('Location: /my/home'));
}
*/

if (session_status() != PHP_SESSION_ACTIVE) {
    session_start();
}

$istoomany = count(UserUtils::GetAllUsers()) > 100;
$pendingKey = UserUtils::GetPendingRegistrationKey();

// KEY KONTROLÜ TAMAMEN KALDIRILDI

if (
    isset($_POST['ANORRL$Signup$Username']) &&
    isset($_POST['ANORRL$Signup$Password']) &&
    isset($_POST['ANORRL$Signup$ConfirmPassword']) &&
    isset($_POST['ANORRL$Signup$Submit']) && !$istoomany
) {
    $username = trim($_POST['ANORRL$Signup$Username']);
    $password = trim($_POST['ANORRL$Signup$Password']);
    $confirm_password = trim($_POST['ANORRL$Signup$ConfirmPassword']);

    // pendingKey tamamen devre dışı
    $result = UserUtils::RegisterUser($username, $password, $confirm_password, '');

    if ($result == 'success') {
        die(header('Location: /my/home'));
    }

    // key hatası kontrolü de kaldırıldı
    $_SESSION['signup_errors'] = $result;
    die(header('Location: /register'));
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php
    $page_title = 'Register - Zomium';
    $page_scripts = !$istoomany ? ['/js/forms.js?v=' . time()] : [];
    include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/head.php';
    ?>
    <?php if (!$istoomany): ?>
        <script>
            $(function () {
                $('#ANORRL_Signup_Username').on('input change', function () { ANORRL.Register.CheckUsername(this, $(this).val()); });
                $('#ANORRL_Signup_Password').on('input change', function () { ANORRL.Register.CheckMainPassword(this, $(this).val()); });
                $('#ANORRL_Signup_ConfirmPassword').on('input change', function () { ANORRL.Register.CheckSecondPassword(this, $(this).val()); });
                $('form').submit(function (e) {
                    ANORRL.Register.CheckUsername(document.getElementById('ANORRL_Signup_Username'), $('#ANORRL_Signup_Username').val());
                    ANORRL.Register.CheckMainPassword(document.getElementById('ANORRL_Signup_Password'), $('#ANORRL_Signup_Password').val());
                    ANORRL.Register.CheckSecondPassword(document.getElementById('ANORRL_Signup_ConfirmPassword'), $('#ANORRL_Signup_ConfirmPassword').val());
                    if (!($('.Invalid').length == 0 && $('.Valid').length == 3)) {
                        e.preventDefault();
                        alert('Please fix the highlighted fields.');
                    }
                });
            });
        </script>
    <?php else: ?>
        <script>
            window.alert("There's too many users on the site. Registration is closed right now.");
            window.location.href = '/login';
        </script>
    <?php endif; ?>
</head>
<body>
    <?php if (!$istoomany): ?>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/header.php'; ?>
        <main class="app-main">
            <div class="container" style="margin-bottom:20px;margin-top:20px;">
                <div class="row">
                    <div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
                        <div class="card">
                            <div class="card-header">Register</div>
                            <div class="card-body text-center">
                                <img src="/images/ZoniumBIG2.png" class="mb-4" style="max-width: 240px; width: 100%;">
                                <h4>Welcome to Zomium!</h4>
                                <p class="mb-1">Remember to read our <a href="/info/terms.php">terms of service</a> and <a href="/info/privacy.php">privacy policy</a> first.</p>
                                <p class="mb-4">Your access key was accepted. Finish creating your account below.</p>
                                <form method="POST" class="text-start mt-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="ANORRL_Signup_Username">Username</label>
                                        <input class="form-control" type="text" id="ANORRL_Signup_Username" name="ANORRL$Signup$Username" maxlength="20" minlength="3" required>
                                        <div class="small text-danger mt-2" id="v_username"><?php if (isset($_SESSION['signup_errors']['username'])) { echo $_SESSION['signup_errors']['username']; } ?></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="ANORRL_Signup_Password">Password</label>
                                        <input class="form-control mb-2" type="password" id="ANORRL_Signup_Password" name="ANORRL$Signup$Password" required>
                                        <input class="form-control" type="password" id="ANORRL_Signup_ConfirmPassword" name="ANORRL$Signup$ConfirmPassword" required>
                                        <div class="small text-danger mt-2" id="v_password"><?php if (isset($_SESSION['signup_errors']['password'])) { echo $_SESSION['signup_errors']['password']; } ?></div>
                                        <div class="small text-danger mt-1" id="v_confirmpassword"></div>
                                    </div>
                                    <button class="btn btn-primary w-100" type="submit" id="ANORRL_Signup_Submit" name="ANORRL$Signup$Submit">Register</button>
                                    <h1 class="h6 mt-3">You'd have a account? <a href="/app/login.php">Login</a></h1>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/footer.php'; ?>
    <?php endif; ?>
</body>
</html>
<?php
unset($_SESSION['login_errors'], $_SESSION['signup_errors']);
?>
