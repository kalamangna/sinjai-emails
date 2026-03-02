<aside id="sidebar" class="fixed top-0 left-0 z-50 w-64 h-screen transition-transform bg-slate-800 border-r border-slate-700 flex flex-col lg:translate-x-0 -translate-x-full">
    <!-- Logo Section -->
    <div class="flex items-center h-16 px-6 border-b border-slate-700 flex-shrink-0">
        <a href="<?= site_url('/') ?>" class="flex items-center no-underline">
            <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center mr-3 shadow-sm border border-white/10">
                <i class="fas fa-fingerprint text-white text-sm"></i>
            </div>
            <div>
                <span class="block text-xs font-bold tracking-tight text-white leading-none uppercase">sinjai<span class="text-slate-300">emails</span></span>
                <span class="text-[8px] font-bold text-slate-300 uppercase tracking-widest block mt-0.5">identitas digital</span>
            </div>
        </a>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-grow py-6 px-4 space-y-1 overflow-y-auto custom-scrollbar">
        <!-- Dashboard -->
        <a href="<?= site_url('/') ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all <?= current_url() == site_url() ? 'bg-slate-700 text-white shadow-lg shadow-slate-900/20' : 'text-slate-100 hover:bg-slate-700/80 hover:text-white' ?>">
            <div class="w-5 h-5 flex items-center justify-center mr-3 shrink-0">
                <i class="fas fa-th-large <?= current_url() == site_url() ? 'text-white' : 'text-slate-300' ?>"></i>
            </div>
            Dashboard
        </a>

        <!-- Email -->
        <a href="<?= site_url('email') ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all <?= (strpos(current_url(), 'email') !== false && strpos(current_url(), 'pimpinan') === false && strpos(current_url(), 'eselon') === false && strpos(current_url(), 'unit_kerja') === false) ? 'bg-slate-700 text-white shadow-lg shadow-slate-900/20' : 'text-slate-100 hover:bg-slate-700/80 hover:text-white' ?>">
            <div class="w-5 h-5 flex items-center justify-center mr-3 shrink-0">
                <i class="fas fa-envelope <?= (strpos(current_url(), 'email') !== false && strpos(current_url(), 'pimpinan') === false && strpos(current_url(), 'eselon') === false && strpos(current_url(), 'unit_kerja') === false) ? 'text-white' : 'text-slate-300' ?>"></i>
            </div>
            Email
        </a>

        <!-- Pegawai Submenu -->
        <div x-data="{ open: <?= (strpos(current_url(), 'pppk_list') !== false || strpos(current_url(), 'pppk_pw_list') !== false || strpos(current_url(), 'pns_list') !== false) ? 'true' : 'false' ?> }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium text-slate-100 rounded-lg hover:bg-slate-700/80 hover:text-white transition-all focus:outline-none">
                <div class="flex items-center">
                    <div class="w-5 h-5 flex items-center justify-center mr-3 shrink-0">
                        <i class="fas fa-users text-slate-300"></i>
                    </div>
                    <span>Pegawai</span>
                </div>
                <i class="fas fa-chevron-down text-[10px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-collapse class="mt-1 ml-4 pl-4 border-l border-slate-700 space-y-1">
                <a href="<?= site_url('email/pns_list') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= current_url() == site_url('email/pns_list') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                    PNS
                </a>
                <a href="<?= site_url('email/pppk_list') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= current_url() == site_url('email/pppk_list') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                    PPPK
                </a>
                <a href="<?= site_url('email/pppk_pw_list') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= current_url() == site_url('email/pppk_pw_list') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                    PPPK PW
                </a>
            </div>
        </div>

        <!-- Pejabat Submenu -->
        <div x-data="{ open: <?= (strpos(current_url(), 'pimpinan') !== false) ? 'true' : 'false' ?> }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium text-slate-100 rounded-lg hover:bg-slate-700/80 hover:text-white transition-all focus:outline-none">
                <div class="flex items-center">
                    <div class="w-5 h-5 flex items-center justify-center mr-3 shrink-0">
                        <i class="fas fa-user-tie text-slate-300"></i>
                    </div>
                    <span>Pejabat</span>
                </div>
                <i class="fas fa-chevron-down text-[10px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-collapse class="mt-1 ml-4 pl-4 border-l border-slate-700 space-y-1">
                <a href="<?= site_url('email/pimpinan') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= current_url() == site_url('email/pimpinan') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                    Pimpinan
                </a>
                <a href="<?= site_url('email/pimpinan_desa') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= current_url() == site_url('email/pimpinan_desa') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                    Kepala Desa
                </a>
            </div>
        </div>

        <!-- Organisasi Submenu -->
        <div x-data="{ open: <?= (strpos(current_url(), 'unit_kerja') !== false || strpos(current_url(), 'eselon') !== false) && strpos(current_url(), 'manage') === false ? 'true' : 'false' ?> }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium text-slate-100 rounded-lg hover:bg-slate-700/80 hover:text-white transition-all focus:outline-none">
                <div class="flex items-center">
                    <div class="w-5 h-5 flex items-center justify-center mr-3 shrink-0">
                        <i class="fas fa-building text-slate-300"></i>
                    </div>
                    <span>Organisasi</span>
                </div>
                <i class="fas fa-chevron-down text-[10px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-collapse class="mt-1 ml-4 pl-4 border-l border-slate-700 space-y-1">
                <a href="<?= site_url('email/unit_kerja') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= current_url() == site_url('email/unit_kerja') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                    Unit Kerja
                </a>
                <a href="<?= site_url('email/eselon_list') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= current_url() == site_url('email/eselon_list') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                    Eselon
                </a>
            </div>
        </div>

        <!-- Website Submenu -->
        <div x-data="{ open: <?= (strpos(current_url(), 'web_') !== false) ? 'true' : 'false' ?> }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium text-slate-100 rounded-lg hover:bg-slate-700/80 hover:text-white transition-all focus:outline-none">
                <div class="flex items-center">
                    <div class="w-5 h-5 flex items-center justify-center mr-3 shrink-0">
                        <i class="fas fa-globe text-slate-300"></i>
                    </div>
                    <span>Website</span>
                </div>
                <i class="fas fa-chevron-down text-[10px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-collapse class="mt-1 ml-4 pl-4 border-l border-slate-700 space-y-1">
                <a href="<?= site_url('web_opd') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= current_url() == site_url('web_opd') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                    Website OPD
                </a>
                <a href="<?= site_url('web_desa_kelurahan') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= current_url() == site_url('web_desa_kelurahan') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                    Website Desa dan Kelurahan
                </a>
            </div>
        </div>

        <!-- Batch Submenu -->
        <?php if (session()->get('role') === 'super_admin'): ?>
            <div x-data="{ open: <?= (strpos(current_url(), 'batch') !== false) ? 'true' : 'false' ?> }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium text-slate-100 rounded-lg hover:bg-slate-700/80 hover:text-white transition-all focus:outline-none">
                    <div class="flex items-center">
                        <div class="w-5 h-5 flex items-center justify-center mr-3 shrink-0">
                            <i class="fas fa-layer-group text-slate-300"></i>
                        </div>
                        <span>Batch</span>
                    </div>
                    <i class="fas fa-chevron-down text-[10px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-collapse class="mt-1 ml-4 pl-4 border-l border-slate-700 space-y-1">
                    <a href="<?= site_url('batch') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= current_url() == site_url('batch') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                        Buat Akun Massal
                    </a>
                    <a href="<?= site_url('batch/update') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= current_url() == site_url('batch/update') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                        Edit Akun Massal
                    </a>
                    <a href="<?= site_url('batch/pk') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= current_url() == site_url('batch/pk') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                        Edit PK Massal
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Log Layanan -->
        <a href="<?= site_url('assistance') ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all <?= strpos(current_url(), 'assistance') !== false ? 'bg-slate-700 text-white shadow-lg shadow-slate-900/20' : 'text-slate-100 hover:bg-slate-700/80 hover:text-white' ?>">
            <div class="w-5 h-5 flex items-center justify-center mr-3 shrink-0">
                <i class="fas fa-clipboard-list <?= strpos(current_url(), 'assistance') !== false ? 'text-white' : 'text-slate-300' ?>"></i>
            </div>
            Log Layanan
        </a>

        <!-- Master Data Submenu -->
        <div x-data="{ open: <?= (strpos(current_url(), 'unit_kerja/manage') !== false) ? 'true' : 'false' ?> }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium text-slate-100 rounded-lg hover:bg-slate-700/80 hover:text-white transition-all focus:outline-none">
                <div class="flex items-center">
                    <div class="w-5 h-5 flex items-center justify-center mr-3 shrink-0">
                        <i class="fas fa-database text-slate-300"></i>
                    </div>
                    <span>Master Data</span>
                </div>
                <i class="fas fa-chevron-down text-[10px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-collapse class="mt-1 ml-4 pl-4 border-l border-slate-700 space-y-1">
                <a href="<?= site_url('unit_kerja/manage') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg transition-all <?= current_url() == site_url('unit_kerja/manage') ? 'text-white bg-slate-700' : 'text-slate-100 hover:text-white hover:bg-slate-700/80' ?>">
                    Unit Kerja
                </a>
            </div>
        </div>
    </nav>

    <!-- User Section at Bottom -->
    <div class="p-4 border-t border-slate-700">
        <div class="flex items-center p-2 rounded-lg bg-slate-700/50 border border-white/5">
            <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-slate-100 mr-3 shrink-0 border border-white/10">
                <i class="fas fa-user text-xs"></i>
            </div>
            <div class="flex-grow overflow-hidden">
                <p class="text-xs font-bold text-white truncate uppercase"><?= session()->get('username') ?></p>
                <p class="text-[10px] text-slate-300 uppercase font-medium opacity-70"><?= session()->get('role') == 'super_admin' ? 'Super Admin' : 'Admin' ?></p>
            </div>
            <div class="flex items-center gap-1">
                <a href="<?= site_url('user/change_password') ?>" class="w-7 h-7 flex items-center justify-center text-slate-300 hover:text-white hover:bg-slate-700/80 rounded-lg transition-colors" title="Ganti Password">
                    <i class="fas fa-key text-[10px]"></i>
                </a>
                <a href="<?= site_url('logout') ?>" class="w-7 h-7 flex items-center justify-center text-slate-300 hover:text-red-600 hover:bg-red-600/10 rounded-lg transition-colors" title="Keluar">
                    <i class="fas fa-power-off text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>
</aside>
