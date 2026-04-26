<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/userutils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/classes/comment.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/classes/asset.php';

function IsRewrite() {
    if(!empty($_SERVER['IIS_WasUrlRewritten'])) return true;
    else if(array_key_exists('HTTP_MOD_REWRITE',$_SERVER)) return true;
    else if(array_key_exists('REDIRECT_URL', $_SERVER)) return true;
    else return false;
}

if(!IsRewrite()) { die(header('Location: /my/home')); }
if(!isset($_GET['id'])) { die(header('Location: /my/home')); }

$get_user = User::FromID(intval($_GET['id']));
if($get_user == null) { die(header('Location: /my/home')); }

$user = UserUtils::RetrieveUser($get_user);
if($user == null) { die(header('Location: /login')); }

// Comment Posting Logic
if(isset($_POST['content'])) {
    $result = Comment::Post($get_user, $_POST['content']);
    if($result['error']) {
        $_SESSION['ANORRL$Comment$Post$Error'] = $result['reason'];
    }
    die(header('Location: /users/'.$get_user->id.'/profile'));
}

$friends = $get_user->GetFriends();
$friend_count = count($friends);
$games = $get_user->GetPlaces();
$badges = $get_user->GetProfileBadges();
$comments = Comment::GetCommentsOn($get_user);
$bgm = $get_user->profilebgm;

$page_title = $get_user->name . "'s Profile - Zomium";
$page_styles = ['/users/'.$get_user->id.'/css?t='.time()];
require_once $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php';


function renderBlurb(string $rawBlurb, User $profileUser): string {
    $safe = htmlspecialchars($rawBlurb, ENT_QUOTES, 'UTF-8');
    $safe = nl2br($safe);
    $ziuBalance = htmlspecialchars(number_format($profileUser->GetZiu()), ENT_QUOTES, 'UTF-8');
    $ziuHtml = '<img src="/images/ziu_16.png" alt="ZIU" style="width:14px;height:14px;vertical-align:middle;margin-right:2px;">' . $ziuBalance;
    $safe = str_replace('&#36;{myZius}', $ziuHtml, $safe); // htmlspecialchars $ yi değiştirmez, { } de değişmez — bu yüzden doğrudan replace
    $safe = str_replace('${myZius}', $ziuHtml, $safe);
    return $safe;
}
?>

<style>
    body {
        background-color: #343a40 !important;
        color: #ececec !important;
        font-family: "Source Sans Pro", "Helvetica Neue", Roboto, Arial, sans-serif;
    }

    .container {
        max-width: 1140px;
    }

    .card {
        background-color: #313438 !important;
        color: #ececec !important;
        border: 1px solid hsla(0,0%,96.5%,.125) !important;
        border-radius: .25rem;
        margin-bottom: 1rem;
    }

    .card-header {
        background-color: #6c757d;
        border-bottom: 1px solid hsla(0,0%,96.5%,.125) !important;
        font-weight: 500;
        padding: .75rem 1.25rem;
    }

    .btn-theme {
        color: #ececec !important;
        background-color: #4e5155 !important;
        border-color: #35383c !important;
        transition: all .2s linear;
        text-transform: lowercase;
        font-size: 12px;
        padding: .375rem .75rem;
        border-radius: .25rem;
        text-decoration: none;
        display: inline-block;
    }



    .btn-primary {
        background-color: #3182ce !important;
        border-color: #3182ce !important;
        text-transform: lowercase;
        font-size: 12px;
    }

    .btn-success {
        background-color: #38a169 !important;
        border-color: #38a169 !important;
        text-transform: lowercase;
        font-size: 12px;
    }

    .badge-dark {
        background-color: #343a40 !important;
        color: #fff !important;
    }

    .text-primary {
        color: #3182ce !important;
    }

    .text-danger {
        color: #e53e3e !important;
    }

    .nowrap {
        white-space: nowrap;
    }

    .full-width {
        width: 100%;
    }

    .profile-avatar-img {
        width: 15rem;
        height: auto;
    }

    .blurb-container {
    max-height: 6rem; /* eskiden 15remdi */
    width: 17rem;
    overflow: auto;
    word-break: break-word;
}
    .section-title {
        font-family: "proxima-nova", sans-serif;
        font-weight: 500;
    }
    
    .grayscale-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .grayscale-scroll::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
    }
