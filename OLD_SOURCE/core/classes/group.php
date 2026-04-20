<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/classes/user.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/utilities/slurutils.php';

class GroupRecord {
    public int $id;
    public string $name;
    public string $description;
    public string $logoPath;
    public ?User $owner;
    public DateTime $createdAt;
    public int $memberCount;
    public ?string $viewerRole;

    public function __construct(array $row) {
        $this->id = (int) $row['group_id'];
        $this->name = (string) $row['group_name'];
        $this->description = (string) $row['group_description'];
        $this->logoPath = (string) ($row['group_logo_path'] ?? '');
        $this->owner = User::FromID((int) $row['group_owner_user_id']);
        $this->createdAt = DateTime::createFromFormat('Y-m-d H:i:s', $row['group_created_at']) ?: new DateTime();
        $this->memberCount = isset($row['member_count']) ? (int) $row['member_count'] : 0;
        $this->viewerRole = isset($row['viewer_role']) && $row['viewer_role'] !== null ? (string) $row['viewer_role'] : null;
    }

    public function GetLogoUrl(): string {
        return $this->logoPath !== '' ? $this->logoPath : '/images/unavailable.png';
    }
}

class Group {
    public const CREATE_COST = 100;
    private const MAX_LOGO_SIZE = 4194304;
    private static bool $bootstrapped = false;

    private static function db(): mysqli {
        include $_SERVER['DOCUMENT_ROOT'] . '/core/connection.php';
        return $con;
    }

