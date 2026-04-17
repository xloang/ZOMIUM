<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/utilities/userutils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/classes/group.php';

function group_role_class(string $role): string {
    $role = strtolower(trim($role));
    if (!in_array($role, ['owner', 'admin', 'member'], true)) {
        $role = 'member';
    }

    return 'role-pill ' . $role;
}

function group_allowed_roles(?string $actorRole, string $targetRole, int $actorId, int $targetId): array {
    if ($actorRole === 'owner') {
        if ($targetId === $actorId || $targetRole === 'owner') {
            return ['owner'];
        }

        return ['member', 'admin', 'owner'];
    }

    if ($actorRole === 'admin') {
        if ($targetRole === 'member') {
            return ['member', 'admin'];
        }

        return [$targetRole];
    }

    return [$targetRole];
}

function group_can_remove(?string $actorRole, string $targetRole): bool {
    if ($actorRole === 'owner') {
        return $targetRole !== 'owner';
    }

    if ($actorRole === 'admin') {
        return $targetRole === 'member';
    }

    return false;
}

$user = UserUtils::RetrieveUser();
if ($user === null) {
    die(header('Location: /login'));
}

Group::Boot();
$query = trim($_GET['query'] ?? '');
$selectedGroupId = max(0, (int) ($_GET['group'] ?? 0));
$flash = $_SESSION['group_flash'] ?? null;
unset($_SESSION['group_flash']);

$userGroups = Group::GetGroupsForUser($user->id, 100);
$searchResults = Group::SearchGroups($query, $user->id, 36);
$selectedGroup = $selectedGroupId > 0 ? Group::GetById($selectedGroupId, $user->id) : null;
$selectedMembership = $selectedGroup !== null ? Group::GetMembership($selectedGroup->id, $user->id) : null;
$selectedRole = $selectedMembership['role'] ?? null;
$canManage = $selectedGroup !== null && $selectedRole !== null && in_array($selectedRole, ['owner', 'admin'], true);
$selectedMembers = $selectedGroup !== null ? Group::GetMembers($selectedGroup->id) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $page_title = 'Groups - Zomium';
    $page_styles = ['/css/new/groups.css?v=1'];
    include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/head.php';
    ?>
</head>
<body>

