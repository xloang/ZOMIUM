<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/core/classes/user.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/userutils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/slurutils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/utilutils.php';

class ForumThread {
    public string $id;
    public User $poster;
    public string $title;
    public string $content;
    public string $mediaType;
    public string $mediaPath;
    public DateTime $createdAt;

    public function __construct(array $row) {
        $this->id = (string) $row['thread_id'];
        $this->poster = User::FromID((int) $row['thread_poster']);
        $this->title = self::escapeText((string) $row['thread_title']);
        $this->content = self::escapeText((string) $row['thread_content']);
        $this->mediaType = (string) ($row['thread_media_type'] ?? '');
        $this->mediaPath = (string) ($row['thread_media_path'] ?? '');
        $this->createdAt = DateTime::createFromFormat('Y-m-d H:i:s', $row['thread_created_at']);
    }

    private static function escapeText(string $text): string {
        return str_replace('<', '&lt;', str_replace('>', '&gt;', $text));
    }
}

class ForumReply {
    public string $id;
    public string $threadId;
    public User $poster;
    public string $content;
    public string $mediaType;
    public string $mediaPath;
    public DateTime $createdAt;

    public function __construct(array $row) {
        $this->id = (string) $row['reply_id'];
        $this->threadId = (string) $row['thread_id'];
        $this->poster = User::FromID((int) $row['reply_poster']);
        $this->content = str_replace('<', '&lt;', str_replace('>', '&gt;', (string) $row['reply_content']));
        $this->mediaType = (string) ($row['reply_media_type'] ?? '');
        $this->mediaPath = (string) ($row['reply_media_path'] ?? '');
        $this->createdAt = DateTime::createFromFormat('Y-m-d H:i:s', $row['reply_created_at']);
    }
}

class Forum {
    private static bool $bootstrapped = false;

    private static function db(): mysqli {
        include $_SERVER['DOCUMENT_ROOT'].'/core/connection.php';
        return $con;
    }

