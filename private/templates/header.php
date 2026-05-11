<?php
	$currentUser = null;
	if (defined('SESSION') && SESSION && isset(SESSION->user)) {
		$currentUser = SESSION->user;
	}

	$pendingRequests = $currentUser ? $currentUser->getPendingFriendRequestsCount() : 0;
	$siteAnnouncement = isset(CONFIG->site_announcement) ? trim((string) CONFIG->site_announcement) : '';
	$announcementColorKey = isset(CONFIG->site_announcement_color) ? (string) CONFIG->site_announcement_color : 'sky';
	$announcementColors = [
		'sky' => ['background' => '#20a8c9', 'text' => '#0f1b22'],
		'green' => ['background' => '#38a169', 'text' => '#f4fff7'],
		'gold' => ['background' => '#d6a21d', 'text' => '#221700'],
		'red' => ['background' => '#c84b4b', 'text' => '#fff5f5'],
		'violet' => ['background' => '#7b61c8', 'text' => '#f8f5ff']
	];
	if (!array_key_exists($announcementColorKey, $announcementColors)) {
		$announcementColorKey = 'sky';
	}
	$announcementColor = $announcementColors[$announcementColorKey];
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= htmlspecialchars($this->title ?? "Zomium", ENT_QUOTES, 'UTF-8') ?></title>
	<link rel="icon" type="image/x-icon" href="/favicon.ico">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
	<link rel="stylesheet" href="/public/css/new/app.css?v=8">
	<?php foreach ($this->stylesheets as $stylesheet): ?>
		<link rel="stylesheet" href="<?= htmlspecialchars($stylesheet, ENT_QUOTES, 'UTF-8') ?>">
	<?php endforeach ?>
	<?php foreach ($this->metas as $meta): ?>
		<meta property="<?= htmlspecialchars($meta['type'], ENT_QUOTES, 'UTF-8') ?>" content="<?= htmlspecialchars($meta['contents'], ENT_QUOTES, 'UTF-8') ?>">
	<?php endforeach ?>
	<?php foreach ($this->scripts as $script): ?>
		<script src="<?= htmlspecialchars($script, ENT_QUOTES, 'UTF-8') ?>"></script>
	<?php endforeach ?>
</head>
<body <?= $this->settings->nightbg ? "night" : "" ?>>
<style>
	.app-navbar .nav-link,
	.app-subnav .nav-link,
	.nav-scroller-inner > a.nav-link,
	.navbar-brand,
	.dropdown-item {
		font-family: "Source Sans Pro", "Helvetica Neue", Helvetica, Arial, sans-serif !important;
		font-weight: 650 !important;
	}

	.global-announcement {
		font-size: 1rem;
		padding: .45rem 1rem;
		text-align: center;
	}
