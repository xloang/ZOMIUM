<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/core/classes/forum.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/userutils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/utilutils.php';

Forum::Boot();
$user = UserUtils::RetrieveUser();

if (!isset($_SESSION['forum_flash'])) {
    $_SESSION['forum_flash'] = null;
}

function forum_flash_set(bool $error, string $message): void {
    $_SESSION['forum_flash'] = ['error' => $error, 'message' => $message];
}

function forum_flash_take(): ?array {
    $flash = $_SESSION['forum_flash'] ?? null;
    $_SESSION['forum_flash'] = null;
    return $flash;
}

function forum_render_media(string $type, string $path): string {
    if ($path === '') {
        return '';
    }

    $safePath = htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
    if ($type === 'image') {
        return '<img src="'.$safePath.'" alt="Forum attachment" class="img-fluid rounded forum-media-preview">';
    }

    if ($type === 'video') {
        return '<video src="'.$safePath.'" controls preload="metadata" class="w-100 rounded forum-media-preview"></video>';
    }

    return '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($user === null) {
        forum_flash_set(true, 'You must be logged in to use the forum.');
        header('Location: /login');
        exit;
    }

    if (isset($_POST['forum_action']) && $_POST['forum_action'] === 'create_thread') {
        $result = Forum::CreateThread($_POST['thread_title'] ?? '', $_POST['thread_content'] ?? '', $_FILES['thread_media'] ?? null);
        if ($result['error']) {
            forum_flash_set(true, $result['reason']);
            header('Location: /forum');
            exit;
        }

        forum_flash_set(false, 'Thread created.');
        header('Location: /forum?thread='.$result['id']);
        exit;
    }

    if (isset($_POST['forum_action']) && $_POST['forum_action'] === 'create_reply') {
        $threadId = $_POST['thread_id'] ?? '';
        $result = Forum::CreateReply($threadId, $_POST['reply_content'] ?? '', $_FILES['reply_media'] ?? null);
        if ($result['error']) {
            forum_flash_set(true, $result['reason']);
            header('Location: /forum?thread='.urlencode($threadId));
            exit;
        }

        forum_flash_set(false, 'Reply posted.');
        header('Location: /forum?thread='.urlencode($threadId).'#reply-'.$result['id']);
        exit;
    }
}

