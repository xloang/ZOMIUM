<?php
	use anorrl\UserSettings;
	use anorrl\utilities\ClientDetector;
	use anorrl\utilities\Splasher;
	use anorrl\utilities\FileSplasher;
	use anorrl\utilities\UtilUtils;

	$header_check_user = SESSION ? $GLOBALS['__session']->user : null;

	$rand_pic = (new Splasher(UtilUtils::GetFilesArray("/public/images/randoms/"), false, "RandomImages"))->getRandomSplash();
	$rand_splash_pic = (new Splasher(UtilUtils::GetFilesArray("/public/images/splashes/"), false, "SplashScreen"))->getRandomSplash();

	$randomsignsplash = (new FileSplasher("sign"))->getRandomSplash();

	$splashscreencaptions_path = $_SERVER["DOCUMENT_ROOT"]."/private/splashes/screens.txt";
	$splashscreencaptions = is_file($splashscreencaptions_path)
		? file($splashscreencaptions_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
		: [];
	$splash_index = intval(str_replace(["ANORRLStudioSplash-", ".png"], "", $rand_splash_pic)) - 1;
	$splashscreencaption = $splashscreencaptions[$splash_index] ?? "Welcome back.";
	$rand_splash_src = strlen($rand_splash_pic) != 0
		? "/public/images/splashes/{$rand_splash_pic}"
		: "/public/images/header/logo.png";
	
	if(session_status() == PHP_SESSION_NONE)
		session_start();

	if(isset($_SESSION['ANORRL$UserPage$RandomImages']))
		unset($_SESSION['ANORRL$UserPage$RandomImages']);

	//this is so that if the user ever sets 'background:' on the profile css it'll not apply the night background
	//because the night background can override the user's background
	$hasBackground = false;

	$userCSS = isset($get_user) ? UserSettings::Get($get_user)->css : (SESSION ? $GLOBALS['__session']->settings->css : "");
	if (!empty($userCSS) && preg_match('/background\s*:/i', $userCSS)) {
		$hasBackground = true;
	}

	/*
	$hasBackground = false;
	if (isset($get_user)) {
   		$userCss = $header_data->GetUserCSS();
    	if (!empty($userCss) && preg_match('/background\s*:/i', $userCss)) {
        	$hasBackground = true;
    	}
	}
	*/
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?= $this->title ?><?php if(!str_contains($this->title, "ANORRL")): ?> - ANORRL<?php endif ?></title>
		<link rel="icon" type="image/x-icon" href="/favicon.ico">
		
		<?php foreach($this->scripts as $script): ?>
		<script src="<?= $script ?>"></script>
		<?php endforeach ?>
		
		<?php foreach($this->stylesheets as $stylesheet): ?>
		<link rel="stylesheet" href="<?= $stylesheet ?>">
		<?php endforeach ?>
		<link rel="stylesheet" href="/public/css/new/finobe-port.css?v=2">

		<?php foreach($this->metas as $meta): ?>
		<meta property="<?= $meta['type'] ?>" content="<?= $meta['contents'] ?>">
		<?php endforeach ?>
		
		<?php if($this->settings->loadingscreens_enabled && !ClientDetector::IsAClient()): ?>
		<style>
			#LoadingScreen {
				inset: 0;
				position: fixed;
				width: 100vw;
				height: 100vh;
				background: linear-gradient(#33333399, #00000099);
				z-index: 10000;
				color: white;
				text-align: center;
				display:flex;
				font-size: 16px;
				justify-content: center;
				align-items: center;
				backdrop-filter: blur(10px);
				opacity: 0;
			}

			#LoadingScreen p[caption] {
				margin-top: 3px;
				margin-bottom: 25px;
				font-size: 14px;
				letter-spacing: 0px;
				font-style:italic;
				font-weight: bold;
			}

			#LoadingScreen img[splash] {
				border-radius: 5px;
				border: 3px solid black;
			}

			#LoadingScreen img[loading] {
				width: 100px;
			}
		</style>
		<script>
			const wait = (delay = 0) =>	new Promise(resolve => setTimeout(resolve, delay));

			function setVisible(element, visible) {
				if(element == "#LoadingScreen")
					$(element).css("opacity", visible ? 1 : 0);
			}

			// do loading screen if the page hasn't loaded in a second.

			var hasLoaded = false;
			var initiateLoading = false;

			$(window).load(function() {
				hasLoaded = true;
				$("#LoadingScreen").css("transition", "opacity 0.75s");
				if(initiateLoading) {
					// mom im a genius
					wait(200).then(() => {
						setVisible("#LoadingScreen", false);
						$("#LoadingScreen").css("pointer-events", "none");
						
					});
					wait(1500).then(() => {
						$("#LoadingScreen").remove();
					});
				} else {
					$("#LoadingScreen").remove();
				}
			});

			wait(500).then(() => {
				if(!hasLoaded) {
					$("#LoadingScreen").css("transition", "opacity 0.25s");
					setVisible('#LoadingScreen', true);
					initiateLoading = true;
				}
			})

			
		</script>
		<?php endif ?>
	</head>
	<body <?= $this->settings->nightbg_enabled && !$hasBackground ? "night" : "" ?>>
		<?php if($this->settings->loadingscreens_enabled && !ClientDetector::IsAClient()): ?>
		<div id="LoadingScreen">
			<div>
				<img src="<?= $rand_splash_src ?>" splash>
				<p caption><?= $splashscreencaption?></p>
				<p id="LoadingText">Loading <?= $this->title ?>...</p>
				<img src="/public/images/spinner100x100_white.gif" loading>
			</div>
		</div>
		<?php endif ?>
		<?php if($this->bad_apple): ?>
		<style>
			body {
				background: url('/public/images/badapple.gif') !important;
			}
		</style>
		<?php endif ?>
		<?php if($this->settings->randoms_enabled && strlen($rand_pic) != 0): ?>
		<img src="/public/images/randoms/<?= $rand_pic ?>" style="position: fixed;bottom: 0px;left: 0px;width: 250px;z-index: 9999;pointer-events: none;">
		<?php endif ?>
		<?php if($this->settings->teto_enabled): ?>
		<div id="TetoContainer">
			<div id="TetoSplashContainer">
				<p id="TetoSplash"><?= (new FileSplasher("teto"))->getRandomSplash(); ?></p>
			</div>
			<img id="Teto" src="/public/images/tetospeech.png">
		</div>
		<?php endif ?>
		<?php if($this->settings->accessibility_enabled): ?>
		<style>
			@font-face {
				font-family: 'punk';
				src: url('/css/SplendidB.ttf');
			}
		</style>
		<?php endif ?>
		<div id="Container">
			<div id="Header">
				<?php $pendingreqscount = $header_check_user != null ? $header_check_user->GetPendingFriendRequestsCount() : 0; ?>
				<nav class="navbar navbar-expand-lg navbar-light bg-faded navbar-static-top">
					<div class="container">
						<a href="/" class="navbar-brand">
							<img src="/public/images/header/logo.png" alt="ANORRL" class="navbar-brandimg">
							ANORRL
						</a>
						<div class="anorrl-navbar-cluster">
							<ul class="nav navbar-nav mr-auto">
								<?php if($header_check_user != null): ?>
								<li class="nav-item"><a href="/users/<?= $header_check_user->id ?>/profile" class="nav-link">Profile</a></li>
								<?php else: ?>
								<li class="nav-item"><a href="/" class="nav-link">Home</a></li>
								<?php endif ?>
								<li class="nav-item"><a href="/games" class="nav-link">Games</a></li>
								<li class="nav-item"><a href="/catalog" class="nav-link">Catalog</a></li>
								<li class="nav-item"><a href="/vandals" class="nav-link">Vandals</a></li>
								<li class="nav-item"><a href="/download" class="nav-link">Download</a></li>
								<?php if($header_check_user != null && $header_check_user->isAdmin()): ?>
								<li class="nav-item"><a href="/Admi" class="nav-link">Admin</a></li>
								<?php endif ?>
							</ul>
							<?php if($header_check_user != null): ?>
							<ul class="nav navbar-nav my-2 my-lg-0 anorrl-userbar">
								<li class="nav-item anorrl-userbar-item">
									<a href="/my/friends" class="nav-link">
										<img src="/public/images/icons/messages<?= $pendingreqscount == 0 ? "" : "_notify" ?>.png" alt="">
										<span><?= $pendingreqscount ?> requests</span>
									</a>
								</li>
								<li class="nav-item anorrl-userbar-item">
									<a href="/my/friends" class="nav-link">
										<img src="/public/images/icons/friends.png" alt="">
										<span><?= $header_check_user->getFriendsCount() ?> friends</span>
									</a>
								</li>
								<li class="nav-item anorrl-userbar-item anorrl-userbar-profile">
									<a href="/users/<?= $header_check_user->id ?>/profile" class="nav-link">
										<span class="anorrl-userbar-name"><?= $header_check_user->name ?></span>
										<span class="anorrl-userbar-note"><?= htmlspecialchars($randomsignsplash, ENT_QUOTES) ?></span>
									</a>
								</li>
								<li class="nav-item"><a id="LogoutSign" href="javascript:ANORRL.Logout()" class="nav-link anorrl-logout-link">Logout</a></li>
							</ul>
							<?php else: ?>
							<ul class="nav navbar-nav my-2 my-lg-0">
								<li class="nav-item"><a href="/login" id="LoginSign" class="nav-link">Login</a></li>
								<li class="nav-item"><a href="/register" id="RegisterSign" class="nav-link">Register</a></li>
							</ul>
							<?php endif ?>
						</div>
					</div>
				</nav>
				<?php if($header_check_user != null): ?>
				<div class="nav-scroller navbar-light bg-faded">
					<div class="container">
						<nav class="nav nav-underline nav-scroller-inner" id="UserLinks">
							<a href="/my/home" class="nav-link" <?php if($this->internal_name == "my/home"): ?>selected<?php endif ?>>Home</a>
							<a href="/my/profile" class="nav-link" <?php if($this->internal_name == "my/profile"): ?>selected<?php endif ?>>Account</a>
							<a href="/my/character" class="nav-link" <?php if($this->internal_name == "my/character"): ?>selected<?php endif ?>>Character</a>
							<a href="/my/friends" class="nav-link" <?php if($this->internal_name == "my/friends"): ?>selected<?php endif ?>>Friends</a>
							<a href="/create/" class="nav-link" <?php if($this->internal_name == "my/create"): ?>selected<?php endif ?>>Create</a>
							<a href="/my/stuff" class="nav-link" <?php if($this->internal_name == "my/stuff"): ?>selected<?php endif ?>>Stuff</a>
							<a href="/download" class="nav-link" <?php if($this->internal_name == "download/index"): ?>selected<?php endif ?>>Download</a>
						</nav>
					</div>
				</div>
				<?php endif ?>
			</div>
			<?php if(!ClientDetector::IsAClient()): ?>
			<div class="DisplayMobileWarning" style="display: none">
				<div id="MobileWarningText">
					<h1>HEADS UP!</h1>
					<p>This isn't optimised for mobile devices, best to use a pc (as this was designed for that)</p>
					<button onclick="ANORRL.HideMobileWarning()">Continue anyways...</button>
				</div>
			</div>
			<?php endif ?>
			<div id="Body">
				<div id="BodyContainer" class="content-container finobe-modern-style">
					
