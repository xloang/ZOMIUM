<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/userutils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/classes/user.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/utilutils.php';

$user = UserUtils::RetrieveUser(); // LOL BUNU KOYMAYI UNUTMUŞTUM

/* if ($user == null || !$user->IsAdmin()) {
    http_response_code(404);
    require $_SERVER['DOCUMENT_ROOT'] . '/core/error/404.php';
    exit;
}
*/
include $_SERVER['DOCUMENT_ROOT'].'/core/connection.php';
$con->query("CREATE TABLE IF NOT EXISTS `user_warnings` (`id` INT NOT NULL AUTO_INCREMENT, `user_id` INT NOT NULL, `admin_id` INT NOT NULL, `reason` TEXT NOT NULL, `is_read` TINYINT(1) DEFAULT 0, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `user_id` (`user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
$con->query("CREATE TABLE IF NOT EXISTS `user_bans` (`id` INT NOT NULL AUTO_INCREMENT, `user_id` INT NOT NULL, `admin_id` INT NOT NULL, `reason` TEXT NOT NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uniq_user_ban` (`user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

if (!isset($_SESSION['admi_flash'])) {
    $_SESSION['admi_flash'] = null;
}

function admi_flash(bool $error, string $message): void {
    $_SESSION['admi_flash'] = ['error' => $error, 'message' => $message];
}

function admi_take_flash(): ?array {
    $flash = $_SESSION['admi_flash'] ?? null;
    $_SESSION['admi_flash'] = null;
    return $flash;
}

function admi_redirect(?int $userId = null, string $query = ''): void {
    $target = '/admi';
    $parts = [];
    if ($userId !== null) {
        $parts[] = 'user_id='.$userId;
    }
    if ($query !== '') {
        $parts[] = 'q='.urlencode($query);
    }
    if (count($parts) > 0) {
        $target .= '?'.implode('&', $parts);
    }
    header('Location: '.$target);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $targetUserId = isset($_POST['target_user_id']) ? intval($_POST['target_user_id']) : 0;
    $targetUser = $targetUserId > 0 ? User::FromID($targetUserId) : null;
    $action = $_POST['action'];
    $reason = trim($_POST['reason'] ?? '');

    if ($targetUser == null && $action !== 'search') {
        admi_flash(true, 'User not found.');
        admi_redirect(null, trim($_POST['q'] ?? ''));
    }

    if ($action === 'give_badge') {
        $badgeId = intval($_POST['badge_id'] ?? 0);
        try {
            $badge = ANORRLBadge::index($badgeId);
            $targetUser->GiveProfileBadge($badge);
            admi_flash(false, 'Badge assigned: ' . $badge->name);
        } catch (Throwable $e) {
            admi_flash(true, 'Failed to assign badge: ' . $e->getMessage());
        }
        admi_redirect($targetUser->id);
    }

    if ($action === 'remove_badge') {
        $badgeId = intval($_POST['badge_id'] ?? 0);
        try {
            $badge = ANORRLBadge::index($badgeId);
            $targetUser->RemoveProfileBadge($badge);
            admi_flash(false, 'Badge removed: ' . $badge->name);
        } catch (Throwable $e) {
            admi_flash(true, 'Failed to remove badge: ' . $e->getMessage());
        }
        admi_redirect($targetUser->id);
    }

    if ($action === 'promote_admin') {
        $targetUser->GiveProfileBadge(ANORRLBadge::ADMINISTRATOR);
        admi_flash(false, 'User promoted to Admin.');
        admi_redirect($targetUser->id);
    }

    if ($action === 'demote_admin') {
        if ($targetUser->id === $admin->id) {
            admi_flash(true, 'You cannot demote yourself.');
        } else {
            $targetUser->RemoveProfileBadge(ANORRLBadge::ADMINISTRATOR);
            admi_flash(false, 'User demoted from Admin.');
        }
        admi_redirect($targetUser->id);
    }

    if ($action === 'warn_user') {
        if ($reason === '') {
            admi_flash(true, 'Warning reason is required.');
            admi_redirect($targetUser->id);
        }
        $stmt = $con->prepare('INSERT INTO `user_warnings` (`user_id`, `admin_id`, `reason`, `is_read`) VALUES (?, ?, ?, 0)');
        $stmt->bind_param('iis', $targetUser->id, $admin->id, $reason);
        $stmt->execute();
        admi_flash(false, 'Warning added.');
        admi_redirect($targetUser->id);
    }

    if ($action === 'delete_warning') {
        $warningId = intval($_POST['warning_id'] ?? 0);
        $stmt = $con->prepare('DELETE FROM `user_warnings` WHERE `id` = ?');
        $stmt->bind_param('i', $warningId);
        $stmt->execute();
        admi_flash(false, 'Warning removed.');
        admi_redirect($targetUser->id);
    }

    if ($action === 'ban_user') {
        if ($reason === '') {
            admi_flash(true, 'Ban reason is required.');
            admi_redirect($targetUser->id);
        }
        $stmt = $con->prepare('INSERT INTO `user_bans` (`user_id`, `admin_id`, `reason`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `admin_id` = VALUES(`admin_id`), `reason` = VALUES(`reason`), `created_at` = CURRENT_TIMESTAMP');
        $stmt->bind_param('iis', $targetUser->id, $admin->id, $reason);
        $stmt->execute();
        admi_flash(false, 'User banned.');
        admi_redirect($targetUser->id);
    }

    if ($action === 'unban_user') {
        $stmt = $con->prepare('DELETE FROM `user_bans` WHERE `user_id` = ?');
        $stmt->bind_param('i', $targetUser->id);
        $stmt->execute();
        admi_flash(false, 'User unbanned.');
        admi_redirect($targetUser->id);
    }

    if ($action === 'give_ziu') {
        $ziuAmount = intval($_POST['ziu_amount'] ?? 0);
        if ($ziuAmount <= 0) {
            admi_flash(true, 'ZIU miktarı 0\'dan büyük olmalı.');
        } else {
            $targetUser->AddZiu($ziuAmount);
            $newBalance = $targetUser->GetZiu();
            admi_flash(false, htmlspecialchars($targetUser->name, ENT_QUOTES, 'UTF-8') . ' kullanıcısına ' . $ziuAmount . ' ZIU verildi. Yeni bakiye: ' . $newBalance . ' ZIU');
        }
        admi_redirect($targetUser->id);
    }

    if ($action === 'delete_user') {
        if ($targetUser->id === $admin->id) {
            admi_flash(true, 'You cannot delete your own admin account.');
            admi_redirect($targetUser->id);
        }

        $deletes = [
            'DELETE FROM `users` WHERE `user_id` = ?',
            'DELETE FROM `profilebadges` WHERE `badge_userid` = ?',
            'DELETE FROM `user_bans` WHERE `user_id` = ?',
            'DELETE FROM `user_warnings` WHERE `user_id` = ?',
            'DELETE FROM `statuses` WHERE `status_poster` = ?',
            'DELETE FROM `comments` WHERE `comment_poster` = ?',
            'DELETE FROM `activity` WHERE `userid` = ?',
            'DELETE FROM `forum_threads` WHERE `thread_poster` = ?',
            'DELETE FROM `forum_replies` WHERE `reply_poster` = ?',
            'DELETE FROM `gallery_items` WHERE `item_poster` = ?'
        ];
        foreach ($deletes as $sql) {
            $stmt = $con->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('i', $targetUser->id);
                $stmt->execute();
            }
        }
        $profilePath = $_SERVER['DOCUMENT_ROOT'].'/../users/profile_'.$targetUser->id.'.png';
        if (file_exists($profilePath)) {
            @unlink($profilePath);
        }
        admi_flash(false, 'User account deleted.');
        admi_redirect();
    }
}

$q = trim($_GET['q'] ?? '');
$selectedUser = null;
if (isset($_GET['user_id'])) {
    $selectedUser = User::FromID(intval($_GET['user_id']));
}
if ($selectedUser == null && $q !== '') {
    if (ctype_digit($q)) {
        $selectedUser = User::FromID(intval($q));
    }
    if ($selectedUser == null) {
        $selectedUser = User::FromName($q);
    }
}

$recentUsers = [];
$stmt = $con->prepare('SELECT `user_id` FROM `users` ORDER BY `user_joindate` DESC LIMIT 20');
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $u = User::FromID(intval($row['user_id']));
    if ($u != null) {
        $recentUsers[] = $u;
    }
}

