<aside id="sidebar" class="fixed top-0 left-0 z-50 w-64 h-screen transition-transform bg-slate-800 border-r border-slate-700 flex flex-col lg:translate-x-0 -translate-x-full">
    <!-- Logo Section -->
    <div class="flex items-center h-16 px-6 border-b border-slate-700 flex-shrink-0">
        <a href="<?= site_url('/') ?>" data-sidebar-clear class="flex items-center no-underline">
            <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center mr-3 shadow-sm border border-white/10">
                <i class="fas fa-fingerprint text-white text-sm"></i>
            </div>
            <div>
                <span class="block text-xs font-bold tracking-tight text-white leading-none uppercase">sinjai<span class="text-slate-300">emails</span></span>
                <span class="text-[8px] font-bold text-slate-300 uppercase tracking-widest block mt-0.5">identitas digital</span>
            </div>
        </a>
    </div>

    <?php
    // Get full URL including query parameters for strict matching
    $full_url = current_url();
    if (!empty($_SERVER['QUERY_STRING'])) {
        $full_url .= '?' . $_SERVER['QUERY_STRING'];
    }

    // Helper to check if a URL is active (strict match)
    $isActive = function($url) use ($full_url) {
        return $full_url === site_url($url);
    };

    // Determine default active group based on child matches
    $default_active = '';
    if ($isActive('email/pns_list') || $isActive('email/pppk_list') || $isActive('email/pppk_pw_list')) {
        $default_active = 'pegawai';
    } elseif ($isActive('email/pimpinan') || $isActive('email/pimpinan_desa')) {
        $default_active = 'pejabat';
    } elseif (strpos($full_url, site_url('email/unit_kerja')) !== false && strpos($full_url, 'manage') === false) {
        $default_active = 'organisasi';
    } elseif ($isActive('email/eselon_list') || strpos($full_url, site_url('email/eselon_detail')) !== false) {
        $default_active = 'organisasi';
    } elseif ($isActive('web_opd') || $isActive('web_desa_kelurahan')) {
        $default_active = 'website';
    } elseif ($isActive('batch') || $isActive('batch/update') || $isActive('batch/pk')) {
        $default_active = 'batch';
    } elseif ($isActive('unit_kerja/manage') || strpos($full_url, site_url('auth/users')) !== false) {
        $default_active = 'master';
    }
    ?>

    <!-- Navigation Menu -->
    <nav class="flex-grow py-6 px-4 space-y-1 overflow-y-auto custom-scrollbar">
        <!-- Dashboard -->
        <a href="<?= site_url('/') ?>" data-sidebar-clear class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $isActive('/') ? 'bg-slate-700 text-white shadow-lg shadow-slate-900/20' : 'text-slate-100 hover:bg-slate-700/80 hover:text-white' ?>">
            <div class="w-5 h-5 flex items-center justify-center mr-3 shrink-0">
                <i class="fas fa-th-large <?= $isActive('/') ? 'text-white' : 'text-slate-300' ?>"></i>
            </div>
            Dashboard
        </a>

        <!-- Email -->
        <a href="<?= site_url('email') ?>" data-sidebar-clear class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $isActive('email') ? 'bg-slate-700 text-white shadow-lg shadow-slate-900/20' : 'text-slate-100 hover:bg-slate-700/80 hover:text-white' ?>">
            <div class="w-5 h-5 flex items-center justify-center mr-3 shrink-0">
                <i class="fas fa-envelope <?= $isActive('email') ? 'text-white' : 'text-slate-300' ?>"></i>
            </div>
            Email
        </a>

        <!-- Pegawai Submenu -->
        <div>
            <button data-sidebar-toggle="pegawai" aria-expanded="<?= $default_active === 'pegawai' ? 'true' : 'false' ?>" class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium text-slate-100 rounded-lg hover:bg-slate-700/80 hover:text-white transition-all focus:outline-none">
                <div class="flex items-center">
                    <div class="w-5 h-5 flex items-center justify-center mr-3 shrink-0">
                        <i class="fas fa-users text-slate-300"></i>
                    </div>
                    <span>Pegawai</span>
                </div>
                <i class="fas fa-chevron-down text-[10px] transition-transform duration-200"></i>
            </button>
            <div id="submenu-pegawai" class="sidebar-submenu mt-1 ml-4 pl-4 border-l border-slate-700 space-y-1">
                <a href="<?= site_url('email/pns_list') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $isActive('email/pns_list') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                    PNS
                </a>
                <a href="<?= site_url('email/pppk_list') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $isActive('email/pppk_list') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                    PPPK
                </a>
                <a href="<?= site_url('email/pppk_pw_list') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $isActive('email/pppk_pw_list') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                    PPPK PW
                </a>
            </div>
        </div>

        <!-- Pejabat Submenu -->
        <div>
            <button data-sidebar-toggle="pejabat" aria-expanded="<?= $default_active === 'pejabat' ? 'true' : 'false' ?>" class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium text-slate-100 rounded-lg hover:bg-slate-700/80 hover:text-white transition-all focus:outline-none">
                <div class="flex items-center">
                    <div class="w-5 h-5 flex items-center justify-center mr-3 shrink-0">
                        <i class="fas fa-user-tie text-slate-300"></i>
                    </div>
                    <span>Pejabat</span>
                </div>
                <i class="fas fa-chevron-down text-[10px] transition-transform duration-200"></i>
            </button>
            <div id="submenu-pejabat" class="sidebar-submenu mt-1 ml-4 pl-4 border-l border-slate-700 space-y-1">
                <a href="<?= site_url('email/pimpinan') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $isActive('email/pimpinan') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                    Pimpinan
                </a>
                <a href="<?= site_url('email/pimpinan_desa') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $isActive('email/pimpinan_desa') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                    Kepala Desa
                </a>
            </div>
        </div>

        <!-- Organisasi Submenu -->
        <div>
            <button data-sidebar-toggle="organisasi" aria-expanded="<?= $default_active === 'organisasi' ? 'true' : 'false' ?>" class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium text-slate-100 rounded-lg hover:bg-slate-700/80 hover:text-white transition-all focus:outline-none">
                <div class="flex items-center">
                    <div class="w-5 h-5 flex items-center justify-center mr-3 shrink-0">
                        <i class="fas fa-building text-slate-300"></i>
                    </div>
                    <span>Organisasi</span>
                </div>
                <i class="fas fa-chevron-down text-[10px] transition-transform duration-200"></i>
            </button>
            <div id="submenu-organisasi" class="sidebar-submenu mt-1 ml-4 pl-4 border-l border-slate-700 space-y-1">
                <a href="<?= site_url('email/unit_kerja') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $isActive('email/unit_kerja') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                    Unit Kerja
                </a>
                <a href="<?= site_url('email/eselon_list') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $isActive('email/eselon_list') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                    Eselon
                </a>
            </div>
        </div>

        <!-- Website Submenu -->
        <div>
            <button data-sidebar-toggle="website" aria-expanded="<?= $default_active === 'website' ? 'true' : 'false' ?>" class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium text-slate-100 rounded-lg hover:bg-slate-700/80 hover:text-white transition-all focus:outline-none">
                <div class="flex items-center">
                    <div class="w-5 h-5 flex items-center justify-center mr-3 shrink-0">
                        <i class="fas fa-globe text-slate-300"></i>
                    </div>
                    <span>Website</span>
                </div>
                <i class="fas fa-chevron-down text-[10px] transition-transform duration-200"></i>
            </button>
            <div id="submenu-website" class="sidebar-submenu mt-1 ml-4 pl-4 border-l border-slate-700 space-y-1">
                <a href="<?= site_url('web_opd') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $isActive('web_opd') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                    Website OPD
                </a>
                <a href="<?= site_url('web_desa_kelurahan') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $isActive('web_desa_kelurahan') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                    Website Desa dan Kelurahan
                </a>
            </div>
        </div>

        <!-- Batch Submenu -->
        <?php if (in_array(session()->get('role'), ['super_admin', 'admin'])): ?>
            <div>
                <button data-sidebar-toggle="batch" aria-expanded="<?= $default_active === 'batch' ? 'true' : 'false' ?>" class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium text-slate-100 rounded-lg hover:bg-slate-700/80 hover:text-white transition-all focus:outline-none">
                    <div class="flex items-center">
                        <div class="w-5 h-5 flex items-center justify-center mr-3 shrink-0">
                            <i class="fas fa-layer-group text-slate-300"></i>
                        </div>
                        <span>Batch</span>
                    </div>
                    <i class="fas fa-chevron-down text-[10px] transition-transform duration-200"></i>
                </button>
                <div id="submenu-batch" class="sidebar-submenu mt-1 ml-4 pl-4 border-l border-slate-700 space-y-1">
                    <a href="<?= site_url('batch') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $isActive('batch') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                        Buat Akun Massal
                    </a>
                    <a href="<?= site_url('batch/update') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $isActive('batch/update') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                        Edit Akun Massal
                    </a>
                    <a href="<?= site_url('batch/pk') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $isActive('batch/pk') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                        Edit PK Massal
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Log Layanan -->
        <?php if (session()->get('role') === 'super_admin'): ?>
            <a href="<?= site_url('assistance') ?>" data-sidebar-clear class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $isActive('assistance') ? 'bg-slate-700 text-white shadow-lg shadow-slate-900/20' : 'text-slate-100 hover:bg-slate-700/80 hover:text-white' ?>">
                <div class="w-5 h-5 flex items-center justify-center mr-3 shrink-0">
                    <i class="fas fa-clipboard-list <?= $isActive('assistance') ? 'text-white' : 'text-slate-300' ?>"></i>
                </div>
                Log Layanan
            </a>
        <?php endif; ?>

        <!-- Master Data Submenu -->
        <?php if (session()->get('role') === 'super_admin'): ?>
            <div>
                <button data-sidebar-toggle="master" aria-expanded="<?= $default_active === 'master' ? 'true' : 'false' ?>" class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium text-slate-100 rounded-lg hover:bg-slate-700/80 hover:text-white transition-all focus:outline-none">
                    <div class="flex items-center">
                        <div class="w-5 h-5 flex items-center justify-center mr-3 shrink-0">
                            <i class="fas fa-database text-slate-300"></i>
                        </div>
                        <span>Master Data</span>
                    </div>
                    <i class="fas fa-chevron-down text-[10px] transition-transform duration-200"></i>
                </button>
                <div id="submenu-master" class="sidebar-submenu mt-1 ml-4 pl-4 border-l border-slate-700 space-y-1">
                    <a href="<?= site_url('unit_kerja/manage') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $isActive('unit_kerja/manage') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                        Unit Kerja
                    </a>
                    <a href="<?= site_url('auth/users') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $isActive('auth/users') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                        User Login
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </nav>
</aside>
