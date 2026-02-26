<!-- Sidebar Component -->
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transition-all duration-300 transform lg:translate-x-0 -translate-x-full overflow-y-auto custom-scrollbar">
    <div class="flex flex-col h-full">
        <!-- Logo Section -->
        <div class="flex items-center h-16 px-6 border-b border-gray-100 flex-shrink-0">
            <a href="<?= site_url('/') ?>" class="flex items-center">
                <div class="w-8 h-8 bg-gray-900 rounded-lg flex items-center justify-center mr-3 shadow-sm">
                    <i class="fas fa-fingerprint text-white text-sm"></i>
                </div>
                <span class="text-sm font-bold tracking-tight text-gray-900 uppercase">Identitas Digital</span>
            </a>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-grow py-6 px-4 space-y-1">
            <!-- Dashboard -->
            <a href="<?= site_url('/') ?>" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl transition-all <?= current_url() == site_url() ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
                <i class="fas fa-th-large w-5 mr-3 text-gray-400"></i> Dashboard
            </a>

            <!-- Email Submenu -->
            <div x-data="{ open: <?= (strpos(current_url(), 'email') !== false && strpos(current_url(), 'manage') === false) ? 'true' : 'false' ?> }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2.5 text-sm font-medium text-gray-600 rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-all">
                    <div class="flex items-center">
                        <i class="fas fa-envelope w-5 mr-3 text-gray-400"></i>
                        <span>Email</span>
                    </div>
                    <i class="fas fa-chevron-down text-[10px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-collapse class="mt-1 ml-4 pl-4 border-l border-gray-100 space-y-1">
                    <a href="<?= site_url('email') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg <?= current_url() == site_url('email') ? 'text-gray-900 bg-gray-50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' ?>">
                        Semua Akun
                    </a>
                    <a href="<?= site_url('email/pimpinan') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg <?= current_url() == site_url('email/pimpinan') ? 'text-gray-900 bg-gray-50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' ?>">
                        Pimpinan
                    </a>
                    <a href="<?= site_url('email/pimpinan_desa') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg <?= current_url() == site_url('email/pimpinan_desa') ? 'text-gray-900 bg-gray-50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' ?>">
                        Kepala Desa
                    </a>
                    <a href="<?= site_url('email/unit_kerja') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg <?= current_url() == site_url('email/unit_kerja') ? 'text-gray-900 bg-gray-50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' ?>">
                        Unit Kerja
                    </a>
                    <a href="<?= site_url('email/eselon_list') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg <?= current_url() == site_url('email/eselon_list') ? 'text-gray-900 bg-gray-50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' ?>">
                        Daftar Eselon
                    </a>
                </div>
            </div>

            <!-- Website Submenu -->
            <div x-data="{ open: <?= (strpos(current_url(), 'web_') !== false) ? 'true' : 'false' ?> }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2.5 text-sm font-medium text-gray-600 rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-all">
                    <div class="flex items-center">
                        <i class="fas fa-globe w-5 mr-3 text-gray-400"></i>
                        <span>Website</span>
                    </div>
                    <i class="fas fa-chevron-down text-[10px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-collapse class="mt-1 ml-4 pl-4 border-l border-gray-100 space-y-1">
                    <a href="<?= site_url('web_opd') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg <?= current_url() == site_url('web_opd') ? 'text-gray-900 bg-gray-50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' ?>">
                        Website OPD
                    </a>
                    <a href="<?= site_url('web_desa_kelurahan') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg <?= current_url() == site_url('web_desa_kelurahan') ? 'text-gray-900 bg-gray-50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' ?>">
                        Website Desa dan Kelurahan
                    </a>
                </div>
            </div>

            <!-- Batch Submenu -->
            <?php if (session()->get('role') === 'super_admin'): ?>
                <div x-data="{ open: <?= (strpos(current_url(), 'batch') !== false) ? 'true' : 'false' ?> }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2.5 text-sm font-medium text-gray-600 rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-all">
                        <div class="flex items-center">
                            <i class="fas fa-layer-group w-5 mr-3 text-gray-400"></i>
                            <span>Batch</span>
                        </div>
                        <i class="fas fa-chevron-down text-[10px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse class="mt-1 ml-4 pl-4 border-l border-gray-100 space-y-1">
                        <a href="<?= site_url('batch') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg <?= current_url() == site_url('batch') ? 'text-gray-900 bg-gray-50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' ?>">
                            Tambah Massal
                        </a>
                        <a href="<?= site_url('batch/update') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg <?= current_url() == site_url('batch/update') ? 'text-gray-900 bg-gray-50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' ?>">
                            Perbarui Massal
                        </a>
                        <a href="<?= site_url('batch/pk') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg <?= current_url() == site_url('batch/pk') ? 'text-gray-900 bg-gray-50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' ?>">
                            Perbarui PK
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Log Layanan -->
            <a href="<?= site_url('assistance') ?>" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl transition-all <?= strpos(current_url(), 'assistance') !== false ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
                <i class="fas fa-clipboard-list w-5 mr-3 text-gray-400"></i> Log Layanan
            </a>

            <!-- Master Data Submenu -->
            <div x-data="{ open: <?= (strpos(current_url(), 'unit_kerja/manage') !== false) ? 'true' : 'false' ?> }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2.5 text-sm font-medium text-gray-600 rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-all">
                    <div class="flex items-center">
                        <i class="fas fa-database w-5 mr-3 text-gray-400"></i>
                        <span>Master Data</span>
                    </div>
                    <i class="fas fa-chevron-down text-[10px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-collapse class="mt-1 ml-4 pl-4 border-l border-gray-100 space-y-1">
                    <a href="<?= site_url('unit_kerja/manage') ?>" class="block px-4 py-2 text-sm font-medium rounded-lg <?= current_url() == site_url('unit_kerja/manage') ? 'text-gray-900 bg-gray-50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' ?>">
                        Unit Kerja
                    </a>
                </div>
            </div>
        </nav>

        <!-- User Section at Bottom -->
        <div class="p-4 border-t border-gray-100">
            <div class="flex items-center p-2 rounded-xl bg-gray-50">
                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 mr-3">
                    <i class="fas fa-user text-xs"></i>
                </div>
                <div class="flex-grow overflow-hidden">
                    <p class="text-xs font-bold text-gray-900 truncate"><?= session()->get('username') ?></p>
                    <p class="text-[10px] text-gray-500 uppercase font-medium"><?= session()->get('role') == 'super_admin' ? 'Super Admin' : 'Admin' ?></p>
                </div>
                <a href="<?= site_url('logout') ?>" class="text-gray-400 hover:text-red-600 transition-colors">
                    <i class="fas fa-power-off text-xs"></i>
                </a>
            </div>
        </div>
    </div>
</aside>

<!-- Alpine.js Plugins -->
<script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
<!-- Alpine.js Core -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>