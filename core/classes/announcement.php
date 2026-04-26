<?php

class Announcement {

    public int    $id;
    public string $message;
    public string $color;
    public ?DateTime $expires_at;
    public DateTime  $created_at;
    public bool   $is_active;

    function __construct(array $row) {
        $this->id         = (int)$row['id'];
        $this->message    = htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8');
        $this->color      = $row['color'];
        $this->expires_at = !empty($row['expires_at'])
            ? DateTime::createFromFormat('Y-m-d H:i:s', $row['expires_at'])
            : null;
        $this->created_at = DateTime::createFromFormat('Y-m-d H:i:s', $row['created_at']);
        $this->is_active  = (bool)$row['is_active'];
    }

    /** Check whether this announcement should still be displayed */
    public function IsVisible(): bool {
        if (!$this->is_active) return false;
        if ($this->expires_at === null) return true;
        return $this->expires_at > new DateTime();
    }

    // ─────────────────────────────────────────
    // Static helpers
    // ─────────────────────────────────────────

    /** Get all active (non-expired) announcements — shown site-wide */
    public static function GetActive(): array {
        include $_SERVER['DOCUMENT_ROOT'] . '/core/connection.php';
        $stmt = $con->prepare(
            "SELECT * FROM `announcements`
             WHERE `is_active` = 1
               AND (`expires_at` IS NULL OR `expires_at` > NOW())
             ORDER BY `created_at` DESC"
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $out = [];
        while ($row = $result->fetch_assoc()) {
            $out[] = new self($row);
        }
        return $out;
    }

    /** Get ALL announcements (for admin panel) */
    public static function GetAll(): array {
        include $_SERVER['DOCUMENT_ROOT'] . '/core/connection.php';
        $stmt = $con->prepare(
            "SELECT * FROM `announcements` ORDER BY `created_at` DESC"
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $out = [];
        while ($row = $result->fetch_assoc()) {
            $out[] = new self($row);
        }
        return $out;
    }

    /** Create a new announcement */
    public static function Create(string $message, string $color, ?string $expires_at): bool {
        $allowed_colors = ['success', 'primary', 'danger', 'info', 'warning'];
        if (!in_array($color, $allowed_colors)) $color = 'info';
        if (strlen(trim($message)) < 3) return false;

        include $_SERVER['DOCUMENT_ROOT'] . '/core/connection.php';
        $stmt = $con->prepare(
            "INSERT INTO `announcements` (`message`, `color`, `expires_at`) VALUES (?, ?, ?)"
        );
        $stmt->bind_param('sss', $message, $color, $expires_at);
        return $stmt->execute();
    }

    /** Toggle active/inactive */
    public static function SetActive(int $id, bool $active): bool {
        include $_SERVER['DOCUMENT_ROOT'] . '/core/connection.php';
        $val  = $active ? 1 : 0;
        $stmt = $con->prepare("UPDATE `announcements` SET `is_active` = ? WHERE `id` = ?");
        $stmt->bind_param('ii', $val, $id);
        return $stmt->execute();
    }

    /** Hard-delete an announcement */
    public static function Delete(int $id): bool {
        include $_SERVER['DOCUMENT_ROOT'] . '/core/connection.php';
        $stmt = $con->prepare("DELETE FROM `announcements` WHERE `id` = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
