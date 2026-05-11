<?php
	use anorrl\Asset;
	use anorrl\enums\CharacterMeshType;
	use anorrl\Page;
	use anorrl\enums\AssetType;
	use anorrl\utilities\AssetUploader;

	if(isset($type)) {
		$type = trim(strtolower($type));
	}

	$user = SESSION->user;

	$validtypes = [
		"hats",
		"faces",
		"shirts",
		"tshirts",
		"pants",
		"gears",
		"audio",
		"decals",
		"models",
		"meshes",
		"animations",
		"images",
		"lua",
		"body",
		"emotes"
	];

	$types = [
		"faces" => AssetType::FACE,
		"shirts" => AssetType::SHIRT,
		"tshirts" => AssetType::TSHIRT,
		"pants" => AssetType::PANTS,
		"audio" => AssetType::AUDIO,
		"decals" => AssetType::DECAL,
		"models" => AssetType::MODEL,
		"gears" => AssetType::GEAR,
		"meshes" => AssetType::MESH,
		"images" => AssetType::IMAGE,
		"lua" => AssetType::LUA,
		"hats" => AssetType::HAT,
		"animations" => AssetType::ANIMATION,
		"emotes" => AssetType::EMOTE,
	];

	if(!in_array($type, $validtypes))
		die(header("Location: /create/hats"));

	if(count($_POST) != 0) {
		if(in_array($type, $validtypes)) {
			if(isset($_POST['ANORRL$CreateAsset$Name']) &&
				isset($_POST ['ANORRL$CreateAsset$Description']) &&
				isset($_FILES['ANORRL$CreateAsset$File']) &&
				isset($_POST['ANORRL$CreateAsset$BodyType'])
			) {
				
				$result = null;
				$name = trim($_POST['ANORRL$CreateAsset$Name']);

				$description = trim($_POST['ANORRL$CreateAsset$Description']);
				$public = isset($_POST['ANORRL$CreateAsset$Public']);
				$comments_enabled = isset($_POST['ANORRL$CreateAsset$CommentsEnabled']);
				$on_sale = isset($_POST['ANORRL$CreateAsset$Purchasable']);

				$body_type = CharacterMeshType::index(intval($_POST['ANORRL$CreateAsset$BodyType']));

				if($body_type == null) {
					$_SESSION['ANORRL$CreateAsset$Error'] = true;
					$_SESSION['ANORRL$CreateAsset$Result'] = "Invalid body type!";
					
					die(header("Location: /create/".$type));
				}

				$asset_type = $type == "body" ? $body_type->assettype() : $types[$type];

				$result = AssetUploader::UploadAsset($_FILES['ANORRL$CreateAsset$File'], $asset_type, $name, $description, $public, $on_sale, $comments_enabled);
				
				if(isset($result)) {
					if($result['error']) {
						$_SESSION['ANORRL$CreateAsset$Error'] = true;
						$_SESSION['ANORRL$CreateAsset$Result'] = $result['reason'];
					} else {
						$_SESSION['ANORRL$CreateAsset$Error'] = false;
						$_SESSION['ANORRL$CreateAsset$Result'] = $result['id'];
					}
					
					die(header("Location: /create/".$type));
				}
			}
		} else {
			die("Not valid type...");
		}
	}

	$page = new Page("Create", "my/create");

	$page->addStylesheet("/css/new/create.css?v=2");
	$page->addStylesheet("/css/new/stuff.css?v=2");
	$page->addStylesheet("/css/new/forms.css?v=1");
	$page->addStylesheet("/css/new/window.css");

	$page->addScript("/js/create.js?t=1776537578");
	$page->loadHeader();
?>
<div class="Asset" template>
	<a id="NameAndThumbs" class="create-asset-card">
		<div class="create-asset-thumb">
			<img src="" alt="Asset thumb">
		</div>
		<div class="create-asset-copy">
			<div id="Pricing"></div>
			<span>AssetName</span>
		</div>
	</a>
