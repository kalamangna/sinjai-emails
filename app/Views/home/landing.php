<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $title ?? 'Sistem Identitas Digital' ?> | Kabupaten Sinjai</title>

    <meta name="description" content="Portal Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai. Akses layanan integrasi data email, verifikasi dokumen TTE, dan helpdesk.">
    <link rel="canonical" href="<?= current_url() ?>">

    <!-- Meta Tags -->
    <meta property="og:site_name" content="Sistem Identitas Digital Sinjai">
    <meta property="og:title" content="<?= $title ?? 'Sistem Identitas Digital' ?> | Kabupaten Sinjai">
    <meta property="og:description" content="Portal Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai. Akses layanan integrasi data email, verifikasi dokumen TTE, dan helpdesk.">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:image" content="<?= base_url('meta.png') ?>">
    <meta property="og:type" content="website">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title ?? 'Sistem Identitas Digital' ?> | Kabupaten Sinjai">
    <meta name="twitter:description" content="Portal Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai. Akses layanan integrasi data email, verifikasi dokumen TTE, dan helpdesk.">
    <meta name="twitter:image" content="<?= base_url('meta.png') ?>">

    <link rel="icon" type="image/png" href="<?= base_url('logo.png') ?>">
    <link href="<?= base_url('css/output.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Subtle animation for landing glow */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .float-animation {
            animation: float 4s ease-in-out infinite;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col justify-between selection:bg-slate-700 selection:text-white relative overflow-x-hidden">

    <!-- Decorative Soft Glows (Light Mode aligned with slate dashboard theme) -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] left-[-10%] w-[50vw] h-[50vw] rounded-full bg-slate-200/50 blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[50vw] h-[50vw] rounded-full bg-indigo-50/50 blur-[120px]"></div>
    </div>

    <!-- Header / Navbar -->
    <header class="w-full bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50 shrink-0">
        <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="<?= base_url('logo.png') ?>" alt="Logo Kabupaten Sinjai" class="w-10 h-10 object-contain drop-shadow-sm">
                <div>
                    <span class="block text-sm font-extrabold tracking-tight text-slate-800 uppercase">sinjai<span class="text-slate-500">emails</span></span>
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mt-0.5">identitas digital</span>
                </div>
            </div>
            <div>
                <a href="<?= site_url('login') ?>" id="nav-login-btn" class="btn btn-solid flex items-center gap-2 hover:scale-[1.02]">
                    <i class="fas fa-sign-in-alt text-[10px]"></i> Masuk
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="w-full max-w-5xl mx-auto px-6 py-16 flex-grow flex flex-col items-center justify-center z-10 relative">
        
        <!-- Hero Section -->
        <div class="text-center max-w-3xl space-y-6 mb-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-slate-100 border border-slate-200 rounded-full text-slate-600 text-[10px] font-bold uppercase tracking-widest">
                <i class="fas fa-shield-halved text-xs text-slate-500"></i> Portal Layanan Digital Resmi
            </div>
            
            <h1 class="text-3xl md:text-5xl font-black tracking-tight text-slate-800 leading-tight uppercase">
                Sistem Identitas Digital <br>
                <span class="text-slate-500 font-extrabold">Kabupaten Sinjai</span>
            </h1>
            
            <p class="text-slate-500 text-xs md:text-sm max-w-2xl mx-auto leading-relaxed font-medium">
                Pusat integrasi data akun aparatur, verifikasi dokumen elektronik tersertifikasi (TTE BSrE), serta layanan bantuan TIK terpadu dalam lingkup Pemerintah Kabupaten Sinjai.
            </p>
        </div>

        <!-- Service Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 w-full max-w-5xl">
            
            <!-- Card 1: Verifikasi PDF -->
            <div class="group bg-white border border-slate-200 hover:border-slate-400 rounded-2xl p-8 flex flex-col justify-between transition-all duration-300 hover:shadow-xl hover:shadow-slate-200/50 hover:-translate-y-1 relative overflow-hidden">
                <div class="space-y-4">
                    <div class="w-12 h-12 bg-slate-100 border border-slate-200 rounded-xl flex items-center justify-center text-slate-700 text-lg group-hover:scale-110 transition-transform">
                        <i class="fas fa-file-shield"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-tight">Verifikasi PDF</h3>
                    <p class="text-xs text-slate-500 leading-relaxed font-medium">
                        Periksa keaslian dokumen dinas elektronik Anda dan status sertifikat tanda tangan digital (TTE) BSrE secara instan.
                    </p>
                </div>
                <div class="pt-6">
                    <a href="<?= site_url('verifikasi-pdf') ?>" id="action-verify-pdf" class="btn btn-outline w-full flex items-center justify-center gap-2 group-hover:bg-slate-700 group-hover:text-white group-hover:border-slate-700">
                        Mulai Verifikasi <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>

            <!-- Card 2: Helpdesk Layanan -->
            <div class="group bg-white border border-slate-200 hover:border-slate-400 rounded-2xl p-8 flex flex-col justify-between transition-all duration-300 hover:shadow-xl hover:shadow-slate-200/50 hover:-translate-y-1 relative overflow-hidden">
                <div class="space-y-4">
                    <div class="w-12 h-12 bg-slate-100 border border-slate-200 rounded-xl flex items-center justify-center text-slate-700 text-lg group-hover:scale-110 transition-transform">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-tight">Helpdesk Layanan</h3>
                    <p class="text-xs text-slate-500 leading-relaxed font-medium">
                        Laporkan kendala pembuatan akun, pemulihan password email dinas, atau isu terkait sertifikat tanda tangan elektronik.
                    </p>
                </div>
                <div class="pt-6">
                    <a href="<?= site_url('helpdesk') ?>" id="action-helpdesk" class="btn btn-outline w-full flex items-center justify-center gap-2 group-hover:bg-slate-700 group-hover:text-white group-hover:border-slate-700">
                        Kirim Laporan <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>

            <!-- Card 3: Administrator Login -->
            <div class="group bg-white border border-slate-200 hover:border-slate-400 rounded-2xl p-8 flex flex-col justify-between transition-all duration-300 hover:shadow-xl hover:shadow-slate-200/50 hover:-translate-y-1 relative overflow-hidden">
                <div class="space-y-4">
                    <div class="w-12 h-12 bg-slate-100 border border-slate-200 rounded-xl flex items-center justify-center text-slate-700 text-lg group-hover:scale-110 transition-transform">
                        <i class="fas fa-user-gear"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-tight">Login Admin</h3>
                    <p class="text-xs text-slate-500 leading-relaxed font-medium">
                        Akses dashboard internal bagi administrator untuk pengelolaan data email aparatur, website pemda, dan log audit.
                    </p>
                </div>
                <div class="pt-6">
                    <a href="<?= site_url('login') ?>" id="action-admin-login" class="btn btn-solid w-full flex items-center justify-center gap-2">
                        Portal Admin <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="w-full border-t border-slate-200 bg-white py-6 z-10 shrink-0">
        <div class="max-w-5xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">
                &copy; <?= tahunSekarang() ?> Diskominfo-SP Sinjai
            </p>
        </div>
    </footer>

</body>

</html>
