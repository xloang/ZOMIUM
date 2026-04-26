<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/utilities/userutils.php';
$user = UserUtils::RetrieveUser();

if ($user == null) {
    die(header('Location: /login'));
}

if (isset($_POST['ANORRL$Home$Status$Text']) && isset($_POST['ANORRL$Home$Status$Submit'])) {
    $result = Status::Send($user->id, trim($_POST['ANORRL$Home$Status$Text']));

    if ($result['error']) {
        $_SESSION['ANORRL$Home$StatusError'] = true;
        $_SESSION['ANORRL$Home$StatusResult'] = $result['reason'];
    } else {
        $_SESSION['ANORRL$Home$StatusError'] = false;
        $_SESSION['ANORRL$Home$StatusResult'] = 'Success!';
    }

    die(header('Location: /my/home'));
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php
    $page_title = 'Dashboard - Zomium';
    $page_scripts = ['/js/home.js?t=1771413807'];
    include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/head.php';
    ?>
    <style>

        .home-kicker,
        .home-title,
        .home-subtitle,
        .profile-name,
        .feed-heading {
            font-family: "Source Sans Pro", Arial, Helvetica, sans-serif;
        }

        body {
            background: #101011 !important;
            background-image: none !important;
            font-family: "Source Sans Pro", Arial, Helvetica, sans-serif;
            font-weight: 500;
        }

        .home-hero {
            position: relative;
            min-height: 350px;
            display: flex;
            align-items: flex-end;
            overflow: hidden;
            border-bottom: 1px solid rgba(255,255,255,.08);
            background:
                linear-gradient(180deg, rgba(19, 12, 34, .15) 0%, rgba(8, 8, 10, .72) 55%, #0f0f10 100%),
                url('/s/img/xmas_small.jpg') center center / cover no-repeat;
        }

        .home-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            backdrop-filter: blur(7px);
            background: rgba(135, 170, 210, .12);
        }

        .home-hero > .container {
            position: relative;
            z-index: 1;
            padding-top: 4.5rem;
            padding-bottom: 4rem;
        }

        .home-kicker {
            font-size: .9rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(255,255,255,.72);
        }

        .home-title {
            font-size: clamp(2.25rem, 4vw, 3.25rem);
            font-weight: 600;
            color: #f3f5f8;
            margin-bottom: .75rem;
        }

        .home-subtitle {
            max-width: 42rem;
            color: rgba(255,255,255,.82);
            font-size: 1.05rem;
        }

        .dashboard-shell {
            margin-top: -2.5rem;
            position: relative;
            z-index: 2;
        }

        .dashboard-panel {
            background: #17171a;
            border: 0px solid rgba(255,255,255,.08);
            border-radius: 0px;
            box-shadow: 0 22px 44px rgba(0,0,0,.28);
        }

        .profile-panel {
            padding: 1rem;
            text-align: center;
            min-height: 10%;
            background-color: transparent;
        }

        .profile-headshot {
            width: 190px;
            height: 190px;
            margin: 0 auto 1.25rem;
            display: block;
            object-fit: cover;
            border-radius: 1rem;
            border: 1px solid rgba(255,255,255,.08);
            background: #111;
        }

        .profile-name {
            font-size: 1.35rem;
            font-weight: 600;
            color: #fff;
        }

        .profile-meta {
            color: #aeb3bc;
            font-size: .95rem;
        }

        .feed-panel {
            padding: 1rem;
        }

        .feed-heading {
            color: #fff;
            font-size: 2rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .feed-composer {
            background: #303238;
            border: 1px solid rgba(255,255,255,.08);
            border-radius: .25rem;
            color: #eef2f7;
            min-height: 110px;
            resize: vertical;
        }

        .feed-composer::placeholder {
            color: #9aa2ae;
        }

        .feed-submit {
            min-width: 110px;
        }

        #Feeds {
            border-collapse: separate;
            border-spacing: 0 1rem;
        }

        #Feeds .feed-row {
            background: #1f1f23;
            border: 1px solid rgba(255,255,255,.06);
        }

        #Feeds .feed-row td {
            padding: 1rem;
            vertical-align: top;
            background: #1f1f23;
            color: #edf1f7;
        }

        #Feeds .feed-row td:first-child {
            width: 110px;
            border-top-left-radius: .4rem;
            border-bottom-left-radius: .4rem;
        }

        #Feeds .feed-row td:last-child {
            border-top-right-radius: .4rem;
            border-bottom-right-radius: .4rem;
        }

        #Feeds .User a {
            color: #fff;
            text-decoration: none;
        }

        #Feeds .User img {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: .75rem;
            border: 1px solid rgba(255,255,255,.08);
            background: #121212;
        }

        #Feeds #Content code {
            display: block;
            white-space: pre-wrap;
            font-family: inherit;
            color: #eef2f7;
            background: transparent;
            padding: 0;
        }

        #DatePosted {
            color: #8d95a3;
        }

        .feed-pager a {
            color: #d7e9ff;
            text-decoration: none;
        }

        .feed-pager a:hover {
            text-decoration: underline;
            text-decoration-color: #6fb7ff;
            text-underline-offset: .25rem;
        }
    </style>
