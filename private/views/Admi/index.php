<?php
	use anorrl\Page;

	$user = $GLOBALS['__session']->user;

	if(!$user->isAdmin()) {
		die("Hey... You're not an admin I don't think...");
	}

	$page = new Page("Admin Panel");
	$page->addStylesheet("/css/new/admin-panel.css?v=1");
	$page->loadHeader();
?>
<div id="AdminDashboard">
	<div class="admin-hero">
		<p class="admin-kicker">Restricted Access</p>
		<h1>Admin Control Surface</h1>
		<p class="admin-subtitle">Only users with the Administrator profile badge can see this page.</p>
	</div>

	<div class="admin-grid">
		<section class="admin-card">
			<h2>Moderation</h2>
			<p>Use this area as the entry point for reports, bans, content review and live moderation actions.</p>
			<div class="admin-actions">
				<a class="admin-button" href="/vandals">Open Vandals</a>
				<a class="admin-button admin-button-secondary" href="/catalog">Review Catalog</a>
			</div>
		</section>

		<section class="admin-card">
			<h2>User Signals</h2>
			<ul class="admin-list">
				<li>Signed in as: <strong><?= htmlspecialchars($user->name, ENT_QUOTES) ?></strong></li>
				<li>Role source: <strong>Administrator badge</strong></li>
				<li>Scope: <strong>Frontend-only dashboard for now</strong></li>
			</ul>
		</section>

		<section class="admin-card">
			<h2>Quick Actions</h2>
			<div class="admin-actions admin-actions-stack">
				<a class="admin-button" href="/create/">Create Asset</a>
				<a class="admin-button" href="/download">Downloads</a>
				<a class="admin-button admin-button-secondary" href="/my/home">Back Home</a>
			</div>
		</section>
	</div>
</div>
<?php $page->loadFooter(); ?>
