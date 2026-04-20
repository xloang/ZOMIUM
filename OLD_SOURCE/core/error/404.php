<?php ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $page_title = '404 - Zomium';
    include $_SERVER['DOCUMENT_ROOT'].'/core/ui/head.php';
    ?>
</head>

<body>
<?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/header.php'; ?>

<main class="app-main">
    <div class="container">
        <div class="card border-0">
            <div class="card-body p-5 text-center">

                <?php
                $images = [
                    "/images/1.png",
                    "/images/2.png",
                    "/images/3.png",
                    "/images/4.png",
                    "/images/5.png",
                    "/images/5nobe2.png",
                    "/images/6.png",
                    "/images/error.png"
                ];

                $playMusic = (rand(1, 750) === 1); // %0.1 chance yes actually
                $randomImage = $images[array_rand($images)];
                ?>

                <?php if ($playMusic): ?>
                    <audio id="music" autoplay muted>
                        <source src="/images/gyrozeppeli.mp3" type="audio/mpeg">
                    </audio>

                    <script>
                        setTimeout(() => {
                            const audio = document.getElementById("music");
                            audio.muted = false;
                            audio.play().catch(() => {});
                        }, 1000);
                    </script>
                <?php endif; ?>

                <img src="<?= $randomImage ?>" alt="Error" style="max-width:180px" class="mb-4">

                <h1 class="h2 mb-3">Page not found</h1>
                <p class="text-secondary mb-4">
                    This page does not exist.
                </p>

                <?php if ($playMusic): ?>
                    <div class="mb-3 text-warning">
                        YOU MADE IT CONGRATS !!!
                    </div>

                    <audio controls style="width:100%; max-width:300px;">
                        <source src="/images/gyrozeppeli.mp3" type="audio/mpeg">
                    </audio>
                <?php endif; ?>

                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <button class="btn btn-outline-light" onclick="window.history.back();">Back</button>
                    <a class="btn btn-primary" href="/my/home">Home</a>
                </div>

            </div>
        </div>
    </div>
</main>

<?php include $_SERVER['DOCUMENT_ROOT'].'/core/ui/footer.php'; ?>
</body>
</html>