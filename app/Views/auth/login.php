<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk | Sistem Identitas Digital</title>

    <!-- Meta Tags -->
    <meta property="og:title" content="Masuk | Sistem Identitas Digital">
    <meta property="og:description" content="Portal Manajemen Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai">
    <meta property="og:image" content="<?= base_url('og-image.png') ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="<?= base_url('og-image.png') ?>">

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
            <div class="inline-flex items-center justify-center w-12 h-12 bg-slate-800 rounded-lg shadow-sm mb-4">
                <i class="fas fa-fingerprint text-white text-xl"></i>
            </div>
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