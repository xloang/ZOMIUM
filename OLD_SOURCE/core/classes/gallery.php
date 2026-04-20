<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/core/classes/user.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/userutils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/slurutils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/utilutils.php';

class GalleryItem {
    public string $id;
    public ?User $poster;
    public string $title;
    public string $description;
    public string $mediaType;
    public string $mediaPath;
    public DateTime $createdAt;

    public function __construct(array $row) {
        $this->id = (string) $row['item_id'];
        $this->poster = User::FromID((int) $row['item_poster']);
        $this->title = str_replace('<', '&lt;', str_replace('>', '&gt;', (string) $row['item_title']));
        $this->description = str_replace('<', '&lt;', str_replace('>', '&gt;', (string) $row['item_description']));
        $this->mediaType = (string) $row['item_media_type'];
        $this->mediaPath = (string) $row['item_media_path'];
        $this->createdAt = DateTime::createFromFormat('Y-m-d H:i:s', $row['item_created_at']);
    }
}

class Gallery {
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
        $con->query("CREATE TABLE IF NOT EXISTS `gallery_items` (
            `item_id` varchar(20) NOT NULL,
            `item_poster` int NOT NULL,
            `item_title` varchar(120) NOT NULL,
            `item_description` text NOT NULL,
            `item_media_type` varchar(16) NOT NULL,
            `item_media_path` varchar(255) NOT NULL,
            `item_created_at` datetime NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`item_id`),
            KEY `item_poster` (`item_poster`),
            KEY `item_created_at` (`item_created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $uploadRoot = $_SERVER['DOCUMENT_ROOT'].'/media/gallery';
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

    private static function generateUniqueId(): string {
        self::Boot();
        $con = self::db();

        do {
            $id = self::randomId();
            $stmt = $con->prepare('SELECT 1 FROM `gallery_items` WHERE `item_id` = ? LIMIT 1');
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $exists = $stmt->get_result()->num_rows > 0;
            $stmt->close();
        } while ($exists);

        return $id;
    }

    private static function normalizeText(string $text): string {
        $blockedchars = ['𒐫', '‮', '﷽', '𒈙', '⸻ ', '꧅'];
        return SlurUtils::ProcessText(trim(str_replace($blockedchars, '', $text)));
    }

    private static function lastItemFromUser(User $user): ?GalleryItem {
        self::Boot();
        $con = self::db();
        $stmt = $con->prepare('SELECT * FROM `gallery_items` WHERE `item_poster` = ? ORDER BY `item_created_at` DESC LIMIT 1');
        $stmt->bind_param('i', $user->id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row ? new GalleryItem($row) : null;
    }

    private static function handleUpload(array $file): array {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['error' => true, 'reason' => 'Please choose an image or video file.'];
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

        $folder = '/media/gallery/'.date('Y').'/'.date('m');
        $fullFolder = $_SERVER['DOCUMENT_ROOT'].$folder;
        if (!is_dir($fullFolder)) {
            mkdir($fullFolder, 0777, true);
        }

        $filename = self::randomId(24).'.'.$extension;
        $fullPath = $fullFolder.'/'.$filename;
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return ['error' => true, 'reason' => 'Failed to save uploaded file.'];
        }

        return ['error' => false, 'path' => $folder.'/'.$filename, 'type' => $type];
    }

    public static function Upload(string $title, string $description, array $file): array {
        self::Boot();
        $user = UserUtils::RetrieveUser();
        if ($user === null) {
            return ['error' => true, 'reason' => 'You must be logged in to upload.'];
        }

        $title = self::normalizeText($title);
        $description = self::normalizeText($description);

        if (strlen($title) < 3 || strlen($title) > 120) {
            return ['error' => true, 'reason' => 'Title must be between 3 and 120 characters.'];
        }

        if (strlen($description) < 3 || strlen($description) > 2000) {
            return ['error' => true, 'reason' => 'Description must be between 3 and 2000 characters.'];
        }

        $lastItem = self::lastItemFromUser($user);
        if (!$user->IsAdmin() && $lastItem !== null) {
            $difference = time() - $lastItem->createdAt->getTimestamp();
            if ($difference < 10) {
                return ['error' => true, 'reason' => 'Wait '.(10 - $difference).' seconds before uploading again.'];
            }
        }

        $upload = self::handleUpload($file);
        if ($upload['error']) {
            return $upload;
        }

        $itemId = self::generateUniqueId();
        $con = self::db();
        $stmt = $con->prepare('INSERT INTO `gallery_items` (`item_id`, `item_poster`, `item_title`, `item_description`, `item_media_type`, `item_media_path`) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sissss', $itemId, $user->id, $title, $description, $upload['type'], $upload['path']);
        $stmt->execute();
        $stmt->close();

        return ['error' => false, 'id' => $itemId];
    }

    public static function GetItem(string $id): ?GalleryItem {
        self::Boot();
        $con = self::db();
        $stmt = $con->prepare('SELECT * FROM `gallery_items` WHERE `item_id` = ? LIMIT 1');
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? new GalleryItem($row) : null;
    }

    private static function GetItemsInternal(int $page, int $perPage, ?string $mediaType = null): array {
        self::Boot();
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;
        $con = self::db();

        if ($mediaType === null) {
            $stmt = $con->prepare('SELECT * FROM `gallery_items` ORDER BY `item_created_at` DESC LIMIT ?, ?');
            $stmt->bind_param('ii', $offset, $perPage);
        } else {
            $stmt = $con->prepare('SELECT * FROM `gallery_items` WHERE `item_media_type` = ? ORDER BY `item_created_at` DESC LIMIT ?, ?');
            $stmt->bind_param('sii', $mediaType, $offset, $perPage);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = new GalleryItem($row);
        }

        $stmt->close();
        return $items;
    }

    public static function GetItems(int $page, int $perPage): array {
        return self::GetItemsInternal($page, $perPage);
    }

    public static function GetItemsByType(int $page, int $perPage, string $mediaType): array {
        $mediaType = strtolower(trim($mediaType));
        if ($mediaType !== 'image' && $mediaType !== 'video') {
            return [];
        }

        return self::GetItemsInternal($page, $perPage, $mediaType);
    }

    public static function GetVideoItems(int $page, int $perPage): array {
        return self::GetItemsInternal($page, $perPage, 'video');
    }

    private static function CountItemsInternal(?string $mediaType = null): int {
        self::Boot();
        $con = self::db();

        if ($mediaType === null) {
            $result = $con->query('SELECT COUNT(*) AS total FROM `gallery_items`');
            $row = $result ? $result->fetch_assoc() : ['total' => 0];
            return (int) $row['total'];
        }

        $stmt = $con->prepare('SELECT COUNT(*) AS total FROM `gallery_items` WHERE `item_media_type` = ?');
        $stmt->bind_param('s', $mediaType);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : ['total' => 0];
        $stmt->close();

        return (int) $row['total'];
    }

    public static function CountItems(): int {
        return self::CountItemsInternal();
    }

    public static function CountItemsByType(string $mediaType): int {
        $mediaType = strtolower(trim($mediaType));
        if ($mediaType !== 'image' && $mediaType !== 'video') {
            return 0;
        }

        return self::CountItemsInternal($mediaType);
    }

    public static function CountVideoItems(): int {
        return self::CountItemsInternal('video');
    }
}
?>

