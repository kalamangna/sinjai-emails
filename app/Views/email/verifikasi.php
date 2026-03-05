<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $title ?? 'Verifikasi Akun' ?> | Sistem Identitas Digital</title>

    <!-- Meta Tags -->
    <meta property="og:title" content="<?= $title ?? 'Verifikasi Akun' ?> | Sistem Identitas Digital">
    <meta property="og:description" content="Portal Manajemen Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai">
    <meta property="og:image" content="<?= base_url('og-image.png') ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="<?= base_url('og-image.png') ?>">

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

        /* Force 100% height and disable page scroll */
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased flex items-center justify-center p-4">
    <div class="w-full max-w-lg bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden flex flex-col h-full max-h-[850px] my-auto">
        <!-- Header Identity -->
        <div class="bg-slate-800 p-8 sm:p-10 text-center relative overflow-hidden shrink-0">
            <div class="absolute inset-0 opacity-10 pointer-events-none">
                <i class="fas fa-fingerprint text-white text-[120px] absolute -right-8 -bottom-8 rotate-12"></i>
            </div>
            
            <div class="relative z-10">
                <div class="w-20 h-20 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-white/20 shadow-lg backdrop-blur-sm">
                    <i class="fas fa-user-shield text-white text-3xl"></i>
                </div>
                <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest mb-3">Sertifikat Elektronik Terverifikasi</p>
                <h1 class="text-xl font-bold text-white uppercase tracking-tight leading-tight"><?= esc($email['name']) ?></h1>
                <p class="text-slate-300 text-xs font-bold tracking-widest mt-2 lowercase"><?= esc($email['email']) ?></p>
            </div>
        </div>

        <!-- Detail Data - Scrollable if content exceeds height -->
        <div class="flex-grow p-8 sm:p-10 space-y-8 overflow-y-auto custom-scrollbar">
            <div class="grid grid-cols-1 gap-8">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Jabatan</label>
                    <p class="text-base font-semibold text-slate-800 uppercase leading-snug"><?= esc($email['jabatan']) ?: '-' ?></p>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Unit Kerja</label>
                    <div class="space-y-1">
                        <?php if (!empty($parent_unit_kerja)): ?>
                            <p class="text-[10px] font-bold text-slate-500 uppercase leading-none"><?= esc($parent_unit_kerja['nama_unit_kerja']) ?></p>
                        <?php endif; ?>
                        <p class="text-sm font-bold text-slate-800 uppercase tracking-tight"><?= esc($unit_kerja['nama_unit_kerja'] ?? '-') ?></p>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Instansi</label>
                    <p class="text-sm font-bold text-slate-800 uppercase tracking-tight">Pemerintah Kabupaten Sinjai</p>
                </div>
            </div>
        </div>

        <!-- Footer inside card -->
        <div class="bg-slate-50 p-6 border-t border-slate-100 text-center shrink-0">
            <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">
                &copy; <?= tahunSekarang() ?> Diskominfo-SP Sinjai
            </p>
        </div>
    </div>
</body>

</html>
