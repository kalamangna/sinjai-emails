<!-- Header / Topbar -->
<header class="h-16 bg-white border-b border-gray-100 sticky top-0 z-40 flex items-center justify-between px-6">
    <div class="flex items-center">
        <!-- Mobile Toggle -->
        <button id="sidebar-toggle" class="lg:hidden w-10 h-10 flex items-center justify-center text-gray-500 hover:bg-gray-50 rounded-lg mr-2">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- App Title (Mobile Only) -->
        <div class="flex items-center lg:hidden">
            <div class="w-8 h-8 bg-gray-900 rounded-lg flex items-center justify-center mr-3 shadow-sm">
                <i class="fas fa-fingerprint text-white text-sm"></i>
            </div>
            <div class="flex flex-col">
                <span class="block text-sm font-bold tracking-tight text-gray-900 leading-none uppercase">sinjai <span class="text-gray-500">emails</span></span>
                <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest block mt-0.5">identitas digital</span>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-4">
        <!-- User Information -->
        <div class="flex items-center gap-3">
            <div class="hidden sm:flex flex-col items-end">
                <p class="text-xs font-bold text-gray-900 leading-none"><?= session()->get('username') ?></p>
                <p class="text-[9px] font-bold text-gray-500 uppercase mt-1 tracking-widest">
                    <?= session()->get('role') == 'super_admin' ? 'Super Admin' : 'Admin' ?>
                </p>
            </div>
            <div class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center text-gray-500 border border-gray-200 shadow-sm">
                <i class="fas fa-user-shield text-sm"></i>
            </div>
        </div>
    </div>
</header>
