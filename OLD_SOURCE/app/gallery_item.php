<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/core/classes/gallery.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/utilutils.php';

Gallery::Boot();
$itemId = trim($_GET['id'] ?? '');
$item = $itemId !== '' ? Gallery::GetItem($itemId) : null;
if ($item == null) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'].'/core/error/404.php';
    exit;
}

$recent = array_values(array_filter(Gallery::GetItems(1, 6), fn($recentItem) => $recentItem->id !== $item->id));

$user = UserUtils::RetrieveUser();

if (!isset($_SESSION['gallery_flash'])) {
    $_SESSION['gallery_flash'] = null;
}

$admin = UserUtils::RetrieveUser(); // TODO: improve this page and make it public soon
if ($admin == null || !$admin->IsAdmin()) {
    die(header('Location: /login'));
}
function gallery_detail_media(string $type, string $path): string {
    $safePath = htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
    if ($type === 'image') {
        return '<img src="'.$safePath.'" alt="Gallery item" class="gallery-detail-media">';
    }
    return '<video src="'.$safePath.'" controls preload="metadata" playsinline class="gallery-detail-media"></video>';
} // TODO: use this page for videos.php lol
?>
<!DOCTYPE html>
<html>
<head>
    <?php
    $page_title = $item->title.' - Gallery';
    include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php';
    ?>
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/header.php'; ?>
<main class="app-main">
    <div class="container py-4">
        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <article class="card overflow-hidden">
                    <div class="gallery-detail-frame">
                        <?= gallery_detail_media($item->mediaType, $item->mediaPath) ?>
                    </div>
                    <div class="card-body p-4">
                        <div class="section-title mb-2">Gallery Post</div>
                        <h1 class="display-6 fw-bold mb-3"><?= $item->title ?></h1>
                        <div class="d-flex align-items-center gap-2 small text-secondary mb-4">
                            <?php if ($item->poster !== null): ?>
                            <img src="/thumbs/headshot?id=<?= $item->poster->id ?>&sxy=100" alt="<?= htmlspecialchars($item->poster->name, ENT_QUOTES, 'UTF-8') ?>" class="gallery-author-avatar rounded">
                            <span><a href="/users/<?= $item->poster->id ?>/profile"><?= htmlspecialchars($item->poster->name, ENT_QUOTES, 'UTF-8') ?></a> &bull; <?= UtilUtils::GetTimeAgo($item->createdAt) ?></span>
                            <?php else: ?>
                            <img src="/thumbs/headshot?id=0&sxy=100" alt="Unknown" class="gallery-author-avatar rounded">
                            <span>Unknown User &bull; <?= UtilUtils::GetTimeAgo($item->createdAt) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="forum-post-body"><?= nl2br($item->description) ?></div>
                    </div>
                </article>
            </div>
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Post Info</h2>
                        <div class="small mb-2"><strong>Type:</strong> <?= htmlspecialchars($item->mediaType, ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="small mb-2"><strong>Item ID:</strong> <code><?= htmlspecialchars($item->id, ENT_QUOTES, 'UTF-8') ?></code></div>
                        <div class="small"><strong>Created:</strong> <?= $item->createdAt?->format('Y-m-d H:i:s') ?></div>
                        <div class="small"><strong>Information:</strong> This page is work in progress lol</div> <!-- TODO: Improve this page -->
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h2 class="h5 mb-3">More Uploads</h2>
                        <div class="d-grid gap-3">
                            <?php foreach ($recent as $recentItem): ?>
                            <a class="text-reset text-decoration-none" href="/gallery/<?= urlencode($recentItem->id) ?>">
                                <div class="card bg-transparent border">
                                    <div class="card-body py-3">
                                        <div class="fw-semibold mb-1"><?= $recentItem->title ?></div>
                                        <div class="small text-secondary"><?= htmlspecialchars($recentItem->poster !== null ? $recentItem->poster->name : 'Unknown User', ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/footer.php'; ?>
</body>
</html>
