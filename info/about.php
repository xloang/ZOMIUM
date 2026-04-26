<?php
$page_title = 'About Us - Zomium';
?>
<!DOCTYPE html>
<html>

<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/head.php'; ?>
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/header.php'; ?>
    <main class="app-main">
        <div class="container py-4">
            <section class="legal-banner mb-4">
                <div class="container d-flex justify-content-center align-items-center flex-column text-center py-5">
                    <div class="mb-3"><img src="/s/img/finnobe3llogo.png" alt="Zomium" class="auth-logo"></div>
                    <h1 class="h2 mb-3">About Us</h1>
                    <div class="small text-white-50"><i class="fas fa-chevron-down"></i></div>
                </div>
            </section>
            <div class="row g-4">
                <div class="col-lg-10 offset-lg-1">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">Who are we?</div>
                        <div class="card-body">
                            <p>Welcome to Zomium! We aim to be the best and most reliable asset hosting service out
                                there. We currently offer community-driven site features, uploads, and social systems.
                                Developers are free to do anything that conforms to our <a href="/terms">terms</a>.</p>
                            <p>In general, these are the limitations we impose on our users:</p>
                            <ul>
                                <li>You can curse, as long as it is not a slur.</li>
                                <li>Only users that are 13 years of age or older are allowed.</li>
                                <li>There is a primitive opt-out filter system and active moderation.</li>
                                <li>Games and uploads may contain more intense content than mainstream platforms allow.
                                </li>
                            </ul>
                            <p>In addition, many additions or changes to the site are driven by active users and direct
                                community feedback.</p>
                            <p class="mb-0">Generally speaking, Zomium is a <strong>not-for-profit website</strong>. Any
                                money spent on the platform goes toward keeping the site online and usable.</p>
                        </div>
                    </div>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">What is unique about Zomium?</div>
                        <div class="card-body">
                            <p>Zomium has many classic revival-style features, including community uploads, forum
                                posting, a gallery, player profiles, and collectible site content.</p>
                            <p>We also have a <a href="/forum">forum</a>, where users can talk and discuss what is going
                                on.</p>
                            <p class="mb-0">Finally, we have a full <a href="/catalog">catalog</a> and character-focused
                                site flow so you can collect and show off items familiar to you.</p>
                        </div>
                    </div>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">Why is Zomium still a thing?</div>
                        <div class="card-body">
                            <p>As stated above, we aim to be a stable asset hosting and community platform.</p>
                            <p class="mb-0">We believe most revival projects die because they never become stable enough
                                to keep users. Zomium exists to avoid that cycle.</p>
                        </div>
                    </div>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">Who works on Zomium?</div>
                        <div class="card-body">
                            <p>This project is maintained by the local site team and contributors working across
                                frontend, backend, game support, and media content.</p>
                        </div>
                    </div>
                    <div class="card mb-0">
                        <div class="card-header bg-primary text-white">How do I get started?</div>
                        <div class="card-body">
                            <p>Zomium currently uses the site's existing registration and invite rules.</p>
                            <p class="mb-0">If registration is closed or invite-limited, you will need a valid access
                                key to join.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between my-5 text-center ending gap-3">
                        <div class="left-line flex-grow-1"></div>
                        <p class="mb-0">That is all. We are <img src="/s/img/finnobe3llogo.png" alt="Zomium"
                                class="d-inline align-middle ms-1" style="height: 28px;"> Zomium.</p>
                        <div class="right-line flex-grow-1"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/footer.php'; ?>
</body>

</html>