<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= htmlspecialchars($this->title ?? "Zomium", ENT_QUOTES, 'UTF-8') ?></title>
	<link rel="icon" type="image/x-icon" href="/favicon.ico">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
	<link rel="stylesheet" href="/public/css/new/app.css?v=8">
	<?php foreach ($this->scripts as $script): ?>
	<script src="<?= htmlspecialchars($script, ENT_QUOTES, 'UTF-8') ?>"></script>
	<?php endforeach ?>
	<?php foreach ($this->stylesheets as $stylesheet): ?>
	<link rel="stylesheet" href="<?= htmlspecialchars($stylesheet, ENT_QUOTES, 'UTF-8') ?>">
	<?php endforeach ?>
	<?php foreach ($this->metas as $meta): ?>
	<meta property="<?= htmlspecialchars($meta['type'], ENT_QUOTES, 'UTF-8') ?>" content="<?= htmlspecialchars($meta['contents'], ENT_QUOTES, 'UTF-8') ?>">
	<?php endforeach ?>
</head>
<body <?= $this->settings->nightbg ? "night" : "" ?>>