    public static function Boot(): void {
        if (self::$bootstrapped) {
            return;
        }

        $con = self::db();
        $con->query("CREATE TABLE IF NOT EXISTS `site_groups` (
            `group_id` int NOT NULL AUTO_INCREMENT,
            `group_name` varchar(64) NOT NULL,
            `group_description` text NOT NULL,
            `group_logo_path` varchar(255) NOT NULL,
            `group_owner_user_id` int NOT NULL,
            `group_created_at` datetime NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`group_id`),
            UNIQUE KEY `group_name` (`group_name`),
            KEY `group_owner_user_id` (`group_owner_user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $con->query("CREATE TABLE IF NOT EXISTS `site_group_members` (
            `group_id` int NOT NULL,
            `user_id` int NOT NULL,
            `group_role` varchar(16) NOT NULL,
            `group_joined_at` datetime NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`group_id`, `user_id`),
            KEY `user_id` (`user_id`),
            KEY `group_role` (`group_role`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $uploadRoot = $_SERVER['DOCUMENT_ROOT'] . '/media/groups';
        if (!is_dir($uploadRoot)) {
            mkdir($uploadRoot, 0777, true);
        }

        self::$bootstrapped = true;
    }

    private static function randomId(int $length = 24): string {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $id;
    }

    private static function normalizeText(string $text): string {
        return trim(SlurUtils::ProcessText($text));
    }

    private static function normalizeRole(string $role): string {
        return strtolower(trim($role));
    }

    private static function isValidRole(string $role): bool {
        return in_array(self::normalizeRole($role), ['member', 'admin', 'owner'], true);
    }

    private static function roleSortSql(string $column): string {
        return "FIELD($column, 'owner', 'admin', 'member')";
    }

    private static function handleLogoUpload(array $file, bool $required): array {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return $required
                ? ['error' => true, 'reason' => 'Group logo is required.']
                : ['error' => false, 'path' => ''];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => true, 'reason' => 'Logo upload failed.'];
        }

        if ((int) $file['size'] <= 0 || (int) $file['size'] > self::MAX_LOGO_SIZE) {
            return ['error' => true, 'reason' => 'Group logo must be under 4 MB.'];
        }

        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowedExtensions, true)) {
            return ['error' => true, 'reason' => 'Group logo must be a JPG, PNG, GIF, or WEBP image.'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
        if ($finfo) {
            finfo_close($finfo);
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mime, $allowedMimes, true)) {
            return ['error' => true, 'reason' => 'Invalid group logo image.'];
        }

        $folder = '/media/groups/' . date('Y') . '/' . date('m');
        $fullFolder = $_SERVER['DOCUMENT_ROOT'] . $folder;
        if (!is_dir($fullFolder)) {
            mkdir($fullFolder, 0777, true);
        }

        $filename = self::randomId() . '.' . $extension;
        $fullPath = $fullFolder . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return ['error' => true, 'reason' => 'Failed to save group logo.'];
        }

        return ['error' => false, 'path' => $folder . '/' . $filename];
    }

    private static function groupNameExists(string $name, ?int $ignoreGroupId = null): bool {
        self::Boot();
        $con = self::db();
        $normalized = strtolower($name);

        if ($ignoreGroupId === null) {
            $stmt = $con->prepare('SELECT `group_id` FROM `site_groups` WHERE LOWER(`group_name`) = ? LIMIT 1');
            $stmt->bind_param('s', $normalized);
        } else {
            $stmt = $con->prepare('SELECT `group_id` FROM `site_groups` WHERE LOWER(`group_name`) = ? AND `group_id` != ? LIMIT 1');
            $stmt->bind_param('si', $normalized, $ignoreGroupId);
        }

        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public static function Create(User $owner, string $name, string $description, array $logoFile): array {
        self::Boot();
        $name = self::normalizeText($name);
        $description = self::normalizeText($description);

        if (strlen($name) < 3 || strlen($name) > 64) {
            return ['error' => true, 'reason' => 'Group name must be between 3 and 64 characters.'];
        }

        if (strlen($description) < 10 || strlen($description) > 2000) {
            return ['error' => true, 'reason' => 'Description must be between 10 and 2000 characters.'];
        }

        if (self::groupNameExists($name)) {
            return ['error' => true, 'reason' => 'That group name is already taken.'];
        }

        $upload = self::handleLogoUpload($logoFile, true);
        if ($upload['error']) {
            return $upload;
        }

        $con = self::db();
        $con->begin_transaction();

        try {
            $stmtSpend = $con->prepare('UPDATE `users` SET `user_ziu` = `user_ziu` - ? WHERE `user_id` = ? AND `user_ziu` >= ?');
            $cost = self::CREATE_COST;
            $stmtSpend->bind_param('iii', $cost, $owner->id, $cost);
            $stmtSpend->execute();
            if ($stmtSpend->affected_rows !== 1) {
                $stmtSpend->close();
                $con->rollback();
                return ['error' => true, 'reason' => 'You need 100 ZIU to create a group.'];
            }
            $stmtSpend->close();

            $stmtGroup = $con->prepare('INSERT INTO `site_groups` (`group_name`, `group_description`, `group_logo_path`, `group_owner_user_id`) VALUES (?, ?, ?, ?)');
            $stmtGroup->bind_param('sssi', $name, $description, $upload['path'], $owner->id);
            $stmtGroup->execute();
            $groupId = (int) $con->insert_id;
            $stmtGroup->close();

            $ownerRole = 'owner';
            $stmtMember = $con->prepare('INSERT INTO `site_group_members` (`group_id`, `user_id`, `group_role`) VALUES (?, ?, ?)');
            $stmtMember->bind_param('iis', $groupId, $owner->id, $ownerRole);
            $stmtMember->execute();
            $stmtMember->close();

            $con->commit();
            return ['error' => false, 'group_id' => $groupId];
        } catch (Throwable $e) {
            $con->rollback();
            return ['error' => true, 'reason' => 'Failed to create group right now.'];
        }
    }

    public static function SearchGroups(string $query = '', int $viewerId = 0, int $limit = 24): array {
        self::Boot();
        $con = self::db();
        $limit = max(1, $limit);
        $like = '%' . trim($query) . '%';
        $sql = 'SELECT g.*, vm.group_role AS viewer_role,
            (SELECT COUNT(*) FROM `site_group_members` gm WHERE gm.group_id = g.group_id) AS member_count
            FROM `site_groups` g
            LEFT JOIN `site_group_members` vm ON vm.group_id = g.group_id AND vm.user_id = ?
            WHERE g.group_name LIKE ? OR g.group_description LIKE ?
            ORDER BY g.group_created_at DESC
            LIMIT ?';

        $stmt = $con->prepare($sql);
        $stmt->bind_param('issi', $viewerId, $like, $like, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $groups = [];
        while ($row = $result->fetch_assoc()) {
            $groups[] = new GroupRecord($row);
        }

        $stmt->close();
        return $groups;
    }

    public static function GetGroupsForUser(int $userId, int $limit = 50): array {
        self::Boot();
        $con = self::db();
        $limit = max(1, $limit);
        $sort = self::roleSortSql('m.group_role');
        $sql = "SELECT g.*, m.group_role AS viewer_role,
            (SELECT COUNT(*) FROM `site_group_members` gm WHERE gm.group_id = g.group_id) AS member_count
            FROM `site_group_members` m
            INNER JOIN `site_groups` g ON g.group_id = m.group_id
            WHERE m.user_id = ?
            ORDER BY $sort, g.group_name ASC
            LIMIT ?";

        $stmt = $con->prepare($sql);
        $stmt->bind_param('ii', $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $groups = [];
        while ($row = $result->fetch_assoc()) {
            $groups[] = new GroupRecord($row);
        }

        $stmt->close();
        return $groups;
    }

    public static function GetById(int $groupId, int $viewerId = 0): ?GroupRecord {
        self::Boot();
        $con = self::db();
        $stmt = $con->prepare('SELECT g.*, vm.group_role AS viewer_role,
            (SELECT COUNT(*) FROM `site_group_members` gm WHERE gm.group_id = g.group_id) AS member_count
            FROM `site_groups` g
            LEFT JOIN `site_group_members` vm ON vm.group_id = g.group_id AND vm.user_id = ?
            WHERE g.group_id = ?
            LIMIT 1');
        $stmt->bind_param('ii', $viewerId, $groupId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row ? new GroupRecord($row) : null;
    }

    public static function GetMembership(int $groupId, int $userId): ?array {
        self::Boot();
        $con = self::db();
        $stmt = $con->prepare('SELECT * FROM `site_group_members` WHERE `group_id` = ? AND `user_id` = ? LIMIT 1');
        $stmt->bind_param('ii', $groupId, $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return null;
        }

        return [
            'group_id' => (int) $row['group_id'],
            'user_id' => (int) $row['user_id'],
            'role' => (string) $row['group_role'],
            'joined_at' => DateTime::createFromFormat('Y-m-d H:i:s', $row['group_joined_at']) ?: new DateTime(),
        ];
    }

    public static function GetMembers(int $groupId): array {
        self::Boot();
        $con = self::db();
        $sort = self::roleSortSql('group_role');
        $stmt = $con->prepare("SELECT * FROM `site_group_members` WHERE `group_id` = ? ORDER BY $sort, `group_joined_at` ASC");
        $stmt->bind_param('i', $groupId);
        $stmt->execute();
        $result = $stmt->get_result();

        $members = [];
        while ($row = $result->fetch_assoc()) {
            $user = User::FromID((int) $row['user_id']);
            if ($user === null) {
                continue;
            }

            $members[] = [
                'user' => $user,
                'role' => (string) $row['group_role'],
                'joined_at' => DateTime::createFromFormat('Y-m-d H:i:s', $row['group_joined_at']) ?: new DateTime(),
            ];
        }

        $stmt->close();
        return $members;
    }

    public static function CanManage(User $user, int $groupId): bool {
        $membership = self::GetMembership($groupId, $user->id);
        return $membership !== null && in_array($membership['role'], ['owner', 'admin'], true);
    }

    public static function UpdateGroup(User $actor, int $groupId, string $name, string $description, array $logoFile): array {
        self::Boot();
        $membership = self::GetMembership($groupId, $actor->id);
        if ($membership === null || !in_array($membership['role'], ['owner', 'admin'], true)) {
            return ['error' => true, 'reason' => 'You cannot edit this group.'];
        }

        $group = self::GetById($groupId, $actor->id);
        if ($group === null) {
            return ['error' => true, 'reason' => 'Group not found.'];
        }

        $name = self::normalizeText($name);
        $description = self::normalizeText($description);

        if (strlen($name) < 3 || strlen($name) > 64) {
            return ['error' => true, 'reason' => 'Group name must be between 3 and 64 characters.'];
        }

        if (strlen($description) < 10 || strlen($description) > 2000) {
            return ['error' => true, 'reason' => 'Description must be between 10 and 2000 characters.'];
        }

        if (self::groupNameExists($name, $groupId)) {
            return ['error' => true, 'reason' => 'That group name is already taken.'];
        }

        $logoPath = $group->logoPath;
        $upload = self::handleLogoUpload($logoFile, false);
        if ($upload['error']) {
            return $upload;
        }
        if ($upload['path'] !== '') {
            $logoPath = $upload['path'];
        }

        $con = self::db();
        $stmt = $con->prepare('UPDATE `site_groups` SET `group_name` = ?, `group_description` = ?, `group_logo_path` = ? WHERE `group_id` = ?');
        $stmt->bind_param('sssi', $name, $description, $logoPath, $groupId);
        $stmt->execute();
        $stmt->close();

        return ['error' => false];
    }

    public static function AddMember(User $actor, int $groupId, string $username, string $role = 'member'): array {
        self::Boot();
        $membership = self::GetMembership($groupId, $actor->id);
        if ($membership === null || !in_array($membership['role'], ['owner', 'admin'], true)) {
            return ['error' => true, 'reason' => 'You cannot manage this group.'];
        }

        $role = self::normalizeRole($role);
        if (!in_array($role, ['member', 'admin'], true)) {
            return ['error' => true, 'reason' => 'Invalid group role.'];
        }

        if ($membership['role'] !== 'owner' && $role !== 'member') {
            return ['error' => true, 'reason' => 'Only the owner can add admins directly.'];
        }

        $target = User::FromName(trim($username));
        if ($target === null) {
            return ['error' => true, 'reason' => 'User not found.'];
        }

        if (self::GetMembership($groupId, $target->id) !== null) {
            return ['error' => true, 'reason' => 'That user is already in the group.'];
        }

        $con = self::db();
        $stmt = $con->prepare('INSERT INTO `site_group_members` (`group_id`, `user_id`, `group_role`) VALUES (?, ?, ?)');
        $stmt->bind_param('iis', $groupId, $target->id, $role);
        $stmt->execute();
        $stmt->close();

        return ['error' => false];
    }

    public static function UpdateMemberRole(User $actor, int $groupId, int $targetUserId, string $newRole): array {
        self::Boot();
        $newRole = self::normalizeRole($newRole);
        if (!self::isValidRole($newRole)) {
            return ['error' => true, 'reason' => 'Invalid group role.'];
        }

        $actorMembership = self::GetMembership($groupId, $actor->id);
        $targetMembership = self::GetMembership($groupId, $targetUserId);
        if ($actorMembership === null || $targetMembership === null) {
            return ['error' => true, 'reason' => 'Group membership not found.'];
        }

        if (!in_array($actorMembership['role'], ['owner', 'admin'], true)) {
            return ['error' => true, 'reason' => 'You cannot manage roles in this group.'];
        }

        if ($actorMembership['role'] === 'admin') {
            if ($targetMembership['role'] !== 'member') {
                return ['error' => true, 'reason' => 'Admins can only manage members.'];
            }
            if (!in_array($newRole, ['member', 'admin'], true)) {
                return ['error' => true, 'reason' => 'Admins cannot assign that role.'];
            }
        }

        if ($actorMembership['role'] === 'owner') {
            if ($targetUserId === $actor->id && $newRole !== 'owner') {
                return ['error' => true, 'reason' => 'Transfer ownership before changing your own owner role.'];
            }

            if ($newRole === 'owner') {
                if ($targetUserId === $actor->id) {
                    return ['error' => false];
                }

                $con = self::db();
                $con->begin_transaction();

                try {
                    $ownerRole = 'owner';
                    $adminRole = 'admin';
                    $stmtOldOwner = $con->prepare('UPDATE `site_group_members` SET `group_role` = ? WHERE `group_id` = ? AND `user_id` = ?');
                    $stmtOldOwner->bind_param('sii', $adminRole, $groupId, $actor->id);
                    $stmtOldOwner->execute();
                    $stmtOldOwner->close();

                    $stmtNewOwner = $con->prepare('UPDATE `site_group_members` SET `group_role` = ? WHERE `group_id` = ? AND `user_id` = ?');
                    $stmtNewOwner->bind_param('sii', $ownerRole, $groupId, $targetUserId);
                    $stmtNewOwner->execute();
                    $stmtNewOwner->close();

                    $stmtGroup = $con->prepare('UPDATE `site_groups` SET `group_owner_user_id` = ? WHERE `group_id` = ?');
                    $stmtGroup->bind_param('ii', $targetUserId, $groupId);
                    $stmtGroup->execute();
                    $stmtGroup->close();

                    $con->commit();
                    return ['error' => false];
                } catch (Throwable $e) {
                    $con->rollback();
                    return ['error' => true, 'reason' => 'Failed to transfer ownership.'];
                }
            }
        }

        if ($targetMembership['role'] === 'owner' && $newRole !== 'owner') {
            return ['error' => true, 'reason' => 'Transfer ownership instead of removing the owner role directly.'];
        }

        $con = self::db();
        $stmt = $con->prepare('UPDATE `site_group_members` SET `group_role` = ? WHERE `group_id` = ? AND `user_id` = ?');
        $stmt->bind_param('sii', $newRole, $groupId, $targetUserId);
        $stmt->execute();
        $stmt->close();

        return ['error' => false];
    }

    public static function RemoveMember(User $actor, int $groupId, int $targetUserId): array {
        self::Boot();
        $actorMembership = self::GetMembership($groupId, $actor->id);
        $targetMembership = self::GetMembership($groupId, $targetUserId);
        if ($actorMembership === null || $targetMembership === null) {
            return ['error' => true, 'reason' => 'Group membership not found.'];
        }

        if (!in_array($actorMembership['role'], ['owner', 'admin'], true)) {
            return ['error' => true, 'reason' => 'You cannot manage this group.'];
        }

        if ($targetMembership['role'] === 'owner') {
            return ['error' => true, 'reason' => 'Owner cannot be removed from the group.'];
        }

        if ($actorMembership['role'] === 'admin' && $targetMembership['role'] !== 'member') {
            return ['error' => true, 'reason' => 'Admins can only remove members.'];
        }

        $con = self::db();
        $stmt = $con->prepare('DELETE FROM `site_group_members` WHERE `group_id` = ? AND `user_id` = ?');
        $stmt->bind_param('ii', $groupId, $targetUserId);
        $stmt->execute();
        $stmt->close();

        return ['error' => false];
    }
}
?>



