<!DOCTYPE html>
<html lang="id">

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
            background: #e2e8f0;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }

        /* Choices.js Neutral Theme Overrides */
        .choices__inner {
            @apply bg-white border-gray-300 rounded-lg text-sm font-medium text-gray-900 !important;
            min-height: 38px !important;
            padding: 4px 12px !important;
        }
        .choices__list--dropdown {
            @apply bg-white border-gray-300 rounded-lg shadow-xl !important;
        }
        .choices__list--dropdown .choices__item--selectable.is-highlighted {
            @apply bg-gray-100 !important;
        }
        .choices__input {
            @apply bg-transparent text-sm !important;
        }
        .choices__placeholder {
            @apply text-gray-400 opacity-100 !important;
        }
    </style>

    <?= $this->renderSection('styles') ?>
</head>

<body class="bg-gray-50 text-gray-900 antialiased">
    <!-- Sidebar -->
    <?= $this->include('components/sidebar') ?>

    <!-- Main Wrapper -->
    <div id="main-content" class="lg:ml-64 min-h-screen flex flex-col">
        <!-- Topbar -->
        <?= $this->include('components/topbar') ?>

        <!-- Content Area -->
        <main class="flex-grow p-6">
            <!-- Global Flash Messages -->
            <?php if (session()->getFlashdata('success') || session()->getFlashdata('message') || session()->getFlashdata('error') || session()->getFlashdata('info')): ?>
                <div class="mb-6 space-y-2">
                    <?php if ($msg = session()->getFlashdata('success') ?: session()->getFlashdata('message')): ?>
                        <div class="flash-message bg-gray-900 text-white px-5 py-3 rounded-xl flex items-center justify-between shadow-lg transform transition-all duration-500 ease-in-out" role="alert">
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
                        <div class="flash-message bg-red-600 text-white px-5 py-3 rounded-xl flex items-center justify-between shadow-lg transform transition-all duration-500 ease-in-out" role="alert">
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
                        <div class="flash-message bg-blue-600 text-white px-5 py-3 rounded-xl flex items-center justify-between shadow-lg transform transition-all duration-500 ease-in-out" role="alert">
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
    </script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>
