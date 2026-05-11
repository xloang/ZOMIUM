<?php
	use anorrl\Page;

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

	$page = new Page("Zomium", "index");
	$page->loadHeader();
?>
<style>
body {
	background: #0d1117;
	margin: 0;
	min-height: 100vh;
	position: relative;
	overflow-x: hidden;
}

body::before {
	content: "";
	position: fixed;
	inset: 0;

	background:
		linear-gradient(180deg, rgba(9,15,25,.55), rgba(9,15,25,.85)),
		url('/public/images/xmas_small.jpg');

	background-size: cover;
	background-position: center;
	background-repeat: no-repeat;

	filter: blur(8px);
	transform: scale(1.15);

	z-index: -2;
}

body::after {
	content: "";
	position: fixed;
	inset: 0;
	background: rgba(0,0,0,.35);
	z-index: -1;
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
</style>
<main class="app-main p-0">
	<section class="landing-shell">
		<div class="landing-bg"></div>
		<div class="landing-overlay"></div>
		<div class="landing-content">
			<img class="landing-logo" src="/public/images/ZoniumBIG2.png" alt="Zomium">
			<div class="landing-message">
				<?= htmlspecialchars($messages[array_rand($messages)], ENT_QUOTES, 'UTF-8') ?>
			</div>
		</div>
	</section>
</main>
<?php $page->loadFooter(); ?>