</style>

<?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/header.php'; ?>

<div class="container" style="margin-bottom:10px">
    <?php if($get_user->id == 0): ?>
        <div class="alert alert-danger mb-2 mt-3">
            <div class="container" style="color: #FFF;">
                <h5 class="font-weight-light my-2"><i class="far fa-exclamation-triangle fa-fw align-middle mr-1"></i> This user is banned.</h5>
                <p class="mb-2">This user has been suspended from website.</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12 mt-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex row justify-content-around">
                        <div class="offset-md-1">
                            <img src="/thumbs/player?id=<?= $get_user->id ?>&sxy=400" class="profile-avatar-img" alt="Avatar">
                        </div>

                        <div class="d-flex flex-column">
                            <div style="margin-bottom: auto;">
                                <h4 class="section-title"><i class='fal fa-mars fa-1x'></i> <?= htmlspecialchars($get_user->name) ?></h4>
                                <p class="mb-1 text-<?= $get_user->IsAdmin() ? 'danger' : 'primary' ?>">
                                    <strong><?= $get_user->IsAdmin() ? 'Admin' : 'Member' ?></strong>
                                </p>
                                <p class="mt-1"><strong>Friends: </strong><?= $friend_count ?> <a href="/users/<?= $get_user->id ?>/friends">(View ›)</a></p>
                                <p class="mb-1 nowrap"><strong>Join Date: </strong><?= $get_user->join_date->format('Y-m-d') ?></p>
                                
                                <div class="d-flex justify-content-start mt-5">
                                    <a href="/app/inbox/compose?user=<?= urlencode($get_user->name) ?>" class="btn btn-theme mr-2 nowrap">Send Message</a>
                                    <a href="#" class="btn btn-theme mr-2 nowrap">Trade</a>
                                    <?php if($user->id != $get_user->id): ?>
                                        <button class="btn btn-primary mr-2 nowrap" onclick="ANORRL.User.Friend(<?= $get_user->id ?>)">Send Friend Request</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if(!empty($badges)): ?>
                                <div class="mt-2 mb-0 d-flex flex-wrap gap-1">
                                <?php foreach($badges as $badge): ?>
                                    <span class="badge badge-primary mr-1" title="<?= htmlspecialchars($badge->description) ?>"><?= htmlspecialchars($badge->name) ?></span>
                                <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="d-none d-lg-block">
                            <p class="mt-4"><strong>Blurb:</strong></p>
                            <div class="blurb-container grayscale-scroll">
                                <p class="mt-1 small"><?= $get_user->blurb ? renderBlurb($get_user->blurb, $get_user) : 'This user has no blurb.' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container" style="margin-bottom:10px">
    <div class="row">
        <!-- friend -->
        <div class="col-md-5 mt-2">
            <div class="card h-100">
                <div class="card-header text-center">
                    Friends <a href="/users/<?= $get_user->id ?>/friends" class="small">(All ›)</a>
                </div>
                <div class="card-body">
                    <?php if($friend_count > 0): ?>
                        <div class="row g-2">
                            <?php foreach(array_slice($friends, 0, 3) as $friend): ?>
                                <div class="col-4 text-center">
                                    <a class="d-flex flex-column align-items-center text-decoration-none" href="/users/<?= $friend->id ?>/profile">
                                        <img src="/thumbs/headshot?id=<?= $friend->id ?>&sxy=120" class="rounded-circle border border-secondary border-opacity-25 mb-1" height="64" width="64">
                                        <span class="full-width small text-white text-truncate"><?= htmlspecialchars($friend->name) ?></span>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="mb-0 text-center opacity-50 small">No friends. What a party-pooper.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- places-->
        <div class="col-md-7 mt-2">
            <div class="card h-100">
                <div class="card-header">
                    Places
                </div>
                <div class="card-body">
                    <?php if(!empty($games)): ?>
                        <?php foreach($games as $index => $game): if(!$game->public) continue; ?>
                            <button class="btn btn-primary full-width mt-2 d-flex justify-content-between align-items-center" onclick="openGame(<?= $index ?>);">
                                <span><?= htmlspecialchars($game->name) ?></span>
                                <span class="badge badge-dark">2016</span>
                            </button>
                            <div class="card mt-2" id="game-<?= $index ?>" style="display: none; background: rgba(0,0,0,0.1) !important;">
                                <div class="card-body text-center">
                                    <a href="/place?id=<?= $game->id ?>" class="text-info text-decoration-none"><p class="mb-2 fw-bold"><?= htmlspecialchars($game->name) ?></p></a>
                                    <a href="/place?id=<?= $game->id ?>"><img src="/thumbs/?id=<?= $game->id ?>&sx=400&sy=225" class="mt-2 rounded" style="max-width: 100%; max-height: 150px; object-fit: contain;"></a>
                                    <a href="/place?id=<?= $game->id ?>" class="btn btn-success btn-lg full-width mt-3 text-capitalize"><i class="far fa-play mr-1"></i> Play</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <script>
                            function openGame(number) {
                                var game = document.getElementById('game-' + number);
                                game.style.display = game.style.display == "none" ? "" : "none";
                            }
                        </script>
                    <?php else: ?>
                        <p class="mb-0 text-center opacity-50 small">This user has no active places.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="container mt-3">
    <div class="row">
        <div class="col-md-2 mt-2">
            <div class="card">
                <div class="card-header text-center small uppercase">Type</div>
                <div class="card-body p-2 d-grid gap-1">
                    <button class="btn btn-theme w-100">hats</button>
                    <button class="btn btn-primary w-100">shirts</button>
                    <button class="btn btn-theme w-100">pants</button>
                    <button class="btn btn-theme w-100">gears</button>
                    <button class="btn btn-theme w-100">faces</button>
                    <button class="btn btn-theme w-100">heads</button>
                </div>
            </div>
        </div>

        <div class="col-md-10 mt-2">
            <div class="card h-100">
                <div class="card-body p-3">
                    <div class="row g-2" id="inventory-results">
                        <?php 
                        $hats = $get_user->GetAllOwnedAssetsOfTypePaged(AssetType::HAT, 1, 8);
                        if(empty($hats)): ?>
                            <div class="col-12 text-center py-5 opacity-50 small">This user doesnt have any hats...</div>
                        <?php else: ?>
                            <?php foreach($hats as $hat): ?>
                                <div class="col-md-3 col-6">
                                    <a href="/<?= $hat->GetURLTitle() ?>-item?id=<?= $hat->id ?>" class="card inventory-item-card text-decoration-none p-2 h-100">
                                        <img src="/thumbs/?id=<?= $hat->id ?>&sxy=150" class="img-fluid rounded mb-1" style="aspect-ratio: 1; object-fit: contain;">
                                        <span class="d-block text-white small text-truncate text-center"><?= htmlspecialchars($hat->name) ?></span>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 
