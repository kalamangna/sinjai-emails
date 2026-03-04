<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
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
            @apply bg-slate-300;
        }

        /* Choices.js Slate Theme Overrides */
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

        /* Sidebar Desktop Collapse */
        @media (min-width: 1024px) {
            .sidebar-collapsed #sidebar {
                transform: translateX(-100%);
            }
            .sidebar-collapsed #main-content {
                margin-left: 0;
            }
        }
        
        #sidebar, #main-content {
            transition: transform 0.3s ease-in-out, margin-left 0.3s ease-in-out;
        }
    </style>

    <?= $this->renderSection('styles') ?>

    <!-- Alpine.js Plugins -->
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Alpine.js Core -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <script>
        /**
         * Sidebar Persistence Init
         * Applied in head to prevent UI flicker before render.
         */
        (function() {
            const collapsed = localStorage.getItem('sidebar-collapsed') === 'true';
            if (collapsed && window.innerWidth >= 1024) {
                document.documentElement.classList.add('sidebar-collapsed');
            }
            
            const activeMenu = localStorage.getItem('sidebar-active-menu');
            if (activeMenu) {
                document.documentElement.setAttribute('data-sidebar-menu', activeMenu);
            }
        })();
    </script>

    <style>
        /* Essential behavior classes only (no design changes) */
        .sidebar-submenu {
            display: none;
            overflow: hidden;
            transition: height 0.3s ease-in-out;
        }
        
        /* Force show based on persisted data-attribute */
        html[data-sidebar-menu="pegawai"] #submenu-pegawai,
        html[data-sidebar-menu="pejabat"] #submenu-pejabat,
        html[data-sidebar-menu="organisasi"] #submenu-organisasi,
        html[data-sidebar-menu="website"] #submenu-website,
        html[data-sidebar-menu="batch"] #submenu-batch,
        html[data-sidebar-menu="master"] #submenu-master {
            display: block;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        /* Prevent transitions on load */
        .no-transition * {
            transition: none !important;
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
            @apply bg-slate-300;
        }

        /* Choices.js Slate Theme Overrides */
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

        /* Sidebar Desktop Collapse */
        @media (min-width: 1024px) {
            .sidebar-collapsed #sidebar {
                transform: translateX(-100%);
            }
            .sidebar-collapsed #main-content {
                margin-left: 0;
            }
        }
        
        #sidebar, #main-content {
            transition: transform 0.3s ease-in-out, margin-left 0.3s ease-in-out;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased font-inter no-transition">
    <script>
        // Remove no-transition after first paint
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                document.body.classList.remove('no-transition');
            }, 100);
        });
    </script>
    <!-- Global Loading Overlay -->
    <div id="global-loading" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-all duration-300">
        <div class="bg-white p-8 rounded-2xl shadow-2xl flex flex-col items-center gap-4 max-w-xs w-full border border-white/20">
            <div class="relative">
                <div class="w-12 h-12 border-4 border-slate-100 rounded-full"></div>
                <div class="w-12 h-12 border-4 border-slate-700 border-t-transparent rounded-full animate-spin absolute inset-0"></div>
            </div>
            <div class="text-center">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-tight">Mohon Tunggu</h3>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Sedang Memproses...</p>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <?= $this->include('components/sidebar') ?>

    <!-- Main Wrapper -->
    <div id="main-content" class="lg:ml-64 min-h-screen flex flex-col">
        <!-- Header / Topbar -->
        <header class="h-16 bg-white border-b border-slate-200 sticky top-0 z-40 flex items-center justify-between px-6">
            <div class="flex items-center">
                <!-- Sidebar Toggle -->
                <button id="sidebar-toggle" class="w-10 h-10 flex items-center justify-center text-slate-700 hover:bg-slate-50 rounded-lg mr-2 transition-colors">
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
                        <div class="flash-message bg-slate-700 text-white px-5 py-3 rounded-lg flex items-center justify-between shadow-sm transform transition-all duration-500 ease-in-out" role="alert">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle mr-3 text-white"></i>
                                <span class="font-bold text-xs uppercase tracking-wider"><?= $msg === true ? 'Berhasil' : $msg ?></span>
                            </div>
                            <button onclick="const p = this.parentElement; const container = p.parentElement; p.remove(); if(container && container.children.length === 0) container.remove();" class="text-white/50 hover:text-white transition-colors focus:outline-none">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($err = session()->getFlashdata('error')): ?>
                        <div class="flash-message bg-red-600 text-white px-5 py-3 rounded-lg flex items-center justify-between shadow-sm transform transition-all duration-500 ease-in-out" role="alert">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle mr-3 text-white"></i>
                                <span class="font-bold text-xs uppercase tracking-wider"><?= $err ?></span>
                            </div>
                            <button onclick="const p = this.parentElement; const container = p.parentElement; p.remove(); if(container && container.children.length === 0) container.remove();" class="text-white/50 hover:text-white transition-colors focus:outline-none">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($info = session()->getFlashdata('info')): ?>
                        <div class="flash-message bg-slate-800 text-white px-5 py-3 rounded-lg flex items-center justify-between shadow-sm transform transition-all duration-500 ease-in-out" role="alert">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle mr-3 text-white"></i>
                                <span class="font-bold text-xs uppercase tracking-wider"><?= $info ?></span>
                            </div>
                            <button onclick="const p = this.parentElement; const container = p.parentElement; p.remove(); if(container && container.children.length === 0) container.remove();" class="text-white/50 hover:text-white transition-colors focus:outline-none">
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
        /**
         * Sidebar & Navigation Interaction Logic
         * Implements accordion behavior, strict URL matching, and mobile offcanvas.
         */
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const html = document.documentElement;
            const allLinks = sidebar.querySelectorAll('a');
            const submenus = sidebar.querySelectorAll('.sidebar-submenu');
            const currentUrl = window.location.href.split('#')[0]; // Strict match including query params

            // --- 1. ACTIVE STATE & AUTO-EXPAND ---
            let activeGroupId = null;
            let foundActive = false;

            allLinks.forEach(link => {
                const linkUrl = link.href.split('#')[0];
                
                // Strict match
                if (linkUrl === currentUrl) {
                    link.setAttribute('aria-current', 'page');
                    foundActive = true;
                    
                    // Prevent reload on active link
                    link.addEventListener('click', (e) => {
                        if (link.href === window.location.href) e.preventDefault();
                    });

                    // Identify parent group
                    const parentSubmenu = link.closest('.sidebar-submenu');
                    if (parentSubmenu) {
                        activeGroupId = parentSubmenu.id.replace('submenu-', '');
                        localStorage.setItem('sidebar-active-menu', activeGroupId);
                        html.setAttribute('data-sidebar-menu', activeGroupId);
                    }
                }
            });

            // If we are on a page that doesn't belong to any group (like Dashboard), clear storage
            if (!activeGroupId && foundActive) {
                localStorage.setItem('sidebar-active-menu', '');
                html.setAttribute('data-sidebar-menu', '');
            }

            // --- 2. ACCORDION LOGIC ---
            const clearAllActive = () => {
                submenus.forEach(menu => {
                    menu.style.display = 'none';
                    const parentBtn = menu.previousElementSibling;
                    if (parentBtn) {
                        parentBtn.setAttribute('aria-expanded', 'false');
                        parentBtn.classList.remove('active-parent');
                    }
                });
                localStorage.setItem('sidebar-active-menu', '');
                html.setAttribute('data-sidebar-menu', '');
            };

            const toggleSubmenu = (groupId, forceOpen = null) => {
                const targetId = `submenu-${groupId}`;
                
                submenus.forEach(menu => {
                    const parentBtn = menu.previousElementSibling;
                    const isTarget = menu.id === targetId;
                    const shouldOpen = forceOpen !== null ? (isTarget && forceOpen) : (isTarget && window.getComputedStyle(menu).display === 'none');

                    if (shouldOpen) {
                        menu.style.display = 'block';
                        if (parentBtn) {
                            parentBtn.setAttribute('aria-expanded', 'true');
                            parentBtn.classList.add('active-parent');
                        }
                    } else {
                        // Close others (Accordion)
                        menu.style.display = 'none';
                        if (parentBtn) {
                            parentBtn.setAttribute('aria-expanded', 'false');
                            parentBtn.classList.remove('active-parent');
                        }
                    }
                });

                if (forceOpen === null) {
                    const isOpen = window.getComputedStyle(document.getElementById(targetId)).display === 'block';
                    const activeMenuValue = isOpen ? groupId : '';
                    localStorage.setItem('sidebar-active-menu', activeMenuValue);
                    html.setAttribute('data-sidebar-menu', activeMenuValueValue);
                }
            };

            // Attach toggle listeners
            sidebar.querySelectorAll('[data-sidebar-toggle]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    toggleSubmenu(btn.getAttribute('data-sidebar-toggle'));
                });
            });

            // Attach clear listeners
            sidebar.querySelectorAll('[data-sidebar-clear]').forEach(link => {
                link.addEventListener('click', () => {
                    clearAllActive();
                });
            });

            // --- 3. MOBILE OFF-CANVAS & OVERLAY ---
            let overlay = document.getElementById('sidebar-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'sidebar-overlay';
                overlay.className = 'fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 lg:hidden transition-opacity duration-300 opacity-0 pointer-events-none';
                document.body.appendChild(overlay);
            }

            const closeSidebar = () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0', 'pointer-events-none');
                overlay.classList.remove('opacity-100', 'pointer-events-auto');
                document.body.style.overflow = '';
            };

            const openSidebar = () => {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('opacity-100', 'pointer-events-auto');
                overlay.classList.remove('opacity-0', 'pointer-events-none');
                document.body.style.overflow = 'hidden';
            };

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', () => {
                    if (window.innerWidth < 1024) {
                        const isHidden = sidebar.classList.contains('-translate-x-full');
                        isHidden ? openSidebar() : closeSidebar();
                    } else {
                        html.classList.toggle('sidebar-collapsed');
                        localStorage.setItem('sidebar-collapsed', html.classList.contains('sidebar-collapsed'));
                    }
                });
            }

            overlay.addEventListener('click', closeSidebar);
            document.addEventListener('keydown', (e) => { e.key === 'Escape' && closeSidebar(); });
            allLinks.forEach(l => l.addEventListener('click', () => { window.innerWidth < 1024 && closeSidebar(); }));

            // --- 4. INITIALIZE STATE ---
            const initialGroup = localStorage.getItem('sidebar-active-menu') || activeGroupId;
            if (initialGroup) {
                toggleSubmenu(initialGroup, true);
            }

            // Remove no-transition after first paint
            setTimeout(() => { document.body.classList.remove('no-transition'); }, 100);
        });

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
                    setTimeout(() => {
                        const parent = msg.parentElement;
                        msg.remove();
                        if (parent && parent.children.length === 0) {
                            parent.remove();
                        }
                    }, 500);
                }, 5000);
            });
        });

        // Global status color mapper
        function getJsStatusColor(status) {
            status = status.toUpperCase();
            if (['ISSUE', 'AKTIF', 'ACTIVE', 'YA'].includes(status)) return 'bg-emerald-100 text-emerald-800 border-transparent';
            if (['EXPIRED', 'REVOKE', 'SUSPEND', 'NONAKTIF', 'INACTIVE', 'DITANGGUHKAN', 'TIDAK'].includes(status)) return 'bg-red-100 text-red-700 border-transparent';
            if (['WAITING_FOR_VERIFICATION', 'RENEW', 'PENDING', 'NO_CERTIFICATE'].includes(status)) return 'bg-amber-50 text-amber-500 border-amber-200';
            if (['NEW', 'BARU'].includes(status)) return 'bg-blue-100 text-slate-700 border-transparent';
            return 'bg-slate-100 text-slate-700 border-slate-200';
        }

        // Global loading helper
        function showGlobalLoading(show = true) {
            const overlay = document.getElementById('global-loading');
            if (!overlay) return;
            if (show) {
                overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }
    </script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>