</style>
<div class="app-shell d-flex flex-column min-vh-100">
	<nav class="navbar navbar-expand-lg navbar-dark app-navbar shadow-sm sticky-top">
		<div class="container">
			<a class="navbar-brand d-flex align-items-center" href="/my/home">
				<img src="/public/images/legacy/finnobe3llogo.png" alt="Zomium" class="app-brand-logo">
			</a>

			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>

			<div class="collapse navbar-collapse" id="navbarMain">
				<ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center">
					<li class="nav-item"><a class="nav-link" href="/my/home">ZOMIUM</a></li>
					<?php if ($currentUser): ?>
						<li class="nav-item"><a class="nav-link" href="/users/<?= $currentUser->id ?>/profile">Profile</a></li>
					<?php endif; ?>
					<li class="nav-item"><a class="nav-link" href="/games">Games</a></li>
					<li class="nav-item"><a class="nav-link" href="/catalog">Catalog</a></li>
					<?php if ($currentUser): ?>
						<li class="nav-item"><a class="nav-link" href="/create">Create</a></li>
						<li class="nav-item"><a class="nav-link" href="/forum">Forum</a></li>
					<?php endif; ?>
					<li class="nav-item"><a class="nav-link" href="/badges">Badges</a></li>
				</ul>

				<?php if (!$currentUser): ?>
					<div class="d-flex flex-column flex-lg-row gap-2 my-3 my-lg-0">
						<a class="btn btn-outline-light" href="/login">Login</a>
						<a class="btn btn-primary" href="/register">Register</a>
					</div>
				<?php else: ?>
					<ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
						<li class="nav-item">
							<a class="nav-link icon-nav-link" href="/my/friends" title="Friend requests">
								<i class="fas fa-user-friends"></i>
								<?php if ($pendingRequests > 0): ?>
									<span class="badge rounded-pill bg-danger badge-notification"><?= $pendingRequests ?></span>
								<?php endif; ?>
							</a>
						</li>
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
								<span class="user-pill-avatar">
									<img src="<?= htmlspecialchars($currentUser->getThumbsUrlService($currentUser->setprofilepicture ? "profile" : "headshot", 100, 100), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($currentUser->name, ENT_QUOTES, 'UTF-8') ?>">
								</span>
								<span><?= htmlspecialchars($currentUser->name, ENT_QUOTES, 'UTF-8') ?></span>
							</a>
							<ul class="dropdown-menu dropdown-menu-end shadow">
								<li><a class="dropdown-item" href="/my/home"><i class="fas fa-columns me-2"></i>Dashboard</a></li>
								<li><a class="dropdown-item" href="/users/<?= $currentUser->id ?>/profile"><i class="fas fa-user me-2"></i>Profile</a></li>
								<li><a class="dropdown-item" href="/my/stuff"><i class="fas fa-box-open me-2"></i>Inventory</a></li>
								<li><a class="dropdown-item" href="/my/character"><i class="fas fa-tshirt me-2"></i>Character</a></li>
								<li><a class="dropdown-item" href="/my/places"><i class="fas fa-map me-2"></i>Places</a></li>
								<?php if ($currentUser->isAdmin()): ?>
									<li><a class="dropdown-item" href="/create"><i class="fas fa-plus me-2"></i>Create</a></li>
								<?php endif; ?>
								<li><a class="dropdown-item" href="/download"><i class="fas fa-download me-2"></i>Download</a></li>
								<li><hr class="dropdown-divider"></li>
								<li><a class="dropdown-item text-danger" href="javascript:ANORRL.Logout()"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
							</ul>
						</li>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</nav>

	<?php if ($currentUser): ?>
		<div class="app-subnav">
			<div class="container">
				<div class="nav nav-pills nav-finobe flex-nowrap overflow-auto py-2">
					<?php if ($currentUser->isAdmin()): ?>
						<a class="nav-link" href="/create"><i class="fas fa-plus me-2"></i>Create</a>
					<?php endif; ?>
					<a class="nav-link" href="/forum"><i class="fas fa-comments me-2"></i>Forum</a>
					<a class="nav-link" href="/my/character"><i class="fas fa-user me-2"></i>Character</a>
					<a class="nav-link" href="/my/stuff"><i class="fas fa-box-open me-2"></i>Inventory</a>
					<a class="nav-link" href="/my/friends"><i class="fas fa-user-friends me-2"></i>Friends</a>
					<a class="nav-link" href="/my/places"><i class="fas fa-map me-2"></i>Places</a>
					<li class="nav-item"><a class="nav-link" href="/download">Download</a></li>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if ($siteAnnouncement !== ''): ?>
		<div class="global-announcement" style="background: <?= htmlspecialchars($announcementColor['background'], ENT_QUOTES, 'UTF-8') ?>; color: <?= htmlspecialchars($announcementColor['text'], ENT_QUOTES, 'UTF-8') ?>;">
			<?= htmlspecialchars($siteAnnouncement, ENT_QUOTES, 'UTF-8') ?>
		</div>
	<?php endif; ?>

	<div class="DisplayMobileWarning alert alert-warning rounded-0 border-0 text-center mb-0 d-none">
		Mobile support is limited on this build.
		<button class="btn btn-sm btn-dark ms-2" onclick="ANORRL.HideMobileWarning()">Continue</button>
	</div>

	<main class="app-main flex-fill">
		<div class="container">
