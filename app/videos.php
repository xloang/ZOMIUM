<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/utilities/userutils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/classes/gallery.php';

$user = UserUtils::RetrieveUser();

if ($user == null) {
    die(header('Location: /login'));
}

$settings = UserSettings::Get($user);

Gallery::Boot();





$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 24;
$videos = Gallery::GetVideoItems($page, $perPage);
$totalVideos = Gallery::CountVideoItems();
$totalPages = max(1, (int) ceil($totalVideos / $perPage));

function video_card_media(string $path): string {
    $safePath = htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
    return '<video src="'.$safePath.'" muted playsinline preload="metadata" class="video-card-media"></video>';
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php
    $page_title = 'Videos - Zomium';
    $page_styles = ['/css/new/videos.css?v=1'];
    include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php';
    ?>
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/header.php'; ?>
<main class="app-main videos-main">
    <div class="container videos-container">
        <h1 class="videos-title">Videos [W.I.P] </h1>

        <?php if ($totalVideos === 0): ?>
        <div class="videos-empty">No videos uploaded yet.</div>
        <?php else: ?>
        <section class="videos-grid" aria-label="Uploaded videos">
            <?php foreach ($videos as $item): ?>
            <?php $posterName = $item->poster !== null ? $item->poster->name : 'Unknown User'; ?>
            <a class="video-card" href="<?= htmlspecialchars($item->mediaPath, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                <div class="video-card-frame">
                    <?= video_card_media($item->mediaPath) ?>
                </div>
                <div class="video-card-meta">
                    <h2 class="video-card-title"><?= htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') ?></h2>
                    <div class="video-card-author"><?= htmlspecialchars($posterName, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </section>

        <?php if ($totalPages > 1): ?>
        <nav class="mt-4" aria-label="Videos pages">
            <ul class="pagination videos-pagination mb-0">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="/videos?page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/footer.php'; ?>
</body>
</html>
