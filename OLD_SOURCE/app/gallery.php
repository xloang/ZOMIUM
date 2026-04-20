<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/core/classes/gallery.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/userutils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/core/utilities/utilutils.php';

Gallery::Boot();
$user = UserUtils::RetrieveUser();

if (!isset($_SESSION['gallery_flash'])) {
    $_SESSION['gallery_flash'] = null;
}

$admin = UserUtils::RetrieveUser(); // TODO: improve this page and make it public soon
if ($admin == null || !$admin->IsAdmin()) {
    die(header('Location: /login'));
}

function gallery_flash_set(bool $error, string $message): void {
    $_SESSION['gallery_flash'] = ['error' => $error, 'message' => $message];
}

function gallery_flash_take(): ?array {
    $flash = $_SESSION['gallery_flash'] ?? null;
    $_SESSION['gallery_flash'] = null;
    return $flash;
}

function gallery_render_media(string $type, string $path): string {
    $safePath = htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
    if ($type === 'image') {
        return '<img src="'.$safePath.'" alt="Gallery item" class="gallery-card-media">';
    }

    return '<video src="'.$safePath.'" muted playsinline preload="metadata" class="gallery-card-media gallery-card-video"></video>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($user === null) {
        gallery_flash_set(true, 'You must be logged in to upload.');
        header('Location: /login');
        exit;
    }

    $result = Gallery::Upload($_POST['gallery_title'] ?? '', $_POST['gallery_description'] ?? '', $_FILES['gallery_media'] ?? []);
    if ($result['error']) {
        gallery_flash_set(true, $result['reason']);
        header('Location: /gallery');
        exit;
    }

    gallery_flash_set(false, 'Upload completed.');
    header('Location: /gallery/'.urlencode($result['id']));
    exit;
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 12;
$items = Gallery::GetItems($page, $perPage);
$totalItems = Gallery::CountItems();
$totalPages = max(1, (int) ceil($totalItems / $perPage));
$flash = gallery_flash_take();
?>
<!DOCTYPE html>
<html>
<head>
    <?php
    $page_title = 'Gallery - Zomium';
    include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php';
    ?>
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/header.php'; ?>
<main class="app-main">
    <div class="container py-4">
        <div class="hero-panel mb-4">
            <div>
                <div class="section-title mb-2">Media</div>
                <h1 class="display-6 fw-bold mb-2">Gallery</h1>
                <p class="mb-0 text-white-50">W.I.P PLEASE DONT UPLOAD STUFF HERE</p>
            </div>
        </div>

        <?php if ($flash !== null): ?>
        <div class="alert <?= $flash['error'] ? 'alert-danger' : 'alert-success' ?> mb-4">
            <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <div class="row g-4 align-items-start">
            <div class="col-lg-4">
                <div class="card sticky-lg-top" style="top: 6rem;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0">Upload Image / Video</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($user === null): ?>
                        <p class="text-secondary mb-3">Yukleme yapmak icin giris yapman gerekiyor.</p>
                        <a class="btn btn-primary" href="/login">Login</a>
                        <?php else: ?>
                        <form method="POST" enctype="multipart/form-data" class="d-grid gap-3">
                            <div>
                                <label class="form-label" for="gallery_title">Title</label>
                                <input class="form-control" id="gallery_title" name="gallery_title" maxlength="120" required>
                            </div>
                            <div>
                                <label class="form-label" for="gallery_description">Description</label>
                                <textarea class="form-control" id="gallery_description" name="gallery_description" rows="5" maxlength="2000" required></textarea>
                            </div>
                            <div>
                                <label class="form-label" for="gallery_media">Image or Video</label>
                                <input class="form-control" id="gallery_media" name="gallery_media" type="file" accept="image/*,video/*" required>
                                <div class="small mt-2 text-secondary">Images: 8 MB. Videos: 64 MB.</div>
                            </div>
                            <button class="btn btn-primary" type="submit">Upload</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h4 mb-1">Latest Uploads</h2>
                        <div class="small text-secondary"><?= $totalItems ?> total uploads</div>
                    </div>
                </div>

                <div class="row g-4">
                    <?php if (count($items) === 0): ?>
                    <div class="col-12">
                        <div class="card"><div class="card-body">No uploads yet.</div></div>
                    </div>
                    <?php endif; ?>

                    <?php foreach ($items as $item): ?>
                    <div class="col-12 col-md-6">
                        <a class="gallery-card-link text-reset text-decoration-none d-block" href="/gallery/<?= urlencode($item->id) ?>">
                            <article class="card h-100 gallery-card">
                                <div class="gallery-card-frame">
                                    <?= gallery_render_media($item->mediaType, $item->mediaPath) ?>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h3 class="h5 mb-2"><?= $item->title ?></h3>
                                    <p class="text-secondary mb-3 flex-grow-1"><?= nl2br($item->description) ?></p>
                                    <div class="d-flex align-items-center gap-2 small text-secondary">
                                        <img src="/thumbs/headshot?id=<?= $item->poster->id ?>&sxy=100" alt="<?= htmlspecialchars($item->poster->name, ENT_QUOTES, 'UTF-8') ?>" class="gallery-author-avatar rounded">
                                        <span><span><?= htmlspecialchars($item->poster->name, ENT_QUOTES, 'UTF-8') ?></span> � <?= UtilUtils::GetTimeAgo($item->createdAt) ?></span>
                                    </div>
                                </div>
                            </article>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="/gallery?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/footer.php'; ?>
</body>
</html>
