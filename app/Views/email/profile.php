<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">
    <div class="flex-grow flex items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden">
            <!-- Header Identity -->
            <div class="bg-slate-800 p-8 text-center relative overflow-hidden">
                <div class="absolute inset-0 opacity-10 pointer-events-none">
                    <i class="fas fa-fingerprint text-white text-[120px] absolute -right-10 -bottom-10 rotate-12"></i>
                </div>
                
                <div class="relative z-10">
                    <div class="w-20 h-20 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-white/20 shadow-lg backdrop-blur-sm">
                        <i class="fas fa-user-shield text-white text-3xl"></i>
                    </div>
                    <h1 class="text-lg font-bold text-white uppercase tracking-tight leading-tight"><?= esc($email['name']) ?></h1>
                    <p class="text-slate-300 text-[10px] font-bold uppercase tracking-widest mt-2"><?= esc($email['email']) ?></p>
                </div>
            </div>

            <!-- Verification Status -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-center gap-3">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Status TTE:</span>
                <?php
                $st = $email['bsre_status'] ?? '';
                $colorClass = 'bg-slate-100 text-slate-700 border-transparent';
                $statusLabel = $st ?: 'NOT_SYNCED';

                if ($st === 'ISSUE') {
                    $colorClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                } elseif (in_array($st, ['EXPIRED', 'REVOKE', 'SUSPEND'])) {
                    $colorClass = 'bg-red-100 text-red-700 border-red-200';
                } elseif (in_array($st, ['WAITING_FOR_VERIFICATION', 'RENEW', 'NO_CERTIFICATE'])) {
                    $colorClass = 'bg-amber-50 text-amber-500 border-amber-200';
                } elseif ($st === 'NEW') {
                    $colorClass = 'bg-blue-100 text-slate-700 border-blue-200';
                }
                ?>
                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase border <?= $colorClass ?> shadow-sm">
                    <?= $statusLabel ?>
                </span>
            </div>

            <!-- Detail Data -->
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">NIP / NIK</label>
                        <p class="text-sm font-semibold text-slate-800 font-mono"><?= esc($email['nip']) ?: esc($email['nik']) ?: '-' ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Jabatan</label>
                        <p class="text-sm font-semibold text-slate-800 uppercase leading-snug"><?= esc($email['jabatan']) ?: '-' ?></p>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Unit Kerja</label>
                        <div class="space-y-1">
                            <?php if (!empty($parent_unit_kerja)): ?>
                                <p class="text-[10px] font-bold text-slate-500 uppercase leading-none"><?= esc($parent_unit_kerja['nama_unit_kerja']) ?></p>
                            <?php endif; ?>
                            <p class="text-xs font-bold text-slate-800 uppercase tracking-tight"><?= esc($unit_kerja['nama_unit_kerja'] ?? '-') ?></p>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 text-center">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">
                        Terakhir diperbarui: <?= formatTanggalWaktu($email['mtime'] ?? 'now') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-8 px-6 text-center">
        <div class="flex items-center justify-center mb-4">
            <img src="<?= base_url('logo.png') ?>" alt="Logo" class="h-8 w-auto">
        </div>
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
            &copy; <?= date('Y') ?> Pemerintah Kabupaten Sinjai
        </p>
        <p class="text-[9px] font-medium text-slate-400 uppercase tracking-tight mt-1">Sistem Identitas Digital terintegrasi</p>
    </footer>
</body>

</html>
