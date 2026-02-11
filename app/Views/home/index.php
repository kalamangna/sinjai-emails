<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto space-y-16">
    <div class="text-center space-y-6">
        <h1 class="text-4xl md:text-6xl font-black text-slate-100 tracking-tighter leading-none">
            Portal Manajemen<br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-indigo-400">Identitas Digital</span>
        </h1>
        <p class="max-w-2xl mx-auto text-base md:text-lg text-slate-400 leading-relaxed font-medium">
            Sistem pengelolaan terpadu untuk akun surat elektronik, pemantauan website daerah, dan dokumentasi layanan SPBE Pemerintah Kabupaten Sinjai.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
        <!-- Dashboard Utama (Email) -->
        <a href="<?= site_url('email') ?>" class="group bg-slate-900 border border-slate-800 rounded-[2.5rem] p-8 hover:border-blue-500/50 hover:bg-slate-900/50 transition-all shadow-xl shadow-black/20 overflow-hidden relative">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/5 rounded-full blur-2xl group-hover:bg-blue-500/10 transition-colors"></div>
            <div class="relative z-10 space-y-6">
                <div class="w-14 h-14 bg-blue-500/10 border border-blue-500/20 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-envelope-open-text text-blue-500 text-2xl"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-slate-100 uppercase tracking-tight">Manajemen Email</h3>
                    <p class="text-slate-400 text-sm leading-relaxed font-medium">Dasbor utama pengelolaan akun email Perangkat Daerah dan status sertifikat elektronik.</p>
                </div>
                <div class="pt-4">
                    <span class="inline-flex items-center text-xs font-black text-blue-400 uppercase tracking-[0.2em] group-hover:translate-x-2 transition-transform">
                        Buka Dasbor <i class="fas fa-arrow-right ml-3"></i>
                    </span>
                </div>
            </div>
        </a>

        <!-- Unit Kerja -->
        <a href="<?= site_url('email/unit_kerja') ?>" class="group bg-slate-900 border border-slate-800 rounded-[2.5rem] p-8 hover:border-blue-500/50 hover:bg-slate-900/50 transition-all shadow-xl shadow-black/20 overflow-hidden relative">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/5 rounded-full blur-2xl group-hover:bg-blue-500/10 transition-colors"></div>
            <div class="relative z-10 space-y-6">
                <div class="w-14 h-14 bg-blue-500/10 border border-blue-500/20 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-building text-blue-500 text-2xl"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-slate-100 uppercase tracking-tight">Unit Kerja</h3>
                    <p class="text-slate-400 text-sm leading-relaxed font-medium">Navigasi data email berdasarkan struktur Organisasi Perangkat Daerah dan Kecamatan.</p>
                </div>
                <div class="pt-4">
                    <span class="inline-flex items-center text-xs font-black text-blue-400 uppercase tracking-[0.2em] group-hover:translate-x-2 transition-transform">
                        Pilih Unit <i class="fas fa-arrow-right ml-3"></i>
                    </span>
                </div>
            </div>
        </a>

        <!-- Pimpinan & Pejabat -->
        <a href="<?= site_url('email/pimpinan_hub') ?>" class="group bg-slate-900 border border-slate-800 rounded-[2.5rem] p-8 hover:border-green-500/50 hover:bg-slate-900/50 transition-all shadow-xl shadow-black/20 overflow-hidden relative">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-green-500/5 rounded-full blur-2xl group-hover:bg-green-500/10 transition-colors"></div>
            <div class="relative z-10 space-y-6">
                <div class="w-14 h-14 bg-green-500/10 border border-green-500/20 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-user-tie text-green-500 text-2xl"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-slate-100 uppercase tracking-tight">Data Pimpinan</h3>
                    <p class="text-slate-400 text-sm leading-relaxed font-medium">Akses cepat data email Pejabat Eselon, Kepala OPD, serta Kepala Desa dan Lurah.</p>
                </div>
                <div class="pt-4">
                    <span class="inline-flex items-center text-xs font-black text-green-400 uppercase tracking-[0.2em] group-hover:translate-x-2 transition-transform">
                        Lihat Pejabat <i class="fas fa-arrow-right ml-3"></i>
                    </span>
                </div>
            </div>
        </a>

        <!-- Proses Batch -->
        <a href="<?= site_url('email/batch_hub') ?>" class="group bg-slate-900 border border-slate-800 rounded-[2.5rem] p-8 hover:border-cyan-500/50 hover:bg-slate-900/50 transition-all shadow-xl shadow-black/20 overflow-hidden relative">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-cyan-500/5 rounded-full blur-2xl group-hover:bg-cyan-500/10 transition-colors"></div>
            <div class="relative z-10 space-y-6">
                <div class="w-14 h-14 bg-cyan-500/10 border border-cyan-500/20 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-layer-group text-cyan-500 text-2xl"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-slate-100 uppercase tracking-tight">Layanan Batch</h3>
                    <p class="text-slate-400 text-sm leading-relaxed font-medium">Pembuatan akun massal, pembaruan data, dan penyusunan dokumen PK PPPK.</p>
                </div>
                <div class="pt-4">
                    <span class="inline-flex items-center text-xs font-black text-cyan-400 uppercase tracking-[0.2em] group-hover:translate-x-2 transition-transform">
                        Mulai Proses <i class="fas fa-arrow-right ml-3"></i>
                    </span>
                </div>
            </div>
        </a>

        <!-- Website -->
        <a href="<?= site_url('website_hub') ?>" class="group bg-slate-900 border border-slate-800 rounded-[2.5rem] p-8 hover:border-indigo-500/50 hover:bg-slate-900/50 transition-all shadow-xl shadow-black/20 overflow-hidden relative">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl group-hover:bg-indigo-500/10 transition-colors"></div>
            <div class="relative z-10 space-y-6">
                <div class="w-14 h-14 bg-indigo-500/10 border border-indigo-500/20 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-globe text-indigo-500 text-2xl"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-slate-100 uppercase tracking-tight">Monitoring Website</h3>
                    <p class="text-slate-400 text-sm leading-relaxed font-medium">Pantau keaktifan domain, platform, dan masa berlaku website OPD serta Desa.</p>
                </div>
                <div class="pt-4">
                    <span class="inline-flex items-center text-xs font-black text-indigo-400 uppercase tracking-[0.2em] group-hover:translate-x-2 transition-transform">
                        Pantau Situs <i class="fas fa-arrow-right ml-3"></i>
                    </span>
                </div>
            </div>
        </a>

        <!-- Pendampingan -->
        <a href="<?= site_url('assistance') ?>" class="group bg-slate-900 border border-slate-800 rounded-[2.5rem] p-8 hover:border-amber-500/50 hover:bg-slate-900/50 transition-all shadow-xl shadow-black/20 overflow-hidden relative">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-500/5 rounded-full blur-2xl group-hover:bg-amber-500/10 transition-colors"></div>
            <div class="relative z-10 space-y-6">
                <div class="w-14 h-14 bg-amber-500/10 border border-amber-500/20 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-hands-helping text-amber-500 text-2xl"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-slate-100 uppercase tracking-tight">Pendampingan</h3>
                    <p class="text-slate-400 text-sm leading-relaxed font-medium">Log kegiatan fasilitasi layanan SPBE dan dukungan teknis bagi pengguna.</p>
                </div>
                <div class="pt-4">
                    <span class="inline-flex items-center text-xs font-black text-amber-400 uppercase tracking-[0.2em] group-hover:translate-x-2 transition-transform">
                        Buka Log <i class="fas fa-arrow-right ml-3"></i>
                    </span>
                </div>
            </div>
        </a>
    </div>
</div>
<?= $this->endSection() ?>