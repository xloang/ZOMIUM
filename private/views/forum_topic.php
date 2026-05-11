<?php
	use anorrl\ForumBoard;
	use anorrl\Page;

	$user = SESSION->user;
	$forumError = null;

	if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["forum_action"]) && $_POST["forum_action"] === "reply_topic") {
		$result = ForumBoard::replyToTopic(
			$user,
			intval($topicId),
			$_POST["forum_content"] ?? ""
		);

		if ($result["error"]) {
			$forumError = $result["reason"];
		} else {
			die(header("Location: /forum/topic/" . intval($topicId)));
		}
	}

	$topic = ForumBoard::getTopic(intval($topicId));
	if (!$topic) {
		http_response_code(404);
		die("Forum topic not found.");
	}

	$posts = ForumBoard::getPosts(intval($topicId));
	$categories = ForumBoard::getCategories();

	$page = new Page($topic["title"] . " - Forum");
	$page->addStylesheet("/css/new/forum.css?v=1");
	$page->loadHeader();
?>
<div class="forum-shell py-4">
	<div class="forum-layout row g-4 align-items-start">
		<div class="col-lg-3">
			<aside class="forum-sidebar-stack">
				<section class="forum-panel">
					<h3 class="forum-side-title">Browse</h3>
					<div class="forum-category-list">
						<a class="forum-category-link" href="/forum">All Subforums</a>
						<?php foreach ($categories as $category): ?>
							<a class="forum-category-link<?= $category["slug"] === $topic["category_slug"] ? " is-active" : "" ?>" href="/forum/category/<?= htmlspecialchars($category["slug"], ENT_QUOTES, 'UTF-8') ?>">
								<span class="forum-category-dot" style="background: <?= htmlspecialchars($category["color"], ENT_QUOTES, 'UTF-8') ?>"></span>
								<span><?= htmlspecialchars($category["name"], ENT_QUOTES, 'UTF-8') ?></span>
								<small><?= intval($category["topic_count"]) ?></small>
							</a>
						<?php endforeach; ?>
					</div>
				</section>

				<section class="forum-panel">
					<h3 class="forum-side-title">Reply</h3>
					<?php if ($forumError): ?>
						<div class="alert alert-danger mb-3"><?= htmlspecialchars($forumError, ENT_QUOTES, 'UTF-8') ?></div>
					<?php endif; ?>
					<form method="post" class="forum-compose-form">
						<input type="hidden" name="forum_action" value="reply_topic">
						<div class="mb-3">
							<label class="form-label" for="forum_content">Message</label>
							<textarea class="form-control forum-textarea" id="forum_content" name="forum_content" rows="8" maxlength="4000" required></textarea>
						</div>
						<button class="btn btn-primary w-100 btn-normal-case" type="submit">Post reply</button>
					</form>
				</section>
			</aside>
		</div>

		<div class="col-lg-9">
			<section class="forum-hero">
				<p class="forum-kicker">Forum / <?= htmlspecialchars($topic["category_name"], ENT_QUOTES, 'UTF-8') ?></p>
				<h1 class="page-title"><?= htmlspecialchars($topic["title"], ENT_QUOTES, 'UTF-8') ?></h1>
				<p class="forum-hero-copy">
					Started by <a href="/users/<?= intval($topic["author_id"]) ?>/profile"><?= htmlspecialchars($topic["author_name"], ENT_QUOTES, 'UTF-8') ?></a>
					<?= htmlspecialchars(anorrl\utilities\UtilUtils::GetTimeAgo(new DateTime($topic["created_at"])), ENT_QUOTES, 'UTF-8') ?>
				</p>
			</section>

			<section class="forum-post-list">
				<?php foreach ($posts as $post): ?>
					<article class="forum-post-card">
						<div class="forum-post-aside">
							<a href="/users/<?= intval($post["author_id"]) ?>/profile" class="forum-post-avatar-link">
								<img class="forum-post-avatar" src="<?= htmlspecialchars(anorrl\User::FromID(intval($post["author_id"]))->getThumbsUrlService("headshot", 100, 100), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($post["author_name"], ENT_QUOTES, 'UTF-8') ?>">
							</a>
							<div class="forum-post-author"><?= htmlspecialchars($post["author_name"], ENT_QUOTES, 'UTF-8') ?></div>
						</div>
						<div class="forum-post-main">
							<div class="forum-post-header">
								<div class="forum-post-type"><?= $post["is_topic"] ? "Original post" : "Reply" ?></div>
								<div class="forum-post-time"><?= htmlspecialchars(anorrl\utilities\UtilUtils::GetTimeAgo(new DateTime($post["created_at"])), ENT_QUOTES, 'UTF-8') ?></div>
							</div>
							<div class="forum-post-body"><?= nl2br(htmlspecialchars($post["content"], ENT_QUOTES, 'UTF-8')) ?></div>
						</div>
					</article>
				<?php endforeach; ?>
			</section>
		</div>
	</div>
</div>
<?php $page->loadFooter(); ?>
