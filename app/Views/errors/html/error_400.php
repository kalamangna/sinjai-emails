<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>400 - Permintaan Buruk | Sistem Identitas Digital</title>

    <!-- Tailwind CSS (Local Build) -->
    <link href="/css/output.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased font-inter flex flex-col min-h-screen">
    <div class="flex-grow flex items-center justify-center">
        <div class="bg-white p-12 rounded-2xl shadow-2xl flex flex-col items-center gap-6 max-w-md w-full border border-slate-200 text-center">
            <div class="w-16 h-16 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600">
                <i class="fas fa-exclamation-circle text-3xl"></i>
            </div>
            <div class="space-y-1">
                <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">400 - Permintaan Buruk</h1>
                <p class="text-sm text-slate-600">
                    <?php if (ENVIRONMENT !== 'production') : ?>
                        <?= nl2br(esc($message)) ?>
                    <?php else : ?>
                        <?= lang('Errors.sorryBadRequest') ?>
                    <?php endif; ?>
                </p>
            </div>
            <a href="/" class="btn btn-outline no-underline mt-4">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
    <footer class="py-6 px-6 text-center">
        <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">
            &copy; <?= date('Y') ?> Diskominfo-SP Sinjai
        </p>
    </footer>
</body>

</html>
