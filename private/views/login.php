<?php


	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	use anorrl\Page;
	use anorrl\utilities\UserUtils;

	if (defined('SESSION') && SESSION) {
		die(header("Location: /my/home"));
	}

	$messages = [
		"login...",
		"welcome back!",
		"sigma",
		"yes",
		"login"
	];

	if (
		isset($_POST['ANORRL$Login$Username']) &&
		isset($_POST['ANORRL$Login$Password']) &&
		isset($_POST['ANORRL$Login$Submit'])
	) {
		$username = trim($_POST['ANORRL$Login$Username']);
		$password = trim($_POST['ANORRL$Login$Password']);
		$result = UserUtils::LoginUser($username, $password);

		if (isset($result['login']) && $result['login'] != 'Incorrect details provided!') {
			die(header('Location: /my/home'));
		}

		$_SESSION['login_errors'] = $result;
		die(header('Location: /login'));
	}

	$page = new Page("Login");
	$page->addScript("/js/forms.js");
	$page->loadBasicHeader();
?>
<script>
$(function () {
	$('#ANORRL_Login_Username').on('input change', function () {
		ANORRL.Login.CheckUsername(this, $(this).val());
	});
	$('#ANORRL_Login_Password').on('input change', function () {
		ANORRL.Login.CheckPassword(this, $(this).val());
	});
	$('form').submit(function (e) {
		ANORRL.Login.CheckUsername(document.getElementById('ANORRL_Login_Username'), $('#ANORRL_Login_Username').val());
		ANORRL.Login.CheckPassword(document.getElementById('ANORRL_Login_Password'), $('#ANORRL_Login_Password').val());
		if (!($('.Invalid').length === 0 && $('.Valid').length === 2)) {
			e.preventDefault();
			alert('Please fix the highlighted fields.');
		}
	});
});
</script>
<div class="auth-shell px-3 min-vh-100 d-flex align-items-center justify-content-center">
	<form method="POST" class="card auth-card py-5 d-flex flex-column align-items-center justify-content-center text-center" style="width:min(100%,540px);">
		<div class="mb-3">
			<img src="/public/images/legacy/finnobe3llogo.png" alt="Zomium" class="img-fluid auth-logo" style="max-width:280px;">
		</div>
		<h2 class="my-3"><?= htmlspecialchars($messages[array_rand($messages)], ENT_QUOTES, 'UTF-8') ?></h2>
		<div class="form-group w-75 text-start mb-3">
			<label for="ANORRL_Login_Username">Username</label>
			<div class="input-group">
				<input class="form-control" type="text" id="ANORRL_Login_Username" name="ANORRL$Login$Username" placeholder="username">
			</div>
			<div class="small text-danger mt-2" id="v_username">
				<?php if (isset($_SESSION['login_errors']['username'])) { echo $_SESSION['login_errors']['username']; } ?>
			</div>
			<?php if (isset($_SESSION['login_errors']['login'])): ?>
				<small class="text-danger"><?= htmlspecialchars($_SESSION['login_errors']['login'], ENT_QUOTES, 'UTF-8') ?></small>
			<?php endif; ?>
		</div>
		<div class="form-group w-75 text-start mb-3">
			<label for="ANORRL_Login_Password">Password</label>
			<input class="form-control" type="password" id="ANORRL_Login_Password" name="ANORRL$Login$Password" placeholder="Password">
			<div class="small text-danger mt-2" id="v_password">
				<?php if (isset($_SESSION['login_errors']['password'])) { echo $_SESSION['login_errors']['password']; } ?>
			</div>
		</div>
		<div class="form-group d-flex justify-content-between align-items-start w-75 gap-2 mb-3">
			<button class="btn btn-primary flex-grow-1" type="submit" id="ANORRL_Login_Submit" name="ANORRL$Login$Submit">Login</button>
			<a href="/" class="btn btn-secondary flex-grow-1">&lt; Back</a>
		</div>
		<div class="form-group"><a href="/register" class="clearfix">Need an account? Register here.</a></div>
	</form>
</div>
<?php
	$page->loadBasicFooter();
	unset($_SESSION['login_errors']);
?>
