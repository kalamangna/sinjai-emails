<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $title ?? 'Verifikasi Akun' ?> | Sistem Identitas Digital</title>

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

<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-lg bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden flex flex-col my-auto">
        <!-- Header Identity -->
        <div class="bg-slate-800 p-8 sm:p-12 text-center relative overflow-hidden shrink-0">
            <div class="absolute inset-0 opacity-10 pointer-events-none">
                <i class="fas fa-fingerprint text-white text-[120px] absolute -right-8 -bottom-8 rotate-12"></i>
            </div>
            
            <div class="relative z-10">
                <div class="w-20 h-20 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-white/20 shadow-lg backdrop-blur-sm">
                    <i class="fas fa-user-shield text-white text-3xl"></i>
                </div>
                <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest mb-3">Sertifikat Elektronik Terverifikasi</p>
                <h1 class="text-xl font-bold text-white uppercase tracking-tight leading-tight"><?= esc($email['name']) ?></h1>
                <p class="text-slate-300 text-xs font-bold tracking-widest mt-2"><?= esc($email['email']) ?></p>
            </div>
        </div>

        <!-- Detail Data -->
        <div class="p-8 sm:p-12 space-y-8">
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
