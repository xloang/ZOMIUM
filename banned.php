<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/userutils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/classes/user.php';

$user = UserUtils::RetrieveUser();
if ($user == null) {
    header("Location: /login");
    exit;
}

if (!$user->IsBanned()) {
    header("Location: /my/home");
    exit;
}

include $_SERVER['DOCUMENT_ROOT'].'/core/connection.php';
$stmt = $con->prepare("SELECT * FROM `user_bans` WHERE `user_id` = ? LIMIT 1");
$stmt->bind_param("i", $user->id);
$stmt->execute();
$ban = $stmt->get_result()->fetch_assoc();

$admin = User::FromID(intval($ban['admin_id']));
?>
<!DOCTYPE html>
<html>
<head>
    <?php $page_title = 'Suspended - Zomium'; include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php'; ?>
    <style>
        body { background-color: #000; }
        .ban-container {
            max-width: 600px;
            margin: 100px auto;
            border-top: 5px solid #dc3545;
        }
    </style>
</head>
<body class="text-white">
    <div class="container py-5">
        <div class="card ban-container bg-dark text-white border-0 shadow-lg">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="fas fa-gavel text-danger display-1 mb-3"></i>
                    <h1 class="fw-bold h2">Account Banned</h1>
                    <p class="text-muted">You are banned by our staff because of the thing what you did</p>
                </div>
                
                <div class="bg-black bg-opacity-50 p-4 rounded-3 border border-secondary border-opacity-25 mb-4">
                    <div class="mb-3">
                        <label class="text-uppercase small fw-bold text-muted d-block mb-1">Reason for Banishment</label>
                        <p class="mb-0 fs-5"><?= htmlspecialchars($ban['reason'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="row pt-3 border-top border-secondary border-opacity-10">
                        <div class="col-6">
                            <label class="text-uppercase small fw-bold text-muted d-block mb-1">Moderator</label>
                            <span class="text-danger fw-bold"><?= htmlspecialchars($admin?->name ?? 'System', ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="col-6">
                            <label class="text-uppercase small fw-bold text-muted d-block mb-1">Date</label>
                            <span><?= date('F j, Y, g:i a', strtotime($ban['created_at'])) ?></span>
                        </div>
                    </div>
                </div>

                <div class="text-secondary small mb-4">
                    If you believe this action was taken in error, create a ticket on our discord server.
                </div>

                <a href="/logout" class="btn btn-outline-light w-100 py-3 fw-bold text-uppercase">Log Out</a>
            </div>
        </div>
    </div>
</body>
</html>
