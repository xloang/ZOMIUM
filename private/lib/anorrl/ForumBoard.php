<?php
	namespace anorrl;

	use anorrl\utilities\SlurUtils;
	use anorrl\utilities\UtilUtils;

	class ForumBoard {
		private const CATEGORY_SEED = [
			["general", "General", "#2fd67b", 1],
			["off-topic", "Off-Topic", "#7357ff", 2],
			["help", "Help", "#ff3b81", 3],
			["development", "Development", "#44d7ff", 4],
			["updates", "Updates", "#ff6b3d", 5],
			["announcements", "Announcements", "#a1a7b3", 6],
			["russian", "Russkiy", "#e04141", 7]
		];

		public static function ensureSchema(): void {
			$db = Database::singleton();

			$db->run(
				"CREATE TABLE IF NOT EXISTS `forum_categories` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`slug` varchar(50) NOT NULL,
					`name` varchar(80) NOT NULL,
					`color` varchar(20) NOT NULL DEFAULT '#4d8fe8',
					`sort_order` int(11) NOT NULL DEFAULT 0,
					PRIMARY KEY (`id`),
					UNIQUE KEY `forum_categories_slug` (`slug`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
			);

			$db->run(
				"CREATE TABLE IF NOT EXISTS `forum_topics` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`category_id` int(11) NOT NULL,
					`author_id` int(11) NOT NULL,
					`title` varchar(120) NOT NULL,
					`content` text NOT NULL,
					`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
					`updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
					`last_post_at` timestamp NOT NULL DEFAULT current_timestamp(),
					`last_post_user_id` int(11) NOT NULL,
					PRIMARY KEY (`id`),
					KEY `forum_topics_category_id` (`category_id`),
					KEY `forum_topics_author_id` (`author_id`),
					KEY `forum_topics_last_post_at` (`last_post_at`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
			);

			$db->run(
				"CREATE TABLE IF NOT EXISTS `forum_posts` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`topic_id` int(11) NOT NULL,
					`author_id` int(11) NOT NULL,
					`content` text NOT NULL,
					`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
					PRIMARY KEY (`id`),
					KEY `forum_posts_topic_id` (`topic_id`),
					KEY `forum_posts_author_id` (`author_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
			);

			$categoryCount = intval($db->run("SELECT COUNT(*) FROM `forum_categories`")->fetchColumn());
			if ($categoryCount === 0) {
				foreach (self::CATEGORY_SEED as [$slug, $name, $color, $sortOrder]) {
					$db->run(
						"INSERT INTO `forum_categories` (`slug`, `name`, `color`, `sort_order`) VALUES (:slug, :name, :color, :sort_order)",
						[
							":slug" => $slug,
							":name" => $name,
							":color" => $color,
							":sort_order" => $sortOrder
						]
					);
				}
			}
		}

		private static function normalizeText(string $text): string {
			$text = trim(UtilUtils::StripUnicode($text));
			return SlurUtils::ProcessText($text);
		}

		public static function getCategories(): array {
			self::ensureSchema();

			$rows = Database::singleton()->run(
				"SELECT
					c.*,
					(
						SELECT COUNT(*)
						FROM `forum_topics` t
						WHERE t.`category_id` = c.`id`
					) AS topic_count
				FROM `forum_categories` c
				ORDER BY c.`sort_order` ASC, c.`name` ASC"
			)->fetchAll(\PDO::FETCH_ASSOC);

			return $rows ?: [];
		}

		public static function getCategoryBySlug(string $slug): ?array {
			self::ensureSchema();

			$row = Database::singleton()->run(
				"SELECT * FROM `forum_categories` WHERE `slug` = :slug LIMIT 1",
				[":slug" => $slug]
			)->fetch(\PDO::FETCH_ASSOC);

			return $row ?: null;
		}

		public static function getTopics(?string $categorySlug = null, string $query = ""): array {
			self::ensureSchema();

			$db = Database::singleton();
			$sql = "SELECT
					t.*,
					c.`slug` AS category_slug,
					c.`name` AS category_name,
					c.`color` AS category_color,
					u.`name` AS author_name,
					lp.`name` AS last_post_user_name,
					(
						SELECT COUNT(*)
						FROM `forum_posts` p
						WHERE p.`topic_id` = t.`id`
					) AS reply_count
				FROM `forum_topics` t
				INNER JOIN `forum_categories` c ON c.`id` = t.`category_id`
				INNER JOIN `users` u ON u.`id` = t.`author_id`
				INNER JOIN `users` lp ON lp.`id` = t.`last_post_user_id`
				WHERE 1 = 1";

			$args = [];

			if ($categorySlug !== null && $categorySlug !== "") {
				$sql .= " AND c.`slug` = :category_slug";
				$args[":category_slug"] = $categorySlug;
			}

			if ($query !== "") {
				$sql .= " AND (t.`title` LIKE :query OR t.`content` LIKE :query)";
				$args[":query"] = "%" . $query . "%";
			}

			$sql .= " ORDER BY t.`last_post_at` DESC, t.`id` DESC";

			$rows = $db->run($sql, $args)->fetchAll(\PDO::FETCH_ASSOC);
			return $rows ?: [];
		}

		public static function getTopic(int $topicId): ?array {
			self::ensureSchema();

			$row = Database::singleton()->run(
				"SELECT
					t.*,
					c.`slug` AS category_slug,
					c.`name` AS category_name,
					c.`color` AS category_color,
					u.`name` AS author_name,
					lp.`name` AS last_post_user_name,
					(
						SELECT COUNT(*)
						FROM `forum_posts` p
						WHERE p.`topic_id` = t.`id`
					) AS reply_count
				FROM `forum_topics` t
				INNER JOIN `forum_categories` c ON c.`id` = t.`category_id`
				INNER JOIN `users` u ON u.`id` = t.`author_id`
				INNER JOIN `users` lp ON lp.`id` = t.`last_post_user_id`
				WHERE t.`id` = :topic_id
				LIMIT 1",
				[":topic_id" => $topicId]
			)->fetch(\PDO::FETCH_ASSOC);

			return $row ?: null;
		}

		public static function getPosts(int $topicId): array {
			self::ensureSchema();

			$topic = self::getTopic($topicId);
			if (!$topic) {
				return [];
			}

			$posts = [[
				"id" => "topic-" . $topic["id"],
				"author_id" => $topic["author_id"],
				"author_name" => $topic["author_name"],
				"content" => $topic["content"],
				"created_at" => $topic["created_at"],
				"is_topic" => true
			]];

			$replyRows = Database::singleton()->run(
				"SELECT
					p.`id`,
					p.`author_id`,
					p.`content`,
					p.`created_at`,
					u.`name` AS author_name
				FROM `forum_posts` p
				INNER JOIN `users` u ON u.`id` = p.`author_id`
				WHERE p.`topic_id` = :topic_id
				ORDER BY p.`created_at` ASC, p.`id` ASC",
				[":topic_id" => $topicId]
			)->fetchAll(\PDO::FETCH_ASSOC);

			foreach ($replyRows as $row) {
				$row["is_topic"] = false;
				$posts[] = $row;
			}

			return $posts;
		}

		public static function createTopic(User $user, string $title, string $content, string $categorySlug): array {
			self::ensureSchema();

			$title = self::normalizeText($title);
			$content = self::normalizeText($content);
			$category = self::getCategoryBySlug($categorySlug);

			if (!$category) {
				return ["error" => true, "reason" => "Invalid forum category."];
			}

			if (strlen($title) < 4 || strlen($title) > 120) {
				return ["error" => true, "reason" => "Topic title must be between 4 and 120 characters."];
			}

			if (strlen($content) < 8 || strlen($content) > 4000) {
				return ["error" => true, "reason" => "Topic body must be between 8 and 4000 characters."];
			}

			$db = Database::singleton();
			$db->run(
				"INSERT INTO `forum_topics`
					(`category_id`, `author_id`, `title`, `content`, `last_post_user_id`)
				VALUES
					(:category_id, :author_id, :title, :content, :last_post_user_id)",
				[
					":category_id" => intval($category["id"]),
					":author_id" => $user->id,
					":title" => $title,
					":content" => $content,
					":last_post_user_id" => $user->id
				]
			);

			return [
				"error" => false,
				"topic_id" => intval($db->lastInsertId())
			];
		}

		public static function replyToTopic(User $user, int $topicId, string $content): array {
			self::ensureSchema();

			$topic = self::getTopic($topicId);
			if (!$topic) {
				return ["error" => true, "reason" => "Topic not found."];
			}

			$content = self::normalizeText($content);
			if (strlen($content) < 2 || strlen($content) > 4000) {
				return ["error" => true, "reason" => "Reply must be between 2 and 4000 characters."];
			}

			$db = Database::singleton();
			$db->run(
				"INSERT INTO `forum_posts` (`topic_id`, `author_id`, `content`) VALUES (:topic_id, :author_id, :content)",
				[
					":topic_id" => $topicId,
					":author_id" => $user->id,
					":content" => $content
				]
			);

			$db->run(
				"UPDATE `forum_topics`
				SET `updated_at` = CURRENT_TIMESTAMP(),
					`last_post_at` = CURRENT_TIMESTAMP(),
					`last_post_user_id` = :last_post_user_id
				WHERE `id` = :topic_id",
				[
					":last_post_user_id" => $user->id,
					":topic_id" => $topicId
				]
			);

			return ["error" => false];
		}
	}
?>
