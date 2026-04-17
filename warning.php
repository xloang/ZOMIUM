<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/userutils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/classes/user.php';

$user = UserUtils::RetrieveUser();
if ($user == null) {
    header("Location: /login");
    exit;
}

include $_SERVER['DOCUMENT_ROOT'].'/core/connection.php';

// Check if user is trying to acknowledge
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acknowledge'])) {
    $stmt = $con->prepare("UPDATE `user_warnings` SET `is_read` = 1 WHERE `user_id` = ?");
    $stmt->bind_param("i", $user->id);
    $stmt->execute();
    header("Location: /my/home");
    exit;
}

// Get unread warning
$stmt = $con->prepare("SELECT * FROM `user_warnings` WHERE `user_id` = ? AND `is_read` = 0 ORDER BY `created_at` DESC LIMIT 1");
$stmt->bind_param("i", $user->id);
$stmt->execute();
$warning = $stmt->get_result()->fetch_assoc();

if ($warning == null) {
    header("Location: /my/home");
    exit;
}

$admin = User::FromID(intval($warning['admin_id']));
?>
<!DOCTYPE html>
<html>
<head>
    <?php $page_title = 'Warning - Zomium'; include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php'; ?>
    <style>
        body { background-color: #000; }
        .warning-container {
            max-width: 600px;
            margin: 100px auto;
            border-top: 5px solid #ffc107;
        }
        .warning-bg {
            background: linear-gradient(180deg, rgba(255, 193, 7, 0.1) 0%, rgba(0, 0, 0, 0) 100%);
        }
    </style>
</head>
<body class="warning-bg text-white">
    <div class="container py-5">
        <div class="card warning-container bg-dark text-white border-0 shadow-lg">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="fas fa-exclamation-triangle text-warning display-1 mb-3"></i>
                    <h1 class="fw-bold h2">Warning</h1>
                    <p class="text-muted">You are warned by our staff for some reason. (dont do that again)</p>
                </div>
                
                <div class="bg-black bg-opacity-50 p-4 rounded-3 border border-secondary border-opacity-25 mb-4">
                    <div class="mb-3">
                        <label class="text-uppercase small fw-bold text-muted d-block mb-1">Reason</label>
                        <p class="mb-0 fs-5"><?= htmlspecialchars($warning['reason'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="row pt-3 border-top border-secondary border-opacity-10">
                        <div class="col-6">
                            <label class="text-uppercase small fw-bold text-muted d-block mb-1">Issued By</label>
                            <span class="text-info"><?= htmlspecialchars($admin?->name ?? 'System', ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="col-6">
                            <label class="text-uppercase small fw-bold text-muted d-block mb-1">Date</label>
                            <span><?= date('F j, Y, g:i a', strtotime($warning['created_at'])) ?></span>
                        </div>
                    </div>
                </div>

                <div class="text-secondary small mb-4">
                    Please ensure you follow our <a href="/terms" class="text-warning">Terms of Service</a> to avoid further moderation actions, including account suspension.
                </div>

                <form method="POST">
                    <button type="submit" name="acknowledge" class="btn btn-warning w-100 py-3 fw-bold text-uppercase">I understand and i wont do it again.</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
