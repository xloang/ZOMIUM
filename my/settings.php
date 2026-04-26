<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/utilities/userutils.php';
$user = UserUtils::RetrieveUser();

if ($user == null) {
    die(header('Location: /login'));
}

$settings = UserSettings::Get($user);

if (isset($_POST['ANORRL$Update$Profile$Bio']) && isset($_POST['ANORRL$Update$Profile$Submit'])) {
    $result = $user->UpdateBio(trim($_POST['ANORRL$Update$Profile$Bio']));
    $_SESSION['ANORRL$Update$Settings$Error'] = $result['error'];
    $_SESSION['ANORRL$Update$Settings$Result'] = $result['reason'] ?? 'Blurb updated.';
    die(header('Location: /my/settings.php'));
}

if (isset($_POST['ANORRL$Update$Profile$BGM']) && isset($_POST['ANORRL$Update$Profile$BGM$Submit'])) {
    $result = $user->UpdateBGM(trim($_POST['ANORRL$Update$Profile$BGM']));
    $_SESSION['ANORRL$Update$Settings$Error'] = $result['error'];
    $_SESSION['ANORRL$Update$Settings$Result'] = $result['reason'] ?? 'Profile music updated.';
    die(header('Location: /my/settings.php'));
}

if (isset($_POST['ANORRL$Update$Profile$CSS']) && isset($_POST['ANORRL$Update$Profile$CSS$Submit'])) {
    $result = $user->SetUserCSS(trim($_POST['ANORRL$Update$Profile$CSS']));
    $_SESSION['ANORRL$Update$Settings$Error'] = !$result;
    $_SESSION['ANORRL$Update$Settings$Result'] = $result ? 'Profile CSS updated.' : 'That was invalid CSS!';
    die(header('Location: /my/settings.php'));
}

if (isset($_FILES['ANORRL$Update$Profile$Picture'])) {
    $result = $user->SetProfilePicture($_FILES['ANORRL$Update$Profile$Picture']);
    $_SESSION['ANORRL$Update$Settings$Error'] = $result['error'];
    $_SESSION['ANORRL$Update$Settings$Result'] = $result['reason'] ?? 'Profile picture updated.';
    die(header('Location: /my/settings.php'));
}

if (isset($_POST['action']) && $_POST['action'] === 'ANORRL$Update$Profile$ResetProfilePicture') {
    $user->ResetProfilePicture();
    $_SESSION['ANORRL$Update$Settings$Error'] = false;
    $_SESSION['ANORRL$Update$Settings$Result'] = 'Profile picture removed.';
    die(header('Location: /my/settings.php'));
}

if (isset($_POST['ANORRL$Update$Settings$Submit'])) {
    $settings->SetEmoteSoundsEnabled(isset($_POST['ANORRL$Update$Settings$EmoteSoundsEnabled']));
    $settings->SetAccessibilityEnabled(isset($_POST['ANORRL$Update$Settings$AccessibilityEnabled']));
    $settings->SetHeadshotsEnabled(isset($_POST['ANORRL$Update$Settings$HeadshotsEnabled']));
    $settings->SetNightBGEnabled(isset($_POST['ANORRL$Update$Settings$NightBGEnabled']));

    $_SESSION['ANORRL$Update$Settings$Error'] = false;
    $_SESSION['ANORRL$Update$Settings$Result'] = 'Settings updated.';
    die(header('Location: /my/settings.php'));
}

$settingsMessage = $_SESSION['ANORRL$Update$Settings$Result'] ?? null;
$settingsError = $_SESSION['ANORRL$Update$Settings$Error'] ?? false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $page_title = 'Settings - Zomium';
    include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/head.php';
    ?>
    <style>
        .settings-shell { padding-top: 1.5rem; padding-bottom: 3rem; }
        .settings-nav { position: sticky; top: 6.5rem; }
        .settings-nav p, .settings-nav a, .settings-nav span {
            display: block;
            margin-bottom: .45rem;
            text-decoration: none;
        }
        .settings-nav .active { color: var(--bs-primary); font-weight: 700; }
        .settings-nav .muted-item { color: var(--bs-secondary-color); }
        .settings-card + .settings-card { margin-top: 1rem; }
        .settings-label { font-size: .95rem; font-weight: 700; margin-bottom: .5rem; }
        .settings-note { color: var(--bs-secondary-color); font-size: .95rem; }
        .settings-avatar {
            width: 128px;
            height: 128px;
            object-fit: cover;
            border-radius: 1rem;
            box-shadow: 0 12px 30px rgba(0,0,0,.16);
        }
    </style>

    <script>
        function removePicture() {
            const form = document.getElementById('reset-picture-form');
            if (form) form.submit();
        }

        window.addEventListener('DOMContentLoaded', function () {
            const pictureInput = document.getElementById('settings-picture-input');
            if (pictureInput) {
                pictureInput.addEventListener('change', function () {
                    if (pictureInput.files.length > 0) {
                        document.getElementById('picture-upload-form').submit();
                    }
                });
            }
        });
    </script>
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/header.php'; ?>

<main class="app-main">
    <div class="container settings-shell">
        <div class="row g-4">

            <div class="col-lg-2">
                <div class="settings-nav">
                    <strong><p>Settings</p></strong>
                    <p class="active mb-1">User Details</p>
                    <span class="muted-item">Password</span>
                    <p class="active mb-1">Language</p>
                    <span class="muted-item">Security</span>
                    <span class="muted-item">Privacy</span>
                    <span class="muted-item">Theming</span>
                    <span class="muted-item">Places</span>
                    <span class="muted-item">Connect</span>
                </div>
            </div>

            <div class="col-lg-10">

                <?php if ($settingsMessage !== null): ?>
                    <div class="alert <?= $settingsError ? 'alert-danger' : 'alert-success' ?> mb-3">
                        <?= htmlspecialchars($settingsMessage, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <div class="card border-0 settings-card shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                            <div>
                                <p class="mb-1"><strong>Username:</strong> <?= htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="mb-1"><strong>Profile ID:</strong> <?= $user->id ?></p>
                            </div>
                            <div class="text-md-end">
                                <a class="btn btn-outline-primary" href="/users/<?= $user->id ?>/profile">View profile</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-1">

                    <div class="col-xl-8">

                        <div class="card border-0 settings-card shadow-sm">
                            <div class="card-body p-4">
                                <form method="POST">
                                    <label class="settings-label">Blurb</label>
                                    <textarea class="form-control" name="ANORRL$Update$Profile$Bio" rows="8"><?= htmlspecialchars($user->blurb, ENT_QUOTES, 'UTF-8') ?></textarea>
                                    <button type="submit" class="btn btn-success mt-3" name="ANORRL$Update$Profile$Submit">Update</button>
                                </form>
                            </div>
                        </div>

                        

                    </div>
                    
</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/footer.php'; ?>
</body>
</html>

<?php unset($_SESSION['ANORRL$Update$Settings$Error'], $_SESSION['ANORRL$Update$Settings$Result']); ?>