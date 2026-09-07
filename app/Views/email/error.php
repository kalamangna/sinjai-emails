<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="robots" content="noindex, follow">
    <title><?= $title ?? 'Verifikasi Akun' ?> | Sistem Identitas Digital</title>

    <meta name="description" content="Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai">
    <link rel="canonical" href="<?= current_url() ?>">

    <!-- Meta Tags -->
    <meta property="og:site_name" content="Sistem Identitas Digital Sinjai">
    <meta property="og:title" content="<?= $title ?? 'Verifikasi Akun' ?> | Sistem Identitas Digital">
    <meta property="og:description" content="Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:image" content="<?= base_url('meta.png') ?>">
    <meta property="og:type" content="website">

    <link rel="icon" type="image/png" href="<?= base_url('logo.png') ?>">
    <link href="<?= base_url('css/output.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-xl bg-white border border-slate-200 rounded-2xl shadow-xl shadow-slate-200/50 overflow-hidden flex flex-col my-auto">
        <!-- Header Card -->
        <div class="bg-slate-800 p-8 text-center relative overflow-hidden shrink-0">
            <div class="absolute inset-0 opacity-10 pointer-events-none">
                <i class="fas fa-exclamation-triangle text-white text-[120px] absolute -right-8 -bottom-8 rotate-12"></i>
            </div>
            
            <div class="relative z-10 text-white">
                <img src="<?= base_url('logo.png') ?>" alt="Logo" class="w-16 h-16 object-contain mx-auto mb-4 drop-shadow-md">
                <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest mb-1">Sistem Identitas Digital</p>
                <h1 class="text-lg font-bold uppercase tracking-tight leading-tight">Verifikasi Akun</h1>
            </div>
        </div>
        
        <!-- Content Card -->
        <div class="p-6 sm:p-8 space-y-6 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-red-50 border border-red-150 rounded-2xl text-red-650 shadow-sm">
                <i class="fas fa-bug text-2xl"></i>
            </div>
            
            <div class="space-y-2">
                <h2 class="text-xs font-bold text-red-700 uppercase tracking-widest">Gagal Melakukan Verifikasi</h2>
                <p class="text-sm font-semibold text-slate-700 leading-relaxed uppercase tracking-tight">
                    <?= esc($error ?? 'Data verifikasi tidak valid atau link kedaluwarsa.') ?>
                </p>
            </div>
            
            <p class="text-[11px] text-slate-500 leading-relaxed max-w-xs mx-auto">Tautan verifikasi yang Anda buka mungkin sudah tidak aktif, salah ketik, atau telah dihapus oleh administrator sistem.</p>
            
            <div class="pt-4 border-t border-slate-100">
                <a href="javascript:void(0);" onclick="history.back();" class="w-full btn btn-solid !bg-slate-850 hover:!bg-slate-900 !text-white !py-2.5 rounded-lg flex items-center justify-center gap-2 no-underline text-xs font-bold uppercase tracking-wider">
                    <i class="fas fa-arrow-left text-xs text-white/80"></i> Kembali
                </a>
            </div>
        </div>
        
        <!-- Footer inside card -->
        <div class="bg-slate-50 p-4 border-t border-slate-100 text-center shrink-0">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                &copy; <?= tahunSekarang() ?> Diskominfo-SP Sinjai
            </p>
        </div>
    </div>
</body>

</html>