</div>
<style>
	#StuffContainer {
		color: #eef2f6;
	}

	.create-shell {
		display: grid;
		grid-template-columns: 280px minmax(0, 1fr);
		gap: 1.25rem;
		align-items: start;
	}

	.create-hero {
		margin-bottom: 1.25rem;
		padding: 1.6rem 1.7rem;
		background: #18181b;
		border: 1px solid rgba(255,255,255,.08);
		border-radius: .45rem;
	}

	.create-hero h1 {
		margin: 0;
		font-size: 2rem;
		font-weight: 700;
		color: #fff;
	}

	.create-hero p {
		margin: .5rem 0 0;
		color: #b9c2cd;
	}

	#StuffNavigation {
		position: sticky;
		top: 6.5rem;
	}

	#StuffNavigation ul,
	.create-panel,
	.create-rule-card,
	#createUploadCard,
	#AssetsContainer {
		background: #18181b;
		border: 1px solid rgba(255,255,255,.08);
		border-radius: .4rem;
	}

	#StuffNavigation ul {
		list-style: none;
		margin: 0;
		padding: 1rem;
	}

	#StuffNavigation h4 {
		margin: 0 0 .75rem;
		color: #fff;
		font-size: 1rem;
		text-transform: uppercase;
		letter-spacing: .08em;
	}

	#StuffNavigation li {
		display: block;
		margin-bottom: .45rem;
		padding: .72rem .85rem;
		background: #121214;
		border: 1px solid rgba(255,255,255,.05);
		border-radius: .35rem;
		color: #e6edf6;
		cursor: pointer;
		transition: background-color .15s ease, border-color .15s ease, color .15s ease;
	}

	#StuffNavigation li[selected],
	#StuffNavigation li:hover {
		background: #4d8fe8;
		border-color: #4d8fe8;
		color: #fff;
	}

	#StuffNavigation li a {
		color: inherit !important;
		text-decoration: none !important;
		font-weight: 700;
		display: block;
	}

	#StuffNavigation hr {
		margin: .9rem 0;
		border-color: rgba(255,255,255,.08);
	}

	.create-main {
		display: grid;
		gap: 1rem;
	}

	.create-rule-grid {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: 1rem;
	}

	.create-rule-card {
		padding: 1.1rem 1.2rem;
		border-radius: .4rem;
	}

	.create-rule-card h3,
	.create-panel-title,
	.create-assets-title {
		margin: 0 0 .75rem;
		font-size: 1.2rem;
		font-weight: 700;
		color: #fff;
	}

	.create-rule-card ul {
		margin: 0;
		padding-left: 1.15rem;
		color: #c9d1db;
	}

	.create-rule-card p,
	.create-panel-subtitle,
	.create-assets-subtitle,
	.create-note {
		color: #b4bdc8;
	}

	#createUploadCard,
	#AssetsContainer {
		padding: 1.25rem;
		border-radius: .4rem;
	}

	.create-panel-head {
		display: flex;
		justify-content: space-between;
		gap: 1rem;
		margin-bottom: 1rem;
	}

	.create-panel-head h2 {
		margin: 0;
		color: #fff;
		font-size: 1.45rem;
		font-weight: 700;
	}

	.create-form-grid {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: 1rem;
	}

	.create-form-row-full {
		grid-column: 1 / -1;
	}

	.create-field label {
		display: block;
		margin-bottom: .45rem;
		color: #e7edf5;
		font-weight: 700;
	}

	.create-field textarea {
		min-height: 140px;
		resize: vertical;
	}

	.create-field .form-control,
	.create-field .form-select {
		border-radius: .35rem !important;
		background: #2f3137 !important;
	}

	.create-file-picker {
		display: flex;
		align-items: center;
		gap: .8rem;
		flex-wrap: wrap;
	}

	.create-file-picker label[for="files"] {
		margin: 0;
		display: inline-flex;
		align-items: center;
		padding: .7rem 1rem;
		background: #4d8fe8;
		border: 1px solid #4d8fe8;
		border-radius: .35rem;
		color: #fff;
		cursor: pointer;
		font-weight: 700;
	}

	#filename {
		color: #b7c0ca;
	}

	.create-toggle-grid {
		display: grid;
		grid-template-columns: repeat(3, minmax(0, 1fr));
		gap: .85rem;
	}

	.create-toggle {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: .85rem 1rem;
		background: #111214;
		border: 1px solid rgba(255,255,255,.06);
		border-radius: .35rem;
	}

	.create-submit-row {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 1rem;
		margin-top: 1rem;
	}

	.create-submit-row input[type="submit"] {
		min-width: 160px;
		padding: .8rem 1.4rem;
		background: #4d8fe8;
		border: 0;
		border-radius: .35rem;
		color: #fff;
		font-weight: 700;
	}

	.create-assets-head {
		display: flex;
		align-items: baseline;
		justify-content: space-between;
		gap: 1rem;
		margin-bottom: 1rem;
	}

	#AssetsContainer table {
		width: 100%;
		border-collapse: separate;
		border-spacing: 0 1rem;
	}

	#AssetsContainer td {
		width: 25%;
		padding-right: 1rem;
		vertical-align: top;
	}

	#AssetsContainer td:last-child {
		padding-right: 0;
	}

	.create-asset-card {
		display: block;
		background: #111214;
		border: 1px solid rgba(255,255,255,.06);
		border-radius: .4rem;
		color: #eef2f6 !important;
		text-decoration: none !important;
		overflow: hidden;
	}

	.create-asset-thumb {
		aspect-ratio: 1 / 1;
		padding: 1rem;
		background: #0f0f10;
		border-bottom: 1px solid rgba(255,255,255,.06);
	}

	.create-asset-thumb img {
		width: 100%;
		height: 100%;
		object-fit: contain;
		display: block;
	}

	.create-asset-copy {
		padding: .9rem;
	}

	.create-asset-copy span {
		display: block;
		font-weight: 700;
	}

	#StatusText {
		color: #c8d0da;
		margin-bottom: 1rem;
	}

	#NoAssets {
		display: none;
		padding: 1.2rem;
		background: #111214;
		border: 1px solid rgba(255,255,255,.06);
		text-align: center;
	}

	#Paginator {
		display: none;
		margin-top: 1rem;
		color: #c0c8d1;
	}

	#Paginator a {
		color: #fff !important;
		text-decoration: none !important;
	}

	.RequiredThing {
		color: #ff7373;
		font-weight: 700;
		user-select: none;
	}

	@media (max-width: 991.98px) {
		.create-shell,
		.create-rule-grid,
		.create-form-grid,
		.create-toggle-grid {
			grid-template-columns: 1fr;
		}

		#StuffNavigation {
			position: static;
		}

		#AssetsContainer td {
			width: auto;
			padding-right: 0;
			display: block;
			margin-bottom: 1rem;
		}

		.create-submit-row,
		.create-panel-head,
		.create-assets-head {
			flex-direction: column;
			align-items: flex-start;
		}
	}
