<!-- Sidebar Component -->
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 transition-all duration-300 transform lg:translate-x-0 -translate-x-full border-r border-slate-800 shadow-xl overflow-y-auto custom-scrollbar">
    <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-6 bg-slate-950 border-b border-slate-800 flex-shrink-0">
            <a href="<?= site_url('/') ?>" class="flex items-center group">
                <div class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center mr-3 shadow-lg shadow-emerald-900/20">
                    <i class="fas fa-envelope-open-text text-white text-sm"></i>
                </div>
                <div>
                    <span class="block text-sm font-bold tracking-tight text-white leading-none">SINJAI<span class="text-emerald-500">EMAILS</span></span>
                    <span class="text-[8px] font-semibold text-slate-500 uppercase tracking-widest block mt-0.5">Identitas Digital</span>
                </div>
            </a>
        </div>

        <!-- Menu -->
        <nav class="flex-grow py-6 px-4 space-y-8">
            <div>
                <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4">Utama</p>
                <div class="space-y-1">
                    <a href="<?= site_url('/') ?>" class="flex items-center px-4 py-2.5 text-xs font-semibold rounded-lg transition-all <?= current_url() == site_url() ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-600/20' : 'hover:bg-slate-800 hover:text-white' ?>">
                        <i class="fas fa-th-large w-5 text-[14px] mr-2.5"></i> Dashboard
                    </a>
                    <a href="<?= site_url('email') ?>" class="flex items-center px-4 py-2.5 text-xs font-semibold rounded-lg transition-all <?= strpos(current_url(), site_url('email')) !== false && strpos(current_url(), 'unit_kerja') === false && strpos(current_url(), 'pimpinan') === false && strpos(current_url(), 'batch') === false && strpos(current_url(), 'eselon') === false ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-600/20' : 'hover:bg-slate-800 hover:text-white' ?>">
                        <i class="fas fa-envelope w-5 text-[14px] mr-2.5"></i> Email
                    </a>
                </div>
            </div>

            <div>
                <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4">Organisasi</p>
                <div class="space-y-1">
                    <a href="<?= site_url('email/unit_kerja') ?>" class="flex items-center px-4 py-2.5 text-xs font-semibold rounded-lg transition-all <?= strpos(current_url(), 'unit_kerja') !== false ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-600/20' : 'hover:bg-slate-800 hover:text-white' ?>">
                        <i class="fas fa-building w-5 text-[14px] mr-2.5"></i> Unit Kerja
                    </a>
                    <a href="<?= site_url('email/pimpinan') ?>" class="flex items-center px-4 py-2.5 text-xs font-semibold rounded-lg transition-all <?= strpos(current_url(), 'pimpinan') !== false ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-600/20' : 'hover:bg-slate-800 hover:text-white' ?>">
                        <i class="fas fa-user-tie w-5 text-[14px] mr-2.5"></i> Pimpinan
                    </a>
                </div>
            </div>

            <div>
                <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4">Layanan</p>
                <div class="space-y-1">
                    <?php if (session()->get('role') === 'super_admin'): ?>
                    <a href="<?= site_url('email/batch_hub') ?>" class="flex items-center px-4 py-2.5 text-xs font-semibold rounded-lg transition-all <?= strpos(current_url(), 'batch') !== false ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-600/20' : 'hover:bg-slate-800 hover:text-white' ?>">
                        <i class="fas fa-layer-group w-5 text-[14px] mr-2.5"></i> Batch
                    </a>
                    <?php endif; ?>
                    <a href="<?= site_url('web_opd') ?>" class="flex items-center px-4 py-2.5 text-xs font-semibold rounded-lg transition-all <?= strpos(current_url(), 'web_') !== false ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-600/20' : 'hover:bg-slate-800 hover:text-white' ?>">
                        <i class="fas fa-globe w-5 text-[14px] mr-2.5"></i> Web
                    </a>
                    <a href="<?= site_url('assistance') ?>" class="flex items-center px-4 py-2.5 text-xs font-semibold rounded-lg transition-all <?= strpos(current_url(), 'assistance') !== false ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-600/20' : 'hover:bg-slate-800 hover:text-white' ?>">
                        <i class="fas fa-clipboard-list w-5 text-[14px] mr-2.5"></i> Logs
                    </a>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800">
                <a href="<?= site_url('unit_kerja/manage') ?>" class="flex items-center px-4 py-2.5 text-xs font-semibold rounded-lg transition-all <?= strpos(current_url(), 'manage') !== false ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-600/20' : 'hover:bg-slate-800 hover:text-white' ?>">
                    <i class="fas fa-cog w-5 text-[14px] mr-2.5 text-slate-500"></i> Settings
                </a>
            </div>
        </nav>
    </div>
</aside>
