<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard' ?> | Sistem Identitas Digital</title>

    <!-- Tailwind CSS (Local Build) -->
    <link href="<?= base_url('css/output.css') ?>" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Choices.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            @apply bg-slate-200;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            @apply bg-slate-200;
        }

        /* Choices.js Neutral Theme Overrides */
        .choices__inner {
            @apply bg-white border-slate-200 rounded-lg text-sm font-medium text-slate-700 !important;
            min-height: 38px !important;
            padding: 4px 12px !important;
        }

        .choices__list--dropdown {
            @apply bg-white border-slate-200 rounded-lg shadow-xl !important;
        }

        .choices__list--dropdown .choices__item--selectable.is-highlighted {
            @apply bg-slate-100 !important;
        }

        .choices__input {
            @apply bg-transparent text-sm !important;
        }

        .choices__placeholder {
            @apply text-slate-700 opacity-100 !important;
        }
    </style>

    <?= $this->renderSection('styles') ?>

    <!-- Alpine.js Plugins -->
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Alpine.js Core -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-slate-50 text-slate-700 antialiased font-inter">
    <!-- Sidebar -->
    <?= $this->include('components/sidebar') ?>

    <!-- Main Wrapper -->
    <div id="main-content" class="lg:ml-64 min-h-screen flex flex-col">
        <!-- Header / Topbar -->
        <header class="h-16 bg-white border-b border-slate-200 sticky top-0 z-40 flex items-center justify-between px-6">
            <div class="flex items-center">
                <!-- Mobile Toggle -->
                <button id="sidebar-toggle" class="lg:hidden w-10 h-10 flex items-center justify-center text-slate-700 hover:bg-slate-50 rounded-lg mr-2 transition-colors">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- App Title (Mobile Only) -->
                <div class="flex items-center lg:hidden">
                    <div class="w-8 h-8 bg-slate-800 rounded-lg flex items-center justify-center mr-3 shadow-sm">
                        <i class="fas fa-fingerprint text-white text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="block text-sm font-bold tracking-tight text-slate-800 leading-none uppercase">sinjai<span class="text-slate-700">emails</span></span>
                        <span class="text-[8px] font-bold text-slate-700 uppercase tracking-widest block mt-0.5">identitas digital</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <!-- User Information -->
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex flex-col items-end">
                        <p class="text-xs font-bold text-slate-800 leading-none uppercase"><?= session()->get('username') ?></p>
                        <p class="text-[9px] font-bold text-slate-700 uppercase mt-1 tracking-widest">
                            <?= session()->get('role') == 'super_admin' ? 'Super Admin' : 'Admin' ?>
                        </p>
                    </div>
                    <div class="w-9 h-9 bg-slate-100 rounded-lg flex items-center justify-center text-slate-700 border border-slate-200 shadow-sm">
                        <i class="fas fa-user-shield text-sm"></i>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="flex-grow p-6">
            <!-- Global Flash Messages -->
            <?php if (session()->getFlashdata('success') || session()->getFlashdata('message') || session()->getFlashdata('error') || session()->getFlashdata('info')): ?>
                <div class="mb-6 space-y-2">
                    <?php if ($msg = session()->getFlashdata('success') ?: session()->getFlashdata('message')): ?>
                        <div class="flash-message bg-emerald-600 text-white px-5 py-3 rounded-xl flex items-center justify-between shadow-sm transform transition-all duration-500 ease-in-out" role="alert">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle mr-3 text-white"></i>
                                <span class="font-bold text-xs uppercase tracking-wider"><?= $msg === true ? 'Berhasil' : $msg ?></span>
                            </div>
                            <button onclick="this.parentElement.remove()" class="text-white/50 hover:text-white transition-colors focus:outline-none">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($err = session()->getFlashdata('error')): ?>
                        <div class="flash-message bg-red-600 text-white px-5 py-3 rounded-xl flex items-center justify-between shadow-sm transform transition-all duration-500 ease-in-out" role="alert">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle mr-3 text-white"></i>
                                <span class="font-bold text-xs uppercase tracking-wider"><?= $err ?></span>
                            </div>
                            <button onclick="this.parentElement.remove()" class="text-white/50 hover:text-white transition-colors focus:outline-none">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($info = session()->getFlashdata('info')): ?>
                        <div class="flash-message bg-blue-600 text-white px-5 py-3 rounded-xl flex items-center justify-between shadow-sm transform transition-all duration-500 ease-in-out" role="alert">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle mr-3 text-white"></i>
                                <span class="font-bold text-xs uppercase tracking-wider"><?= $info ?></span>
                            </div>
                            <button onclick="this.parentElement.remove()" class="text-white/50 hover:text-white transition-colors focus:outline-none">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </main>

        <!-- Footer Component -->
        <footer class="py-6 px-6 border-t border-slate-200 bg-white">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">
                    &copy; <?= tahunSekarang() ?> Diskominfo-SP Sinjai
                </p>
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        // Global Choices.js initialization
        document.addEventListener('DOMContentLoaded', () => {
            const searchSelects = document.querySelectorAll('.choices-search');
            searchSelects.forEach(select => {
                new Choices(select, {
                    searchEnabled: true,
                    itemSelectText: '',
                    placeholder: true,
                    searchPlaceholderValue: 'Cari...',
                    shouldSort: false,
                    loadingText: 'Memuat...',
                    noResultsText: 'Tidak ditemukan',
                    noChoicesText: 'Tidak ada pilihan',
                });
            });
        });

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

        // Sidebar Toggle for Mobile
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebar-toggle');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 1024) {
                if (sidebar && !sidebar.contains(e.target) && sidebarToggle && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.add('-translate-x-full');
                }
            }
        });

        // Global status color mapper
        function getJsStatusColor(status) {
            status = status.toUpperCase();
            if (['ISSUE', 'AKTIF', 'ACTIVE', 'YA'].includes(status)) return 'bg-emerald-50 text-emerald-600 border-emerald-200';
            if (['EXPIRED', 'REVOKE', 'SUSPEND', 'NONAKTIF', 'INACTIVE', 'DITANGGUHKAN', 'TIDAK'].includes(status)) return 'bg-red-50 text-red-600 border-red-200';
            if (['WAITING_FOR_VERIFICATION', 'RENEW', 'PENDING'].includes(status)) return 'bg-amber-50 text-amber-500 border-amber-200';
            if (['NEW', 'BARU'].includes(status)) return 'bg-blue-50 text-blue-600 border-blue-200';
            return 'bg-slate-100 text-slate-700 border-slate-200';
        }
    </script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>