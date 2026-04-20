<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/utilities/userutils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/classes/group.php';

$user = UserUtils::RetrieveUser();
if ($user === null) {
    die(header('Location: /login'));
}

Group::Boot();
$flash = $_SESSION['group_flash'] ?? null;
$form = $_SESSION['group_form'] ?? ['name' => '', 'description' => ''];
unset($_SESSION['group_flash'], $_SESSION['group_form']);
$balance = $user->GetZiu();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $page_title = 'Create Group - Zomium';
    $page_styles = ['/css/new/groups.css?v=1'];
    include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/head.php';
    ?>
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/header.php'; ?>
<main class="app-main groups-page py-4">
    <div class="container groups-shell">
        <section class="groups-hero">
            <div>
                <h1 class="groups-title">Create Group</h1>
                <p class="groups-subtitle">Write the name, upload the logo, set the description, and pay 100 ZIU.</p>
            </div>
            <div class="groups-statbar">
                <div class="groups-stat">
                    <span class="groups-stat-label">Create Cost</span>
                    <span class="groups-stat-value">100 ZIU</span>
                </div>
                <div class="groups-stat">
                    <span class="groups-stat-label">Your Balance</span>
                    <span class="groups-stat-value"><?= number_format($balance) ?> ZIU</span>
                </div>
            </div>
        </section>

        <?php if ($flash !== null): ?>
        <div class="alert <?= $flash['error'] ? 'alert-danger' : 'alert-success' ?> mb-0">
            <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <section class="group-create-layout">
            <article class="group-create-card">
                <h2 class="group-card-heading">New Group</h2>
                <form action="/api/groups" method="POST" enctype="multipart/form-data" class="group-form-stack">
                    <input type="hidden" name="action" value="create">
                    <div>
                        <label class="form-label" for="group_name">Group Name</label>
                        <input class="form-control" id="group_name" name="group_name" maxlength="64" required value="<?= htmlspecialchars($form['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label class="form-label" for="group_description">Description</label>
                        <textarea class="form-control" id="group_description" name="group_description" rows="7" maxlength="2000" required><?= htmlspecialchars($form['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div>
                        <label class="form-label" for="group_logo">Group Logo</label>
                        <input class="form-control" id="group_logo" name="group_logo" type="file" accept="image/*" required>
                        <div class="group-create-note mt-2">Accepted: JPG, PNG, GIF, WEBP. Max 4 MB.</div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-primary" type="submit">Create Group</button>
                        <a class="btn btn-theme" href="/my/groups.php">Back to Groups</a>
                    </div>
                </form>
            </article>

            <aside class="group-create-sidecopy">
                <div class="group-create-card">
                    <div class="group-create-logo-box">
                        <img class="group-create-logo-preview" src="/images/unavailable.png" alt="Group emblem preview">
                    </div>
                </div>
                <div class="group-create-bullet">
                    <strong>Roles</strong>
                    Owner, admin, and member are ready as soon as the group is created.
                </div>
                <div class="group-create-bullet">
                    <strong>Management</strong>
                    After creation you can add members, promote admins, change the emblem, and edit the description.
                </div>
                <div class="group-create-bullet">
                    <strong>Groups page</strong>
                    The search box on the groups page will search real groups by name and description.
                </div>
            </aside>
        </section>
    </div>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/footer.php'; ?>
</body>
</html>

