<?php
	use anorrl\Page;
	use anorrl\utilities\FileSplasher;

	$fs = new FileSplasher("client");
	$randomsplash = $fs->getRandomSplash();

	$page = new Page("Download", "download/index");
	$page->addStylesheet("/css/new/download.css");

	$page->loadHeader();
?>
<h2><?= $randomsplash ?></h2>
<div id="DownloadContainer">
	<div id="DownloadLinks">
		<H1>Download Zomium</H1>
		<p>DOWNLOAD IT</p>
		<a href="/download/windows" class="btn btn-primary">Download for Windows</a>
		<a href="/skibisi" class="btn btn-primary">SOON!!!!!!</a>
	</div>
</div>
<?php $page->loadFooter() ?>