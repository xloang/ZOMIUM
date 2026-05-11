<?php
	use anorrl\enums\ANORRLBadge;
	use anorrl\Page;

	$page = new Page("Badges");

	$page->loadHeader();
	
if(!$user->isAdmin()) {
		die("Hey... You're not an admin I don't think...");
	}
	function createBadge(ANORRLBadge $badge) {
		$badgenamefile = str_replace(" ", "", $badge->name());
		echo <<<EOT
			<li id="Badge{$badge->ordinal()}" class="Badge">
				<div class="BadgePadding">&nbsp;</div>
				<div class="BadgeContent">
					<div class="BadgeImage">
						<img src="/public/images/Badges/$badgenamefile.png?v=2" alt="{$badge->name()}" width="100" height="100">
					</div>
					<div class="BadgeDescription">
						<h3>{$badge->name()} Badge</h3>
						<p class="notranslate">{$badge->description()}</p>
					</div>
					<div style="clear:both"></div>
				</div>
			</li>
		EOT;
	}
?>
<div class="Content">
	<h1>Badges</h1>
	<img src="/public/images/error.png" alt="All Badges" width="400" height="400">
	<p>THIS PAGE IS WORK IN PROGRESS!</p>
