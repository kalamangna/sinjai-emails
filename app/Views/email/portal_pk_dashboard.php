<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $title ?? 'Portal TTE PPPK' ?> | Sistem Identitas Digital</title>

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

<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">
    <!-- Navbar Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <img src="<?= base_url('logo.png') ?>" alt="Logo" class="w-9 h-9 object-contain">
                <div>
                    <h1 class="text-sm font-bold text-slate-800 uppercase tracking-tight">Portal TTE Perjanjian Kerja</h1>
                    <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Kabupaten Sinjai</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden sm:block text-right">
                    <p class="text-xs font-bold text-slate-800 uppercase"><?= esc($email['name']) ?></p>
                    <p class="text-[10px] text-slate-500 font-mono">NIP. <?= esc($email['nip']) ?></p>
                </div>
                <a href="<?= site_url('portal-pk/logout') ?>" class="btn btn-outline text-xs py-2 px-3 flex items-center gap-1.5" title="Keluar">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="hidden sm:inline">Keluar</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center justify-between text-xs font-semibold shadow-xs">
                <div class="flex items-center gap-2">
                    <i class="fas fa-exclamation-circle text-red-500 text-sm shrink-0"></i>
                    <span><?= session()->getFlashdata('error') ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-700"><i class="fas fa-times"></i></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center justify-between text-xs font-semibold shadow-xs">
                <div class="flex items-center gap-2">
                    <i class="fas fa-check-circle text-emerald-500 text-sm shrink-0"></i>
                    <span><?= session()->getFlashdata('success') ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800"><i class="fas fa-times"></i></button>
            </div>
        <?php endif; ?>

        <!-- Info & Status Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Card 1: Data Pegawai -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Identitas Pegawai</span>
                    <h3 class="text-base font-bold text-slate-800 uppercase mt-1"><?= esc($email['name']) ?></h3>
                    <p class="text-xs text-slate-500 mt-0.5"><?= esc($email['jabatan'] ?? 'PPPK') ?></p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 space-y-1.5 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-400">NIP:</span>
                        <span class="font-mono font-medium text-slate-700"><?= esc($email['nip']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Unit Kerja:</span>
                        <span class="font-medium text-slate-700 text-right truncate max-w-[180px]"><?= esc($email['unit_kerja'] ?? '-') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Status:</span>
                        <span class="font-bold text-slate-800 uppercase"><?= esc($email['nama_status_asn'] ?? 'PPPK') ?></span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Informasi Kontrak PK -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Perjanjian Kerja</span>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs font-bold text-slate-800 uppercase">No. PK:</span>
                        <span class="text-xs font-mono font-semibold bg-slate-100 text-slate-800 px-2 py-0.5 rounded-md">
                            <?= esc($pk['nomor'] ?? 'Draf Belum Bernomor') ?>
                        </span>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 space-y-1.5 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Masa Kontrak:</span>
                        <span class="font-medium text-slate-700">
                            <?= !empty($pk['tanggal_kontrak_awal']) ? date('d/m/Y', strtotime($pk['tanggal_kontrak_awal'])) : '-' ?> s.d. 
                            <?= !empty($pk['tanggal_kontrak_akhir']) ? date('d/m/Y', strtotime($pk['tanggal_kontrak_akhir'])) : '-' ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Gaji:</span>
                        <span class="font-semibold text-slate-800">
                            <?= !empty($pk['gaji_nominal']) ? 'Rp ' . number_format((float)str_replace(['Rp', '.', ' '], '', $pk['gaji_nominal']), 0, ',', '.') : '-' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Status TTE & Aksi -->
            <?php 
                $isSigned = ($pk['tte_status'] ?? '') === 'signed_pppk';
                $bsreActive = strtoupper($email['bsre_status'] ?? '') === 'ISSUE';
            ?>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status TTE BSrE</span>
                        <?php if ($bsreActive): ?>
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Sertifikat Aktif
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Belum Siap
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="mt-2.5">
                        <?php if ($isSigned): ?>
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                                    <i class="fas fa-check-double text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800 uppercase">Sudah Ditandatangani</p>
                                    <p class="text-[10px] text-slate-500">
                                        <?= !empty($pk['tte_pegawai_at']) ? date('d M Y, H:i', strtotime($pk['tte_pegawai_at'])) . ' WITA' : '-' ?>
                                    </p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                                    <i class="fas fa-hourglass-half text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800 uppercase">Menunggu Tanda Tangan</p>
                                    <p class="text-[10px] text-slate-500">Silakan bubuhkan TTE di bawah ini</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap gap-2">
                    <?php if ($isSigned): ?>
                        <a href="<?= site_url('portal-pk/download') ?>" class="btn btn-solid w-full py-2.5 text-xs flex items-center justify-center gap-2">
                            <i class="fas fa-download"></i>
                            <span>Unduh PK Bertandatangan (PDF)</span>
                        </a>
                    <?php else: ?>
                        <?php if ($bsreActive): ?>
                            <button type="button" onclick="openSignModal()" class="btn btn-solid w-full py-2.5 text-xs flex items-center justify-center gap-2">
                                <i class="fas fa-pen-nib"></i>
                                <span>Tanda Tangani PK Sekarang</span>
                            </button>
                        <?php else: ?>
                            <button type="button" disabled class="btn btn-outline w-full py-2.5 text-xs opacity-60 cursor-not-allowed">
                                <i class="fas fa-lock mr-1"></i> Sertifikat BSrE Belum Aktif
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Document Preview Section -->
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
            <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <i class="fas fa-file-pdf text-red-500 text-sm"></i>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                        Pratinjau Dokumen Perjanjian Kerja <?= $isSigned ? '(Versi TTE)' : '(Draf)' ?>
                    </h3>
                </div>

                <div class="flex items-center gap-2">
                    <a href="<?= site_url('portal-pk/preview') ?>" target="_blank" class="btn btn-outline text-xs py-1.5 px-3 flex items-center gap-1.5" title="Buka di Tab Baru">
                        <i class="fas fa-external-link-alt text-[10px]"></i>
                        <span>Buka Layar Penuh</span>
                    </a>
                    <a href="<?= site_url('portal-pk/download') ?>" class="btn btn-outline text-xs py-1.5 px-3 flex items-center gap-1.5">
                        <i class="fas fa-download text-[10px]"></i>
                        <span>Unduh Dokumen</span>
                    </a>
                </div>
            </div>

            <!-- PDF Viewer Iframe -->
            <div class="w-full bg-slate-100" style="height: 75vh;">
                <iframe src="<?= site_url('portal-pk/preview') ?>#toolbar=0" class="w-full h-full border-none" title="Pratinjau Dokumen PK"></iframe>
            </div>
        </div>
    </main>

    <!-- Modal TTE Passphrase -->
    <div id="modal-tte" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 transform transition-all">
            <div class="flex items-start justify-between border-b border-slate-100 pb-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-tight">Tanda Tangan Elektronik</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Otentikasi Balai Sertifikasi Elektronik (BSrE)</p>
                </div>
                <button type="button" onclick="closeSignModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            <form action="<?= site_url('portal-pk/sign') ?>" method="POST" id="form-tte" class="space-y-4">
                <?= csrf_field() ?>

                <div class="bg-slate-50 rounded-xl p-3 border border-slate-200 text-xs space-y-1">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Penandatangan:</span>
                        <span class="font-bold text-slate-800 uppercase"><?= esc($email['name']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">NIK:</span>
                        <span class="font-mono text-slate-700"><?= esc($email['nik']) ?></span>
                    </div>
                </div>

                <div>
                    <label for="passphrase" class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">
                        Passphrase BSrE <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" id="passphrase" name="passphrase" required autofocus
                            class="block w-full pl-3 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 transition-all placeholder-slate-400"
                            placeholder="Masukkan Passphrase pribadi Anda...">
                        <button type="button" onclick="togglePassphraseVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700">
                            <i id="eye-icon" class="fas fa-eye text-xs"></i>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i> Passphrase tidak disimpan di sistem dan diteruskan secara terenkripsi langsung ke server BSrE.
                    </p>
                </div>

                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-[11px] text-amber-800 flex gap-2.5">
                    <i class="fas fa-exclamation-triangle text-amber-600 mt-0.5 shrink-0"></i>
                    <div>
                        Dengan menandatangani dokumen ini, Anda menyatakan telah membaca, memahami, dan menyetujui seluruh klausul dalam Perjanjian Kerja secara sadar.
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeSignModal()" class="btn btn-outline text-xs py-2 px-4">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-sign" class="btn btn-solid text-xs py-2 px-4 flex items-center gap-1.5">
                        <i class="fas fa-pen-nib"></i>
                        <span>Bubuhkan TTE</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script Modal & UI Handling -->
    <script>
        function openSignModal() {
            document.getElementById('modal-tte').classList.remove('hidden');
            setTimeout(() => {
                const input = document.getElementById('passphrase');
                if (input) input.focus();
            }, 100);
        }

        function closeSignModal() {
            document.getElementById('modal-tte').classList.add('hidden');
        }

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
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i> Memproses TTE...';
        });
    </script>
</body>

</html>
