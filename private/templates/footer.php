		</div>
	</main>

	<style>
	.hidden { display: none; }
	.app-footer { background: #121212; }
	.app-footer-inner {
		background: transparent !important;
		border-radius: 0 !important;
		padding: 20px;
		border: 1px solid #1f1f1f;
	}
	.app-footer-inner a {
		color: #11b0ff;
		text-decoration: none;
	}
	.app-footer-inner a:hover {
		color: #0a8cd1;
		text-decoration: none;
	}
	</style>

	<footer class="app-footer mt-auto py-4">
		<div class="container">
			<div class="app-footer-inner d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
				<div>
					<div class="fw-bold">Zomium</div>
					<div class="small text-muted">made by xloang</div>
					<div class="small mt-1">
						<b>Lucky number:</b> <?= $this->lucky_number ?>
					</div>
				</div>

				<div class="d-flex flex-wrap gap-3 small">
					<a href="/info/credits">Credits</a> |
					<a href="/legal">Legal</a> |
					<a href="/Privacy">Privacy</a> |
					<a href="https://discord.gg/5RsJrCCAzx">Discord</a>
				</div>

				<div class="small text-lg-end">
					&copy; <?= date('Y') ?> Zomium
				</div>
			</div>
		</div>
	</footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
if (!document.getElementById('ZMRrwOVaKiIQ')) {
	function toggleElement() {
		var parentDiv = document.querySelector(".nav-scroller");
		var alert = document.getElementById("dynamicElement");
		if (!alert && parentDiv) {
			parentDiv.insertAdjacentHTML("afterend", '<div id="dynamicElement" class="alert alert-danger text-center" style="display:block;color:rgba(255,255,255,0.9);font-size:1.25rem;">Please disable AdBlock to support us.</div>');
		}
	}
	setInterval(toggleElement, 100);
}
</script>
</body>
</html>
