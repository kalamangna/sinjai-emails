<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $title ?? 'Sistem Identitas Digital' ?> | Kabupaten Sinjai</title>

    <meta name="description" content="Portal Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai. Akses layanan integrasi data email, verifikasi dokumen TTE, dan helpdesk.">
    <link rel="icon" type="image/png" href="<?= base_url('logo.png') ?>">
    <link href="<?= base_url('css/output.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        }
        h1, h2, h3, .font-heading {
            font-family: 'Outfit', sans-serif;
        }
        /* Custom dynamic gradient animations */
        @keyframes gradient-bg {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .animate-gradient {
            background-size: 200% 200%;
            animation: gradient-bg 8s ease infinite;
        }
    </style>
</head>

<body class="bg-slate-900 text-slate-100 antialiased min-h-screen flex flex-col justify-between selection:bg-indigo-500 selection:text-white relative overflow-x-hidden">

    <!-- Decorative Glows -->
    <div class="absolute top-[-10%] left-[-10%] w-[50vw] h-[50vw] rounded-full bg-indigo-900/20 blur-[120px] pointer-events-none z-0"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[50vw] h-[50vw] rounded-full bg-violet-900/20 blur-[120px] pointer-events-none z-0"></div>

    <!-- Header / Navbar -->
    <header class="w-full max-w-7xl mx-auto px-6 py-6 flex items-center justify-between z-10 shrink-0">
        <div class="flex items-center gap-3">
            <img src="<?= base_url('logo.png') ?>" alt="Logo Kabupaten Sinjai" class="w-10 h-10 object-contain drop-shadow-lg">
            <div>
                <span class="block text-sm font-bold tracking-tight text-white uppercase">sinjai<span class="text-indigo-400">emails</span></span>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mt-0.5">identitas digital</span>
            </div>
        </div>
        <div>
            <a href="<?= site_url('login') ?>" id="nav-login-btn" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white text-xs font-bold uppercase tracking-wider rounded-lg transition-all shadow-lg shadow-indigo-600/20 hover:scale-[1.02]">
                <i class="fas fa-sign-in-alt mr-2"></i> Masuk
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="w-full max-w-7xl mx-auto px-6 py-12 flex-grow flex flex-col items-center justify-center z-10 relative">
        
        <!-- Hero Section -->
        <div class="text-center max-w-3xl space-y-6 mb-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-500/10 border border-indigo-500/20 rounded-full text-indigo-300 text-[10px] font-bold uppercase tracking-widest animate-pulse">
                <i class="fas fa-shield-halved text-xs"></i> Portal Layanan Digital Resmi
            </div>
            
            <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight text-white leading-tight uppercase">
                Sistem Identitas Digital <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 animate-gradient">Kabupaten Sinjai</span>
            </h1>
            
            <p class="text-slate-400 text-sm md:text-base max-w-2xl mx-auto leading-relaxed">
                Pusat integrasi data akun aparatur, verifikasi dokumen elektronik tersertifikasi (TTE BSrE), serta layanan bantuan TIK terpadu dalam lingkup Pemerintah Kabupaten Sinjai.
            </p>
        </div>

        <!-- Service Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 w-full max-w-5xl">
            
            <!-- Card 1: Verifikasi PDF -->
            <div class="group bg-slate-800/50 border border-slate-700/50 hover:border-indigo-500/40 rounded-2xl p-8 flex flex-col justify-between transition-all duration-300 hover:shadow-xl hover:shadow-indigo-500/5 hover:-translate-y-1 relative overflow-hidden backdrop-blur-sm">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-indigo-600/10 rounded-full blur-2xl group-hover:bg-indigo-600/20 transition-all"></div>
                <div class="space-y-4">
                    <div class="w-12 h-12 bg-indigo-500/10 border border-indigo-500/20 rounded-xl flex items-center justify-center text-indigo-400 text-lg group-hover:scale-110 transition-transform">
                        <i class="fas fa-file-shield"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white uppercase tracking-tight">Verifikasi PDF</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Periksa keaslian dokumen dinas elektronik Anda dan status sertifikat tanda tangan digital (TTE) BSrE secara instan.
                    </p>
                </div>
                <div class="pt-6">
                    <a href="<?= site_url('verifikasi-pdf') ?>" id="action-verify-pdf" class="inline-flex items-center justify-center w-full py-2.5 bg-slate-700 hover:bg-indigo-600 text-white text-xs font-bold uppercase tracking-wider rounded-lg transition-all group-hover:shadow-lg group-hover:shadow-indigo-600/15">
                        Mulai Verifikasi <i class="fas fa-chevron-right ml-2 text-[9px] group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>

            <!-- Card 2: Helpdesk Layanan -->
            <div class="group bg-slate-800/50 border border-slate-700/50 hover:border-violet-500/40 rounded-2xl p-8 flex flex-col justify-between transition-all duration-300 hover:shadow-xl hover:shadow-violet-500/5 hover:-translate-y-1 relative overflow-hidden backdrop-blur-sm">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-violet-600/10 rounded-full blur-2xl group-hover:bg-violet-600/20 transition-all"></div>
                <div class="space-y-4">
                    <div class="w-12 h-12 bg-violet-500/10 border border-violet-500/20 rounded-xl flex items-center justify-center text-violet-400 text-lg group-hover:scale-110 transition-transform">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white uppercase tracking-tight">Helpdesk Layanan</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Laporkan kendala pembuatan akun, pemulihan password email dinas, atau isu terkait sertifikat tanda tangan elektronik.
                    </p>
                </div>
                <div class="pt-6">
                    <a href="<?= site_url('helpdesk') ?>" id="action-helpdesk" class="inline-flex items-center justify-center w-full py-2.5 bg-slate-700 hover:bg-violet-600 text-white text-xs font-bold uppercase tracking-wider rounded-lg transition-all group-hover:shadow-lg group-hover:shadow-violet-600/15">
                        Kirim Laporan <i class="fas fa-chevron-right ml-2 text-[9px] group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>

            <!-- Card 3: Administrator Login -->
            <div class="group bg-slate-800/50 border border-slate-700/50 hover:border-pink-500/40 rounded-2xl p-8 flex flex-col justify-between transition-all duration-300 hover:shadow-xl hover:shadow-pink-500/5 hover:-translate-y-1 relative overflow-hidden backdrop-blur-sm">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-pink-600/10 rounded-full blur-2xl group-hover:bg-pink-600/20 transition-all"></div>
                <div class="space-y-4">
                    <div class="w-12 h-12 bg-pink-500/10 border border-pink-500/20 rounded-xl flex items-center justify-center text-pink-400 text-lg group-hover:scale-110 transition-transform">
                        <i class="fas fa-user-gear"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white uppercase tracking-tight">Login Admin</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Akses dashboard internal bagi administrator untuk pengelolaan data email aparatur, website pemda, dan log audit.
                    </p>
                </div>
                <div class="pt-6">
                    <a href="<?= site_url('login') ?>" id="action-admin-login" class="inline-flex items-center justify-center w-full py-2.5 bg-slate-700 hover:bg-pink-600 text-white text-xs font-bold uppercase tracking-wider rounded-lg transition-all group-hover:shadow-lg group-hover:shadow-pink-600/15">
                        Portal Admin <i class="fas fa-chevron-right ml-2 text-[9px] group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="w-full border-t border-slate-800/60 bg-slate-900/60 backdrop-blur-md py-6 z-10 shrink-0">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
            <div>
                © <?= date('Y') ?> Pemerintah Kabupaten Sinjai. All rights reserved.
            </div>
            <div class="flex items-center gap-6">
                <span class="flex items-center gap-1.5"><i class="fas fa-shield-alt text-slate-600"></i> Secure Connection</span>
                <span class="flex items-center gap-1.5"><i class="fas fa-server text-slate-600"></i> v2.0.0-Stable</span>
            </div>
        </div>
    </footer>

</body>

</html>
