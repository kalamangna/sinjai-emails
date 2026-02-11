<!-- Branding -->
<div class="flex-shrink-0 flex flex-col items-center lg:items-start">
    <a href="<?= site_url('/') ?>" class="flex items-center no-underline group">
        <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center mr-4 shadow-lg shadow-blue-900/20 group-hover:scale-105 transition-transform">
            <i class="fas fa-envelope-open-text text-white text-xl"></i>
        </div>
        <div>
            <span class="block text-xl font-black tracking-tighter text-slate-100 leading-none">SINJAI<span class="text-blue-500">EMAILS</span></span>
            <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] mt-1 block">Portal Identitas Digital</span>
        </div>
    </a>
</div>

<!-- Navigation -->
<nav class="flex-grow">
    <div class="flex flex-wrap items-center justify-center lg:justify-end gap-2 lg:gap-4">

        <!-- Main Dashboard -->
        <a href="<?= site_url('email') ?>" class="flex items-center px-4 py-2.5 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-blue-400 hover:bg-slate-800/50 rounded-xl transition-all no-underline">
            Email
        </a>

        <!-- Daftar Unit Kerja -->
        <div class="relative group">
            <a href="<?= site_url('email/unit_kerja') ?>" class="flex items-center px-4 py-2.5 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-blue-400 hover:bg-slate-800/50 rounded-xl transition-all no-underline">
                Unit Kerja
                <i class="fas fa-chevron-down ml-2 text-[10px] opacity-50 group-hover:rotate-180 transition-transform"></i>
            </a>
            <div class="absolute right-0 hidden group-hover:block w-80 pt-2 z-50">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden py-2 backdrop-blur-xl max-h-[70vh] flex flex-col">
                    <div class="px-5 py-2 text-[9px] font-black text-slate-600 uppercase tracking-widest border-b border-slate-800/50 mb-1">Pilih Perangkat Daerah</div>
                    <div class="overflow-y-auto custom-scrollbar flex-grow">
                        <?php if (!empty($unit_kerja_nav)): ?>
                            <?php foreach ($unit_kerja_nav as $unit): ?>
                                <a href="<?= site_url('email/unit_kerja/' . $unit['id']) ?>" class="flex justify-between items-center px-5 py-2.5 text-[10px] font-bold text-slate-400 hover:bg-slate-800 hover:text-blue-400 transition-colors">
                                    <span class="uppercase truncate mr-4"><?= esc($unit['nama_unit_kerja']) ?></span>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-950 text-slate-500 border border-slate-800"><?= $unit['email_count'] ?></span>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pimpinan & Pejabat -->
        <div class="relative group">
            <button class="flex items-center px-4 py-2.5 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-green-400 hover:bg-slate-800/50 rounded-xl transition-all">
                Pimpinan
                <i class="fas fa-chevron-down ml-2 text-[10px] opacity-50 group-hover:rotate-180 transition-transform"></i>
            </button>
            <div class="absolute right-0 hidden group-hover:block w-64 pt-2 z-50">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden py-2 backdrop-blur-xl max-h-[70vh] flex flex-col">
                    <div class="px-5 py-2 text-[9px] font-black text-slate-600 uppercase tracking-widest border-b border-slate-800/50 mb-1">Pejabat & Eselon</div>
                    <div class="overflow-y-auto custom-scrollbar">
                        <a class="block px-5 py-3 text-xs font-bold text-slate-400 hover:bg-slate-800 hover:text-green-400 transition-colors" href="<?= site_url('email/pimpinan') ?>">Pimpinan OPD</a>
                        <a class="block px-5 py-3 text-xs font-bold text-slate-400 hover:bg-slate-800 hover:text-green-400 transition-colors" href="<?= site_url('email/pimpinan_desa') ?>">Kepala Desa / Lurah</a>
                        
                        <?php if (!empty($eselon_nav)): ?>
                            <div class="px-5 py-2 text-[9px] font-black text-slate-600 uppercase tracking-widest border-y border-slate-800/50 my-1">Berdasarkan Eselon</div>
                            <?php foreach ($eselon_nav as $eselon): ?>
                                <a href="<?= site_url('email/eselon_detail/' . $eselon['id']) ?>" class="flex justify-between items-center px-5 py-2.5 text-[10px] font-bold text-slate-400 hover:bg-slate-800 hover:text-green-400 transition-colors">
                                    <span class="uppercase">ESELON <?= esc($eselon['name']) ?></span>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-950 text-slate-500 border border-slate-800"><?= $eselon['count'] ?></span>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Proses Massal -->
        <div class="relative group">
            <button class="flex items-center px-4 py-2.5 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-blue-400 hover:bg-slate-800/50 rounded-xl transition-all">
                Batch
                <i class="fas fa-chevron-down ml-2 text-[10px] opacity-50 group-hover:rotate-180 transition-transform"></i>
            </button>
            <div class="absolute right-0 hidden group-hover:block w-64 pt-2 z-50">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden py-2 backdrop-blur-xl">
                    <a class="block px-5 py-3 text-xs font-bold text-slate-400 hover:bg-slate-800 hover:text-blue-400 transition-colors" href="<?= site_url('email/batch_perjanjian_kerja') ?>">Dokumen PK Massal</a>
                    <a class="block px-5 py-3 text-xs font-bold text-slate-400 hover:bg-slate-800 hover:text-blue-400 transition-colors" href="<?= site_url('email/batch') ?>">Pembuatan Akun</a>
                    <a class="block px-5 py-3 text-xs font-bold text-slate-400 hover:bg-slate-800 hover:text-blue-400 transition-colors" href="<?= site_url('email/batch_update') ?>">Pembaruan Data</a>
                </div>
            </div>
        </div>

        <!-- Website -->
        <div class="relative group">
            <button class="flex items-center px-4 py-2.5 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-indigo-400 hover:bg-slate-800/50 rounded-xl transition-all">
                Website
                <i class="fas fa-chevron-down ml-2 text-[10px] opacity-50 group-hover:rotate-180 transition-transform"></i>
            </button>
            <div class="absolute right-0 hidden group-hover:block w-64 pt-2 z-50">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden py-2 backdrop-blur-xl">
                    <a class="block px-5 py-3 text-xs font-bold text-slate-400 hover:bg-slate-800 hover:text-indigo-400 transition-colors" href="<?= site_url('web_opd') ?>">Situs Web OPD</a>
                    <a class="block px-5 py-3 text-xs font-bold text-slate-400 hover:bg-slate-800 hover:text-indigo-400 transition-colors" href="<?= site_url('web_desa_kelurahan') ?>">Situs Web Desa</a>
                </div>
            </div>
        </div>

        <!-- Log Pendampingan -->
        <a href="<?= site_url('assistance') ?>" class="flex items-center px-4 py-2.5 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-cyan-400 hover:bg-slate-800/50 rounded-xl transition-all no-underline">
            Pendampingan
        </a>

        <!-- Master Data -->
        <a href="<?= site_url('unit_kerja/manage') ?>" class="flex items-center px-4 py-2.5 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition-all no-underline" title="Pengaturan Master Data">
            <i class="fas fa-cog"></i>
        </a>

    </div>
</nav>