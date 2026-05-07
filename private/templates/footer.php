		</div>
	</main>

	<footer class="app-footer mt-auto py-4">
		<div class="container">
			<div class="app-footer-inner d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
				<div>
					<div class="fw-bold">Zomium</div>
					<div class="small text-muted">made by xloang25</div>
					<div class="small mt-1">
						<b>Lucky number:</b> <?= $this->lucky_number ?>
					</div>
				</div>

				<div class="d-flex flex-wrap gap-3 small">
					<a href="/info/credits">Credits</a>|
					<a href="/discord">discord</a>|
					<a href="/tos">terms</a>|
					<a href="/privacy">privacy</a>|
					<a href="https://discord.gg/5RsJrCCAzx">Discord</a>|
				</div>

				<div class="small text-lg-end">
					&copy; <?= date('Y') ?> Zomium
				</div>
			</div>
		</div>
	</footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
