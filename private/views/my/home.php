<?php
	use anorrl\Page;
	use anorrl\Status;

	$user = SESSION->user;

	if (isset($_POST['ANORRL$Home$Status$Text']) && isset($_POST['ANORRL$Home$Status$Submit'])) {
		$result = Status::Send($user->id, trim($_POST['ANORRL$Home$Status$Text']));

		if ($result['error']) {
			$_SESSION['ANORRL$Home$StatusError'] = true;
			$_SESSION['ANORRL$Home$StatusResult'] = $result['reason'];
		} else {
			$_SESSION['ANORRL$Home$StatusError'] = false;
			$_SESSION['ANORRL$Home$StatusResult'] = 'Success!';
		}

		die(header('Location: /my/home'));
	}

	$statusResult = $_SESSION['ANORRL$Home$StatusResult'] ?? null;
	$statusError = $_SESSION['ANORRL$Home$StatusError'] ?? false;
	unset($_SESSION['ANORRL$Home$StatusResult'], $_SESSION['ANORRL$Home$StatusError']);

	$page = new Page("Dashboard", "my/home");
	$page->addScript("/js/home.js?t=1771413901");
	$page->loadHeader();
?>
<style>
	.home-kicker,
	.home-title,
	.home-subtitle,
	.section-title,
	.feed-heading,
	.social-card-title {
		font-family: "Source Sans Pro", Arial, Helvetica, sans-serif;
	}

	body {
		background: #101011 !important;
		background-image: none !important;
		font-family: "Source Sans Pro", Arial, Helvetica, sans-serif;
		font-weight: 500;
	}

	.app-main > .container {
		max-width: 100%;
		padding-left: 0;
		padding-right: 0;
	}

	.home-hero {
		position: relative;
		min-height: 360px;
		display: flex;
		align-items: center;
		overflow: hidden;
		border-bottom: 1px solid rgba(255,255,255,.08);
		background:
			linear-gradient(180deg, rgba(19, 12, 34, .12) 0%, rgba(8, 8, 10, .7) 58%, #111214 100%),
			url('/public/images/xmas_small.jpg') center center / cover no-repeat;
	}

	.home-hero::before {
		content: "";
		position: absolute;
		inset: 0;
		backdrop-filter: blur(10px);
		background: rgba(125, 165, 205, .1);
	}

	.home-hero > .container {
		position: relative;
		z-index: 1;
		max-width: 1140px;
		padding: 4.5rem 1rem 4rem;
	}

	.home-kicker {
		font-size: .95rem;
		letter-spacing: .08em;
		text-transform: uppercase;
		color: rgba(255,255,255,.72);
		margin-bottom: .75rem;
	}

	.home-title {
		font-size: clamp(2.65rem, 4vw, 4rem);
		font-weight: 400;
		color: #f3f5f8;
		margin-bottom: .85rem;
	}

	.home-subtitle {
		max-width: 42rem;
		color: rgba(255,255,255,.84);
		font-size: 1.1rem;
		margin-bottom: 2rem;
	}

	.home-hero-art {
		display: flex;
		justify-content: center;
		align-items: center;
	}

	.home-hero-avatar {
		width: min(260px, 75%);
		max-width: 260px;
		aspect-ratio: 1 / 1;
		object-fit: cover;
		filter: drop-shadow(0 18px 40px rgba(0,0,0,.4));
	}

	.home-cta {
		display: inline-flex;
		align-items: center;
		gap: .5rem;
		padding: .9rem 1.35rem;
		font-size: 1.6rem;
		border-radius: .25rem;
	}

	.home-content {
		max-width: 1140px;
		padding: 1.5rem 1rem 0;
	}

	.home-section {
		padding: 1.2rem 0 0;
	}

	.section-divider {
		border: 0;
		border-top: 1px solid rgba(255,255,255,.08);
		margin: 0 0 1rem;
	}

	.section-title {
		font-size: 1.2rem;
		font-weight: 400;
		color: #f1f3f5;
		margin-bottom: 1rem;
	}

	.section-copy {
		color: #bcc3cb;
		font-size: 1rem;
		margin-bottom: 0;
	}

	.status-alert {
		margin-bottom: 1rem;
		border-radius: .25rem;
		border: 1px solid rgba(255,255,255,.08);
	}

	.feed-panel {
		padding: 0;
	}

	.feed-heading {
		color: #fff;
		font-size: 1.95rem;
		font-weight: 400;
		margin-bottom: 1rem;
	}

	.feed-composer {
		background: #32353a !important;
		border: 1px solid rgba(255,255,255,.08) !important;
		border-radius: .25rem !important;
		color: #eef2f7 !important;
		min-height: 110px;
		resize: vertical;
	}

	.feed-composer::placeholder {
		color: #98a1ad;
	}

	.feed-submit {
		min-width: 110px;
	}

	#Feeds {
		border-collapse: separate;
		border-spacing: 0 1rem;
		margin-bottom: 0;
	}

	#Feeds .feed-row td {
		padding: 1rem;
		vertical-align: top;
		background: #1f1f23;
		color: #edf1f7;
		border-top: 1px solid rgba(255,255,255,.06);
		border-bottom: 1px solid rgba(255,255,255,.06);
	}

	#Feeds .feed-row td:first-child {
		width: 110px;
		border-left: 1px solid rgba(255,255,255,.06);
		border-top-left-radius: .35rem;
		border-bottom-left-radius: .35rem;
	}

	#Feeds .feed-row td:last-child {
		border-right: 1px solid rgba(255,255,255,.06);
		border-top-right-radius: .35rem;
		border-bottom-right-radius: .35rem;
	}

	#Feeds .User a {
		color: #fff;
		text-decoration: none;
	}

	#Feeds .User img {
		width: 72px;
		height: 72px;
		object-fit: cover;
		border-radius: .75rem;
		border: 1px solid rgba(255,255,255,.08);
		background: #121212;
		display: block;
		margin-bottom: .55rem;
	}

	#Feeds .User #Name {
		display: inline-block;
		font-size: 1rem;
		font-weight: 600;
	}

	#Feeds #Content code {
		display: block;
		white-space: pre-wrap;
		font-family: inherit;
		color: #eef2f7;
		background: transparent;
		padding: 0;
		font-size: 1rem;
		line-height: 1.6;
	}

	#DatePosted {
		color: #8d95a3;
		font-size: .95rem;
		margin-top: .75rem;
	}

	#DatePosted a {
		color: #7bbcff;
		margin-left: .45rem;
	}

	.feed-pager {
		color: #c8d0da;
	}

	.feed-pager a {
		color: #d7e9ff;
		text-decoration: none;
	}

	.feed-pager a:hover {
		text-decoration: underline;
		text-decoration-color: #6fb7ff;
		text-underline-offset: .25rem;
	}

	.social-grid {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: 1.5rem;
	}

	.social-card {
		display: block;
		background: #33363b;
		border: 1px solid rgba(255,255,255,.05);
		border-radius: .25rem;
		padding: 1.25rem;
		color: #dfe6ee !important;
		transition: transform .18s ease, border-color .18s ease, background-color .18s ease;
	}

	.social-card:hover {
		transform: translateY(-2px);
		border-color: rgba(255,255,255,.12);
		background: #383c42;
	}

	.social-card-title {
		display: flex;
		align-items: center;
		gap: .8rem;
		font-size: 1.1rem;
		color: #f7f9fb;
		margin-bottom: .75rem;
	}

	.social-card-title i {
		font-size: 1.55rem;
		width: 1.8rem;
		text-align: center;
	}

	.social-card-title .fa-discord {
		color: #8ea2ff;
	}

	.social-card-title .fa-youtube {
		color: #ff2e2e;
	}

	.social-card-copy {
		color: #c6ced7;
		font-size: 1rem;
		line-height: 1.55;
		margin-bottom: 0;
	}

	.home-note {
		background: #33363b;
		border: 1px solid rgba(255,255,255,.05);
		border-radius: .25rem;
		padding: 1.35rem 1.25rem;
		color: #d8dfe7;
		margin: 3rem 0 1.5rem;
	}

	.home-note p {
		margin: 0 0 .45rem;
	}

	.home-note p:last-child {
		margin-bottom: 0;
	}

	.home-note a {
		color: #56bb57 !important;
	}

	@media (max-width: 991.98px) {
		.home-hero {
			min-height: auto;
		}

		.home-hero > .container {
			padding-top: 3rem;
		}

		.home-hero-art {
			margin-top: 2rem;
		}
	}

	@media (max-width: 767.98px) {
		.home-title {
			font-size: 2.25rem;
		}

		.home-cta {
			font-size: 1.15rem;
			width: 100%;
			justify-content: center;
		}

		.social-grid {
			grid-template-columns: 1fr;
		}
	}
