<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto space-y-12 lg:space-y-16">
    <div class="text-center space-y-4">
        <h1 class="text-3xl md:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
            Portal Manajemen<br>
            <span class="text-blue-600">Identitas Digital</span>
        </h1>
                    <p class="max-w-2xl mx-auto text-sm md:text-base text-slate-500 leading-relaxed font-medium">
                    Portal terpadu manajemen akun email, monitoring website, dan log layanan teknologi informasi Pemerintah Kabupaten Sinjai.
                </p>    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
        <!-- Dashboard Utama (Email) -->
        <a href="<?= site_url('email') ?>" class="group bg-white border border-slate-200 rounded-2xl p-8 hover:border-blue-300 hover:shadow-md transition-all no-underline">
            <div class="space-y-6">
                <div class="w-12 h-12 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-600 transition-colors duration-300">
                    <i class="fas fa-envelope-open-text text-blue-600 text-xl group-hover:text-white transition-colors"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Manajemen Email</h3>
                    <p class="text-slate-500 text-[13px] leading-relaxed font-medium">Kelola akun email dan pantau status sertifikat digital.</p>
                </div>
                <div class="pt-2 flex items-center text-[11px] font-bold text-blue-600 uppercase tracking-wider">
                    Lihat Detail <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
        </a>

        <!-- Unit Kerja -->
        <a href="<?= site_url('email/unit_kerja') ?>" class="group bg-white border border-slate-200 rounded-2xl p-8 hover:border-blue-300 hover:shadow-md transition-all no-underline">
            <div class="space-y-6">
                <div class="w-12 h-12 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center group-hover:bg-blue-600 transition-colors duration-300">
                    <i class="fas fa-building text-slate-600 text-xl group-hover:text-white transition-colors"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Daftar Unit Kerja</h3>
                    <p class="text-slate-500 text-[13px] leading-relaxed font-medium">Tinjau data email berdasarkan struktur organisasi.</p>
                </div>
                <div class="pt-2 flex items-center text-[11px] font-bold text-blue-600 uppercase tracking-wider">
                    Lihat Daftar <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
        </a>

        <!-- Pimpinan & Pejabat -->
        <a href="<?= site_url('email/pimpinan_hub') ?>" class="group bg-white border border-slate-200 rounded-2xl p-8 hover:border-emerald-300 hover:shadow-md transition-all no-underline">
            <div class="space-y-6">
                <div class="w-12 h-12 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center justify-center group-hover:bg-emerald-600 transition-colors duration-300">
                    <i class="fas fa-user-tie text-emerald-600 text-xl group-hover:text-white transition-colors"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-emerald-600 transition-colors">Data Pimpinan</h3>
                    <p class="text-slate-500 text-[13px] leading-relaxed font-medium">Akses data email pejabat dan kepala desa.</p>
                </div>
                <div class="pt-2 flex items-center text-[11px] font-bold text-emerald-600 uppercase tracking-wider">
                    Lihat Daftar <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
        </a>

        <!-- Proses Batch -->
        <a href="<?= site_url('email/batch_hub') ?>" class="group bg-white border border-slate-200 rounded-2xl p-8 hover:border-blue-300 hover:shadow-md transition-all no-underline">
            <div class="space-y-6">
                <div class="w-12 h-12 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-600 transition-colors duration-300">
                    <i class="fas fa-layer-group text-blue-600 text-xl group-hover:text-white transition-colors"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Layanan Batch</h3>
                    <p class="text-slate-500 text-[13px] leading-relaxed font-medium">Lakukan pembuatan, pembaruan akun, dan pembaruan data PK massal.</p>
                </div>
                <div class="pt-2 flex items-center text-[11px] font-bold text-blue-600 uppercase tracking-wider">
                    Mulai Proses <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
        </a>

        <!-- Website -->
        <a href="<?= site_url('website_hub') ?>" class="group bg-white border border-slate-200 rounded-2xl p-8 hover:border-indigo-300 hover:shadow-md transition-all no-underline">
            <div class="space-y-6">
                <div class="w-12 h-12 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center justify-center group-hover:bg-indigo-600 transition-colors duration-300">
                    <i class="fas fa-globe text-indigo-600 text-xl group-hover:text-white transition-colors"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">Monitoring Website</h3>
                    <p class="text-slate-500 text-[13px] leading-relaxed font-medium">Pantau status domain dan masa berlaku website daerah.</p>
                </div>
                <div class="pt-2 flex items-center text-[11px] font-bold text-indigo-600 uppercase tracking-wider">
                    Lihat Daftar <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
        </a>

        <!-- Pendampingan -->
        <a href="<?= site_url('assistance') ?>" class="group bg-white border border-slate-200 rounded-2xl p-8 hover:border-amber-300 hover:shadow-md transition-all no-underline">
            <div class="space-y-6">
                <div class="w-12 h-12 bg-amber-50 border border-amber-100 rounded-xl flex items-center justify-center group-hover:bg-amber-600 transition-colors duration-300">
                    <i class="fas fa-hands-helping text-amber-600 text-xl group-hover:text-white transition-colors"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-amber-600 transition-colors">Log Layanan</h3>
                    <p class="text-slate-500 text-[13px] leading-relaxed font-medium">Dokumentasi fasilitasi dan dukungan teknis.</p>
                </div>
                <div class="pt-2 flex items-center text-[11px] font-bold text-amber-600 uppercase tracking-wider">
                    Lihat Detail <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
        </a>
    </div>
</div>
<?= $this->endSection() ?>