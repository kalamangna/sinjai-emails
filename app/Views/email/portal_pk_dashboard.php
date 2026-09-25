<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $title ?? 'Portal TTE PPPK' ?> | Sistem Identitas Digital</title>

    <meta name="description" content="Portal Tanda Tangan Elektronik (TTE) Perjanjian Kerja PPPK Pemerintah Kabupaten Sinjai">
    <link rel="canonical" href="<?= current_url() ?>">

    <!-- Meta Tags -->
    <meta property="og:site_name" content="Sistem Identitas Digital Sinjai">
    <meta property="og:title" content="<?= $title ?? 'Portal TTE PPPK' ?> | Sistem Identitas Digital">
    <meta property="og:description" content="Portal Tanda Tangan Elektronik (TTE) Perjanjian Kerja PPPK Pemerintah Kabupaten Sinjai">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:image" content="<?= base_url('meta.png') ?>">
    <meta property="og:type" content="website">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title ?? 'Portal TTE PPPK' ?> | Sistem Identitas Digital">
    <meta name="twitter:description" content="Portal Tanda Tangan Elektronik (TTE) Perjanjian Kerja PPPK Pemerintah Kabupaten Sinjai">
    <meta name="twitter:image" content="<?= base_url('meta.png') ?>">

    <link rel="icon" type="image/png" href="<?= base_url('logo.png') ?>">

    <!-- Tailwind CSS (Local Build) -->
    <link href="<?= base_url('css/output.css') ?>" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col justify-between">
    <!-- Navbar Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <img src="<?= base_url('logo.png') ?>" alt="Logo" class="w-8 h-8 object-contain">
                <div>
                    <h1 class="text-sm font-bold text-slate-800 uppercase tracking-tight">sinjai<span class="text-slate-700">emails</span></h1>
                    <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Portal TTE PPPK</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden sm:block text-right">
                    <p class="text-xs font-bold text-slate-800 uppercase truncate max-w-[200px]"><?= esc($email['name']) ?></p>
                    <p class="text-[10px] text-slate-500 font-mono">NIP. <?= esc($email['nip']) ?></p>
                </div>
                <div class="w-9 h-9 bg-slate-100 rounded-lg hidden sm:flex items-center justify-center text-slate-700 border border-slate-200 shadow-sm">
                    <i class="fas fa-user-tie text-sm"></i>
                </div>
                <a href="<?= site_url('portal-pk/logout') ?>" class="btn btn-outline btn-sm flex items-center gap-1.5" title="Keluar">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="hidden sm:inline">Keluar</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- Global Flash Messages (Same as Email Dashboard) -->
        <?php if (session()->getFlashdata('success') || session()->getFlashdata('message') || session()->getFlashdata('error') || session()->getFlashdata('info')): ?>
            <div id="toast-container" class="space-y-2">
                <?php if ($msg = session()->getFlashdata('success') ?: session()->getFlashdata('message')): ?>
                    <div id="toast-success" class="transition-opacity duration-300 bg-slate-700 text-white px-5 py-3 rounded-lg flex items-center justify-between shadow-sm" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-3 text-white"></i>
                            <span class="font-bold text-xs uppercase tracking-wider"><?= $msg === true ? 'Berhasil' : esc($msg) ?></span>
                        </div>
                        <button type="button" onclick="this.closest('[role=\'alert\']').remove()" class="text-white/50 hover:text-white transition-colors focus:outline-none cursor-pointer" aria-label="Tutup">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <script>
                        setTimeout(() => {
                            const toast = document.getElementById('toast-success');
                            if (toast) {
                                toast.classList.add('opacity-0');
                                setTimeout(() => toast.remove(), 300);
                            }
                        }, 5000);
                    </script>
                <?php endif; ?>

                <?php if ($err = session()->getFlashdata('error')): ?>
                    <div id="toast-error" class="transition-opacity duration-300 bg-red-600 text-white px-5 py-3 rounded-lg flex items-center justify-between shadow-sm" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-3 text-white"></i>
                            <span class="font-bold text-xs uppercase tracking-wider"><?= esc($err) ?></span>
                        </div>
                        <button type="button" onclick="this.closest('[role=\'alert\']').remove()" class="text-white/50 hover:text-white transition-colors focus:outline-none cursor-pointer" aria-label="Tutup">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <script>
                        setTimeout(() => {
                            const toast = document.getElementById('toast-error');
                            if (toast) {
                                toast.classList.add('opacity-0');
                                setTimeout(() => toast.remove(), 300);
                            }
                        }, 5000);
                    </script>
                <?php endif; ?>

                <?php if ($info = session()->getFlashdata('info')): ?>
                    <div id="toast-info" class="transition-opacity duration-300 bg-slate-800 text-white px-5 py-3 rounded-lg flex items-center justify-between shadow-sm" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle mr-3 text-white"></i>
                            <span class="font-bold text-xs uppercase tracking-wider"><?= esc($info) ?></span>
                        </div>
                        <button type="button" onclick="this.closest('[role=\'alert\']').remove()" class="text-white/50 hover:text-white transition-colors focus:outline-none cursor-pointer" aria-label="Tutup">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <script>
                        setTimeout(() => {
                            const toast = document.getElementById('toast-info');
                            if (toast) {
                                toast.classList.add('opacity-0');
                                setTimeout(() => toast.remove(), 300);
                            }
                        }, 5000);
                    </script>
                <?php endif; ?>
            </div>
            <script>
                (function() {
                    const container = document.getElementById('toast-container');
                    if (container) {
                        const checkToasts = () => {
                            const activeToasts = Array.from(container.querySelectorAll('[role="alert"]')).filter(el => {
                                return !el.classList.contains('hidden') && el.style.display !== 'none';
                            });
                            if (activeToasts.length === 0) {
                                container.remove();
                                if (typeof observer !== 'undefined') observer.disconnect();
                            }
                        };
                        const observer = new MutationObserver(checkToasts);
                        observer.observe(container, { 
                            childList: true, 
                            subtree: true, 
                            attributes: true, 
                            attributeFilter: ['class', 'style'] 
                        });
                        checkToasts();
                    }
                })();
            </script>
        <?php endif; ?>

        <?php 
            $unitKerja = $email['unit_kerja_name'] ?? ($email['unit_kerja'] ?? '-');
            $parentUnit = $email['parent_unit_kerja_name'] ?? null;
            $statusAsn = $email['status_asn'] ?? ($email['nama_status_asn'] ?? 'PPPK');
        ?>

        <!-- Info & Status Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Card 1: Pegawai -->
            <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-3">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest">Pegawai</h3>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border bg-slate-100 text-slate-700 border-slate-200">
                            <?= esc($statusAsn) ?>
                        </span>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800 uppercase leading-snug" title="<?= esc($email['name']) ?>">
                        <?= esc($email['name']) ?>
                    </h4>
                    <p class="text-xs text-slate-500 font-medium leading-snug mt-1" title="<?= esc($unitKerja) ?>">
                        <?= esc($unitKerja) ?>
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">NIP</span>
                    <span class="font-mono font-medium text-slate-700"><?= esc($email['nip']) ?></span>
                </div>
            </div>

            <!-- Card 2: Perjanjian Kerja -->
            <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-3">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest">Perjanjian Kerja</h3>
                        <span class="text-xs font-mono font-bold text-slate-700">
                            <?= esc($pk['nomor'] ?? '-') ?>
                        </span>
                    </div>
                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Masa Kontrak</span>
                            <span class="font-medium text-slate-700">
                                <?= !empty($pk['tanggal_kontrak_awal']) ? date('d/m/Y', strtotime($pk['tanggal_kontrak_awal'])) : '-' ?> – 
                                <?= !empty($pk['tanggal_kontrak_akhir']) ? date('d/m/Y', strtotime($pk['tanggal_kontrak_akhir'])) : '-' ?>
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Gaji</span>
                            <span class="font-bold text-slate-800">
                                <?= !empty($pk['gaji_nominal']) ? 'Rp ' . number_format((float)str_replace(['Rp', '.', ' '], '', $pk['gaji_nominal']), 0, ',', '.') : '-' ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-start justify-between gap-3 text-xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider shrink-0 mt-0.5">Jabatan</span>
                    <span class="font-medium text-slate-700 text-right leading-snug"><?= esc($email['jabatan'] ?? 'PPPK') ?></span>
                </div>
            </div>

            <!-- Card 3: Status TTE & Aksi -->
            <?php 
                $isSignedPppk   = in_array($pk['tte_status'] ?? '', ['signed_pppk', 'completed']);
                $isSignedBupati = ($pk['tte_status'] ?? '') === 'completed';
                $bsreActive     = strtoupper($email['bsre_status'] ?? '') === 'ISSUE';
            ?>
            <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-3">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest">Status TTE</h3>
                        <?php if ($isSignedBupati): ?>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border bg-emerald-50 text-emerald-700 border-emerald-200">
                                Lengkap
                            </span>
                        <?php elseif ($isSignedPppk): ?>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border bg-blue-50 text-blue-700 border-blue-200">
                                Menunggu TTE Bupati
                            </span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border bg-amber-50 text-amber-700 border-amber-200">
                                Belum TTE
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="py-1">
                        <?php if ($isSignedBupati): ?>
                            <p class="text-xs font-medium text-slate-600">
                                Dokumen telah ditandatangani lengkap oleh kedua pihak.
                            </p>
                        <?php elseif ($isSignedPppk): ?>
                            <p class="text-xs font-medium text-slate-600">
                                Ditandatangani pada <span class="font-bold text-slate-800"><?= !empty($pk['tte_pegawai_at']) ? date('d/m/Y H:i', strtotime($pk['tte_pegawai_at'])) : '-' ?></span>. Menunggu TTE Bupati Sinjai.
                            </p>
                        <?php else: ?>
                            <p class="text-xs font-medium text-slate-600">
                                Dokumen siap ditandatangani.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100">
                    <?php if ($isSignedPppk): ?>
                        <a href="<?= site_url('portal-pk/download') ?>" class="btn btn-solid w-full py-2 flex items-center justify-center gap-2 text-xs">
                            <i class="fas fa-download"></i>
                            <span>Unduh PDF</span>
                        </a>
                    <?php else: ?>
                        <?php if ($bsreActive): ?>
                            <button type="button" onclick="openModal('modal-tte')" class="btn btn-solid w-full py-2 flex items-center justify-center gap-2 text-xs">
                                <i class="fas fa-pen-nib"></i>
                                <span>Tandatangani</span>
                            </button>
                        <?php else: ?>
                            <button type="button" disabled class="btn btn-outline w-full py-2 opacity-60 cursor-not-allowed text-xs">
                                <i class="fas fa-lock mr-1"></i> Sertifikat Tidak Aktif
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Document Preview Section -->
        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">
            <div class="px-4 sm:px-5 py-2.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <i class="fas fa-file-pdf text-red-600 text-sm"></i>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                        Dokumen Perjanjian Kerja
                    </h3>
                </div>

                <div class="flex items-center gap-2">
                    <a href="<?= site_url('portal-pk/preview') ?>" target="_blank" class="btn btn-outline btn-sm text-xs flex items-center gap-1.5" title="Buka di Tab Baru">
                        <i class="fas fa-external-link-alt text-[10px]"></i>
                        <span class="hidden sm:inline">Layar Penuh</span>
                    </a>
                    <a href="<?= site_url('portal-pk/download') ?>" class="btn btn-outline btn-sm text-xs flex items-center gap-1.5">
                        <i class="fas fa-download text-[10px]"></i>
                        <span>Unduh</span>
                    </a>
                </div>
            </div>

            <!-- PDF Viewer Iframe -->
            <div class="w-full bg-slate-100" style="height: 75vh;">
                <iframe src="<?= site_url('portal-pk/preview') ?>#toolbar=0" class="w-full h-full border-none" title="Pratinjau Dokumen PK"></iframe>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">
                &copy; <?= tahunSekarang() ?> DISKOMINFO-SP SINJAI
            </p>
        </div>
    </footer>

    <!-- Modal TTE Passphrase (Standard Component) -->
    <?php
    $modalContent = '
        <form action="' . site_url('portal-pk/sign') . '" method="POST" id="form-tte" class="space-y-4">
            ' . csrf_field() . '
            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-xs">
                <p class="font-bold text-slate-800 uppercase truncate">' . esc($email['name']) . '</p>
                <p class="font-mono text-slate-500 text-[11px] mt-0.5">NIK: ' . esc($email['nik']) . '</p>
            </div>

            <div>
                <label for="passphrase" class="block text-xs font-medium text-slate-700 mb-1 uppercase tracking-wider">
                    Passphrase BSrE
                </label>
                <div class="relative">
                    <input type="password" id="passphrase" name="passphrase" required autofocus
                        class="block w-full pl-3 pr-9 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 placeholder-slate-400 placeholder:font-normal transition-all"
                        placeholder="Masukkan passphrase...">
                    <button type="button" onclick="togglePassphraseVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700" tabindex="-1">
                        <i id="eye-icon" class="fas fa-eye text-xs"></i>
                    </button>
                </div>
            </div>
        </form>
    ';

    $modalFooter = '
        <button type="button" onclick="closeModal(\'modal-tte\')" class="btn btn-outline btn-sm">
            Batal
        </button>
        <button type="submit" form="form-tte" id="btn-submit-sign" class="btn btn-solid btn-sm flex items-center gap-1.5">
            <i class="fas fa-pen-nib"></i>
            <span>Tandatangani</span>
        </button>
    ';

    echo view('components/modal', [
        'id'      => 'modal-tte',
        'title'   => 'Tanda Tangan Elektronik',
        'size'    => 'sm',
        'content' => $modalContent,
        'footer'  => $modalFooter
    ]);
    ?>

    <!-- Script Modal & UI Handling -->
    <script>

        function togglePassphraseVisibility() {
            const input = document.getElementById('passphrase');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.getElementById('form-tte').addEventListener('submit', function() {
            const btn = document.getElementById('btn-submit-sign');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i> Menandatangani...';
        });
    </script>

    <!-- Flowbite JS (for Modal & interactive UI) -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>

</html>