<div class="container mt-4 mb-5">
    <div class="card">
        <div class="card-header">Discussion</div>
        <div class="card-body">
            
                <div class="alert alert-danger small mb-3"></div>
                
            <form method="POST" class="mb-4">
                <textarea class="form-control mb-2 bg-dark text-white border-secondary" placeholder="Add a comment..." name="content" rows="3"></textarea>
                <div class="text-end">
                    <button class="btn btn-primary px-4" type="submit" name="post_comment">POST COMMENT</button>
                </div>
            </form>
            
            <div class="comment-list">
                <?php if(empty($comments)): ?>
                    <p class="text-center opacity-50 small">Be the first to comment!</p>
                <?php else: ?>
                    <?php foreach($comments as $comment): ?>
                        <div class="mb-3 p-3 rounded bg-black bg-opacity-25 border border-secondary border-opacity-10">
                            <div class="d-flex align-items-center mb-2">
                                <a href="/users/<?= $comment->poster->id ?>/profile"><img src="/thumbs/headshot?id=<?= $comment->poster->id ?>&sxy=50" class="rounded-circle me-2" width="30"></a>
                                <a href="/users/<?= $comment->poster->id ?>/profile" class="text-info fw-bold text-decoration-none small me-2"><?= htmlspecialchars($comment->poster->name) ?></a>
                                <span class="text-muted smaller"><?= $comment->postdate->format('M j, Y') ?></span>
                            </div>
                            <div class="small"><?= nl2br(htmlspecialchars($comment->contents)) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
-->
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/core/ui/footer.php'; ?>
</body>
</html>
