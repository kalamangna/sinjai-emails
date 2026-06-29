<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="<?= $meta_robots ?? 'noindex, nofollow' ?>">
    <title><?= $title ?? 'Masuk' ?> | Sistem Identitas Digital</title>

    <meta name="description" content="<?= $meta_description ?? 'Portal Manajemen Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai' ?>">
    <link rel="canonical" href="<?= current_url() ?>">

    <!-- Meta Tags -->
    <meta property="og:site_name" content="Sistem Identitas Digital Sinjai">
    <meta property="og:title" content="<?= $title ?? 'Masuk' ?> | Sistem Identitas Digital">
    <meta property="og:description" content="<?= $meta_description ?? 'Portal Manajemen Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai' ?>">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:image" content="<?= $meta_image ?? base_url('meta.png') ?>">
    <meta property="og:type" content="<?= $meta_type ?? 'website' ?>">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title ?? 'Masuk' ?> | Sistem Identitas Digital">
    <meta name="twitter:description" content="<?= $meta_description ?? 'Portal Manajemen Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai' ?>">
    <meta name="twitter:image" content="<?= $meta_image ?? base_url('meta.png') ?>">

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

<body class="bg-slate-50 text-slate-700 antialiased min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-sm">
        <!-- Branding -->
        <div class="text-center mb-8">
            <img src="<?= base_url('logo.png') ?>" alt="Logo" class="w-12 h-12 object-contain mx-auto mb-4">
            <h1 class="text-xl font-bold text-slate-800 uppercase tracking-tight">sinjai<span class="text-slate-700">emails</span></h1>
            <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest mt-1">identitas digital</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white border border-slate-200 rounded-lg p-8 shadow-sm">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-widest mb-6 border-b border-slate-100 pb-2">Masuk ke Sistem</h2>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="flash-message bg-white border border-slate-200 border-l-4 border-l-slate-700 text-red-600 px-4 py-2 rounded-lg flex items-center justify-between mb-6 text-xs font-bold uppercase transform transition-all duration-500 ease-in-out">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?= session()->getFlashdata('error') ?>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-600/50 hover:text-red-600 transition-colors focus:outline-none">
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('auth/attemptLogin') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label for="username" class="block text-xs font-medium text-slate-700 mb-1 uppercase tracking-wider">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                            <i class="fas fa-user text-xs"></i>
                        </span>
                        <input type="text" name="username" id="username" value="<?= old('username') ?>" required
                            class="block w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 transition-all"
                            placeholder="Username">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-medium text-slate-700 mb-1 uppercase tracking-wider">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                            <i class="fas fa-lock text-xs"></i>
                        </span>
                        <input type="password" name="password" id="password" required
                            class="block w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 transition-all"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full btn btn-solid py-2.5">
                        Masuk <i class="fas fa-sign-in-alt ml-2 text-white/80"></i>
                    </button>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Layanan & Utilitas Publik</p>
                <div class="grid grid-cols-2 gap-2">
                    <a href="<?= site_url('verifikasi-pdf') ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition-all uppercase tracking-tight">
                        <i class="fas fa-file-signature text-slate-500"></i> Verifikasi PDF
                    </a>
                    <a href="<?= site_url('helpdesk') ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition-all uppercase tracking-tight">
                        <i class="fas fa-headset text-slate-500"></i> Helpdesk
                    </a>
                </div>
            </div>
        </div>

        <p class="text-center text-[10px] font-bold text-slate-700 uppercase tracking-widest mt-8">
            &copy; <?= tahunSekarang() ?> DISKOMINFO-SP SINJAI
        </p>
    </div>

    <script>
        // Flash Message Auto Close
        document.addEventListener('DOMContentLoaded', () => {
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(msg => {
                setTimeout(() => {
                    msg.style.opacity = '0';
                    msg.style.transform = 'translateY(-10px)';
                    setTimeout(() => msg.remove(), 500);
                }, 5000);
            });
        });
    </script>
</body>

</html>