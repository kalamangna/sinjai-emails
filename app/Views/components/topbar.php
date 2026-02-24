<!-- Topbar Component -->
<header class="h-16 bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm flex items-center justify-between px-6 lg:px-10">
    <div class="flex items-center">
        <!-- Mobile Toggle -->
        <button id="sidebar-toggle" class="lg:hidden w-10 h-10 flex items-center justify-center text-slate-500 hover:bg-slate-50 rounded-lg mr-2">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Search -->
        <div class="hidden md:flex items-center bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all">
            <i class="fas fa-search text-slate-400 text-xs mr-2"></i>
            <input type="text" placeholder="Cari..." class="bg-transparent border-none text-[13px] font-medium text-slate-700 focus:outline-none w-64 placeholder:text-slate-400">
        </div>
    </div>

    <div class="flex items-center space-y-0 gap-4 lg:gap-6">
        <!-- Update Info -->
        <?php if (isset($last_sync_time)): ?>
        <div class="hidden xl:flex flex-col items-end border-r border-slate-200 pr-6 mr-2">
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Update</span>
            <span class="text-[11px] font-bold text-emerald-600 leading-none">
                <?= date('d M Y, H:i', strtotime($last_sync_time)) ?>
            </span>
        </div>
        <?php endif; ?>

        <!-- User -->
        <div class="flex items-center gap-4">
            <div class="flex items-center group">
                <div class="text-right mr-3 hidden sm:block">
                    <p class="text-[13px] font-bold text-slate-900 leading-tight"><?= session()->get('username') ?? 'User' ?></p>
                    <div class="flex items-center justify-end mt-0.5">
                        <?php 
                            $role = session()->get('role');
                            $roleLabel = ($role === 'super_admin') ? 'Super Admin' : 'Admin';
                            $roleClass = ($role === 'super_admin') ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700';
                        ?>
                        <span class="text-[9px] font-bold <?= $roleClass ?> px-1.5 py-0.5 rounded uppercase tracking-wider">
                            <?= $roleLabel ?>
                        </span>
                    </div>
                </div>
                <div class="w-9 h-9 bg-slate-100 rounded-full border-2 border-white shadow-sm ring-1 ring-slate-200 overflow-hidden group-hover:ring-emerald-500 transition-all flex items-center justify-center text-slate-400">
                    <i class="fas fa-user text-sm"></i>
                </div>
            </div>
            
            <a href="<?= site_url('logout') ?>" class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all border border-transparent hover:border-rose-100" title="Keluar">
                <i class="fas fa-power-off text-sm"></i>
            </a>
        </div>
    </div>
</header>
