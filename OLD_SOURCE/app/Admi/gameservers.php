<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/utilities/userutils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/classes/user.php';
require_once $_SERVER['DOCUMENT_ROOT'] . 'core/gameservers/gameserver.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/utilities/utilutils.php';
$user = UserUtils::RetrieveUser();  //! Dont forget to add this to all pages!




if ($user == null || !$user->IsAdmin()) {
    http_response_code(404);
    require $_SERVER['DOCUMENT_ROOT'] . '/core/error/404.php';
    exit;
}



function admi_redirect(?int $userId = null, string $query = ''): void
{
    $target = '/admi';
    $parts = [];
    if ($userId !== null) {
        $parts[] = 'user_id=' . $userId;
    }
    if ($query !== '') {
        $parts[] = 'q=' . urlencode($query);
    }
    if (count($parts) > 0) {
        $target .= '?' . implode('&', $parts);
    }
    header('Location: ' . $target);
    exit;
}

//? is html page and backend done yet

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zomium - Gameservers</title>
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/header.php';  ?>

    <div>
        <a>
            Gameservers that running now are: <?php $gameservers ?>
        </a>

        <a href="/admi/delete-server.php" class="btn btn-primary">
            Delete Server
        </a>

        <a href="/admi/creae.php" class="btn btn-primary">
            Create server
        </a>
        <!-- o o o o o o o -->

    </div>



    <?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/footer.php';  ?>
</body>

</html>