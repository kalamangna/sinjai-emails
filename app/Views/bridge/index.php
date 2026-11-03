<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= esc($title ?? 'Bridge TTE') ?> | Sistem Identitas Digital</title>
    <link rel="icon" type="image/png" href="<?= esc($logoUrl) ?>">
    <link href="<?= esc($cssUrl) ?>" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-700 antialiased min-h-screen flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-lg p-6 max-w-xs w-full shadow-sm text-center">
        <img src="<?= esc($logoUrl) ?>" alt="Logo Kabupaten Sinjai" class="w-12 h-12 object-contain mx-auto mb-3">
        <h1 class="text-sm font-bold uppercase tracking-wider text-slate-800 mb-4"><?= esc($title ?? 'Bridge TTE') ?></h1>
        <a href="<?= esc($docsUrl) ?>" class="w-full h-10 btn btn-outline">
            Panduan Integrasi
        </a>
    </div>
</body>
</html>