    public static function Boot(): void {
        if (self::$bootstrapped) {
            return;
        }

        $con = self::db();
        $con->query("CREATE TABLE IF NOT EXISTS `forum_threads` (
            `thread_id` varchar(20) NOT NULL,
            `thread_poster` int NOT NULL,
            `thread_title` varchar(120) NOT NULL,
            `thread_content` text NOT NULL,
            `thread_media_type` varchar(16) DEFAULT NULL,
            `thread_media_path` varchar(255) DEFAULT NULL,
            `thread_created_at` datetime NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`thread_id`),
            KEY `thread_poster` (`thread_poster`),
            KEY `thread_created_at` (`thread_created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $con->query("CREATE TABLE IF NOT EXISTS `forum_replies` (
            `reply_id` varchar(20) NOT NULL,
            `thread_id` varchar(20) NOT NULL,
            `reply_poster` int NOT NULL,
            `reply_content` text NOT NULL,
            `reply_media_type` varchar(16) DEFAULT NULL,
            `reply_media_path` varchar(255) DEFAULT NULL,
            `reply_created_at` datetime NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`reply_id`),
            KEY `thread_id` (`thread_id`),
            KEY `reply_poster` (`reply_poster`),
            KEY `reply_created_at` (`reply_created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $uploadRoot = $_SERVER['DOCUMENT_ROOT'].'/media/forum';
        if (!is_dir($uploadRoot)) {
            mkdir($uploadRoot, 0777, true);
        }

        self::$bootstrapped = true;
    }

    private static function randomId(int $length = 14): string {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $id;
    }

    private static function generateUniqueId(string $table, string $column): string {
        self::Boot();
        $con = self::db();

        do {
            $id = self::randomId();
            $stmt = $con->prepare("SELECT 1 FROM `$table` WHERE `$column` = ? LIMIT 1");
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $exists = $stmt->get_result()->num_rows > 0;
            $stmt->close();
        } while ($exists);

        return $id;
    }

    private static function lastThreadFromUser(User $user): ?ForumThread {
        self::Boot();
        $con = self::db();
        $stmt = $con->prepare('SELECT * FROM `forum_threads` WHERE `thread_poster` = ? ORDER BY `thread_created_at` DESC LIMIT 1');
        $stmt->bind_param('i', $user->id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row ? new ForumThread($row) : null;
    }

    private static function lastReplyFromUser(User $user): ?ForumReply {
        self::Boot();
        $con = self::db();
        $stmt = $con->prepare('SELECT * FROM `forum_replies` WHERE `reply_poster` = ? ORDER BY `reply_created_at` DESC LIMIT 1');
        $stmt->bind_param('i', $user->id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row ? new ForumReply($row) : null;
    }

    private static function checkCooldown(?DateTime $lastAction, int $seconds): ?string {
        if ($lastAction === null) {
            return null;
        }

        $difference = time() - $lastAction->getTimestamp();
        if ($difference < $seconds) {
            return 'Wait '.($seconds - $difference).' seconds before posting again.';
        }

        return null;
    }

    private static function handleUpload(?array $file): array {
        if ($file === null || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['error' => false, 'path' => '', 'type' => ''];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => true, 'reason' => 'Upload failed.'];
        }

        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $videoExtensions = ['mp4', 'webm', 'mov', 'ogg'];

        $type = '';
        $maxSize = 0;

        if (in_array($extension, $imageExtensions, true)) {
            $type = 'image';
            $maxSize = 8 * 1024 * 1024;
        } elseif (in_array($extension, $videoExtensions, true)) {
            $type = 'video';
            $maxSize = 64 * 1024 * 1024;
        } else {
            return ['error' => true, 'reason' => 'Only image or video uploads are allowed.'];
        }

        if ((int) $file['size'] <= 0 || (int) $file['size'] > $maxSize) {
            return ['error' => true, 'reason' => $type === 'image' ? 'Images must be under 8 MB.' : 'Videos must be under 64 MB.'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
        if ($finfo) {
            finfo_close($finfo);
        }

        $allowedImageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedVideoMimes = ['video/mp4', 'video/webm', 'video/quicktime', 'video/ogg', 'application/ogg'];

        if ($type === 'image' && !in_array($mime, $allowedImageMimes, true)) {
            return ['error' => true, 'reason' => 'Invalid image file.'];
        }

        if ($type === 'video' && !in_array($mime, $allowedVideoMimes, true)) {
            return ['error' => true, 'reason' => 'Invalid video file.'];
        }

        $folder = '/media/forum/'.date('Y').'/'.date('m');
        $fullFolder = $_SERVER['DOCUMENT_ROOT'].$folder;
        if (!is_dir($fullFolder)) {
            mkdir($fullFolder, 0777, true);
        }

        $filename = self::randomId(24).'.'.$extension;
        $fullPath = $fullFolder.'/'.$filename;
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return ['error' => true, 'reason' => 'Failed to move uploaded file.'];
        }

        return ['error' => false, 'path' => $folder.'/'.$filename, 'type' => $type];
    }

    private static function normalizeText(string $text): string {
        $blockedchars = ['𒐫', '‮', '﷽', '𒈙', '⸻ ', '꧅'];
        return SlurUtils::ProcessText(trim(str_replace($blockedchars, '', $text)));
    }

    public static function CreateThread(string $title, string $content, ?array $file): array {
        self::Boot();
        $user = UserUtils::RetrieveUser();
        if ($user === null) {
            return ['error' => true, 'reason' => 'You must be logged in to post.'];
        }

        $title = self::normalizeText($title);
        $content = self::normalizeText($content);

        if (strlen($title) < 4 || strlen($title) > 120) {
            return ['error' => true, 'reason' => 'Title must be between 4 and 120 characters.'];
        }

        if (strlen($content) < 8 || strlen($content) > 5000) {
            return ['error' => true, 'reason' => 'Message must be between 8 and 5000 characters.'];
        }

        $cooldown = self::checkCooldown(self::lastThreadFromUser($user)?->createdAt, 20);
        if ($cooldown !== null) {
            return ['error' => true, 'reason' => $cooldown];
        }

        $upload = self::handleUpload($file);
        if ($upload['error']) {
            return $upload;
        }

        $con = self::db();
        $threadId = self::generateUniqueId('forum_threads', 'thread_id');
        $stmt = $con->prepare('INSERT INTO `forum_threads` (`thread_id`, `thread_poster`, `thread_title`, `thread_content`, `thread_media_type`, `thread_media_path`) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sissss', $threadId, $user->id, $title, $content, $upload['type'], $upload['path']);
        $stmt->execute();
        $stmt->close();

        return ['error' => false, 'id' => $threadId];
    }

    public static function CreateReply(string $threadId, string $content, ?array $file): array {
        self::Boot();
        $user = UserUtils::RetrieveUser();
        if ($user === null) {
            return ['error' => true, 'reason' => 'You must be logged in to reply.'];
        }

        $thread = self::GetThread($threadId);
        if ($thread === null) {
            return ['error' => true, 'reason' => 'Thread not found.'];
        }

        $content = self::normalizeText($content);
        if (strlen($content) < 2 || strlen($content) > 5000) {
            return ['error' => true, 'reason' => 'Reply must be between 2 and 5000 characters.'];
        }

        $cooldown = self::checkCooldown(self::lastReplyFromUser($user)?->createdAt, 10);
        if ($cooldown !== null) {
            return ['error' => true, 'reason' => $cooldown];
        }

        $upload = self::handleUpload($file);
        if ($upload['error']) {
            return $upload;
        }

        $con = self::db();
        $replyId = self::generateUniqueId('forum_replies', 'reply_id');
        $stmt = $con->prepare('INSERT INTO `forum_replies` (`reply_id`, `thread_id`, `reply_poster`, `reply_content`, `reply_media_type`, `reply_media_path`) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssisss', $replyId, $threadId, $user->id, $content, $upload['type'], $upload['path']);
        $stmt->execute();
        $stmt->close();

        return ['error' => false, 'id' => $replyId];
    }

    public static function GetThread(string $threadId): ?ForumThread {
        self::Boot();
        $con = self::db();
        $stmt = $con->prepare('SELECT * FROM `forum_threads` WHERE `thread_id` = ? LIMIT 1');
        $stmt->bind_param('s', $threadId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row ? new ForumThread($row) : null;
    }

    public static function GetReplies(string $threadId): array {
        self::Boot();
        $con = self::db();
        $stmt = $con->prepare('SELECT * FROM `forum_replies` WHERE `thread_id` = ? ORDER BY `reply_created_at` ASC');
        $stmt->bind_param('s', $threadId);
        $stmt->execute();
        $result = $stmt->get_result();

        $replies = [];
        while ($row = $result->fetch_assoc()) {
            $replies[] = new ForumReply($row);
        }

        $stmt->close();
        return $replies;
    }

    public static function GetThreads(int $page, int $perPage): array {
        self::Boot();
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $con = self::db();
        $stmt = $con->prepare('SELECT * FROM `forum_threads` ORDER BY `thread_created_at` DESC LIMIT ?, ?');
        $stmt->bind_param('ii', $offset, $perPage);
        $stmt->execute();
        $result = $stmt->get_result();

        $threads = [];
        while ($row = $result->fetch_assoc()) {
            $threads[] = new ForumThread($row);
        }

        $stmt->close();
        return $threads;
    }

    public static function CountThreads(): int {
        self::Boot();
        $con = self::db();
        $result = $con->query('SELECT COUNT(*) AS total FROM `forum_threads`');
        $row = $result ? $result->fetch_assoc() : ['total' => 0];
        return (int) $row['total'];
    }
}
?>
