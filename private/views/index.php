<?php
	use anorrl\utilities\FileSplasher;
	use anorrl\utilities\UserUtils;
	use anorrl\UserSettings;
	use anorrl\Page;

	$page = new Page("index");
	$page->addStylesheet("/css/new/frontpage.css?v=6");
	$page->loadHeader();

	$settings = SESSION ? SESSION->settings : UserSettings::Get();

?>

		<div id="Details">
			<h2>ZOMIUM</h2>
			<code>
				Zomium is a revival that is so much work in progress also this page will be edited btw
				<br><br>
				This is a 2016E Private Revival created by <a href="/users/1/profile">xloang</a> 
				<br><br>
				this page will be edited.
				<br><br>
				<span style="font-size: 10px; color: #CCC; display: block; width: 100%; text-align: center">also this shit does have discord server</span>
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

	