<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto space-y-12">
    <!-- Action Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-emerald-50 rounded-full blur-3xl opacity-50"></div>
        
        <div class="relative z-10 flex items-center gap-5">
            <div class="w-14 h-14 bg-emerald-600 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-200">
                <i class="fas fa-user-tie text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight uppercase">Direktori Pimpinan</h1>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1 flex items-center">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2 animate-pulse"></span>
                    Akses Cepat Data Akun Pejabat Strategis
                </p>
            </div>
        </div>
        
        <a href="<?= site_url('/') ?>" class="relative z-10 inline-flex items-center justify-center px-5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black text-slate-500 uppercase tracking-widest hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all shadow-sm no-underline group">
            <i class="fas fa-arrow-left mr-2.5 group-hover:-translate-x-1 transition-transform"></i> Kembali
        </a>
    </div>

    <!-- Grid Layout -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Pimpinan OPD -->
        <a href="<?= site_url('email/pimpinan') ?>" class="group bg-white border border-slate-200 rounded-[2rem] p-10 hover:border-emerald-500 hover:shadow-2xl hover:shadow-emerald-100 transition-all duration-500 no-underline relative overflow-hidden h-full flex flex-col">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 group-hover:bg-emerald-600/10 rounded-bl-[5rem] -mr-12 -mt-12 transition-all duration-500"></div>
            
            <div class="relative z-10 space-y-8 flex-grow">
                <div class="w-16 h-16 bg-slate-100 group-hover:bg-emerald-600 rounded-[1.25rem] flex items-center justify-center shadow-sm group-hover:shadow-lg group-hover:shadow-emerald-200 transition-all duration-500">
                    <i class="fas fa-landmark text-slate-400 group-hover:text-white text-3xl transition-colors duration-500"></i>
                </div>
                <div class="space-y-3">
                    <h3 class="text-xl font-black text-slate-900 group-hover:text-emerald-700 transition-colors uppercase tracking-tight">Pimpinan OPD</h3>
                    <p class="text-slate-400 text-[13px] leading-relaxed font-bold group-hover:text-slate-600 transition-colors">Data email Kepala Perangkat Daerah, Sekretaris, dan Pejabat Struktural tingkat OPD.</p>
                </div>
            </div>
            
            <div class="relative z-10 pt-8 mt-auto border-t border-slate-50 flex items-center justify-between">
                <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Buka Direktori</span>
                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                    <i class="fas fa-chevron-right text-xs"></i>
                </div>
            </div>
        </a>

        <!-- Pimpinan Desa -->
        <a href="<?= site_url('email/pimpinan_desa') ?>" class="group bg-white border border-slate-200 rounded-[2rem] p-10 hover:border-blue-500 hover:shadow-2xl hover:shadow-blue-100 transition-all duration-500 no-underline relative overflow-hidden h-full flex flex-col">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 group-hover:bg-blue-600/10 rounded-bl-[5rem] -mr-12 -mt-12 transition-all duration-500"></div>
            
            <div class="relative z-10 space-y-8 flex-grow">
                <div class="w-16 h-16 bg-slate-100 group-hover:bg-blue-600 rounded-[1.25rem] flex items-center justify-center shadow-sm group-hover:shadow-lg group-hover:shadow-blue-200 transition-all duration-500">
                    <i class="fas fa-users-cog text-slate-400 group-hover:text-white text-3xl transition-colors duration-500"></i>
                </div>
                <div class="space-y-3">
                    <h3 class="text-xl font-black text-slate-900 group-hover:text-blue-700 transition-colors uppercase tracking-tight">Kepala Desa</h3>
                    <p class="text-slate-400 text-[13px] leading-relaxed font-bold group-hover:text-slate-600 transition-colors">Data email seluruh Kepala Desa dan Lurah aktif se-Kabupaten Sinjai.</p>
                </div>
            </div>
            
            <div class="relative z-10 pt-8 mt-auto border-t border-slate-50 flex items-center justify-between">
                <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Buka Direktori</span>
                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                    <i class="fas fa-chevron-right text-xs"></i>
                </div>
            </div>
        </a>

        <!-- Daftar Eselon -->
        <a href="<?= site_url('email/eselon_list') ?>" class="group bg-white border border-slate-200 rounded-[2rem] p-10 hover:border-indigo-500 hover:shadow-2xl hover:shadow-indigo-100 transition-all duration-500 no-underline relative overflow-hidden h-full flex flex-col">
            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 group-hover:bg-indigo-600/10 rounded-bl-[5rem] -mr-12 -mt-12 transition-all duration-500"></div>
            
            <div class="relative z-10 space-y-8 flex-grow">
                <div class="w-16 h-16 bg-slate-100 group-hover:bg-indigo-600 rounded-[1.25rem] flex items-center justify-center shadow-sm group-hover:shadow-lg group-hover:shadow-indigo-200 transition-all duration-500">
                    <i class="fas fa-layer-group text-slate-400 group-hover:text-white text-3xl transition-colors duration-500"></i>
                </div>
                <div class="space-y-3">
                    <h3 class="text-xl font-black text-slate-900 group-hover:text-indigo-700 transition-colors uppercase tracking-tight">Jenjang Eselon</h3>
                    <p class="text-slate-400 text-[13px] leading-relaxed font-bold group-hover:text-slate-600 transition-colors">Klasifikasi data pimpinan berdasarkan tingkatan Eselon (II, III, IV).</p>
                </div>
            </div>
            
            <div class="relative z-10 pt-8 mt-auto border-t border-slate-50 flex items-center justify-between">
                <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Buka Direktori</span>
                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                    <i class="fas fa-chevron-right text-xs"></i>
                </div>
            </div>
        </a>
    </div>
</div>
<?= $this->endSection() ?>