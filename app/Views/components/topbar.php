<!-- Topbar Component -->
<header class="h-16 bg-white border-b border-gray-100 sticky top-0 z-40 flex items-center justify-between px-6">
    <div class="flex items-center">
        <!-- Mobile Toggle -->
        <button id="sidebar-toggle" class="lg:hidden w-10 h-10 flex items-center justify-center text-gray-500 hover:bg-gray-50 rounded-lg mr-2">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- App Title (Mobile) -->
        <span class="lg:hidden text-sm font-bold text-gray-900 uppercase">Identitas Digital</span>

        <!-- Search (Desktop) -->
        <div class="hidden md:flex items-center bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5 focus-within:border-gray-400 focus-within:ring-1 focus-within:ring-gray-400/20 transition-all">
            <i class="fas fa-search text-gray-400 text-xs mr-2"></i>
            <input type="text" placeholder="Pencarian..." class="bg-transparent border-none text-sm font-medium text-gray-700 focus:outline-none w-64 placeholder:text-gray-400">
        </div>
    </div>

    <div class="flex items-center gap-4">
        <!-- System Info -->
        <div class="hidden xl:flex flex-col items-end border-r border-gray-100 pr-4">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none mb-1">Status Sistem</span>
            <div class="flex items-center">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                <span class="text-xs font-semibold text-gray-700 uppercase tracking-tight">Terhubung</span>
            </div>
        </div>

        <!-- User Information -->
        <div class="flex items-center gap-3">
            <div class="hidden sm:flex flex-col items-end">
                <p class="text-sm font-semibold text-gray-900 leading-none"><?= session()->get('username') ?></p>
                <p class="text-[10px] font-medium text-gray-500 uppercase mt-1 tracking-tight">
                    <?= session()->get('role') == 'super_admin' ? 'Super Admin' : 'Admin' ?>
                </p>
            </div>
            <div class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center text-gray-500 border border-gray-200 shadow-sm">
                <i class="fas fa-user-shield text-sm"></i>
            </div>
        </div>
    </div>
</header>