<style>
    .filter-title {
            color: #f1f3f6;
            font-size: 1rem;
            font-weight: 700;
            text-transform: lowercase;
            padding: 1rem 1.1rem;
            border-bottom: 1px solid rgba(255,255,255,.06);
            background: #17171a;
            text-align: left;
        }

        .filter-list {
            list-style: none;
            padding: 1rem;
            margin: 0;
            background: #18181b;
        }

        .filter-list li {
            display: block;
            width: 100%;
            padding: .78rem .95rem;
            margin-bottom: .45rem;
            border-radius: 0;
            background: linear-gradient(180deg, #161617 0%, #161617 100%);
            color: #f3f5f8;
            cursor: pointer;
            transition: background-color .15s ease, border-color .15s ease, color .15s ease;
            text-transform: lowercase;
            text-align: center;
            
        }

        .filter-list li.active,
        .filter-list li[selected] {
            background: linear-gradient(180deg, #4d8fe8 0%, #4d8fe8 100%);
            
        }

        .filter-list li:hover {
            color: #fff;
            background: linear-gradient(180deg, #4d8fe8 0%, #4d8fe8 100%);
            border-color: rgba(255,255,255,.08);
            text-decoration: none;
        }


</style>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/header.php'; ?>
<main class="app-main groups-page py-4">
    <div class="container groups-shell">

        <?php if ($flash !== null): ?>
        <div class="alert <?= $flash['error'] ? 'alert-danger' : 'alert-success' ?> mb-0">
            <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <h1>Groups</h1>

        <form class="groups-search groups-searchbar" method="GET" action="/my/groups.php">
            <input class="form-control form-control-lg" type="text" name="query" value="<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search groups by name or description">
            <button class="btn btn-primary btn-lg" type="submit">Search</button>
            <a class="btn btn-theme btn-lg" href="/my/groups.php">Reset</a>
        </form>

        <section class="groups-layout">
            <aside class="places-panel mb-4 groups-panel">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                    <div class="filter-title">my groups</div>
                    <a class="download-btn" href="/my/group/create.php">Create group</a>
                </div>

                <?php if (count($userGroups) === 0): ?>
                <div class="group-empty">You are not in any groups yet.</div>
                <?php else: ?>
                <ul class="filter-list mb-0">
                    <?php foreach ($userGroups as $group): ?>
                    <li
                        onclick="window.location.href='/my/groups.php?group=<?= $group->id ?><?= $query !== '' ? '&query=' . urlencode($query) : '' ?>'"
                        class="<?= $selectedGroup !== null && $selectedGroup->id === $group->id ? 'selected active' : '' ?>"
                    >
                        <?= htmlspecialchars($group->name, ENT_QUOTES, 'UTF-8') ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </aside>

            <div class="group-detail-grid">
                <?php if ($selectedGroup !== null): ?>
                <article class="group-detail-hero">
                    <div class="group-detail-logo-wrap">
                        <img class="group-detail-logo" src="<?= htmlspecialchars($selectedGroup->GetLogoUrl(), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($selectedGroup->name, ENT_QUOTES, 'UTF-8') ?> logo">
                    </div>
                    <div>
                        <div class="group-meta-row mb-2">
                            <h2 class="group-detail-name"><?= htmlspecialchars($selectedGroup->name, ENT_QUOTES, 'UTF-8') ?></h2>
                            <?php if ($selectedRole !== null): ?>
                            <span class="<?= group_role_class($selectedRole) ?>"><?= htmlspecialchars($selectedRole, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="group-meta-row">
                            <span class="text-secondary">Owner: <?= htmlspecialchars($selectedGroup->owner !== null ? $selectedGroup->owner->name : 'Unknown', ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="text-secondary"><?= $selectedGroup->memberCount ?> members</span>
                            <span class="text-secondary">Created <?= htmlspecialchars($selectedGroup->createdAt->format('Y-m-d'), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <p class="group-detail-description"><?= nl2br(htmlspecialchars($selectedGroup->description, ENT_QUOTES, 'UTF-8')) ?></p>
                    </div>
                </article>

                <?php if ($canManage): ?>
                <section class="group-manage-grid">
                    <article class="group-manage-card">
                        <h3 class="group-card-heading">Edit Group</h3>
                        <form class="group-form-stack" action="/api/groups" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update-group">
                            <input type="hidden" name="group_id" value="<?= $selectedGroup->id ?>">
                            <div>
                                <label class="form-label" for="edit_group_name">Group Name</label>
                                <input class="form-control" id="edit_group_name" name="group_name" maxlength="64" required value="<?= htmlspecialchars($selectedGroup->name, ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div>
                                <label class="form-label" for="edit_group_description">Description</label>
                                <textarea class="form-control" id="edit_group_description" name="group_description" rows="6" maxlength="2000" required><?= htmlspecialchars($selectedGroup->description, ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                            <div>
                                <label class="form-label" for="edit_group_logo">Replace Logo</label>
                                <input class="form-control" id="edit_group_logo" name="group_logo" type="file" accept="image/*">
                            </div>
                            <button class="btn btn-primary" type="submit">Save Changes</button>
                        </form>
                    </article>

                    <article class="group-manage-card">
                        <h3 class="group-card-heading">Add Member</h3>
                        <form class="group-form-stack" action="/api/groups" method="POST">
                            <input type="hidden" name="action" value="add-member">
                            <input type="hidden" name="group_id" value="<?= $selectedGroup->id ?>">
                            <div>
                                <label class="form-label" for="group_member_username">Username</label>
                                <input class="form-control" id="group_member_username" name="username" maxlength="20" required placeholder="Enter exact username">
                            </div>
                            <div>
                                <label class="form-label" for="group_member_role">Starting Role</label>
                                <select class="form-select" id="group_member_role" name="group_role">
                                    <option value="member">member</option>
                                    <?php if ($selectedRole === 'owner'): ?>
                                    <option value="admin">admin</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <button class="btn btn-primary" type="submit">Add to Group</button>
                        </form>
                    </article>
                </section>

                <article class="group-manage-card">
                    <h3 class="group-card-heading">Member Management</h3>
                    <?php if (count($selectedMembers) === 0): ?>
                    <div class="group-empty">This group has no members yet.</div>
                    <?php else: ?>
                    <table class="group-members-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($selectedMembers as $member): ?>
                            <?php
                                $memberUser = $member['user'];
                                $memberRole = $member['role'];
                                $allowedRoles = group_allowed_roles($selectedRole, $memberRole, $user->id, $memberUser->id);
                                $canRemove = group_can_remove($selectedRole, $memberRole);
                            ?>
                            <tr>
                                <td>
                                    <div class="group-member-user">
                                        <div class="group-member-avatar">
                                            <img src="/thumbs/headshot?id=<?= $memberUser->id ?>&sxy=100" alt="<?= htmlspecialchars($memberUser->name, ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                        <div>
                                            <div><?= htmlspecialchars($memberUser->name, ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="text-secondary small">User #<?= $memberUser->id ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="member-role-pill <?= htmlspecialchars($memberRole, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($memberRole, ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td class="text-secondary"><?= htmlspecialchars($member['joined_at']->format('Y-m-d'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <div class="group-member-actions">
                                        <form action="/api/groups" method="POST" class="d-flex gap-2 align-items-center flex-wrap">
                                            <input type="hidden" name="action" value="update-role">
                                            <input type="hidden" name="group_id" value="<?= $selectedGroup->id ?>">
                                            <input type="hidden" name="target_user_id" value="<?= $memberUser->id ?>">
                                            <select class="form-select form-select-sm" name="group_role" <?= count($allowedRoles) === 1 ? 'disabled' : '' ?>>
                                                <?php foreach ($allowedRoles as $roleOption): ?>
                                                <option value="<?= htmlspecialchars($roleOption, ENT_QUOTES, 'UTF-8') ?>" <?= $roleOption === $memberRole ? 'selected' : '' ?>><?= htmlspecialchars($roleOption, ENT_QUOTES, 'UTF-8') ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if (count($allowedRoles) > 1): ?>
                                            <button class="btn btn-sm btn-theme" type="submit">Save</button>
                                            <?php endif; ?>
                                        </form>

                                        <?php if ($canRemove): ?>
                                        <form action="/api/groups" method="POST">
                                            <input type="hidden" name="action" value="remove-member">
                                            <input type="hidden" name="group_id" value="<?= $selectedGroup->id ?>">
                                            <input type="hidden" name="target_user_id" value="<?= $memberUser->id ?>">
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </article>
                <?php else: ?>
                <div class="group-empty">You can view this group here, but only owners and admins can manage members and settings.</div>
                <?php endif; ?>
                <?php endif; ?>

                <article class="groups-panel">
                    <div class="group-meta-row mb-3">
                        <h2 class="groups-panel-title mb-0"><?= $query !== '' ? 'Search Results' : 'All Groups' ?></h2>
                        <span class="text-secondary small"><?= count($searchResults) ?> results</span>
                    </div>

                    <?php if (count($searchResults) === 0): ?>
                    <div class="group-empty">No groups matched that search.</div>
                    <?php else: ?>
                    <div class="groups-grid">
                        <?php foreach ($searchResults as $group): ?>
                        <article class="group-card">
                            <div class="group-card-art">
                                <img class="group-card-logo" src="<?= htmlspecialchars($group->GetLogoUrl(), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($group->name, ENT_QUOTES, 'UTF-8') ?> logo">
                            </div>
                            <div class="group-card-body">
                                <div>
                                    <h3 class="group-card-title"><?= htmlspecialchars($group->name, ENT_QUOTES, 'UTF-8') ?></h3>
                                    <p class="group-card-description"><?= htmlspecialchars(strlen($group->description) > 140 ? substr($group->description, 0, 137) . '...' : $group->description, ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                                <div class="group-card-footer">
                                    <span class="text-secondary small"><?= $group->memberCount ?> members</span>
                                    <?php if ($group->viewerRole !== null): ?>
                                    <span class="<?= group_role_class($group->viewerRole) ?>"><?= htmlspecialchars($group->viewerRole, ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                </div>
                                <a class="btn btn-theme" href="/my/groups.php?group=<?= $group->id ?><?= $query !== '' ? '&query=' . urlencode($query) : '' ?>">Open Group</a>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </article>
            </div>
        </section>
    </div>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/footer.php'; ?>
</body>
</html>





