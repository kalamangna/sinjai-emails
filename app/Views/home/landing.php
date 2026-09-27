<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $title ?? 'Sistem Identitas Digital' ?> | Pemerintah Kabupaten Sinjai</title>

    <meta name="description" content="Portal Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai. Akses layanan integrasi data email, verifikasi dokumen TTE, dan helpdesk.">
    <link rel="canonical" href="<?= current_url() ?>">

    <!-- Meta Tags -->
    <meta property="og:site_name" content="Sistem Identitas Digital Sinjai">
    <meta property="og:title" content="<?= $title ?? 'Sistem Identitas Digital' ?> | Pemerintah Kabupaten Sinjai">
    <meta property="og:description" content="Portal Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai. Akses layanan integrasi data email, verifikasi dokumen TTE, dan helpdesk.">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:image" content="<?= base_url('meta.png') ?>">
    <meta property="og:type" content="website">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title ?? 'Sistem Identitas Digital' ?> | Pemerintah Kabupaten Sinjai">
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
                <a href="<?= site_url('login') ?>" id="nav-login-btn" class="btn btn-solid flex items-center gap-2">
                    <i class="fas fa-sign-in-alt text-[10px]"></i> Masuk
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="w-full max-w-5xl mx-auto px-6 py-12 flex-grow flex flex-col items-center justify-center z-10 relative">
        
        <!-- Hero Section -->
        <div class="text-center max-w-3xl space-y-4 mb-10">
            <h1 class="text-3xl md:text-5xl font-black tracking-tight text-slate-800 leading-tight uppercase">
                Sistem Identitas Digital
            </h1>
            
            <p class="text-slate-500 text-xs md:text-sm max-w-2xl mx-auto leading-relaxed font-medium">
                Identitas digital aparatur, TTE BSrE, dan helpdesk TIK Pemkab Sinjai.
            </p>
        </div>

        <!-- Service Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 w-full max-w-2xl">

            <!-- Card 1: Verifikasi PDF -->
            <div class="group bg-white border border-slate-200 hover:border-slate-400 rounded-2xl p-7 flex flex-col justify-between transition-all duration-300 hover:shadow-xl hover:shadow-slate-200/50 relative overflow-hidden">
                <div class="space-y-4">
                    <div class="w-12 h-12 bg-slate-100 border border-slate-200 rounded-xl flex items-center justify-center text-slate-700 text-lg">
                        <i class="fas fa-file-shield"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-tight mb-1">Verifikasi PDF</h3>
                        <p class="text-xs text-slate-500 leading-relaxed font-medium">
                            Cek keaslian & status TTE dokumen dinas.
                        </p>
                    </div>
                </div>
                <div class="pt-6">
                    <a href="<?= site_url('verifikasi-pdf') ?>" id="action-verify-pdf" class="btn btn-outline w-full flex items-center justify-center gap-2 text-xs py-3 group-hover:bg-slate-700 group-hover:text-white group-hover:border-slate-700">
                        Verifikasi PDF <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Card 2: Helpdesk Layanan -->
            <div class="group bg-white border border-slate-200 hover:border-slate-400 rounded-2xl p-7 flex flex-col justify-between transition-all duration-300 hover:shadow-xl hover:shadow-slate-200/50 relative overflow-hidden">
                <div class="space-y-4">
                    <div class="w-12 h-12 bg-slate-100 border border-slate-200 rounded-xl flex items-center justify-center text-slate-700 text-lg">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-tight mb-1">Helpdesk</h3>
                        <p class="text-xs text-slate-500 leading-relaxed font-medium">
                            Kendala email dinas atau isu teknis TTE.
                        </p>
                    </div>
                </div>
                <div class="pt-6">
                    <a href="<?= site_url('helpdesk') ?>" id="action-helpdesk" class="btn btn-outline w-full flex items-center justify-center gap-2 text-xs py-3 group-hover:bg-slate-700 group-hover:text-white group-hover:border-slate-700">
                        Helpdesk <i class="fas fa-arrow-right text-[10px]"></i>
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
