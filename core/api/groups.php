<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/utilities/userutils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/classes/group.php';

function group_flash_set(bool $error, string $message): void {
    $_SESSION['group_flash'] = ['error' => $error, 'message' => $message];
}

function group_redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

$user = UserUtils::RetrieveUser();
if ($user === null) {
    group_redirect('/login');
}

Group::Boot();
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    group_flash_set(true, 'Invalid request.');
    group_redirect('/my/groups.php');
}

switch ($action) {
    case 'create':
        $_SESSION['group_form'] = [
            'name' => trim($_POST['group_name'] ?? ''),
            'description' => trim($_POST['group_description'] ?? ''),
        ];

        $result = Group::Create(
            $user,
            $_POST['group_name'] ?? '',
            $_POST['group_description'] ?? '',
            $_FILES['group_logo'] ?? []
        );

        if ($result['error']) {
            group_flash_set(true, $result['reason']);
            group_redirect('/my/group/create.php');
        }

        unset($_SESSION['group_form']);
        group_flash_set(false, 'Group created successfully.');
        group_redirect('/my/groups.php?group=' . urlencode((string) $result['group_id']));
        break;

    case 'update-group':
        $groupId = max(0, (int) ($_POST['group_id'] ?? 0));
        $result = Group::UpdateGroup(
            $user,
            $groupId,
            $_POST['group_name'] ?? '',
            $_POST['group_description'] ?? '',
            $_FILES['group_logo'] ?? []
        );

        group_flash_set((bool) $result['error'], $result['error'] ? $result['reason'] : 'Group updated.');
        group_redirect('/my/groups.php?group=' . urlencode((string) $groupId));
        break;

    case 'add-member':
        $groupId = max(0, (int) ($_POST['group_id'] ?? 0));
        $result = Group::AddMember(
            $user,
            $groupId,
            $_POST['username'] ?? '',
            $_POST['group_role'] ?? 'member'
        );

        group_flash_set((bool) $result['error'], $result['error'] ? $result['reason'] : 'Member added.');
        group_redirect('/my/groups.php?group=' . urlencode((string) $groupId));
        break;

    case 'update-role':
        $groupId = max(0, (int) ($_POST['group_id'] ?? 0));
        $targetUserId = max(0, (int) ($_POST['target_user_id'] ?? 0));
        $result = Group::UpdateMemberRole($user, $groupId, $targetUserId, $_POST['group_role'] ?? 'member');

        group_flash_set((bool) $result['error'], $result['error'] ? $result['reason'] : 'Member role updated.');
        group_redirect('/my/groups.php?group=' . urlencode((string) $groupId));
        break;

    case 'remove-member':
        $groupId = max(0, (int) ($_POST['group_id'] ?? 0));
        $targetUserId = max(0, (int) ($_POST['target_user_id'] ?? 0));
        $result = Group::RemoveMember($user, $groupId, $targetUserId);

        group_flash_set((bool) $result['error'], $result['error'] ? $result['reason'] : 'Member removed from the group.');
        group_redirect('/my/groups.php?group=' . urlencode((string) $groupId));
        break;

    default:
        group_flash_set(true, 'Unknown group action.');
        group_redirect('/my/groups.php');
}
?>
