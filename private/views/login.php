<?php
ini_set('session.use_strict_mode', 0);
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 0);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Lax');

session_name("ANORRLSESSID");
session_start();

	use anorrl\Page;
	use anorrl\utilities\UserUtils;

	if(session_status() != PHP_SESSION_ACTIVE) {
		session_start();
	}

	/* ========= LOG SİSTEMİ ========= */
	function writeLog($message) {
		$logFile = 'C:\\laragon\\www\\private\\logs\\login.log';

		$date = date("Y-m-d H:i:s");
		$ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

		$logMessage = "[$date] [IP:$ip] $message" . PHP_EOL;

		file_put_contents($logFile, $logMessage, FILE_APPEND);
	}

	writeLog("Login sayfası açıldı");

	if(defined('SESSION') && SESSION) {
		writeLog("Zaten giriş yapılmış → /my/home yönlendirildi");
		die(header("Location: /my/home"));
	}

	if(isset($_POST['ANORRL$Login$Username']) &&
	   isset($_POST['ANORRL$Login$Password']) &&
	   isset($_POST['ANORRL$Login$Submit'])) {
		
		writeLog("Login isteği gönderildi");

		$username = trim($_POST['ANORRL$Login$Username']);
		$password = trim($_POST['ANORRL$Login$Password']);

		writeLog("Username: " . $username);

		if(empty($username) || empty($password)) {
			writeLog("HATA: Boş alan");
		}

		try {
			$result = UserUtils::LoginUser($username, $password);

			writeLog("Login sonucu: " . json_encode($result));

			if($result["login"] != "Incorrect details provided!") {
				writeLog("GİRİŞ BAŞARILI → /my/home");
				die(header("Location: /my/home"));
			} else {
				writeLog("GİRİŞ BAŞARISIZ");
				$_SESSION['login_errors'] = $result;
				die(header("Location: /login"));
			}

		} catch (Exception $e) {
			writeLog("EXCEPTION: " . $e->getMessage());
		}
	}

	$page = new Page("Login");

	$page->addStylesheet("/css/new/forms.css");
	$page->addScript("/js/forms.js");

	$page->loadHeader();
?>

<script>
	$(function(){
		$("#ANORRL_Login_Username").on("input change", function() {
			ANORRL.Login.CheckUsername(this, $(this).val());
		});
		$("#ANORRL_Login_Password").on("input change", function() {
			ANORRL.Login.CheckPassword(this, $(this).val());
		});

		$("form").submit(function (e) {
			ANORRL.Login.CheckUsername(document.getElementById("ANORRL_Login_Username"), $("#ANORRL_Login_Username").val());
			ANORRL.Login.CheckPassword(document.getElementById("ANORRL_Login_Password"), $("#ANORRL_Login_Password").val());
			if(!($(".Invalid").length == 0 && $(".Valid").length == 2)) {
				e.preventDefault();
				alert("Holy shit you have so much wrong");
			}
		});
	});
</script>

<style>
	.FormImage {
		width: 265px;
		height: 309px;
		border: 2px solid black;
	}

	#BodyContainer > h2 {
		margin: 0px;
		width: calc(100% - 48px);
		margin-bottom: 20px;
		text-align: center;
		background: none repeat-x;
		background-size: 49px auto;
		border: 4px solid black;
		height: 21px;
		background-blend-mode: difference;
		background-image: linear-gradient(#ffb300,#ffb300),url("/public/images/header/navbar.jpg");
		overflow: hidden;
	}
</style>

<div id="FormPanel" style="width: 240px;">
	<form method="POST">
		<div>
			<span class="Validator">
				<?php 
					if(isset($_SESSION['login_errors'])) {
						echo $_SESSION['login_errors']['login'];
					}
				?>
			</span>
		</div>
		<div>
			<h4>Username</h4>
			<span class="Validator" id="v_username">
				<?php 
					if(isset($_SESSION['login_errors'])) {
						if(isset($_SESSION['login_errors']['username'])) {
							echo $_SESSION['login_errors']['username'];
						}
					}
				?>
			</span>
			<input type="text" id="ANORRL_Login_Username" name="ANORRL$Login$Username" minlength="3" maxlength="20" required>
		</div>
		<div>
			<h4>Password</h4>
			<span class="Validator" id="v_password">
				<?php 
					if(isset($_SESSION['login_errors'])) {
						if(isset($_SESSION['login_errors']['password'])) {
							echo $_SESSION['login_errors']['password'];
						}
					}
				?>
			</span>
			<input type="password" id="ANORRL_Login_Password" name="ANORRL$Login$Password" minlength="7" required>
			<span>If you do not have an account then,<br><a href="/register">register here!</a></span>
		</div>
		<div>
			<p></p>
			<input type="submit" id="ANORRL_Login_Submit" name="ANORRL$Login$Submit" value="Login">
		</div>
	</form>						
</div>

<?php 
	$page->loadFooter();
	unset($_SESSION['login_errors']);
?>