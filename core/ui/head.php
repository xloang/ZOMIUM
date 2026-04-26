<?php
if (!isset($page_title)) {
    $page_title = 'Zomium';
}

if (!isset($page_styles) || !is_array($page_styles)) {
    $page_styles = [];
}

if (!isset($page_scripts) || !is_array($page_scripts)) {
    $page_scripts = [];
}

define('APP_HEAD_INCLUDED', true);
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></title>
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="stylesheet" href="/css/new/app.css?v=8">
<?php foreach ($page_styles as $style): ?>
<link rel="stylesheet" href="<?= htmlspecialchars($style, ENT_QUOTES, 'UTF-8') ?>">
<?php endforeach; ?>
<script src="/js/core/jquery.js"></script>
<script src="/js/main.js?t=1771413807"></script>
<?php foreach ($page_scripts as $script): ?>
<script src="<?= htmlspecialchars($script, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endforeach; ?>