<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="<?= $meta_robots ?? 'noindex, nofollow' ?>">
    <title><?= $title ?? 'Dashboard' ?> | Sistem Identitas Digital</title>

    <meta name="description" content="<?= $meta_description ?? 'Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai' ?>">
    <link rel="canonical" href="<?= current_url() ?>">

    <!-- Meta Tags -->
    <meta property="og:site_name" content="Sistem Identitas Digital Sinjai">
    <meta property="og:title" content="<?= $title ?? 'Dashboard' ?> | Sistem Identitas Digital">
    <meta property="og:description" content="<?= $meta_description ?? 'Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai' ?>">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:image" content="<?= $meta_image ?? base_url('meta.png') ?>">
    <meta property="og:type" content="<?= $meta_type ?? 'website' ?>">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title ?? 'Dashboard' ?> | Sistem Identitas Digital">
    <meta name="twitter:description" content="<?= $meta_description ?? 'Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai' ?>">
    <meta name="twitter:image" content="<?= $meta_image ?? base_url('meta.png') ?>">

    <link rel="icon" type="image/png" href="<?= base_url('logo.png') ?>">

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

    <?= $this->renderSection('styles') ?>

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
            
            window.BASE_URL = '<?= rtrim(base_url(), '/') ?>';
        })();
    </script>

    <style>
        [x-cloak] { display: none !important; }
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

    <?php
    $errorContent = '<div id="error-modal-message" class="text-xs font-medium text-slate-600 leading-relaxed max-h-48 overflow-y-auto custom-scrollbar p-2 bg-slate-50 rounded-lg text-center"></div>';
    $errorFooter = '
        <button onclick="closeModal(\'global-error-modal\')" class="px-6 py-2 bg-slate-800 text-white text-[10px] font-bold uppercase tracking-widest rounded-lg hover:bg-slate-700 transition-colors focus:outline-none">
            Tutup
        </button>
    ';
    echo view('components/modal', [
        'id' => 'global-error-modal',
        'title' => 'Terjadi Kesalahan',
        'size' => 'sm',
        'content' => $errorContent,
        'footer' => $errorFooter
    ], ['saveData' => false]);
    ?>

    <!-- Global Sync Result Modal -->
    <?php
    $syncResultContent = '
        <div class="flex flex-col items-center gap-4 py-2">
            <div id="sync-result-icon" class="w-14 h-14 rounded-full flex items-center justify-center text-2xl"></div>
            <p id="sync-result-message" class="text-sm font-semibold text-slate-700 text-center leading-relaxed"></p>
            <div id="sync-result-stats" class="flex gap-3 w-full"></div>
        </div>
    ';
    $syncResultFooter = '
        <button onclick="closeModal(\'global-sync-result-modal\')" class="px-6 py-2 bg-slate-800 text-white text-[10px] font-bold uppercase tracking-widest rounded-lg hover:bg-slate-700 transition-colors focus:outline-none">
            OK
        </button>
    ';
    echo view('components/modal', [
        'id'      => 'global-sync-result-modal',
        'title'   => 'Hasil Sinkronisasi',
        'size'    => 'sm',
        'content' => $syncResultContent,
        'footer'  => $syncResultFooter,
    ], ['saveData' => false]);
    ?>


    <?php $isPublic = $isPublic ?? false; ?>
    <!-- Sidebar -->
    <?php if (!$isPublic): ?>
        <?= $this->include('components/sidebar') ?>
    <?php endif; ?>

    <!-- Main Wrapper -->
    <div id="main-content" class="<?= !$isPublic ? 'lg:ml-64' : '' ?> min-h-screen flex flex-col">
        <!-- Header / Topbar -->
        <?php if (!$isPublic): ?>

        <header class="h-16 bg-white border-b border-slate-200 sticky top-0 z-40 relative">
            <div class="h-16 flex items-center justify-between px-3 sm:px-6 gap-2">

                <!-- Left: Sidebar toggle + Search -->
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <!-- Sidebar Toggle -->
                    <button id="sidebar-toggle" class="w-10 h-10 flex items-center justify-center text-slate-700 hover:bg-slate-50 rounded-lg transition-colors shrink-0" aria-label="Toggle Sidebar">
                        <i class="fas fa-bars"></i>
                    </button>

                    <!-- Global Search (selalu tampil) -->
                    <div class="flex-1 max-w-xl w-full px-2">
                        <?= $this->include('components/global_search') ?>
                    </div>
                </div>

                <!-- Right: Actions -->
                <div class="flex items-center gap-2 shrink-0">
                    <!-- Verifikasi PDF (desktop only) -->
                    <a href="<?= site_url('verifikasi-pdf') ?>" target="_blank" rel="noopener noreferrer" title="Verifikasi PDF" class="hidden sm:flex w-10 h-10 items-center justify-center text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                        <i class="fas fa-file-signature text-base"></i>
                    </a>

                    <!-- Riwayat Laporan (desktop only) -->
                    <a href="<?= site_url('reports/history') ?>" title="Riwayat Laporan" class="hidden sm:flex w-10 h-10 items-center justify-center text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                        <i class="fas fa-history text-base"></i>
                    </a>

                    <!-- User Dropdown (Flowbite) -->
                    <div class="relative" id="user-dropdown-container">
                        <button id="user-dropdown-button" data-dropdown-toggle="user-dropdown-menu" data-dropdown-placement="bottom-end" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-50 transition-all focus:outline-none" aria-haspopup="true">
                            <div class="hidden md:flex flex-col items-end">
                                <p class="text-xs font-bold text-slate-800 leading-none uppercase truncate max-w-[120px]"><?= session()->get('name') ?: session()->get('username') ?></p>
                                <p class="text-[9px] font-bold text-slate-500 uppercase mt-1 tracking-widest">
                                    <?= session()->get('role') == 'super_admin' ? 'Super Admin' : 'Admin' ?>
                                </p>
                            </div>
                            <div id="user-icon-wrapper" class="w-9 h-9 bg-slate-100 rounded-lg flex items-center justify-center text-slate-700 border border-slate-200 shadow-sm transition-transform duration-200">
                                <i class="fas fa-user-shield text-sm"></i>
                            </div>
                            <i id="user-dropdown-chevron" class="fas fa-chevron-down text-[8px] text-slate-400 transition-transform duration-200 hidden sm:inline"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="user-dropdown-menu"
                             class="hidden w-56 bg-white border border-slate-200 rounded-xl shadow-2xl py-2 z-50 overflow-hidden">

                            <!-- User info -->
                            <div class="px-4 py-2 mb-1 border-b border-slate-100">
                                <p class="text-xs font-bold text-slate-800 uppercase truncate"><?= session()->get('name') ?: session()->get('username') ?></p>
                                <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-0.5"><?= session()->get('role') == 'super_admin' ? 'Super Admin' : 'Admin' ?></p>
                            </div>

                            <!-- Mobile-only: Verifikasi PDF & Riwayat Laporan -->
                            <div class="sm:hidden">
                                <a href="<?= site_url('verifikasi-pdf') ?>" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-all uppercase tracking-tight">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100/50">
                                        <i class="fas fa-file-signature text-[10px]"></i>
                                    </div>
                                    Verifikasi PDF
                                </a>
                                <a href="<?= site_url('reports/history') ?>" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-all uppercase tracking-tight">
                                    <div class="w-8 h-8 rounded-lg bg-slate-50 text-slate-600 flex items-center justify-center border border-slate-100/50">
                                        <i class="fas fa-history text-[10px]"></i>
                                    </div>
                                    Riwayat Laporan
                                </a>
                                <div class="h-px bg-slate-100 my-1 mx-2"></div>
                            </div>

                            <a href="<?= site_url('user/change_password') ?>" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-all uppercase tracking-tight">
                                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100/50">
                                    <i class="fas fa-key text-[10px]"></i>
                                </div>
                                Ganti Password
                            </a>

                            <div class="h-px bg-slate-100 my-1 mx-2"></div>

                            <a href="<?= site_url('logout') ?>" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-red-600 hover:bg-red-50 transition-all uppercase tracking-tight">
                                <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center border border-red-100/50">
                                    <i class="fas fa-power-off text-[10px]"></i>
                                </div>
                                Keluar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>


        <?php endif; ?>

        <!-- Content Area -->
        <main class="flex-grow p-6">
            <!-- Global Flash Messages (Flowbite Dismiss) -->
            <?php if (session()->getFlashdata('success') || session()->getFlashdata('message') || session()->getFlashdata('error') || session()->getFlashdata('info')): ?>
                <div class="mb-6 space-y-2">
                    <?php if ($msg = session()->getFlashdata('success') ?: session()->getFlashdata('message')): ?>
                        <div id="toast-success" class="transition-opacity duration-300 bg-slate-700 text-white px-5 py-3 rounded-lg flex items-center justify-between shadow-sm" role="alert">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle mr-3 text-white"></i>
                                <span class="font-bold text-xs uppercase tracking-wider"><?= $msg === true ? 'Berhasil' : $msg ?></span>
                            </div>
                            <button type="button" data-dismiss-target="#toast-success" class="text-white/50 hover:text-white transition-colors focus:outline-none" aria-label="Close">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                        <script>
                            setTimeout(() => {
                                const toast = document.getElementById('toast-success');
                                if (toast) {
                                    toast.classList.add('opacity-0');
                                    setTimeout(() => toast.remove(), 300);
                                }
                            }, 5000);
                        </script>
                    <?php endif; ?>

                    <?php if ($err = session()->getFlashdata('error')): ?>
                        <div id="toast-error" class="transition-opacity duration-300 bg-red-600 text-white px-5 py-3 rounded-lg flex items-center justify-between shadow-sm" role="alert">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle mr-3 text-white"></i>
                                <span class="font-bold text-xs uppercase tracking-wider"><?= $err ?></span>
                            </div>
                            <button type="button" data-dismiss-target="#toast-error" class="text-white/50 hover:text-white transition-colors focus:outline-none" aria-label="Close">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                        <script>
                            setTimeout(() => {
                                const toast = document.getElementById('toast-error');
                                if (toast) {
                                    toast.classList.add('opacity-0');
                                    setTimeout(() => toast.remove(), 300);
                                }
                            }, 5000);
                        </script>
                    <?php endif; ?>

                    <?php if ($info = session()->getFlashdata('info')): ?>
                        <div id="toast-info" class="transition-opacity duration-300 bg-slate-800 text-white px-5 py-3 rounded-lg flex items-center justify-between shadow-sm" role="alert">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle mr-3 text-white"></i>
                                <span class="font-bold text-xs uppercase tracking-wider"><?= $info ?></span>
                            </div>
                            <button type="button" data-dismiss-target="#toast-info" class="text-white/50 hover:text-white transition-colors focus:outline-none" aria-label="Close">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                        <script>
                            setTimeout(() => {
                                const toast = document.getElementById('toast-info');
                                if (toast) {
                                    toast.classList.add('opacity-0');
                                    setTimeout(() => toast.remove(), 300);
                                }
                            }, 5000);
                        </script>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </main>

        <!-- Footer Component -->
        <?php if (!($hideFooter ?? false)): ?>
        <footer class="py-6 px-6 border-t border-slate-200 bg-white">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">
                    &copy; <?= tahunSekarang() ?> Diskominfo-SP Sinjai
                </p>
            </div>
        </footer>
        <?php endif; ?>
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
                    html.setAttribute('data-sidebar-menu', activeMenuValue);
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

            // --- 3. MOBILE OFF-CANVAS & OVERLAY (Flowbite Drawer JS API) ---
            let sidebarDrawer = null;
            
            const initDrawer = () => {
                if (window.innerWidth < 1024) {
                    if (!sidebarDrawer) {
                        const options = {
                            placement: 'left',
                            backdrop: true,
                            bodyScrolling: false,
                            edge: false,
                            backdropClasses: 'bg-slate-900/50 backdrop-blur-sm fixed inset-0 z-40 lg:hidden',
                            onHide: () => {
                                sidebar.classList.add('-translate-x-full');
                            },
                            onShow: () => {
                                sidebar.classList.remove('-translate-x-full');
                            }
                        };
                        sidebarDrawer = new Drawer(sidebar, options);
                    }
                } else {
                    if (sidebarDrawer) {
                        sidebarDrawer.destroy();
                        sidebarDrawer = null;
                        sidebar.classList.remove('-translate-x-full');
                    }
                }
            };

            // Init on load
            initDrawer();
            
            // Re-init on resize
            window.addEventListener('resize', initDrawer);

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', () => {
                    if (window.innerWidth < 1024) {
                        initDrawer();
                        sidebarDrawer.toggle();
                    } else {
                        html.classList.toggle('sidebar-collapsed');
                        localStorage.setItem('sidebar-collapsed', html.classList.contains('sidebar-collapsed'));
                    }
                });
            }

            // Close drawer when sidebar links are clicked (on mobile)
            allLinks.forEach(l => l.addEventListener('click', () => {
                if (window.innerWidth < 1024 && sidebarDrawer) {
                    sidebarDrawer.hide();
                }
            }));

            // --- 4. INITIALIZE STATE ---
            const initialGroup = localStorage.getItem('sidebar-active-menu') || activeGroupId;
            if (initialGroup) {
                toggleSubmenu(initialGroup, true);
            }

            // Remove no-transition after first paint
            setTimeout(() => { document.body.classList.remove('no-transition'); }, 100);
        });

        // Global Choices.js initialization (only for select elements with 10 or more options)
        document.addEventListener('DOMContentLoaded', () => {
            const searchSelects = document.querySelectorAll('.choices-search');
            searchSelects.forEach(select => {
                if (select && select.tagName === 'SELECT' && select.options && select.options.length >= 10) {
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
                }
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

        // Global error modal helpers
        function showGlobalError(title, message) {
            const titleEl = document.getElementById('global-error-modal-title');
            const messageEl = document.getElementById('error-modal-message');
            if (titleEl) titleEl.innerText = title || 'Terjadi Kesalahan';
            if (messageEl) messageEl.innerText = message || 'Gagal memproses permintaan.';
            openModal('global-error-modal');
        }

        function hideGlobalError() {
            closeModal('global-error-modal');
        }

        /**
         * Tampilkan modal hasil batch sync.
         * @param {number} total   - Total item yang diproses
         * @param {number} success - Jumlah yang berhasil
         * @param {number} failed  - Jumlah yang gagal
         */
        function showSyncResult(total, success, failed) {
            const allOk  = failed === 0;
            const allFail = success === 0;

            const iconEl   = document.getElementById('sync-result-icon');
            const msgEl    = document.getElementById('sync-result-message');
            const statsEl  = document.getElementById('sync-result-stats');

            if (iconEl) {
                if (allOk) {
                    iconEl.className = 'w-14 h-14 rounded-full flex items-center justify-center text-2xl bg-emerald-100';
                    iconEl.innerHTML = '<i class="fas fa-check text-emerald-600"></i>';
                } else if (allFail) {
                    iconEl.className = 'w-14 h-14 rounded-full flex items-center justify-center text-2xl bg-red-100';
                    iconEl.innerHTML = '<i class="fas fa-times text-red-500"></i>';
                } else {
                    iconEl.className = 'w-14 h-14 rounded-full flex items-center justify-center text-2xl bg-amber-100';
                    iconEl.innerHTML = '<i class="fas fa-exclamation text-amber-500"></i>';
                }
            }

            if (msgEl) {
                if (allOk) {
                    msgEl.textContent = 'Sinkronisasi selesai. Semua data berhasil diperbarui.';
                } else if (allFail) {
                    msgEl.textContent = 'Sinkronisasi selesai, namun semua item gagal diproses.';
                } else {
                    msgEl.textContent = `Sinkronisasi selesai dengan ${failed} item gagal.`;
                }
            }

            if (statsEl) {
                const statCard = (label, value, color) =>
                    `<div class="flex-1 rounded-lg p-3 text-center ${color}">
                        <div class="text-xl font-bold">${value}</div>
                        <div class="text-[10px] font-bold uppercase tracking-widest mt-0.5">${label}</div>
                    </div>`;
                statsEl.innerHTML =
                    statCard('Total',    total,   'bg-slate-100 text-slate-700') +
                    statCard('Berhasil', success, 'bg-emerald-50 text-emerald-700') +
                    statCard('Gagal',    failed,  'bg-red-50 text-red-600');
            }

            openModal('global-sync-result-modal');
        }

    </script>

    <script src="<?= base_url('js/sync-helper.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>
