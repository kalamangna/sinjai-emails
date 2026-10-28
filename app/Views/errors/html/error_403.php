<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>403 - Akses Ditolak | Sistem Identitas Digital</title>

    <!-- Meta Tags -->
    <meta property="og:title" content="403 - Akses Ditolak | Sistem Identitas Digital">
    <meta property="og:description" content="Portal Manajemen Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai">
    <meta property="og:image" content="<?= base_url('meta.png') ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="<?= base_url('meta.png') ?>">

    <link rel="icon" type="image/png" href="<?= base_url('logo.png') ?>">

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
    <div class="flex-grow flex items-center justify-center p-6">
        <div class="bg-white p-8 md:p-12 rounded-2xl shadow-xl flex flex-col items-center gap-6 max-w-md w-full border border-slate-200 text-center">
            <div class="w-16 h-16 rounded-full bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600">
                <i class="fas fa-shield-halved text-2xl"></i>
            </div>
            <div class="space-y-2">
                <h1 class="text-xl md:text-2xl font-bold text-slate-800 uppercase tracking-tight">403 - Akses Ditolak</h1>
                <p class="text-xs md:text-sm text-slate-600 leading-relaxed font-medium">
                    <?php if (ENVIRONMENT !== 'production') : ?>
                        <?= nl2br(esc($message ?? 'Tindakan yang Anda minta tidak diizinkan.')) ?>
                    <?php else : ?>
                        Sesi validasi keamanan kedaluwarsa atau tindakan tidak diizinkan. Silakan login kembali.
                    <?php endif; ?>
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 w-full mt-2">
                <a href="<?= site_url('login') ?>" class="btn btn-solid w-full justify-center">
                    Masuk Kembali
                </a>
                <a href="<?= site_url('/') ?>" class="btn btn-outline w-full justify-center">
                    Beranda
                </a>
            </div>
        </div>
    </div>
    <footer class="py-6 px-6 text-center">
        <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">
            &copy; <?= date('Y') ?> Diskominfo-SP Sinjai
        </p>
    </footer>
</body>

</html>
