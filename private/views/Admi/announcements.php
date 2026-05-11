<?php
	use anorrl\Page;

	$user = SESSION->user;

	if (!$user->isAdmin()) {
		die("Hey... You're not an admin I don't think...");
	}

	$announcementColors = [
		'sky' => ['label' => 'Sky', 'background' => '#20a8c9', 'text' => '#0f1b22'],
		'green' => ['label' => 'Green', 'background' => '#38a169', 'text' => '#f4fff7'],
		'gold' => ['label' => 'Gold', 'background' => '#d6a21d', 'text' => '#221700'],
		'red' => ['label' => 'Red', 'background' => '#c84b4b', 'text' => '#fff5f5'],
		'violet' => ['label' => 'Violet', 'background' => '#7b61c8', 'text' => '#f8f5ff']
	];

	$settingsCandidates = [
		$_SERVER['DOCUMENT_ROOT'] . '/settings.json',
		$_SERVER['DOCUMENT_ROOT'] . '/../settings.json'
	];
	$settingsPath = null;
	foreach ($settingsCandidates as $candidate) {
		if (file_exists($candidate)) {
			$settingsPath = $candidate;
			break;
		}
	}
	$resultMessage = null;
	$resultError = false;

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$rawSettings = $settingsPath !== null ? @file_get_contents($settingsPath) : false;
		$decodedSettings = $rawSettings !== false ? json_decode($rawSettings) : null;

		if (!is_object($decodedSettings)) {
			$resultMessage = 'Settings file could not be loaded.';
			$resultError = true;
		} else {
			if (isset($_POST['delete_announcement'])) {
				$decodedSettings->site_announcement = '';
				$decodedSettings->site_announcement_color = 'sky';
			}

			if (isset($_POST['save_announcement'])) {
				$decodedSettings->site_announcement = trim((string) ($_POST['announcement_text'] ?? ''));
				$selectedColor = (string) ($_POST['announcement_color'] ?? 'sky');
				$decodedSettings->site_announcement_color = array_key_exists($selectedColor, $announcementColors) ? $selectedColor : 'sky';
			}

			$encodedSettings = json_encode($decodedSettings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			if ($encodedSettings === false || @file_put_contents($settingsPath, $encodedSettings . PHP_EOL) === false) {
				$resultMessage = 'Announcement could not be saved.';
				$resultError = true;
			} else {
				$resultMessage = isset($_POST['delete_announcement']) ? 'Announcement deleted.' : 'Announcement updated.';
				$resultError = false;
			}
		}
	}

	$currentAnnouncement = isset(CONFIG->site_announcement) ? trim((string) CONFIG->site_announcement) : '';
	$currentAnnouncementColor = isset(CONFIG->site_announcement_color) ? (string) CONFIG->site_announcement_color : 'sky';
	if (!array_key_exists($currentAnnouncementColor, $announcementColors)) {
		$currentAnnouncementColor = 'sky';
	}

	if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$resultError) {
		$currentAnnouncement = isset($_POST['delete_announcement']) ? '' : trim((string) ($_POST['announcement_text'] ?? ''));
		$currentAnnouncementColor = isset($_POST['delete_announcement'])
			? 'sky'
			: ((isset($_POST['announcement_color']) && array_key_exists((string) $_POST['announcement_color'], $announcementColors)) ? (string) $_POST['announcement_color'] : 'sky');
	}

	$page = new Page("Admin announcements");
	$page->loadHeader();
