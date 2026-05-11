<?php
	
	use anorrl\utilities\UserUtils;
	use anorrl\Page;
	use anorrl\User;

	$user = SESSION->user;

	$excludelist = [
		1
	];
if(!$user->isAdmin()) {
		header("Location: /");
	}
	function createProfile(int $id, string $description) {
		$profileUser = User::FromID($id);

		if(!$profileUser) {
			return;
		}
		
		$name = $profileUser->name;
		$thumbs = $profileUser->getThumbsUrl();

		if($profileUser != null) {
			global $excludelist;
			$excludelist[] = $id;
			echo <<<EOT
			<td>
				<div>
					<a href="/users/$id/profile">
						<img src="$thumbs&sxy=128" width="128" height="128">
						<span>$name</span>
					</a>
				</div>
				<div style="text-align: center; border: 2px solid black; background: #1a1a1a; padding: 10px;">
					$description
				</div>
			</td>
			EOT;
		}
	}

	$page = new Page("Contributors");
	$page->loadHeader();
?>
<div>

<h1>this page is work in progress but some of the contributors are listed below:</h1>
<hr>
<h1>XLOANG</h1>
<p>he is owner and creator of the project. also its me btw</p>


</div>