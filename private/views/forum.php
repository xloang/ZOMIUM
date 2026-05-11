<?php
	use anorrl\ForumBoard;
	use anorrl\Page;

	$user = SESSION->user;
	$forumError = null;

	if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["forum_action"]) && $_POST["forum_action"] === "create_topic") {
		$result = ForumBoard::createTopic(
			$user,
			$_POST["forum_title"] ?? "",
			$_POST["forum_content"] ?? "",
			$_POST["forum_category"] ?? ""
		);

		if ($result["error"]) {
			$forumError = $result["reason"];
		} else {
			die(header("Location: /forum/topic/" . $result["topic_id"]));
		}
	}

	$page = new Page("Forum");
	$page->addStylesheet("/css/new/forum.css?v=1");
	$page->loadHeader();

	$categorySlug = isset($slug) ? trim(strtolower($slug)) : trim(strtolower($_GET["category"] ?? ""));
	$searchQuery = trim($_GET["q"] ?? "");
	$categories = ForumBoard::getCategories();
	$topics = ForumBoard::getTopics($categorySlug !== "" ? $categorySlug : null, $searchQuery);
	$activeCategory = $categorySlug !== "" ? ForumBoard::getCategoryBySlug($categorySlug) : null;
?>
<div class="forum-shell py-4">
	<div class="forum-layout row g-4 align-items-start">
		<div class="col-lg-3">
			<aside class="forum-sidebar-stack">
				<section class="forum-panel forum-cta-panel">
					<a class="forum-new-topic" href="#forum-new-topic">+ new topic</a>
					<form method="get" action="/forum" class="forum-search-form">
						<div class="input-group">
							<input class="form-control" type="text" name="q" placeholder="Search..." value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>">
							<button class="btn btn-theme" type="submit"><i class="fas fa-search"></i></button>
						</div>
					</form>
				</section>

				<section class="forum-panel">
					<h3 class="forum-side-title">Categories</h3>
					<div class="forum-category-list">
						<a class="forum-category-link<?= $activeCategory ? "" : " is-active" ?>" href="/forum">All Categories</a>
						<?php foreach ($categories as $category): ?>
							<a class="forum-category-link<?= ($activeCategory && $activeCategory["slug"] === $category["slug"]) ? " is-active" : "" ?>" href="/forum/category/<?= htmlspecialchars($category["slug"], ENT_QUOTES, 'UTF-8') ?>">
								<span class="forum-category-dot" style="background: <?= htmlspecialchars($category["color"], ENT_QUOTES, 'UTF-8') ?>"></span>
								<span><?= htmlspecialchars($category["name"], ENT_QUOTES, 'UTF-8') ?></span>
								<small><?= intval($category["topic_count"]) ?></small>
							</a>
						<?php endforeach; ?>
					</div>
				</section>

				<section class="forum-panel">
					<h3 class="forum-side-title" id="forum-new-topic">New Topic</h3>
					<?php if ($forumError): ?>
						<div class="alert alert-danger mb-3"><?= htmlspecialchars($forumError, ENT_QUOTES, 'UTF-8') ?></div>
					<?php endif; ?>
					<form method="post" class="forum-compose-form">
						<input type="hidden" name="forum_action" value="create_topic">
						<div class="mb-3">
							<label class="form-label" for="forum_title">Title</label>
							<input class="form-control" id="forum_title" name="forum_title" maxlength="120" required>
						</div>
						<div class="mb-3">
							<label class="form-label" for="forum_category">Category</label>
							<select class="form-select" id="forum_category" name="forum_category">
								<?php foreach ($categories as $category): ?>
									<option value="<?= htmlspecialchars($category["slug"], ENT_QUOTES, 'UTF-8') ?>"<?= ($activeCategory && $activeCategory["slug"] === $category["slug"]) ? " selected" : "" ?>>
										<?= htmlspecialchars($category["name"], ENT_QUOTES, 'UTF-8') ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="mb-3">
							<label class="form-label" for="forum_content">Message</label>
							<textarea class="form-control forum-textarea" id="forum_content" name="forum_content" rows="6" maxlength="4000" required></textarea>
						</div>
						<button class="btn btn-primary w-100 btn-normal-case" type="submit">+ new topic</button>
					</form>
				</section>
			</aside>
		</div>

		<div class="col-lg-9">
			<section class="forum-hero">
				<p class="forum-kicker">Forum</p>
				<h1 class="page-title">All Subforums</h1>
				<p class="forum-hero-copy">
					<?= $activeCategory ? htmlspecialchars($activeCategory["name"], ENT_QUOTES, 'UTF-8') . " topics only." : "All the posts from every forum." ?>
				</p>
			</section>

			<section class="forum-topic-list">
				<?php if (count($topics) === 0): ?>
					<div class="forum-empty">
						<h2>No topics yet</h2>
						<p>Open the first thread from the left panel.</p>
					</div>
				<?php else: ?>
					<?php foreach ($topics as $topic): ?>
						<article class="forum-topic-card">
							<div class="forum-topic-main">
								<a class="forum-topic-title" href="/forum/topic/<?= intval($topic["id"]) ?>">
									<?= htmlspecialchars($topic["title"], ENT_QUOTES, 'UTF-8') ?>
								</a>
								<div class="forum-topic-meta">
									<span class="forum-pill" style="--forum-pill: <?= htmlspecialchars($topic["category_color"], ENT_QUOTES, 'UTF-8') ?>">
										<?= htmlspecialchars($topic["category_name"], ENT_QUOTES, 'UTF-8') ?>
									</span>
								</div>
								<div class="forum-topic-lines">
									<div>Posted by <a href="/users/<?= intval($topic["author_id"]) ?>/profile"><?= htmlspecialchars($topic["author_name"], ENT_QUOTES, 'UTF-8') ?></a> <?= htmlspecialchars(anorrl\utilities\UtilUtils::GetTimeAgo(new DateTime($topic["created_at"])), ENT_QUOTES, 'UTF-8') ?></div>
									<div>Last reply by <a href="/users/<?= intval($topic["last_post_user_id"]) ?>/profile"><?= htmlspecialchars($topic["last_post_user_name"], ENT_QUOTES, 'UTF-8') ?></a> <?= htmlspecialchars(anorrl\utilities\UtilUtils::GetTimeAgo(new DateTime($topic["last_post_at"])), ENT_QUOTES, 'UTF-8') ?></div>
								</div>
							</div>
							<div class="forum-topic-stats">
								<span class="forum-stat-badge"><?= intval($topic["reply_count"]) ?></span>
								<span><?= intval($topic["reply_count"]) ?> replies</span>
							</div>
						</article>
					<?php endforeach; ?>
				<?php endif; ?>
			</section>
		</div>
	</div>
</div>
<?php $page->loadFooter(); ?>
