<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/userutils.php';
$user = UserUtils::RetrieveUser();

if($user == null) {
    die(header('Location: /login'));
}

$settings = UserSettings::Get($user);

if(isset($_POST['ANORRL$Update$Profile$Bio']) && isset($_POST['ANORRL$Update$Profile$Submit'])) {
    $result = $user->UpdateBio(trim($_POST['ANORRL$Update$Profile$Bio']));
    if($result['error']) {
        $_SESSION['ANORRL$Update$ProfileError'] = true;
        $_SESSION['ANORRL$Update$ProfileResult'] = $result['reason'];
        die(header('Location: /my/profile'));
    } else {
        die(header('Location: /users/'.$user->id.'/profile'));
    }
}

if(isset($_POST['ANORRL$Update$Profile$BGM']) && isset($_POST['ANORRL$Update$Profile$BGM$Submit'])) {
    $result = $user->UpdateBGM(trim($_POST['ANORRL$Update$Profile$BGM']));
    if($result['error']) {
        $_SESSION['ANORRL$Update$ProfileError'] = true;
        $_SESSION['ANORRL$Update$ProfileResult'] = $result['reason'];
        die(header('Location: /my/profile'));
    } else {
        die(header('Location: /users/'.$user->id.'/profile'));
    }
}

if(isset($_POST['ANORRL$Update$Profile$CSS']) && isset($_POST['ANORRL$Update$Profile$CSS$Submit'])) {
    $result = $user->SetUserCSS(trim($_POST['ANORRL$Update$Profile$CSS']));
    if(!$result) {
        $_SESSION['ANORRL$Update$ProfileError'] = true;
        $_SESSION['ANORRL$Update$ProfileResult'] = 'That was invalid css!';
        die(header('Location: /my/profile'));
    } else {
        die(header('Location: /users/'.$user->id.'/profile'));
    }
}

if(isset($_FILES['ANORRL$Update$Profile$Picture'])) {
    $file = $_FILES['ANORRL$Update$Profile$Picture'];
    $result = $user->SetProfilePicture($file);
    if($result['error']) {
        $_SESSION['ANORRL$Update$ProfileError'] = true;
        $_SESSION['ANORRL$Update$ProfileResult'] = $result['reason'];
        die(header('Location: /my/profile'));
    } else {
        die(header('Location: /users/'.$user->id.'/profile'));
    }
}

if(isset($_POST['action']) && $_POST['action'] == 'ANORRL$Update$Profile$ResetProfilePicture') {
    $user->ResetProfilePicture();
}

