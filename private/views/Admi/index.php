<?php
	use anorrl\utilities\UserUtils;
	use anorrl\Page;

	$user = SESSION->user;

	if (!$user->isAdmin()) {
		die("Hey... You're not an admin I don't think...");
	}

	$page = new Page("Admin panel");
	$page->loadHeader();
?>
<style>
	.admin-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
		gap: 1.5rem;
		margin: 2rem 0;
	}

	.admin-card {
		background: #17171a;
		border: 1px solid rgba(255,255,255,.08);
		border-radius: .35rem;
		padding: 1.25rem;
	}

	.admin-card h3 {
		margin-bottom: .6rem;
	}

	.admin-card p {
		color: #b7bdc4;
		margin-bottom: 1rem;
	}

	#NewUsersContainer {
		margin-top: 2rem;
	}
</style>

<div>
	<h1>Admin panel</h1>
	<p>Welcome to the admin panel, here you can do admin stuff and also see some stats about the website.</p>
	<hr>

	<div class="admin-grid">
		<div class="admin-card">
			<h3>Announcements</h3>
			<p>Home sayfasindaki ust duyuru yazisini buradan duzenle veya kaldir.</p>
			<a class="btn btn-primary" href="/admi/announcements">Open announcements</a>
		</div>
	</div>

	<div id="NewUsersContainer">
		<h3>All users!</h3>
		<table id="NewUsersBox">
			<?php
				$users = UserUtils::GetRandomUsers(6);
				$users_count = count($users);
			?>
			<tr>
				<?php foreach ($users as $listedUser): ?>
				<td>
					<div class="User" title="<?= htmlspecialchars($listedUser->name, ENT_QUOTES, 'UTF-8') ?>">
						<a href="/users/<?= $listedUser->id ?>/profile">
							<img src="<?= htmlspecialchars($listedUser->getThumbsUrl(100), ENT_QUOTES, 'UTF-8') ?>">
							<span><?= htmlspecialchars($listedUser->name, ENT_QUOTES, 'UTF-8') ?></span>
						</a>
					</div>
				</td>
				<?php endforeach;
					if ($users_count < 6) {
						$count = 1000 - $users_count;
						for ($i = 0; $i < $count; $i++) {
							echo "<td></td>";
						}
					}
				?>
			</tr>
		</table>
	</div>
</div>

<?php $page->loadFooter(); ?>
