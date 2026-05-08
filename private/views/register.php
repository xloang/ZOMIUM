<?php
	use anorrl\utilities\UserUtils;
	use anorrl\Page;

	if (SESSION) {
		die(header("Location: /my/home"));
	}

	$istoomany = count(UserUtils::GetAllUsers()) > 100;

	if (
		isset($_POST['ANORRL$Signup$Username']) &&
		isset($_POST['ANORRL$Signup$Password']) &&
		isset($_POST['ANORRL$Signup$ConfirmPassword']) &&
		isset($_POST['ANORRL$Signup$Submit']) &&
		!$istoomany
	) {
		$username = trim($_POST['ANORRL$Signup$Username']);
		$password = trim($_POST['ANORRL$Signup$Password']);
		$confirm_password = trim($_POST['ANORRL$Signup$ConfirmPassword']);

		$result = UserUtils::RegisterUser($username, $password, $confirm_password, '');
		if ($result === 'success') {
			die(header('Location: /my/home'));
		}

		$_SESSION['signup_errors'] = $result;
		die(header('Location: /register'));
	}

	$page = new Page("Register");
	$page->addScript("/js/forms.js?v=" . time());
	$page->loadBasicHeader();
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
		if (!($('.Invalid').length === 0 && $('.Valid').length === 3)) {
			e.preventDefault();
			alert('Please fix the highlighted fields.');
		}
	});
});
</script>
<div class="auth-shell px-3 min-vh-100 d-flex align-items-center justify-content-center">
	<div class="card py-5 text-center" style="width:min(100%,640px);">
		<img src="/public/images/ZoniumBIG2.png" class="mb-4 mx-auto" style="max-width: 240px; width: 100%;" alt="Zomium">
		<h4>Welcome to Zomium!</h4>
		<p class="mb-4">Finish creating your account below.</p>
		<form method="POST" class="text-start mt-4 px-4">
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
			<h1 class="h6 mt-3">Need to log in? <a href="/login">Login</a></h1>
		</form>
	</div>
</div>
<?php else: ?>
<script>
window.alert("There's too many users on the site. Registration is closed right now.");
window.location.href = '/login';
</script>
<?php endif; ?>
<?php
	$page->loadBasicFooter();
	unset($_SESSION['login_errors'], $_SESSION['signup_errors']);
?>