if(isset($_POST['ANORRL$Update$Settings$Submit'])) {
    $emotesounds_enabled = isset($_POST['ANORRL$Update$Settings$EmoteSoundsEnabled']);
    $accessibility_enabled = isset($_POST['ANORRL$Update$Settings$AccessibilityEnabled']);
    $headshots_enabled = isset($_POST['ANORRL$Update$Settings$HeadshotsEnabled']);
    $nightbg_enabled = isset($_POST['ANORRL$Update$Settings$NightBGEnabled']);

    $settings->SetEmoteSoundsEnabled($emotesounds_enabled);
    $settings->SetAccessibilityEnabled($accessibility_enabled);
    $settings->SetHeadshotsEnabled($headshots_enabled);
    $settings->SetNightBGEnabled($nightbg_enabled);

    die(header('Location: /my/profile'));
}
?>
<!DOCTYPE html>
<html>
    <head>
        <?php
        $page_title = 'Account Settings - Zomium';
        include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php';
        ?>
        <script>
            function RemovePicture() {
                $.post('/my/profile', {'action': 'ANORRL$Update$Profile$ResetProfilePicture'}, function() {
                    window.location.reload();
                })
            }
            $(function () {
                $('input[type=file]')[0].onchange = e => { $('#PictureForm').submit(); }
            })
        </script>
    </head>
    <body>
        <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/header.php'; ?>
        <main class="app-main">
            <div class="container">
                <div class="hero-panel p-4 p-lg-5 mb-4">
                    <div class="section-title mb-3">Account</div>
                    <h1 class="display-6 fw-bold mb-3">Profile settings</h1>
                    <p class="text-secondary mb-0">Manage your blurb, custom CSS, profile music, site preferences, and profile image from one dark-mode settings page.</p>
                </div>
                <?php if(isset($_SESSION['ANORRL$Update$ProfileError']) && $_SESSION['ANORRL$Update$ProfileError']): ?>
                <div class="alert alert-danger mb-4">Error: <?= $_SESSION['ANORRL$Update$ProfileResult'] ?></div>
                <?php endif; ?>
                <div class="row g-4">
                    <div class="col-lg-8">
                        <form method="POST" class="card border-0 mb-4">
                            <div class="card-body p-4">
                                <h2 class="h4 mb-3">About yourself</h2>
                                <p class="text-secondary">Write a blurb for your public profile.</p>
                                <textarea class="form-control mb-3" name="ANORRL$Update$Profile$Bio" rows="6"><?= htmlspecialchars($user->blurb, ENT_QUOTES, 'UTF-8') ?></textarea>
                                <button class="btn btn-primary" type="submit" name="ANORRL$Update$Profile$Submit">Update blurb</button>
                            </div>
                        </form>
                        <form method="POST" class="card border-0 mb-4">
                            <div class="card-body p-4">
                                <h2 class="h4 mb-3">Profile CSS</h2>
                                <p class="text-secondary">Advanced customization for your public profile page.</p>
                                <textarea class="form-control mb-3" name="ANORRL$Update$Profile$CSS" rows="10"><?= htmlspecialchars($user->GetUserCSS(), ENT_QUOTES, 'UTF-8') ?></textarea>
                                <button class="btn btn-primary" type="submit" name="ANORRL$Update$Profile$CSS$Submit">Update CSS</button>
                            </div>
                        </form>
                        <form method="POST" class="card border-0 mb-4">
                            <div class="card-body p-4">
                                <h2 class="h4 mb-3">Profile music</h2>
                                <p class="text-secondary">Enter a sound asset id to autoplay on your profile page.</p>
                                <input class="form-control mb-3" type="text" name="ANORRL$Update$Profile$BGM" value="<?= htmlspecialchars($user->profilebgm, ENT_QUOTES, 'UTF-8') ?>">
                                <button class="btn btn-primary" type="submit" name="ANORRL$Update$Profile$BGM$Submit">Update music</button>
                            </div>
                        </form>
                        <form method="POST" class="card border-0">
                            <div class="card-body p-4">
                                <h2 class="h4 mb-3">Site preferences</h2>
                                <div class="row g-3">
                                    <div class="col-md-6 form-check ms-2"><input class="form-check-input" name="ANORRL$Update$Settings$EmoteSoundsEnabled" type="checkbox" <?php if($settings->emotesounds_enabled): ?>checked<?php endif ?>><label class="form-check-label">Emote sounds</label></div>
                                    <div class="col-md-6 form-check ms-2"><input class="form-check-input" name="ANORRL$Update$Settings$AccessibilityEnabled" type="checkbox" <?php if($settings->accessibility_enabled): ?>checked<?php endif ?>><label class="form-check-label">Accessibility mode</label></div>
                                    <div class="col-md-6 form-check ms-2"><input class="form-check-input" name="ANORRL$Update$Settings$HeadshotsEnabled" type="checkbox" <?php if($settings->headshots_enabled): ?>checked<?php endif ?>><label class="form-check-label">Prefer headshots</label></div>
                                    <div class="col-md-6 form-check ms-2"><input class="form-check-input" name="ANORRL$Update$Settings$NightBGEnabled" type="checkbox" <?php if($settings->nightbg_enabled): ?>checked<?php endif ?>><label class="form-check-label">Night background</label></div>
                                </div>
                                <button class="btn btn-primary mt-3" type="submit" name="ANORRL$Update$Settings$Submit">Save settings</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-4">
                        <form method="POST" class="card border-0" id="PictureForm" enctype="multipart/form-data">
                            <div class="card-body p-4 text-center">
                                <h2 class="h4 mb-3">Profile picture</h2>
                                <img class="img-fluid rounded mb-3" src="/thumbs/profile?id=<?= $user->id ?>&sxy=290&nocompress" alt="Profile picture">
                                <input class="form-control mb-3" id="thumbfiles" type="file" name="ANORRL$Update$Profile$Picture" accept="image/*">
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-primary" type="button" onclick="document.getElementById('thumbfiles').click()">Choose file</button>
                                    <a class="btn btn-outline-danger" href="javascript:RemovePicture()">Remove</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
        <?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/footer.php'; ?>
    </body>
</html>
<?php
unset($_SESSION['ANORRL$Update$ProfileError']);
?>
