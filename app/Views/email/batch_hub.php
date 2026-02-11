<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto space-y-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div class="space-y-2">
            <h2 class="text-3xl font-black text-slate-100 uppercase tracking-tighter leading-none">Layanan Batch</h2>
            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">Pemrosesan data massal untuk efisiensi administrasi digital</p>
        </div>
        <a href="<?= site_url('/') ?>" class="inline-flex items-center px-6 py-3 bg-slate-900 border border-slate-800 rounded-2xl text-xs font-black text-slate-400 uppercase tracking-widest hover:bg-slate-800 hover:text-slate-200 transition-all shadow-xl no-underline group">
            <i class="fas fa-arrow-left mr-3 group-hover:-translate-x-1 transition-transform"></i> Kembali ke Beranda
        </a>
    </div>

    <!-- Grid Menu -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Pembuatan Akun -->
        <a href="<?= site_url('email/batch') ?>" class="group bg-slate-900 border border-slate-800 rounded-[2.5rem] p-8 hover:border-blue-500/50 hover:bg-slate-900/50 transition-all shadow-2xl relative overflow-hidden no-underline">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-blue-500/5 rounded-full blur-2xl group-hover:bg-blue-500/10 transition-colors"></div>
            <div class="relative z-10 space-y-8">
                <div class="w-16 h-16 bg-blue-500/10 border border-blue-500/20 rounded-3xl flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-plus-circle text-blue-500 text-3xl"></i>
                </div>
                <div class="space-y-3">
                    <h3 class="text-xl font-black text-slate-100 uppercase tracking-tight">Pembuatan Akun</h3>
                    <p class="text-slate-400 text-sm leading-relaxed font-medium">Buat banyak akun email sekaligus dengan impor data dari spreadsheet.</p>
                </div>
                <div class="pt-4 flex items-center text-xs font-black text-blue-400 uppercase tracking-[0.2em] group-hover:translate-x-2 transition-transform">
                    Mulai <i class="fas fa-arrow-right ml-3"></i>
                </div>
            </div>
        </a>

        <!-- Pembaruan Data -->
        <a href="<?= site_url('email/batch_update') ?>" class="group bg-slate-900 border border-slate-800 rounded-[2.5rem] p-8 hover:border-indigo-500/50 hover:bg-slate-900/50 transition-all shadow-2xl relative overflow-hidden no-underline">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-indigo-500/5 rounded-full blur-2xl group-hover:bg-indigo-500/10 transition-colors"></div>
            <div class="relative z-10 space-y-8">
                <div class="w-16 h-16 bg-indigo-500/10 border border-indigo-500/20 rounded-3xl flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-edit text-indigo-500 text-3xl"></i>
                </div>
                <div class="space-y-3">
                    <h3 class="text-xl font-black text-slate-100 uppercase tracking-tight">Pembaruan Data</h3>
                    <p class="text-slate-400 text-sm leading-relaxed font-medium">Perbarui profil, jabatan, dan status kepegawaian secara massal.</p>
                </div>
                <div class="pt-4 flex items-center text-xs font-black text-indigo-400 uppercase tracking-[0.2em] group-hover:translate-x-2 transition-transform">
                    Mulai <i class="fas fa-arrow-right ml-3"></i>
                </div>
            </div>
        </a>

        <!-- Dokumen PK -->
        <a href="<?= site_url('email/batch_perjanjian_kerja') ?>" class="group bg-slate-900 border border-slate-800 rounded-[2.5rem] p-8 hover:border-cyan-500/50 hover:bg-slate-900/50 transition-all shadow-2xl relative overflow-hidden no-underline">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-cyan-500/5 rounded-full blur-2xl group-hover:bg-cyan-500/10 transition-colors"></div>
            <div class="relative z-10 space-y-8">
                <div class="w-16 h-16 bg-cyan-500/10 border border-cyan-500/20 rounded-3xl flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-file-contract text-cyan-500 text-3xl"></i>
                </div>
                <div class="space-y-3">
                    <h3 class="text-xl font-black text-slate-100 uppercase tracking-tight">Dokumen PK</h3>
                    <p class="text-slate-400 text-sm leading-relaxed font-medium">Generasi dokumen Perjanjian Kerja massal bagi PPPK Paruh Waktu.</p>
                </div>
                <div class="pt-4 flex items-center text-xs font-black text-cyan-400 uppercase tracking-[0.2em] group-hover:translate-x-2 transition-transform">
                    Mulai <i class="fas fa-arrow-right ml-3"></i>
                </div>
            </div>
        </a>
    </div>
</div>
<?= $this->endSection() ?>
