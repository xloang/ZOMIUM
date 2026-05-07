<?php
	use anorrl\utilities\FileSplasher;
	use anorrl\utilities\UserUtils;
	use anorrl\UserSettings;
	use anorrl\Page;

	$page = new Page("Admin panel");
	$page->loadHeader();
	$page->addStylesheet("/css/new/frontpage.css?v=6");
	$settings = SESSION ? SESSION->settings : UserSettings::Get();

	
	$user = SESSION->user;

	if(!$user->isAdmin()) {
		die("Hey... You're not an admin I don't think...");
	}
?>
<div>,
	<h1>Admin panel</h1>
	<p>Welcome to the admin panel, here you can do admin stuff and also see some stats about the website.</p>

	<div id="Details">
		<h2>Stats</h2>
		<code>
			Total users: <?= UserUtils::GetTotalUsers() ?>
			<br><br>
			Total visits: <?= FileSplasher::getTotalSplashes("visits") ?>
			<br><br>
			Total videos: <?= FileSplasher::getTotalSplashes("videos") ?>
		</code>
	</div>
</div>

<div id="NewUsersContainer">
		<h3>Random Users!</h3>
		<table id="NewUsersBox">
			<?php 
				$users = UserUtils::GetRandomUsers(6);
				$users_count = count($users);
			?>
			<tr>
				<?php foreach($users as $user): ?>
				<td>
					<div class="User" title="<?= $user->name ?>">
						<a href="/users/<?= $user->id ?>/profile">
							<img src="<?= $user->getThumbsUrl(100) ?>">
							<span><?= $user->name ?></span>
						</a>
					</div>
				</td>
				<?php endforeach;
					if($users_count < 6) {
						$count = 6 - $users_count;
						for($i = 0; $i < $count; $i++) {
							echo "<td></td>";
						}
					}
				?>
			</tr>
		</table>
	</div>

	