</style>

<section class="home-hero">
	<div class="container">
		<div class="row align-items-center g-4">
			<div class="col-lg-8">
				<h1 class="home-title">welcome back, <?= htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') ?></h1>
				<a class="btn btn-success home-cta" href="#feed-section">
					<span>&rsaquo;</span>
					<span>join a game</span>
				</a>
			</div>
			<div class="col-lg-4">
				<div class="home-hero-art">
					<img class="home-hero-avatar" src="<?= htmlspecialchars($user->getThumbsUrlService($user->setprofilepicture ? "profile" : "headshot", 320, 320), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') ?>">
				</div>
			</div>
		</div>
	</div>
</section>

<div class="container home-content">
	<section class="home-section">
		<hr class="section-divider">
		<h2 class="section-title">popular places</h2>
		<p class="section-copy"></p>
	</section>

	<section class="home-section" id="feed-section">
		<hr class="section-divider">
		<div class="feed-panel">
			<h2 class="feed-heading">your feed</h2>

			<?php if ($statusResult !== null): ?>
				<div class="alert <?= $statusError ? 'alert-danger' : 'alert-success' ?> status-alert">
					<?= htmlspecialchars($statusResult, ENT_QUOTES, 'UTF-8') ?>
				</div>
			<?php endif; ?>

			<form method="post" class="mb-4">
				<div class="mb-3">
					<textarea
						class="form-control feed-composer"
						name="ANORRL$Home$Status$Text"
						maxlength="64"
						placeholder="How are you?"></textarea>
				</div>
				<button class="btn btn-success feed-submit" type="submit" name="ANORRL$Home$Status$Submit">post</button>
			</form>

			<div id="FeedsContainer">
				<table class="table align-middle" id="Feeds">
					<tr id="FeedItem" class="feed-row d-none" template>
						<td class="User">
							<a href="#">
								<img src="/thumbs/headshot?id=1&sxy=90" alt="avatar">
								<span id="Name">user</span>
							</a>
						</td>
						<td id="Content">
							<code>status</code>
							<div id="DatePosted">
								<span id="Date">just now</span>
								<a href="#">report</a>
							</div>
						</td>
					</tr>
				</table>

				<div id="Pager" class="feed-pager d-flex justify-content-between align-items-center pt-2" style="display:none;">
					<a href="javascript:ANORRL.Home.DeadvanceFeed()" id="BackPager">previous</a>
					<span id="PageCounter">Page 1 of 1</span>
					<a href="javascript:ANORRL.Home.AdvanceFeed()" id="NextPager">next</a>
				</div>
			</div>
		</div>
	</section>

	<section class="home-section">
		<hr class="section-divider">
		<h2 class="section-title">follow us on our socials</h2>
		<div class="social-grid">
			<a class="social-card" href="https://google.com" target="_blank" rel="noopener noreferrer">
				<div class="social-card-title">
					<i class="fab fa-discord"></i>
					<span>Discord</span>
				</div>
			</a>
			<a class="social-card" href="https://google.com" target="_blank" rel="noopener noreferrer">
				<div class="social-card-title">
					<i class="fab fa-youtube"></i>
					<span>YouTube</span>
				</div>
			</a>
		</div>
	</section>

	
</div>

<?php $page->loadFooter(); ?>