$selectedThread = null;
if (isset($_GET['thread']) && $_GET['thread'] !== '') {
    $selectedThread = Forum::GetThread($_GET['thread']);
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 12;
$threads = Forum::GetThreads($page, $perPage);
$totalThreads = Forum::CountThreads();
$totalPages = max(1, (int) ceil($totalThreads / $perPage));
$flash = forum_flash_take();
$replies = $selectedThread ? Forum::GetReplies($selectedThread->id) : [];
?>
<!DOCTYPE html>
<html>
<head>
    <?php
    $page_title = $selectedThread ? htmlspecialchars($selectedThread->title, ENT_QUOTES, 'UTF-8').' - Forum' : 'Forum - Zomium';
    include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php';
    ?>
    <style>
        body {
            background: #0f0f10 !important;
            background-image: none !important;
        }

        .forum-page {
            color: #eef2f7;
        }

        .forum-topbar {
            background: #111113;
            border-bottom: 1px solid rgba(255,255,255,.08);
            padding: 3.5rem 0 2.5rem;
            text-align: center;
        }

        .forum-topbar h1 {
            color: #fff;
            font-size: clamp(2rem, 3vw, 2.75rem);
            font-weight: 700;
            margin-bottom: .65rem;
        }

        .forum-topbar p {
            color: #c4cad4;
            margin: 0;
            font-size: 1.02rem;
        }

        .forum-sidebar-panel,
        .forum-thread-list,
        .forum-thread-card,
        .forum-post-shell,
        .forum-post-side,
        .forum-post-main,
        .forum-search,
        .forum-compose-card {
            background: #18181b !important;
            border: 1px solid rgba(255,255,255,.08) !important;
            border-radius: .4rem;
        }

        .forum-sidebar-panel,
        .forum-search,
        .forum-thread-list,
        .forum-compose-card {
            padding: 1rem;
        }

        .forum-new-topic {
            display: block;
            width: 100%;
            padding: .8rem 1rem;
            text-align: center;
            border: 1px solid #2f7bda;
            color: #7db9ff;
            text-decoration: none;
            border-radius: .35rem;
            margin-bottom: 1rem;
            background: transparent;
        }

        .forum-new-topic:hover {
            text-decoration: underline;
            text-decoration-color: #6fb7ff;
            text-underline-offset: .25rem;
        }

        .forum-search .form-control,
        .forum-search .btn,
        .forum-compose-card .form-control {
            background: #2f3137 !important;
            border: 1px solid rgba(255,255,255,.08) !important;
            color: #eef2f7 !important;
            box-shadow: none !important;
        }

        .forum-search .form-control::placeholder,
        .forum-compose-card .form-control::placeholder {
            color: #9aa2ae;
        }

        .forum-sidebar-title {
            font-size: 2rem;
            color: #fff;
            margin: 1rem 0 .75rem;
            font-weight: 600;
        }

        .forum-sidebar-link,
        .forum-category-link {
            display: block;
            color: #cfd5de;
            text-decoration: none;
            margin-bottom: .65rem;
        }

        .forum-sidebar-link:hover,
        .forum-category-link:hover,
        .forum-thread-title:hover,
        .forum-meta a:hover,
        .forum-post-user:hover {
            text-decoration: underline;
            text-decoration-color: #6fb7ff;
            text-underline-offset: .25rem;
        }

        .forum-category-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .forum-category-list li {
            display: flex;
            align-items: center;
            gap: .7rem;
            margin-bottom: .7rem;
            color: #dde2ea;
        }

        .forum-dot {
            width: .82rem;
            height: .82rem;
            border-radius: 999px;
            display: inline-block;
            flex: 0 0 auto;
        }

        .forum-thread-list {
            padding: 1.2rem;
        }

        .forum-thread-card {
            padding: 1.25rem;
            color: #eef2f7;
            text-decoration: none;
            display: block;
            margin-bottom: 1rem;
        }

        .forum-thread-card:last-child {
            margin-bottom: 0;
        }

        .forum-thread-title {
            display: inline-block;
            color: #fff;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: .55rem;
            text-decoration: none;
        }

        .forum-pill {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .16rem .5rem;
            border-radius: .35rem;
            font-size: .78rem;
            font-weight: 700;
            vertical-align: middle;
            margin-left: .55rem;
            background: #6a6e75;
            color: #fff;
        }

        .forum-pill.general { background: #3bb36b; }
        .forum-pill.announcement { background: #e05b4d; }
        .forum-pill.offtopic { background: #6f46c7; }

        .forum-meta,
        .forum-replies,
        .forum-muted {
            color: #c4cad4;
        }

        .forum-score {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            height: 2rem;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: .25rem;
            margin-right: .7rem;
            color: #e5e9ef;
            font-weight: 700;
        }

        .forum-post-shell {
            overflow: hidden;
        }

        .forum-post-side {
            padding: 1.5rem 1rem;
            text-align: center;
            min-height: 100%;
            border-right: 1px solid rgba(255,255,255,.08);
        }

        .forum-post-main {
            padding: 1.5rem;
            border: 0 !important;
            border-radius: 0;
        }

        .forum-avatar,
        .forum-post-avatar {
            background: #0f0f10;
            border: 1px solid rgba(255,255,255,.08);
            object-fit: cover;
        }

        .forum-avatar {
            width: 54px;
            height: 54px;
            border-radius: .75rem;
        }

        .forum-post-avatar {
            width: 84px;
            height: 84px;
            border-radius: .9rem;
            margin: 0 auto .8rem;
            display: block;
        }

        .forum-post-user {
            color: #fff;
            font-weight: 700;
            text-decoration: none;
        }

        .forum-post-body {
            color: #eef2f7;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .forum-compose-card .card-header {
            background: transparent !important;
            border-bottom: 1px solid rgba(255,255,255,.08) !important;
            color: #fff;
        }

        .breadcrumb,
        .page-link {
            background: #18181b !important;
            color: #eef2f7 !important;
            border-color: rgba(255,255,255,.08) !important;
        }
    </style>
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/header.php'; ?>
<main class="app-main forum-page py-0">
    <section class="forum-topbar mb-4">
        <div class="container">
            <?php if ($selectedThread === null): ?>
                <h1>All Sections</h1>
                <p>This section of the forum consolidates all of the latest forum posts on the forum.</p>
            <?php else: ?>
                <h1><?= htmlspecialchars($selectedThread->title, ENT_QUOTES, 'UTF-8') ?></h1>
                <p>This section of the forum consolidates all of the latest forum posts on the forum.</p>
            <?php endif; ?>
        </div>
    </section>

    <div class="container pb-5">
        <?php if ($flash !== null): ?>
            <div class="alert <?= $flash['error'] ? 'alert-danger' : 'alert-success' ?> mb-4">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($selectedThread === null): ?>
            <div class="row g-4 align-items-start">
                <div class="col-lg-3">
                    <a href="javascript:void(0)" class="forum-new-topic" data-bs-toggle="modal" data-bs-target="#createThreadModal">+ new topic</a>

                    <div class="forum-search mb-4">
                        <form method="GET" action="/forum">
                            <div class="input-group">
                                <input class="form-control" name="search" placeholder="Search...">
                                <button class="btn btn-dark" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>

                    <div class="forum-sidebar-panel">
                        <div class="forum-sidebar-title">Categories</div>
                        <a href="/forum" class="forum-sidebar-link">All Categories</a>
                        <hr>
                        <ul class="forum-category-list">
                            <li><span class="forum-dot" style="background:#1fe36f"></span><a class="forum-category-link" href="/forum?section=2">General</a></li>
                            <li><span class="forum-dot" style="background:#6526d5"></span><a class="forum-category-link" href="/forum?section=4">Off-Topic</a></li>
                            <li><span class="forum-dot" style="background:#ff2847"></span><a class="forum-category-link" href="/forum?section=1">Announcements</a></li>
                            <li><span class="forum-dot" style="background:#27d4f8"></span><a class="forum-category-link" href="/forum?section=3">Suggestions</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="forum-thread-list">
                        <?php if (count($threads) === 0): ?>
                            <div class="forum-thread-card text-center">
                                <div class="forum-muted">No threads found.</div>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($threads as $thread): ?>
                            <?php
                            $threadReplies = Forum::GetReplies($thread->id);
                            $replyCount = count($threadReplies);
                            $lastReplyPoster = $replyCount > 0 ? $threadReplies[$replyCount - 1]->poster : $thread->poster;
                            $lastReplyAt = $replyCount > 0 ? $threadReplies[$replyCount - 1]->createdAt : $thread->createdAt;
                            ?>
                            <a href="/forum?thread=<?= urlencode($thread->id) ?>" class="forum-thread-card">
                                <div class="d-flex gap-3 align-items-start">
                                    <img src="/thumbs/headshot?id=<?= $thread->poster->id ?>&sxy=100" alt="<?= htmlspecialchars($thread->poster->name, ENT_QUOTES, 'UTF-8') ?>" class="forum-avatar">
                                    <div class="flex-grow-1">
                                        <div>
                                            <span class="forum-thread-title"><?= htmlspecialchars($thread->title, ENT_QUOTES, 'UTF-8') ?></span>
                                            <span class="forum-pill general">General</span>
                                        </div>
                                        <div class="forum-meta mb-1">Posted by <?= htmlspecialchars($thread->poster->name, ENT_QUOTES, 'UTF-8') ?> <?= UtilUtils::GetTimeAgo($thread->createdAt) ?></div>
                                        <div class="forum-meta mb-2">Last reply by <?= htmlspecialchars($lastReplyPoster->name, ENT_QUOTES, 'UTF-8') ?> <?= UtilUtils::GetTimeAgo($lastReplyAt) ?></div>
                                        <div class="forum-replies"><span class="forum-score"><?= $replyCount > 0 ? $replyCount : 0 ?></span><?= $replyCount ?> repl<?= $replyCount === 1 ? 'y' : 'ies' ?></div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <nav class="mt-4 d-flex justify-content-center">
                            <ul class="pagination">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link mx-1 rounded" href="/forum?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>

            <div class="modal fade" id="createThreadModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content forum-compose-card">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold text-white">Create New Topic</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                       
                            <?php if ($user === null): ?> <!-- yes. -->
                                
                            <?php else: ?>
                                <form method="POST" enctype="multipart/form-data" class="d-grid gap-3">
                                    <input type="hidden" name="forum_action" value="create_thread">
                                    <div>
                                        <label class="form-label small fw-bold text-uppercase" for="thread_title">Title</label>
                                        <input class="form-control" id="thread_title" name="thread_title" maxlength="120" required placeholder="What's on your mind?">
                                    </div>
                                    <div>
                                        <label class="form-label small fw-bold text-uppercase" for="thread_content">Message</label>
                                        <textarea class="form-control" id="thread_content" name="thread_content" rows="6" maxlength="5000" required placeholder="Describe your topic in detail..."></textarea>
                                    </div>
                                    <div>
                                        <label class="form-label small fw-bold text-uppercase" for="thread_media">Upload media</label>
                                        <input class="form-control" id="thread_media" name="thread_media" type="file" accept="image/*,video/*">
                                    </div>
                                    <button class="btn btn-primary py-2 fw-bold mt-2" type="submit">Post</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <div class="col-12">
                    <nav class="mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="/forum">Forum Home</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($selectedThread->title, ENT_QUOTES, 'UTF-8') ?></li>
                        </ol>
                    </nav>
                </div>

                <div class="col-12">
                    <div class="forum-post-shell row g-0 mb-4">
                        <div class="col-md-3 col-lg-2 forum-post-side">
                            <a href="/users/<?= $selectedThread->poster->id ?>/profile">
                                <img src="/thumbs/headshot?id=<?= $selectedThread->poster->id ?>&sxy=100" alt="<?= htmlspecialchars($selectedThread->poster->name, ENT_QUOTES, 'UTF-8') ?>" class="forum-post-avatar">
                            </a>
                            <a href="/users/<?= $selectedThread->poster->id ?>/profile" class="forum-post-user d-inline-block mb-2">
                                <?= htmlspecialchars($selectedThread->poster->name, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                            <?php if($selectedThread->poster->id == 1): ?>
                                <div><span class="badge bg-danger rounded-pill small px-3 mb-2">Admin</span></div>
                            <?php endif; ?>
                            <div class="forum-muted small">Posts: 0</div>
                        </div>
                        <div class="col-md-9 col-lg-10 forum-post-main">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="forum-muted">Posted <?= UtilUtils::GetTimeAgo($selectedThread->createdAt) ?></div>
                                <div class="forum-replies"><span class="forum-score">0</span>thread score</div>
                            </div>
                            <div class="forum-post-body"><?= nl2br($selectedThread->content) ?></div>
                            <?php if ($selectedThread->mediaPath !== ''): ?>
                                <div class="mt-4">
                                    <?= forum_render_media($selectedThread->mediaType, $selectedThread->mediaPath) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="h5 fw-bold mb-0 text-white">Replies (<?= count($replies) ?>)</h3>
                        <a href="#reply-form" class="btn btn-primary btn-sm px-3">Post Reply</a>
                    </div>
                </div>

                <div class="col-12 d-grid gap-3 mb-4">
                    <?php foreach ($replies as $reply): ?>
                        <div class="forum-post-shell row g-0" id="reply-<?= htmlspecialchars($reply->id, ENT_QUOTES, 'UTF-8') ?>">
                            <div class="col-md-3 col-lg-2 forum-post-side py-3">
                                <img src="/thumbs/headshot?id=<?= $reply->poster->id ?>&sxy=100" alt="<?= htmlspecialchars($reply->poster->name, ENT_QUOTES, 'UTF-8') ?>" class="forum-post-avatar" style="width:64px;height:64px;">
                                <a href="/users/<?= $reply->poster->id ?>/profile" class="forum-post-user small">
                                    <?= htmlspecialchars($reply->poster->name, ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </div>
                            <div class="col-md-9 col-lg-10 forum-post-main">
                                <div class="forum-muted small mb-2"><?= UtilUtils::GetTimeAgo($reply->createdAt) ?></div>
                                <div class="forum-post-body"><?= nl2br($reply->content) ?></div>
                                <?php if ($reply->mediaPath !== ''): ?>
                                    <div class="mt-3">
                                        <?= forum_render_media($reply->mediaType, $reply->mediaPath) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="col-12">
                    <div class="forum-compose-card" id="reply-form">
                        <div class="card-header py-3">
                            <h4 class="h6 mb-0 fw-bold">Post a Reply</h4>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($user === null): ?>
                                <div class="text-center py-2">
                                    <p class="forum-muted mb-3">Login to participate in the discussion.</p>
                                    <a href="/login" class="btn btn-primary">Login</a>
                                </div>
                            <?php else: ?>
                                <form method="POST" enctype="multipart/form-data" class="d-grid gap-3">
                                    <input type="hidden" name="forum_action" value="create_reply">
                                    <input type="hidden" name="thread_id" value="<?= htmlspecialchars($selectedThread->id, ENT_QUOTES, 'UTF-8') ?>">
                                    <textarea class="form-control" id="reply_content" name="reply_content" rows="5" maxlength="5000" required placeholder="Write your response..."></textarea>
                                    <div class="d-flex justify-content-between align-items-center gap-3 flex-column flex-md-row">
                                        <input class="form-control form-control-sm" id="reply_media" name="reply_media" type="file" accept="image/*,video/*">
                                        <button class="btn btn-primary px-4 fw-bold" type="submit">Send Reply</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/footer.php'; ?>
</body>
</html>