</style>
<script>
	$(function() {
		$(".RequiredThing").each(function() {
			$(this).attr("title", "This is required!");
		});
	})
</script>
<div id="StuffContainer">
	<section class="create-hero">
		<h1 class="page-title">Create</h1>
		<p>Upload and manage assets. This page is back on the older Zomium card language instead of the flat version.</p>
	</section>

	<div class="create-shell">
		<div id="StuffNavigation">
			<ul>
				<h4>Accessories</h4>
			<li data_category="8" ><a>Hats</a></li>
			<li data_category="18"><a>Faces</a></li>
			<li data_category="11"><a>Shirts</a></li>
			<li data_category="2" ><a>T-Shirts</a></li>
			<li data_category="12"><a>Pants</a></li>
			<li data_category="19"><a>Gears</a></li>
			<li data_category="body"><a>Body Type</a></li>
			<li data_category="61"><a>Emotes</a></li>
			<hr>
			<h4>Development</h4>
			<li data_category="13"><a>Decals</a></li>
			<li data_category="3"><a>Audio</a></li>
			<li data_category="4"><a>Meshes</a></li>
			<li data_category="10"><a>Models</a></li>
			<li data_category="24"><a>Animations</a></li>
			
			
			<?php if($user->isAdmin()): ?>
			<hr>
			<h4>Admin</h4>
			<li data_category="1"><a>Images</a></li>
			<li data_category="5"><a>Lua</a></li>
			<?php endif ?>
			</ul>
		</div>

		<div id="CreationPanel" class="create-main">
			<div class="create-rule-grid">
				<div class="create-rule-card" id="HatUploadRules">
					<h3>Hat Uploading Rules</h3>
					<ul>
						<li>do not use this to upload gears</li>
						<li>do not make a hat that alters gameplay and gives you an advantage</li>
						<li>particle effects are fine, but do not make them screen-blocking</li>
						<li>do not upload character meshes through the hat uploader</li>
						<li>do not reupload other people's hats</li>
					</ul>
					<p class="create-note mt-3">Example effects are fine as long as they do not swallow the whole screen.</p>
				</div>

				<div class="create-rule-card" id="GearUploadRules">
					<h3>Gear Uploading Rules</h3>
					<ul>
						<li>do not upload gears that break games such as destructive build tools</li>
						<li>do not upload gears that actively harm players like swords or guns</li>
					</ul>
				</div>
			</div>

			<form method="POST" enctype="multipart/form-data">
				<div id="createUploadCard">
					<div class="create-panel-head">
						<div>
							<h2>Upload <span id="TypaLabel"></span></h2>
							<p class="create-panel-subtitle">Rounded controls, darker cards, and the old layout rhythm are restored here.</p>
						</div>
					</div>

					<?php if(isset($_SESSION['ANORRL$CreateAsset$Error']) && isset($_SESSION['ANORRL$CreateAsset$Result'])): ?>
						<?php if($_SESSION['ANORRL$CreateAsset$Error']): ?>
							<div id="ErrorTime" class="alert alert-danger mb-3">Error: <span id="Message"><?= htmlspecialchars($_SESSION['ANORRL$CreateAsset$Result'], ENT_QUOTES, 'UTF-8') ?></span></div>
						<?php else:
							$uploaded_asset = Asset::FromID($_SESSION['ANORRL$CreateAsset$Result']);
						?>
							<div id="SuccessTime" class="alert alert-success mb-3">
								You've successfully uploaded &quot;<?= htmlspecialchars($uploaded_asset->name, ENT_QUOTES, 'UTF-8') ?>&quot;.
								<span id="Message">Check it out <a href="<?= htmlspecialchars($uploaded_asset->getUrl(), ENT_QUOTES, 'UTF-8') ?>">here</a>.</span>
								<a href="javascript:copyToClipboard(<?= $uploaded_asset->getAssetIDSafe() ?>)">(Copy Asset ID)</a>
							</div>
						<?php endif ?>
					<?php endif ?>

					<div id="InfoWarning" class="alert alert-warning mb-3" style="display:none;">
						Models and package-style uploads may need extra cleanup before they look right in-game.
					</div>

					<div class="create-form-grid">
						<div class="create-field">
							<label for="ANORRL_CreateAsset_Name">Name <span class="RequiredThing">*</span></label>
							<input class="form-control" id="ANORRL_CreateAsset_Name" type="text" name="ANORRL$CreateAsset$Name" minlength="3" maxlength="100" required>
						</div>

						<div class="create-field create-form-row-full">
							<label for="ANORRL_CreateAsset_Description">Description</label>
							<textarea class="form-control" id="ANORRL_CreateAsset_Description" name="ANORRL$CreateAsset$Description" maxlength="1000"></textarea>
						</div>

						<div class="create-field">
							<label>File <span class="RequiredThing">*</span></label>
							<div class="create-file-picker">
								<label for="files">Choose file</label>
								<input id="files" style="display:none;" type="file" name="ANORRL$CreateAsset$File" required>
								<label id="filename">No file chosen</label>
							</div>
						</div>

						<div class="create-field" style="display:none;" id="bodytyperow">
							<label for="ANORRL_CreateAsset_BodyType">Body Type <span class="RequiredThing">*</span></label>
							<select class="form-select" id="ANORRL_CreateAsset_BodyType" name="ANORRL$CreateAsset$BodyType">
								<?php foreach(CharacterMeshType::all() as $type): ?>
									<option value="<?= $type->ordinal() ?>"><?= htmlspecialchars($type->label(), ENT_QUOTES, 'UTF-8') ?></option>
								<?php endforeach ?>
							</select>
						</div>

						<div class="create-field" style="display:none;" id="AssetYear">
							<label for="ANORRL_CreateAsset_Version">Version</label>
							<select class="form-select" id="ANORRL_CreateAsset_Version" disabled>
								<option>Default</option>
							</select>
						</div>

						<div class="create-field create-form-row-full">
							<label>Extras</label>
							<div class="create-toggle-grid">
								<div class="create-toggle">
									<label for="ANORRL_CreateAsset_Public">Public</label>
									<input id="ANORRL_CreateAsset_Public" name="ANORRL$CreateAsset$Public" type="checkbox" checked>
								</div>
								<div class="create-toggle">
									<label for="ANORRL_CreateAsset_CommentsEnabled">Comments</label>
									<input id="ANORRL_CreateAsset_CommentsEnabled" name="ANORRL$CreateAsset$CommentsEnabled" type="checkbox" checked>
								</div>
								<div class="create-toggle">
									<label for="ANORRL_CreateAsset_Purchasable">Purchasable</label>
									<input id="ANORRL_CreateAsset_Purchasable" name="ANORRL$CreateAsset$Purchasable" type="checkbox" checked>
								</div>
							</div>
						</div>
					</div>

					<div class="create-submit-row">
						<div class="create-note"><span class="RequiredThing">*</span> means required fields.</div>
						<input type="submit" value="Upload" name="ANORRL$CreateAsset$Submit" onclick="$(this).attr('disabled', 'true'); document.forms[0].submit()">
					</div>
				</div>
			</form>
			<script>
				function toggleTemplate() {
					if($("#ShowHideTemplate").parent().parent().find("#Contents").is(":visible")) {
						$("#ShowHideTemplate").parent().parent().find("#Contents").css("display", "none");
						$("#ShowHideTemplate").html("(Show)");
					} else {
						$("#ShowHideTemplate").parent().parent().find("#Contents").css("display", "block");
						$("#ShowHideTemplate").html("(Hide)");
					}
				}
			</script>
			<div class="create-rule-card" style="display: none;" id="ShirtPantsTemplate">
				<h3><span id="Title"></span> <a id="ShowHideTemplate" href="javascript:toggleTemplate()">(Show)</a></h3>
				<div id="Contents" style="display: none;" class="text-center">
					<a download="" href="" title="Click to download!">
						<img alt="Click to download!" src="" height="300">
					</a>
				</div>
			</div>

			<div id="AssetsContainer">
				<div class="create-assets-head">
					<div>
						<h3 class="create-assets-title">Your Latest Uploads</h3>
						<p class="create-assets-subtitle">The panel below still uses the legacy asset feed API, just restyled to match the current site.</p>
					</div>
				</div>

				<div id="StatusText">
					<b id="Loading" style="display: none">Loading assets...</b>
					<div id="NoAssets">
						<img src="/public/images/noassets.png" style="width: 110px; display: block; margin: 0 auto 1rem;" alt="No assets">
						You have no <span id="AssetType"></span>!
					</div>
				</div>

				<table hidden></table>

				<div id="Paginator">
					<a href="javascript:ANORRL.Create.DeadvancePager()" id="PrevPager">&lt;&lt;Previous</a>
					Page <input maxlength="4">
					of <span id="Pages">1</span>
					<a href="javascript:ANORRL.Create.AdvancePager()" id="NextPager">Next&gt;&gt;</a>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
	$page->loadFooter();
	unset($_SESSION['ANORRL$CreateAsset$Error']);
	unset($_SESSION['ANORRL$CreateAsset$Result']);
?>
