<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/userutils.php';
$user = UserUtils::RetrieveUser();

if($user == null) {
    die(header('Location: /login'));
}

include $_SERVER['DOCUMENT_ROOT'].'/core/connection.php';

$stmt = $con->prepare('SELECT * FROM `friends` WHERE (`sender` = ? OR `reciever` = ?) ORDER BY `status` ASC');
$stmt->bind_param('ii', $user->id, $user->id);
$stmt->execute();
$result_stmt = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php
        $page_title = 'Friends - Zomium';
        $page_scripts = ['/js/friends.js?t=1771413807'];
        include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php';
        ?>
    </head>
    <body>
        <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/header.php'; ?>
        <main class="app-main">
            <div class="container">
<!-- soon -->
                <div id="FriendsContainer" class="row g-4">
                    <?php if($result_stmt->num_rows != 0): ?>
                        <?php while($row = $result_stmt->fetch_assoc()): ?>
                            <?php $friendo = $row['reciever'] == $user->id ? User::FromID($row['sender']) : User::FromID($row['reciever']); ?>
                            <?php if($friendo == null) { continue; } ?>
                            <?php $fid = $friendo->id; ?>
                            <?php $profile = UserSettings::Get($user)->headshots_enabled ? 'headshot' : ($friendo->setprofilepicture ? 'profile' : 'headshot'); ?>
                            <?php $status = $friendo->IsOnline() ? 'Online' : 'Offline'; ?>
                            <div class="col-md-6 col-xl-4 friend-tile">
                                <div class="card border-0 h-100">
                                    <div class="card-body p-4 text-center">
                                        <a href="/users/<?= $fid ?>/profile" target="_blank">
                                            <img class="img-fluid mb-3" src="/thumbs/<?= $profile ?>?id=<?= $fid ?>&sxy=100" alt="<?= htmlspecialchars($friendo->name, ENT_QUOTES, 'UTF-8') ?>">
                                        </a>
                                        <h2 class="h5 mb-2"><?= htmlspecialchars($friendo->name, ENT_QUOTES, 'UTF-8') ?></h2>
                                        <div class="small mb-3 <?= $status === 'Online' ? 'status-online' : 'status-offline' ?>"><?= $status ?></div>
                                        <?php if($row['status'] == 1): ?>
                                        <a class="btn btn-outline-danger btn-sm" href="javascript:ANORRL.Friends.Remove(<?= $fid ?>)">Remove</a>
                                        <?php elseif($row['reciever'] == $user->id): ?>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a class="btn btn-primary btn-sm" href="javascript:ANORRL.Friends.Accept(<?= $fid ?>)">Accept</a>
                                            <a class="btn btn-outline-light btn-sm" href="javascript:ANORRL.Friends.Reject(<?= $fid ?>)">Reject</a>
                                        </div>
                                        <?php else: ?>
                                        <a class="btn btn-outline-light btn-sm" href="javascript:ANORRL.Friends.Cancel(<?= $fid ?>)">Cancel Request</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                    <div class="col-12"><div class="empty-state">No friends or pending requests yet.</div></div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
        <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/footer.php'; ?>
    </body>
</html>
