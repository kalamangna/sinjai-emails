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

<body class="bg-slate-50 text-slate-700 antialiased min-h-screen flex items-center justify-center p-4 md:p-6">
    <div class="w-full max-w-md">
        <!-- Branding -->
        <div class="text-center mb-6">
            <img src="<?= base_url('logo.png') ?>" alt="Logo Kabupaten Sinjai" class="w-14 h-14 object-contain mx-auto mb-3">
            <h1 class="text-xl font-bold text-slate-800 uppercase tracking-tight">Portal TTE Perjanjian Kerja</h1>
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mt-1">Pemerintah Kabupaten Sinjai</p>
        </div>

        <!-- Verification Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-6">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Verifikasi Identitas</h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Khusus Pegawai PPPK & PPPK Paruh Waktu</p>
                </div>
                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600">
                    <i class="fas fa-file-signature text-sm"></i>
                </div>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-start gap-3 mb-5 text-xs font-medium">
                    <i class="fas fa-exclamation-circle text-red-500 mt-0.5 shrink-0"></i>
                    <div class="flex-1">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-start gap-3 mb-5 text-xs font-medium">
                    <i class="fas fa-check-circle text-emerald-500 mt-0.5 shrink-0"></i>
                    <div class="flex-1">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('portal-pk/auth') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label for="nip" class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">
                        NIP Pegawai <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fas fa-id-badge text-xs"></i>
                        </span>
                        <input type="text" name="nip" id="nip" value="<?= old('nip') ?>" required autofocus
                            inputmode="numeric" maxlength="18" minlength="18"
                            class="block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 transition-all placeholder-slate-400"
                            placeholder="18 Digit NIP (Contoh: 1990...)">
                    </div>
                </div>

                <div>
                    <label for="nik" class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">
                        NIK (KTP) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fas fa-address-card text-xs"></i>
                        </span>
                        <input type="text" name="nik" id="nik" value="<?= old('nik') ?>" required
                            inputmode="numeric" maxlength="16" minlength="16"
                            class="block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 transition-all placeholder-slate-400"
                            placeholder="16 Digit NIK KTP">
                    </div>
                </div>

                <div>
                    <label for="tanggal_lahir" class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">
                        Tanggal Lahir <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fas fa-calendar-alt text-xs"></i>
                        </span>
                        <input type="date" name="tanggal_lahir" id="tanggal_lahir" value="<?= old('tanggal_lahir') ?>" required
                            class="block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 transition-all">
                    </div>
                </div>

                <div class="pt-3">
                    <button type="submit" class="w-full btn btn-solid py-3 text-sm font-semibold rounded-xl flex items-center justify-center gap-2 shadow-sm">
                        <span>Verifikasi & Masuk</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <a href="<?= site_url('/') ?>" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-800 font-medium transition-colors">
                    <i class="fas fa-home text-[10px]"></i>
                    <span>Kembali ke Halaman Utama</span>
                </a>
            </div>
        </div>

        <!-- Security Notice Footer -->
        <div class="mt-6 text-center text-[10px] text-slate-400 font-medium space-y-1">
            <p><i class="fas fa-shield-alt mr-1"></i> Dilindungi enkripsi resmi Balai Sertifikasi Elektronik (BSrE) - BSSN</p>
            <p>© <?= date('Y') ?> Pemerintah Kabupaten Sinjai</p>
        </div>
    </div>
</body>

</html>
