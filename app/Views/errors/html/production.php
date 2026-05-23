<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <!-- Meta Tags -->
    <meta property="og:title" content="<?= lang('Errors.whoops') ?> | Sistem Identitas Digital">
    <meta property="og:description" content="Portal Manajemen Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai">
    <meta property="og:image" content="<?= base_url('og-image.png') ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="<?= base_url('og-image.png') ?>">

    <link rel="icon" type="image/png" href="<?= base_url('logo.png') ?>">

    <title><?= lang('Errors.whoops') ?></title>

    <style>
        <?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'debug.css')) ?>
    </style>
</head>
<body>

    <div class="container text-center">

        <h1 class="headline"><?= lang('Errors.whoops') ?></h1>

        <p class="lead"><?= lang('Errors.weHitASnag') ?></p>

    </div>

</body>

</html>
