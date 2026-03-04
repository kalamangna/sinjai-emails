<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $title ?? 'Verifikasi' ?> | Sistem Identitas Digital</title>

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
        /* Ensure no scroll */
        html, body {
            height: 100%;
            overflow: hidden;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden flex flex-col">
        <!-- Header Identity -->
        <div class="bg-slate-800 p-6 sm:p-8 text-center relative overflow-hidden shrink-0">
            <div class="absolute inset-0 opacity-10 pointer-events-none">
                <i class="fas fa-fingerprint text-white text-[100px] absolute -right-8 -bottom-8 rotate-12"></i>
            </div>
            
            <div class="relative z-10">
                <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-white/20 shadow-lg backdrop-blur-sm">
                    <i class="fas fa-user-shield text-white text-2xl"></i>
                </div>
                <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest mb-3">Sertifikat Elektronik Terverifikasi</p>
                <h1 class="text-base font-bold text-white uppercase tracking-tight leading-tight"><?= esc($email['name']) ?></h1>
                <p class="text-slate-300 text-[9px] font-bold tracking-widest mt-1.5"><?= esc($email['email']) ?></p>
            </div>
        </div>

        <!-- Verification Message -->
        <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 shrink-0">
            <p class="text-xs font-medium text-slate-600 leading-relaxed text-center">
                Dokumen ini telah ditandatangani secara elektronik oleh:
            </p>
        </div>

        <!-- Detail Data -->
        <div class="p-6 sm:p-8 space-y-5 overflow-y-auto custom-scrollbar">
            <div class="grid grid-cols-1 gap-5">
                <div>
                    <label class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">Jabatan</label>
                    <p class="text-sm font-semibold text-slate-800 uppercase leading-snug"><?= esc($email['jabatan']) ?: '-' ?></p>
                </div>

                <div>
                    <label class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">Unit Kerja</label>
                    <div class="space-y-0.5">
                        <?php if (!empty($parent_unit_kerja)): ?>
                            <p class="text-[9px] font-bold text-slate-500 uppercase leading-none"><?= esc($parent_unit_kerja['nama_unit_kerja']) ?></p>
                        <?php endif; ?>
                        <p class="text-xs font-bold text-slate-800 uppercase tracking-tight"><?= esc($unit_kerja['nama_unit_kerja'] ?? '-') ?></p>
                    </div>
                </div>

                <div>
                    <label class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">Instansi</label>
                    <p class="text-xs font-bold text-slate-800 uppercase tracking-tight">Pemerintah Kabupaten Sinjai</p>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 text-center shrink-0">
                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">
                    Terakhir diperbarui: <?= formatTanggalWaktu($email['mtime'] ?? 'now') ?>
                </p>
            </div>
        </div>

        <!-- Footer inside card to save space -->
        <div class="bg-slate-50 p-4 border-t border-slate-100 text-center shrink-0">
            <div class="flex items-center justify-center gap-2 mb-1">
                <img src="<?= base_url('logo.png') ?>" alt="Logo" class="h-4 w-auto grayscale opacity-50">
                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">
                    &copy; <?= date('Y') ?> Pemerintah Kabupaten Sinjai
                </p>
            </div>
        </div>
    </div>
</body>

</html>
