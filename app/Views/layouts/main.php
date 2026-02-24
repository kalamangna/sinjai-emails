<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard' ?> | Sinjai Emails - Portal Identitas Digital</title>

    <!-- Tailwind CSS (Local Build) -->
    <link href="<?= base_url('css/output.css') ?>" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts: Plus Jakarta Sans for a modern tech feel -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
            height: 5px;
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

        /* Sidebar transitions */
        .sidebar-expanded #sidebar { width: 256px; }
        .sidebar-collapsed #sidebar { width: 80px; }
        .sidebar-collapsed #sidebar .block.text-sm { display: none; }
        .sidebar-collapsed #sidebar .text-\[8px\] { display: none; }
        .sidebar-collapsed #sidebar .px-4.text-\[10px\] { display: none; }
        .sidebar-collapsed #sidebar span:not(.fas) { display: none; }
        .sidebar-collapsed #main-content { margin-left: 80px; }
        
        .card-hover-effect {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover-effect:hover {
            transform: translateY(-4px);
        }
    </style>

    <?= $this->renderSection('styles') ?>
</head>

<body class="bg-slate-50 text-slate-800 antialiased selection:bg-emerald-100 selection:text-emerald-900">
    <!-- Sidebar -->
    <?= $this->include('components/sidebar') ?>

    <!-- Main Wrapper -->
    <div id="main-content" class="transition-all duration-300 lg:ml-64 min-h-screen flex flex-col">
        <!-- Topbar -->
        <?= $this->include('components/topbar') ?>

        <!-- Content Area -->
        <main class="flex-grow p-6 lg:p-10">
            <!-- Breadcrumbs / Header Info (Optional) -->
            <?php if (isset($title) && current_url() != site_url()): ?>
            <div class="mb-8">
                <div class="flex items-center text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                    <a href="<?= site_url('/') ?>" class="hover:text-emerald-600 transition-colors">Home</a>
                    <i class="fas fa-chevron-right mx-2 text-[8px]"></i>
                    <span class="text-slate-600"><?= $title ?></span>
                </div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight"><?= $title ?></h1>
            </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </main>

        <?= $this->include('layouts/partials/footer') ?>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <script>
        // Sidebar Toggle for Mobile
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const mainContent = document.getElementById('main-content');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 1024) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.add('-translate-x-full');
                }
            }
        });
    </script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>