?>
<style>
	.announcement-shell {
		max-width: 820px;
	}

	.announcement-card {
		background: #17171a;
		border: 1px solid rgba(255,255,255,.08);
		border-radius: .35rem;
		padding: 1.35rem;
	}

	.announcement-toolbar {
		display: flex;
		gap: .75rem;
		align-items: stretch;
	}

	.announcement-toolbar textarea {
		min-height: 120px;
		resize: vertical;
	}

	.announcement-delete {
		width: 54px;
		min-width: 54px;
		font-size: 1.5rem;
		line-height: 1;
		padding: 0;
	}

	.announcement-preview {
		border-radius: .2rem;
		padding: .55rem .8rem;
		margin-top: 1rem;
	}

	.announcement-muted {
		color: #b7bdc4;
	}

	.announcement-colors {
		display: flex;
		flex-wrap: wrap;
		gap: .75rem;
		margin-top: 1rem;
	}

	.announcement-color-option {
		position: relative;
	}

	.announcement-color-option input {
		position: absolute;
		opacity: 0;
		pointer-events: none;
	}

	.announcement-color-swatch {
		display: flex;
		align-items: center;
		gap: .55rem;
		padding: .55rem .75rem;
		border: 2px solid rgba(255,255,255,.08);
		border-radius: .35rem;
		cursor: pointer;
		min-width: 120px;
	}

	.announcement-color-dot {
		width: 16px;
		height: 16px;
		border-radius: 999px;
		border: 1px solid rgba(0,0,0,.2);
		flex: 0 0 auto;
	}

	.announcement-color-option input:checked + .announcement-color-swatch {
		border-color: #8cc9ff;
		box-shadow: 0 0 0 1px rgba(140, 201, 255, .18);
	}
</style>

<div class="announcement-shell">
	<h1>Announcements</h1>
	<p class="announcement-muted"></p>

	<?php if ($resultMessage !== null): ?>
		<div class="alert <?= $resultError ? 'alert-danger' : 'alert-success' ?>">
			<?= htmlspecialchars($resultMessage, ENT_QUOTES, 'UTF-8') ?>
		</div>
	<?php endif; ?>

	<div class="announcement-card">
		<form method="post">
			<label class="form-label" for="announcement_text">Announcement text</label>
			<div class="announcement-toolbar">
				<textarea
					class="form-control"
					id="announcement_text"
					name="announcement_text"
					placeholder="Write things here"><?= htmlspecialchars($currentAnnouncement, ENT_QUOTES, 'UTF-8') ?></textarea>
				<button
					class="btn btn-danger announcement-delete"
					type="submit"
					name="delete_announcement"
					value="1"
					title="Delete announcement"
					aria-label="Delete announcement">&times;</button>
			</div>

			<div class="mt-3">
				<div class="form-label mb-2">Announcement color</div>
				<div class="announcement-colors">
					<?php foreach ($announcementColors as $colorKey => $colorData): ?>
						<label class="announcement-color-option">
							<input
								type="radio"
								name="announcement_color"
								value="<?= htmlspecialchars($colorKey, ENT_QUOTES, 'UTF-8') ?>"
								<?= $currentAnnouncementColor === $colorKey ? 'checked' : '' ?>>
							<span class="announcement-color-swatch">
								<span class="announcement-color-dot" style="background: <?= htmlspecialchars($colorData['background'], ENT_QUOTES, 'UTF-8') ?>;"></span>
								<span><?= htmlspecialchars($colorData['label'], ENT_QUOTES, 'UTF-8') ?></span>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="d-flex gap-2 mt-3">
				<button class="btn btn-primary" type="submit" name="save_announcement" value="1">Save announcement</button>
				<a class="btn btn-secondary" href="/admi/">Back to admin</a>
			</div>
		</form>

		<?php if ($currentAnnouncement !== ''): ?>
			<div
				class="announcement-preview"
				style="background: <?= htmlspecialchars($announcementColors[$currentAnnouncementColor]['background'], ENT_QUOTES, 'UTF-8') ?>; color: <?= htmlspecialchars($announcementColors[$currentAnnouncementColor]['text'], ENT_QUOTES, 'UTF-8') ?>;">
				<?= htmlspecialchars($currentAnnouncement, ENT_QUOTES, 'UTF-8') ?>
			</div>
		<?php else: ?>
			<p class="announcement-muted mt-3 mb-0">There is no active announcements.</p>
		<?php endif; ?>
	</div>
</div>

<?php $page->loadFooter(); ?>