$userRow = null;
$warnings = [];
$banInfo = null;
if ($selectedUser != null) {
    $stmt = $con->prepare('SELECT * FROM `users` WHERE `user_id` = ? LIMIT 1');
    $stmt->bind_param('i', $selectedUser->id);
    $stmt->execute();
    $userRow = $stmt->get_result()->fetch_assoc();

    $stmt = $con->prepare('SELECT * FROM `user_warnings` WHERE `user_id` = ? ORDER BY `created_at` DESC LIMIT 10');
    $stmt->bind_param('i', $selectedUser->id);
    $stmt->execute();
    $warningResult = $stmt->get_result();
    while ($row = $warningResult->fetch_assoc()) {
        $warnings[] = $row;
    }

    $stmt = $con->prepare('SELECT * FROM `user_bans` WHERE `user_id` = ? LIMIT 1');
    $stmt->bind_param('i', $selectedUser->id);
    $stmt->execute();
    $banInfo = $stmt->get_result()->fetch_assoc();
}

$flash = admi_take_flash();
?>
<!DOCTYPE html>
<html>
<head>
    <?php $page_title = 'Admin Panel - Zomium'; include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php'; ?>
    <style>
        .admin-section-info {
            background-color: #212529;
            background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('/s/img/xmas_small.jpg');
            background-size: cover;
            background-position: center;
            color: #fff;
            border-bottom: 1px solid #343a40;
        }
        .section-title {
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-size: 0.75rem;
            font-weight: 700;
            color: rgba(255,255,255,0.7);
        }
        .finobe-basic-nav .header {
            font-size: .85rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #949494;
            margin-bottom: .5rem;
            padding-left: 1rem;
        }
        .finobe-basic-nav .link {
            display: block;
            padding: .5rem 1rem;
            color: #212529;
            text-decoration: none;
            border-radius: .25rem;
            transition: all .1s linear;
        }

        .finobe-basic-nav .link.active {
            background-color: #0270a6;
            color: #fff !important;
        }
        .admin-sidebar-card {
            position: sticky;
            top: 6.25rem;
            background: #17181c;
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 14px;
            box-shadow: 0 14px 34px rgba(0,0,0,.22);
            overflow: hidden;
        }
        .admin-sidebar-title {
            padding: 1rem 1rem .85rem;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: rgba(255,255,255,.66);
            border-bottom: 1px solid rgba(255,255,255,.06);
            background: #141519;
        }
        .admin-tab-list {
            padding: .75rem;
            display: grid;
            gap: .45rem;
        }
        .admin-tab-link {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: .8rem .9rem;
            border-radius: 10px;
            color: #d9dee6;
            text-decoration: none;
            background: rgba(255,255,255,.02);
            border: 1px solid rgba(255,255,255,.05);
        }
        .admin-tab-link:hover {
            color: #fff;
            background: rgba(90, 143, 224, .18);
            border-color: rgba(123, 169, 239, .35);
            text-decoration: none;
        }
        .admin-tab-link.disabled {
            opacity: .45;
            pointer-events: none;
        }
        .admin-quick-card {
            background: #17181c;
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 14px;
            box-shadow: 0 14px 34px rgba(0,0,0,.18);
        }
        .admin-quick-list a {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            padding: .75rem .9rem;
            border-radius: 10px;
            color: #dce2ea;
            text-decoration: none;
            background: rgba(255,255,255,.02);
            border: 1px solid rgba(255,255,255,.04);
        }
        .admin-quick-list a:hover {
            color: #fff;
            border-color: rgba(123,169,239,.32);
            background: rgba(90,143,224,.14);
            text-decoration: none;
        }
        .admin-card {
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-radius: 12px;
            transition: transform 0.2s;
        }

        .user-header-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .badge-list span {
            font-size: 0.75rem;
            padding: 5px 10px;
        }
    </style>
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/header.php'; ?>
<main class="app-main">
    <div class="admin-section-info mb-4 text-center py-5 shadow-sm">
        <div class="container">
            <h1 class="display-5 fw-bold mb-3">Admin Panel [W.I.P] </h1>
        </div>
    </div>

    <div class="container">
        <?php if ($flash !== null): ?>
        <div class="alert <?= $flash['error'] ? 'alert-danger' : 'alert-success' ?> mb-4 shadow-sm border-0">
            <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <div class="row g-4 mb-5">
            <div class="col-lg-3">
                

               <!-- tessttttt -->

                <div class="card admin-quick-card border-0 mb-4" id="search-panel">
                    <div class="card-body p-3">
                        <div class="section-title mb-3">Find User</div>
                        <form method="GET" class="d-grid gap-2">
                            <input class="form-control form-control-lg bg-dark text-light border-secondary" type="text" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="username or id">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-search me-2"></i>Search</button>
                        </form>
                    </div>
                </div>

                <div class="card admin-quick-card border-0" id="recent-users">
                    <div class="card-body p-3">
                        <div class="section-title mb-3">New Players</div>
                        <div class="admin-quick-list d-grid gap-2">
                            <?php foreach ($recentUsers as $recentUser): ?>
                                <a href="/admi?user_id=<?= $recentUser->id ?>">
                                    <span class="text-truncate"><?= htmlspecialchars($recentUser->name, ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="small text-secondary">#<?= $recentUser->id ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <?php if ($selectedUser == null): ?>
                <div class="card admin-card border-0 py-5 text-center">
                    <div class="card-body text-secondary">
                        <i class="fas fa-user-shield display-4 mb-3 opacity-25"></i>
                        <p class="lead mb-0">
    Welcome to panel, <?= htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') ?>
</p>

<div style="text-align: center; margin-top: 15px;">
    <a href="/admi/itemcreate.php" class="btn btn-primary">
        Create Item
    </a>

    <a href="admi/gameservers.php "class="btn btn-primary">
        Gameservers
    </a>

</div>

                    </div>
                </div>
                <?php else: ?>
                
                <!-- User Profile Summary -->
                <div class="card admin-card border-0 mb-4 overflow-hidden" id="profile-summary">
                    <div class="card-body p-4">
                        <div class="d-flex gap-4 align-items-center">
                            <img src="/thumbs/profile?id=<?= $selectedUser->id ?>&sxy=120&nocompress" alt="<?= htmlspecialchars($selectedUser->name, ENT_QUOTES, 'UTF-8') ?>" class="user-header-img">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h2 class="h3 fw-bold mb-1"><?= htmlspecialchars($selectedUser->name, ENT_QUOTES, 'UTF-8') ?></h2>
                                        <div class="small text-secondary mb-3">
                                            <span class="badge bg-light text-dark border me-2">ID: <?= $selectedUser->id ?></span>
                                            Joined <?= $selectedUser->join_date?->format('F j, Y') ?>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <?php if ($selectedUser->IsBanned()): ?>
                                            <span class="badge bg-danger px-3 py-2">BANNED</span>
                                        <?php else: ?>
                                            <span class="badge bg-success px-3 py-2">ACTIVE</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="row g-3 small bg-light p-3 rounded-3">
                                    <div class="col-md-4">
                                        <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.65rem;">Discord/Invite</div>
                                        <div class="text-dark"><?= htmlspecialchars((string) ($userRow['user_discord'] ?? 'None'), ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.65rem;">Security Token</div>
                                        <code class="text-primary"><?= htmlspecialchars($selectedUser->security_key, ENT_QUOTES, 'UTF-8') ?></code>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-center justify-content-end">
                                        <form method="POST">
                                            <input type="hidden" name="target_user_id" value="<?= $selectedUser->id ?>">
                                            <?php if ($selectedUser->IsAdmin()): ?>
                                            <input type="hidden" name="action" value="demote_admin">
                                            <button class="btn btn-sm btn-outline-danger fw-bold" type="submit">Demote from Admin</button>
                                            <?php else: ?>
                                            <input type="hidden" name="action" value="promote_admin">
                                            <button class="btn btn-sm btn-outline-primary fw-bold" type="submit">Promote to Admin</button>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Grid -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card admin-card border-0 h-100" id="badge-tools">
                            <div class="card-body p-4">
                                <h3 class="h5 fw-bold mb-3 d-flex align-items-center"><i class="fas fa-award text-primary me-2"></i> Badges</h3>
                                <form method="POST" class="d-grid gap-3">
                                    <input type="hidden" name="target_user_id" value="<?= $selectedUser->id ?>">
                                    <input type="hidden" name="action" value="give_badge">
                                    <select class="form-select border-0 bg-light" name="badge_id">
                                        <?php foreach (ANORRLBadge::cases() as $badge): ?>
                                        <option value="<?= $badge->ordinal() ?>"><?= htmlspecialchars($badge->name, ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-primary fw-bold" type="submit">Grant Badge</button>
                                </form>
                                <div class="mt-4 badge-list d-flex flex-wrap gap-2">
                                    <?php foreach ($selectedUser->GetProfileBadges() as $badge): ?>
                                    <div class="badge-item d-inline-flex align-items-center gap-1">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= htmlspecialchars($badge->name, ENT_QUOTES, 'UTF-8') ?></span>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Remove this badge?');">
                                            <input type="hidden" name="target_user_id" value="<?= $selectedUser->id ?>">
                                            <input type="hidden" name="action" value="remove_badge">
                                            <input type="hidden" name="badge_id" value="<?= $badge->id->ordinal() ?>">
                                            <button type="submit" class="btn btn-sm btn-link p-0 text-danger text-decoration-none" title="Remove Badge"><i class="fas fa-times-circle"></i></button>
                                        </form>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php if (count($selectedUser->GetProfileBadges()) === 0): ?>
                                    <span class="text-muted small italic">No badges assigned.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card admin-card border-0 h-100" id="warning-tools">
                            <div class="card-body p-4">
                                <h3 class="h5 fw-bold mb-3 d-flex align-items-center"><i class="fas fa-exclamation-triangle text-warning me-2"></i> Warning</h3>
                                <form method="POST" class="d-grid gap-3">
                                    <input type="hidden" name="target_user_id" value="<?= $selectedUser->id ?>">
                                    <input type="hidden" name="action" value="warn_user">
                                    <textarea class="form-control border-0 bg-light" name="reason" rows="3" placeholder="Describe the violation..."></textarea>
                                    <button class="btn btn-warning fw-bold" type="submit">Issue Warning</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ZIU Management -->
                <div class="row g-4 mb-4" id="ziu-tools">
                    <div class="col-md-12">
                        <div class="card admin-card border-0 h-100">
                            <div class="card-body p-4">
                                <h3 class="h5 fw-bold mb-3 d-flex align-items-center">
                                    <i class="fas fa-coins me-2" style="color:#f5c518;"></i> Give currency
                                </h3>
                                <div class="mb-3 p-3 rounded-3 bg-light d-flex align-items-center gap-3">
                                    <img src="/images/ziu_16.png" style="width:20px;height:20px;">
                                    <div>
                                        <div class="small text-muted fw-bold text-uppercase" style="font-size:0.65rem;">Mevcut Bakiye</div>
                                        <div class="fw-bold fs-5"><?= number_format($selectedUser->GetZiu()) ?> ZIU</div>
                                    </div>
                                </div>
                                <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="target_user_id" value="<?= $selectedUser->id ?>">
                                    <input type="hidden" name="action" value="give_ziu">
                                    <input type="number" class="form-control border-0 bg-light" name="ziu_amount"
                                           placeholder="amount" min="-99999999999999999999" max="99999999999999999999" required>
                                    <button class="btn fw-bold px-4" type="submit"
                                            style="background:#f5c518;color:#1a1a1a;white-space:nowrap;">
                                        <i class="fas fa-plus me-1"></i> Give Currency
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card admin-card border-0 h-100" id="ban-tools">
                            <div class="card-body p-4">
                                <h3 class="h5 fw-bold mb-3 d-flex align-items-center"><i class="fas fa-gavel text-danger me-2"></i> Ban Control</h3>
                                <form method="POST" class="d-grid gap-3 mb-3">
                                    <input type="hidden" name="target_user_id" value="<?= $selectedUser->id ?>">
                                    <input type="hidden" name="action" value="ban_user">
                                    <textarea class="form-control border-0 bg-light" name="reason" rows="3" placeholder="Reason for banishment..."></textarea>
                                    <button class="btn btn-danger fw-bold" type="submit">Execute Ban</button>
                                </form>
                                <?php if ($selectedUser->IsBanned()): ?>
                                <form method="POST">
                                    <input type="hidden" name="target_user_id" value="<?= $selectedUser->id ?>">
                                    <input type="hidden" name="action" value="unban_user">
                                    <button class="btn btn-outline-dark w-100 fw-bold border-2" type="submit">Lift Ban</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card admin-card border-danger h-100">
                            <div class="card-body p-4">
                                <h3 class="h5 fw-bold mb-2 text-danger"><i class="fas fa-trash-alt me-2"></i> Danger Zone</h3>
                                <p class="small text-muted mb-4">Deleting an account is permanent and removes all associated data. Use extreme caution.</p>
                                <form method="POST" onsubmit="return confirm('ARE YOU SURE? This cannot be undone.');">
                                    <input type="hidden" name="target_user_id" value="<?= $selectedUser->id ?>">
                                    <input type="hidden" name="action" value="delete_user">
                                    <button class="btn btn-outline-danger w-100 fw-bold border-2" type="submit">Delete User Permanently</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Logs -->
                <div class="row g-4">
                    <div class="col-md-12">
                        <div class="card admin-card border-0" id="history-tools">
                            <div class="card-body p-4">
                                <h3 class="h5 fw-bold mb-4">Moderation History</h3>
                                <div class="row">
                                    <div class="col-md-6 border-end">
                                        <p class="small fw-bold text-muted text-uppercase mb-3">Warnings</p>
                                        <?php if (count($warnings) === 0): ?>
                                            <div class="text-muted small">No warnings in history.</div>
                                        <?php else: ?>
                                            <?php foreach ($warnings as $warning): $warningAdmin = User::FromID(intval($warning['admin_id'])); ?>
                                            <div class="mb-3 p-3 bg-light rounded-3 shadow-sm border-start border-warning border-4">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span class="fw-bold small"><?= htmlspecialchars($warningAdmin?->name ?? ('#'.$warning['admin_id']), ENT_QUOTES, 'UTF-8') ?></span>
                                                    <span class="text-muted smaller"><?= date('M j, Y H:i', strtotime($warning['created_at'])) ?></span>
                                                </div>
                                                <div class="small mb-2"><?= htmlspecialchars($warning['reason'], ENT_QUOTES, 'UTF-8') ?></div>
                                                <form method="POST" onsubmit="return confirm('Delete this warning?');">
                                                    <input type="hidden" name="target_user_id" value="<?= $selectedUser->id ?>">
                                                    <input type="hidden" name="action" value="delete_warning">
                                                    <input type="hidden" name="warning_id" value="<?= $warning['id'] ?>">
                                                    <button type="submit" class="btn btn-link p-0 text-danger text-decoration-none smaller fw-bold" style="font-size: 0.7rem;">Remove Warning</button>
                                                </form>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 ps-4">
                                        <p class="small fw-bold text-muted text-uppercase mb-3">Active Ban</p>
                                        <?php if ($banInfo == null): ?>
                                            <div class="text-muted small">User is not currently banned.</div>
                                        <?php else: $banAdmin = User::FromID(intval($banInfo['admin_id'])); ?>
                                            <div class="p-4 bg-danger-subtle text-danger rounded-3 shadow-sm border border-danger-subtle">
                                                <div class="fw-bold mb-2">Banned by <?= htmlspecialchars($banAdmin?->name ?? ('#'.$banInfo['admin_id']), ENT_QUOTES, 'UTF-8') ?></div>
                                                <div class="small mb-3"><?= htmlspecialchars($banInfo['reason'], ENT_QUOTES, 'UTF-8') ?></div>
                                                <div class="smaller opacity-75">Banned on <?= date('F j, Y, g:i a', strtotime($banInfo['created_at'])) ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/footer.php'; ?>
</body>
</html>
