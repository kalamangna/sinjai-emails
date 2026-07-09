<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="robots" content="<?= $meta_robots ?? 'noindex, follow' ?>">
    <title><?= $title ?? 'Verifikasi Akun' ?> | Sistem Identitas Digital</title>

    <meta name="description" content="<?= $meta_description ?? 'Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai' ?>">
    <link rel="canonical" href="<?= current_url() ?>">

    <!-- Meta Tags -->
    <meta property="og:site_name" content="Sistem Identitas Digital Sinjai">
    <meta property="og:title" content="<?= $title ?? 'Verifikasi Akun' ?> | Sistem Identitas Digital">
    <meta property="og:description" content="<?= $meta_description ?? 'Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai' ?>">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:image" content="<?= $meta_image ?? base_url('meta.png') ?>">
    <meta property="og:type" content="<?= $meta_type ?? 'website' ?>">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title ?? 'Verifikasi Akun' ?> | Sistem Identitas Digital">
    <meta name="twitter:description" content="<?= $meta_description ?? 'Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai' ?>">
    <meta name="twitter:image" content="<?= $meta_image ?? base_url('meta.png') ?>">

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
        <!-- Header Identity -->
        <div class="bg-slate-800 p-8 text-center relative overflow-hidden shrink-0">
            <div class="absolute inset-0 opacity-10 pointer-events-none">
                <i class="fas fa-fingerprint text-white text-[120px] absolute -right-8 -bottom-8 rotate-12"></i>
            </div>
            
            <div class="relative z-10 text-white">
                <img src="<?= base_url('logo.png') ?>" alt="Logo" class="w-16 h-16 object-contain mx-auto mb-4 drop-shadow-md">
                <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest mb-1">Sistem Identitas Digital</p>
                <h1 class="text-lg font-bold uppercase tracking-tight leading-tight">Verifikasi Akun Pegawai</h1>
            </div>
        </div>

        <!-- Detail Data -->
        <div class="p-6 sm:p-8 space-y-6">
            <div class="flex items-start gap-3 p-4 bg-emerald-50 border border-emerald-100 rounded-xl text-left">
                <i class="fas fa-check-circle text-emerald-600 text-lg shrink-0 mt-0.5"></i>
                <div>
                    <h2 class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Identitas Terverifikasi</h2>
                    <p class="text-[10px] text-emerald-700 leading-normal mt-0.5">Akun ini terdaftar resmi and memiliki Sertifikat Elektronik aktif di Pemerintah Kabupaten Sinjai.</p>
                </div>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                <div class="py-3 flex justify-between gap-4">
                    <span class="text-slate-400 font-medium uppercase tracking-wider text-[10px]">Nama</span>
                    <span class="font-bold text-slate-800 uppercase text-right"><?= esc($email['name']) ?></span>
                </div>
                <div class="py-3 flex justify-between gap-4">
                    <span class="text-slate-400 font-medium uppercase tracking-wider text-[10px]">E-mail</span>
                    <span class="font-bold text-slate-700 lowercase text-right"><?= esc($email['email']) ?></span>
                </div>
                <div class="py-3 flex justify-between gap-4">
                    <span class="text-slate-400 font-medium uppercase tracking-wider text-[10px]">Jabatan</span>
                    <span class="font-semibold text-slate-800 uppercase text-right"><?= esc($email['jabatan']) ?: '-' ?></span>
                </div>
                <div class="py-3 flex justify-between gap-4">
                    <span class="text-slate-400 font-medium uppercase tracking-wider text-[10px]">Unit Kerja</span>
                    <span class="font-semibold text-slate-800 uppercase text-right text-wrap">
                        <?php if (!empty($parent_unit_kerja)): ?>
                            <span class="block text-[9px] text-slate-400 font-bold"><?= esc($parent_unit_kerja['nama_unit_kerja']) ?></span>
                        <?php endif; ?>
                        <?= esc($unit_kerja['nama_unit_kerja'] ?? '-') ?>
                    </span>
                </div>
                <div class="py-3 flex justify-between gap-4">
                    <span class="text-slate-400 font-medium uppercase tracking-wider text-[10px]">Instansi</span>
                    <span class="font-bold text-slate-800 uppercase text-right">Pemerintah Kabupaten Sinjai</span>
                </div>
            </div>
        </div>

        <!-- Footer inside card -->
        <div class="bg-slate-50 p-6 border-t border-slate-100 text-center shrink-0">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                &copy; <?= tahunSekarang() ?> Diskominfo-SP Sinjai
            </p>
        </div>
    </div>
</body>

</html>
