<?php
	use anorrl\Page;

	$page = new Page("Games");
	$page->addScript("/js/games.js?t=1771413807");
	$page->loadHeader();
?>
<style>
body { background: #0f0f10 !important; background-image: none !important; }
.places-shell { color: #f2f4f8; }
.places-title { font-size: 2rem; font-weight: 700; color: #fff; margin-bottom: 1.5rem; }
.places-panel, .places-search, .game-card, .game-thumb-container, .inline-badge, #Paginator .form-control { background: #18181b !important; border: 1px solid rgba(255,255,255,.08) !important; }
.places-panel { border-radius: 0; overflow: hidden; background: #18181b !important; }
.filter-title { color: #f1f3f6; font-size: 1rem; font-weight: 700; text-transform: lowercase; padding: 1rem 1.1rem; border-bottom: 1px solid rgba(255,255,255,.06); background: #17171a; text-align: left; }
.filter-list { list-style: none; padding: 1rem; margin: 0; background: #18181b; }
.filter-list li { display: block; width: 100%; padding: .78rem .95rem; margin-bottom: .45rem; border-radius: .2rem; background: linear-gradient(180deg, #161617 0%, #161617 100%); color: #f3f5f8; cursor: pointer; transition: background-color .15s ease, border-color .15s ease, color .15s ease; text-transform: lowercase; text-align: center; }
.filter-list li.active, .filter-list li[selected] { background: linear-gradient(180deg, #4d8fe8 0%, #4d8fe8 100%); }
.filter-list li:hover { color: #fff; background: linear-gradient(180deg, #4d8fe8 0%, #4d8fe8 100%); border-color: rgba(255,255,255,.08); text-decoration: none; }
.places-search { border-radius: .35rem; overflow: hidden; }
.places-search .form-control, .places-search .btn { background: transparent !important; border: 0 !important; color: #eef2f6 !important; box-shadow: none !important; }
.places-search .form-control::placeholder { color: #8f97a3; }
.game-card { border-radius: .35rem; overflow: hidden; min-height: 100%; box-shadow: none !important; }
.game-thumb-container { position: relative; aspect-ratio: 16 / 10; overflow: hidden; border-bottom: 1px solid rgba(255,255,255,.08); }
.game-thumb-container img { width: 100%; height: 100%; object-fit: cover; display: block; background: #111; }
.game-card .card-body { background: #18181b !important; }
.game-card a, .game-card .text-secondary, .game-card .text-muted, .inline-badge { color: #eef2f7 !important; }
	.game-card a:hover:not(.btn) { text-decoration: none !important; color: #ffffff !important; }
.inline-badge { display: inline-flex; align-items: center; gap: .35rem; padding: .2rem .5rem; border-radius: .3rem; font-size: .82rem; }
.game-flag { position: absolute; top: .55rem; left: .55rem; background: #da5a64; color: #fff; font-size: .72rem; font-weight: 700; padding: .12rem .35rem; border-radius: .2rem; line-height: 1.2; }
.game-year { position: absolute; top: .55rem; right: .55rem; background: rgba(255,255,255,.88); color: #111; font-size: .72rem; font-weight: 700; padding: .12rem .4rem; border-radius: .2rem; }
#StatusText { color: #c5cbd5; }
#NoAssets { background: #17171a; border: 1px solid rgba(255,255,255,.08); border-radius: .45rem; }
.download-btn { display: inline-block; padding: 6px 14px; background: #161617; color: #fff; border-radius: 4px; cursor: pointer; font-size: 14px; border: 1px solid #2a2a2b; transition: all 0.2s ease; }
.download-btn:hover { background: linear-gradient(180deg, #4d8fe8 0%, #4d8fe8 100%); border: 1px solid #3c6fc0; }
</style>
<div class="Game" template>
	<div class="card game-card h-100">
		<div class="game-thumb-container" id="ImageContainer">
			<span id="FavouritesArea" class="game-flag"><span>0</span></span>
			<span id="OriginalArea" class="game-year" style="display:none;"><span>Original</span></span>
			<span id="YearArea" class="game-year"><span>2013</span></span>
			<img src="/public/images/unavailable.jpg" alt="Game thumb">
		</div>
		<div class="card-body p-3">
			<a href="" id="GameName" class="fw-bold d-block text-truncate mb-1 text-decoration-none">Game Name</a>
			<div class="small text-secondary mb-2 text-truncate">By <a href="" id="GameCreator" class="text-decoration-none">creator</a></div>
			<div id="Stats" class="d-flex flex-column gap-1 small">
				<span id="ActivePlayerCountLabel" class="inline-badge"><b id="ActivePlayerCount">0</b> online</span>
				<span id="VisitCountLabel" class="text-muted"><b id="VisitCount">0</b> visits</span>
			</div>
		</div>
	</div>
</div>
<div class="py-4 places-shell" id="Games">
		<h1 class="places-title">Places</h1>
		<div class="row g-4 align-items-start">
			<div class="col-lg-3">
				<aside class="places-panel mb-4">
					<div class="filter-title">filter by</div>
					<ul class="filter-list mb-0">
						<li data_filter="7" selected>top visits</li>
						<li data_filter="8">most popular</li>
						<li data_filter="6">original</li>
						<li data_filter="9">recently created</li>
						<li data_filter="1">recently updated</li>
					</ul>
				</aside>
				<aside class="places-panel">
					<div class="filter-title">year</div>
					<ul class="filter-list mb-0">
						<li data_year="all" selected>all</li>
						<li data_year="2012">2012</li>
						<li data_year="2014">2014</li>
						<li data_year="2016">2016</li>
						<li data_year="2018">2018</li>
					</ul>
				</aside>
			</div>
			<div class="col-lg-9">
				<div style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
					<div class="download-btn" onclick="window.location.href='/public/download/ANORRLPlayerLauncher.exe';">Download 2016 Client</div>
				</div>
				<div class="places-search input-group input-group-lg mb-4">
					<input class="form-control" id="SearchBox" name="query" type="text" placeholder="Search...">
					<button class="btn" type="button" onclick="ANORRL.Games.Submit();">
						<i class="fas fa-search"></i>
					</button>
				</div>
				<div id="StatusText" class="mb-4">
					<div id="Loading" class="text-center py-5">
						<img src="/public/images/thinking.svg" alt="loading" style="width:250px;">
						<h1 class="h4 mt-3">Hold on... i need a moment to think</h1>
					</div>
					<div id="NoAssets" class="empty-state text-center py-5" style="display:none">
						<div class="text-center">
							<img src="/public/images/error.png" alt="Error" style="max-width:150px;" class="mb-3">
							<h4 class="mb-2">no games for now</h4>
							<p class="mb-0 text-secondary">im sorry :/</p>
						</div>
					</div>
				</div>
				<script>
				setTimeout(function () {
					var loading = document.getElementById("Loading");
					var noAssets = document.getElementById("NoAssets");
					if (loading && noAssets) {
						loading.style.display = "none";
						noAssets.style.display = "block";
					}
				}, 15000);
				</script>
				<div id="ContainerThingy" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4"></div>
				<div id="Paginator" class="d-flex justify-content-center align-items-center gap-3 mt-5" style="display:none;">
					<a class="btn btn-light shadow-sm" id="BackPager" href="javascript:ANORRL.Games.PrevPage()" style="display:none; text-decoration:none;">&laquo; Back</a>
					<div class="d-flex align-items-center gap-2">
						<input class="form-control text-center shadow-sm" type="text" id="NumberPutter" maxlength="3" style="width:60px;">
						<span class="text-secondary">of <span id="Counter">1</span></span>
					</div>
					<a class="btn btn-light shadow-sm" id="NextPager" href="javascript:ANORRL.Games.NextPage()" style="display:none; text-decoration:none;">Next &raquo;</a>
				</div>
			</div>
		</div>
</div>
<?php $page->loadFooter(); ?>