</head>
<body>
    <table id="FeedItem" template>
        <tr class="feed-row">
            <td class="User">
                <a href="">
                    <img src="" width="72" height="72" alt="Feed user">
                    <div id="Name" class="mt-2 fw-semibold">Name here</div>
                </a>
            </td>
            <td id="Content">
                <code>Content content</code>
                <div id="DatePosted" class="small mt-3">Posted <span id="Date">DD/MM/YYYY</span></div>
            </td>
        </tr>
    </table>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/header.php'; ?>

    <main class="app-main p-0">
        <section class="home-hero">
            <div class="container">
                <h1 class="home-title">Hello, <?= htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') ?></h1>
                <div class="home-subtitle">"
                    <?php if ($user->GetLatestStatus() != null):?>
                        <?= htmlspecialchars($user->GetLatestStatus()->content, ENT_QUOTES, 'UTF-8') ?>"
                    <?php else: ?>
                        What's going on?
                    <?php endif; ?>
                </div>
            </div>
        </section>

      <div class="container dashboard-shell pb-5">
    <div class="row g-4">

       
        <div class="col-lg-3 d-flex justify-content-center">
    <img
        src="/thumbs/player?id=<?= $user->id ?>&sxy=250"
        alt="<?= htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') ?>"
        style="
            width: 300px;
            height:300px;
            border-radius:50%;
            margin-top:25px;
        ">
</div>

                <div class="col-lg-9">
                    <section id="FeedsContainer" class="dashboard-panel feed-panel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="feed-heading mb-0">My Feed</h2>
                        </div>

                        <?php if (isset($_SESSION['ANORRL$Home$StatusError'])): ?>
                            <div class="alert <?= $_SESSION['ANORRL$Home$StatusError'] ? 'alert-danger' : 'alert-success' ?> mb-3">
                                <?= $_SESSION['ANORRL$Home$StatusResult'] ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="mb-4">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-10">
                                    <textarea
                                        class="form-control feed-composer"
                                        name="ANORRL$Home$Status$Text"
                                        rows="3"
                                        placeholder="What's going on?"></textarea>
                                </div>
                                <div class="col-md-2 d-grid">
                                    <input
                                        class="btn btn-primary feed-submit"
                                        type="submit"
                                        name="ANORRL$Home$Status$Submit"
                                        value="post..">
                                </div>
                            </div>
                        </form>

                        <table id="Feeds" class="table table-borderless mb-0"></table>

                        <div id="Pager" class="feed-pager d-flex justify-content-between align-items-center mt-3" style="display:none;">
                            <a id="BackPager" href="javascript:ANORRL.Home.DeadvanceFeed()">Previous</a>
                            <div id="PageCounter" class="small text-muted"></div>
                            <a id="NextPager" href="javascript:ANORRL.Home.AdvanceFeed()">Next</a>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/footer.php'; ?>
</body>
</html>
<?php
unset($_SESSION['ANORRL$Home$StatusError']);
unset($_SESSION['ANORRL$Home$StatusResult']);
?>
