<!-- Branding -->
<div class="flex-shrink-0 flex items-center justify-between lg:justify-start w-full lg:w-auto">
    <a href="<?= site_url('/') ?>" class="flex items-center no-underline group">
        <div class="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-md group-hover:bg-blue-700 transition-colors">
            <i class="fas fa-envelope-open-text text-white text-lg"></i>
        </div>
        <div>
            <span class="block text-lg font-bold tracking-tight text-slate-900 leading-none">SINJAI<span class="text-blue-600">EMAILS</span></span>
            <span class="text-[9px] font-semibold text-slate-500 uppercase tracking-wider block">Portal Identitas</span>
        </div>
    </a>
</div>

<!-- Navigation -->
<nav class="w-full lg:w-auto">
    <div class="flex flex-wrap items-center justify-center lg:justify-end gap-1 lg:gap-2">

        <!-- Email -->
        <a href="<?= site_url('email') ?>" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-blue-600 hover:bg-slate-50 rounded-lg transition-all no-underline">
            Email
        </a>

        <!-- Unit Kerja -->
        <div class="relative group">
            <button class="flex items-center px-3 py-2 text-xs font-semibold text-slate-600 hover:text-blue-600 hover:bg-slate-50 rounded-lg transition-all">
                Unit Kerja
                <i class="fas fa-chevron-down ml-1.5 text-[9px] text-slate-400 group-hover:rotate-180 transition-transform"></i>
            </button>
            <div class="absolute right-0 hidden group-hover:block w-72 pt-2 z-50">
                <div class="bg-white border border-slate-200 rounded-xl shadow-xl py-2 max-h-[60vh] flex flex-col">
                    <div class="px-4 py-2 text-[9px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 mb-1">Unit Kerja</div>
                    <div class="overflow-y-auto custom-scrollbar flex-grow">
                        <?php if (!empty($unit_kerja_nav)): ?>
                            <?php foreach ($unit_kerja_nav as $unit): ?>
                                <a href="<?= site_url('email/unit_kerja/' . $unit['id']) ?>" class="flex justify-between items-center px-4 py-2 text-[11px] font-medium text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors no-underline">
                                    <span class="truncate mr-3"><?= esc(trim(str_ireplace('KANTOR', '', $unit['nama_unit_kerja']))) ?></span>
                                    <span class="text-[9px] font-bold bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded"><?= $unit['email_count'] ?></span>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="px-2 pt-2 mt-1 border-t border-slate-100">
                        <a href="<?= site_url('email/unit_kerja') ?>" class="block w-full px-2 py-2 text-[10px] font-bold text-center text-blue-600 hover:bg-blue-50 rounded-lg no-underline uppercase tracking-wide">Lihat Semua</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pimpinan -->
        <div class="relative group">
            <button class="flex items-center px-3 py-2 text-xs font-semibold text-slate-600 hover:text-blue-600 hover:bg-slate-50 rounded-lg transition-all">
                Pimpinan
                <i class="fas fa-chevron-down ml-1.5 text-[9px] text-slate-400 group-hover:rotate-180 transition-transform"></i>
            </button>
            <div class="absolute right-0 hidden group-hover:block w-56 pt-2 z-50">
                <div class="bg-white border border-slate-200 rounded-xl shadow-xl py-2">
                    <div class="px-4 py-2 text-[9px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 mb-1">Daftar Pejabat</div>
                    <a class="block px-4 py-2.5 text-[11px] font-medium text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors no-underline" href="<?= site_url('email/pimpinan') ?>">Pimpinan OPD</a>
                    <a class="block px-4 py-2.5 text-[11px] font-medium text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors no-underline" href="<?= site_url('email/pimpinan_desa') ?>">Kepala Desa</a>
                    
                    <?php if (!empty($eselon_nav)): ?>
                        <div class="px-4 py-2 mt-1 text-[9px] font-bold text-slate-400 uppercase tracking-widest border-y border-slate-100 mb-1">Eselon</div>
                        <?php foreach ($eselon_nav as $eselon): ?>
                            <a href="<?= site_url('email/eselon_detail/' . $eselon['id']) ?>" class="flex justify-between items-center px-4 py-2 text-[11px] font-medium text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors no-underline">
                                <span>Eselon <?= esc($eselon['name']) ?></span>
                                <span class="text-[9px] font-bold bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded"><?= $eselon['count'] ?></span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Batch -->
        <div class="relative group">
            <button class="flex items-center px-3 py-2 text-xs font-semibold text-slate-600 hover:text-blue-600 hover:bg-slate-50 rounded-lg transition-all">
                Batch
                <i class="fas fa-chevron-down ml-1.5 text-[9px] text-slate-400 group-hover:rotate-180 transition-transform"></i>
            </button>
            <div class="absolute right-0 hidden group-hover:block w-56 pt-2 z-50">
                <div class="bg-white border border-slate-200 rounded-xl shadow-xl py-2">
                    <a class="block px-4 py-2.5 text-[11px] font-medium text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors no-underline" href="<?= site_url('email/batch_perjanjian_kerja') ?>">PK Massal</a>
                    <a class="block px-4 py-2.5 text-[11px] font-medium text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors no-underline" href="<?= site_url('email/batch') ?>">Buat Akun</a>
                    <a class="block px-4 py-2.5 text-[11px] font-medium text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors no-underline" href="<?= site_url('email/batch_update') ?>">Update Data</a>
                </div>
            </div>
        </div>

        <!-- Website -->
        <div class="relative group">
            <button class="flex items-center px-3 py-2 text-xs font-semibold text-slate-600 hover:text-blue-600 hover:bg-slate-50 rounded-lg transition-all">
                Website
                <i class="fas fa-chevron-down ml-1.5 text-[9px] text-slate-400 group-hover:rotate-180 transition-transform"></i>
            </button>
            <div class="absolute right-0 hidden group-hover:block w-56 pt-2 z-50">
                <div class="bg-white border border-slate-200 rounded-xl shadow-xl py-2">
                    <a class="block px-4 py-2.5 text-[11px] font-medium text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors no-underline" href="<?= site_url('web_opd') ?>">Website OPD</a>
                    <a class="block px-4 py-2.5 text-[11px] font-medium text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors no-underline" href="<?= site_url('web_desa_kelurahan') ?>">Website Desa</a>
                </div>
            </div>
        </div>

        <!-- Pendampingan -->
        <a href="<?= site_url('assistance') ?>" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-blue-600 hover:bg-slate-50 rounded-lg transition-all no-underline">
            Log Layanan
        </a>

        <!-- Master -->
        <a href="<?= site_url('unit_kerja/manage') ?>" class="ml-2 w-8 h-8 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-slate-50 rounded-lg transition-all no-underline" title="Pengaturan Data">
            <i class="fas fa-cog text-sm"></i>
        </a>

    </div>
</nav>