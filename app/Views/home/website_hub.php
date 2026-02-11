<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto space-y-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div class="space-y-2">
            <h2 class="text-3xl font-black text-slate-100 uppercase tracking-tighter leading-none">Monitoring Website</h2>
            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">Pemantauan ketersediaan dan keaktifan situs web pemerintah</p>
        </div>
        <a href="<?= site_url('/') ?>" class="inline-flex items-center px-6 py-3 bg-slate-900 border border-slate-800 rounded-2xl text-xs font-black text-slate-400 uppercase tracking-widest hover:bg-slate-800 hover:text-slate-200 transition-all shadow-xl no-underline group">
            <i class="fas fa-arrow-left mr-3 group-hover:-translate-x-1 transition-transform"></i> Kembali ke Beranda
        </a>
    </div>

    <!-- Grid Menu -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Website OPD -->
        <a href="<?= site_url('web_opd') ?>" class="group bg-slate-900 border border-slate-800 rounded-[2.5rem] p-10 hover:border-blue-500/50 hover:bg-slate-900/50 transition-all shadow-2xl relative overflow-hidden no-underline">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-blue-500/5 rounded-full blur-2xl group-hover:bg-blue-500/10 transition-colors"></div>
            <div class="relative z-10 space-y-8">
                <div class="w-16 h-16 bg-blue-500/10 border border-blue-500/20 rounded-3xl flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-building text-blue-500 text-3xl"></i>
                </div>
                <div class="space-y-3">
                    <h3 class="text-2xl font-black text-slate-100 uppercase tracking-tight">Website OPD</h3>
                    <p class="text-slate-400 text-sm leading-relaxed font-medium">Monitoring ketersediaan dan masa berlaku domain situs web Perangkat Daerah.</p>
                </div>
                <div class="pt-4 flex items-center text-xs font-black text-blue-400 uppercase tracking-[0.2em] group-hover:translate-x-2 transition-transform">
                    Buka Monitoring <i class="fas fa-arrow-right ml-3"></i>
                </div>
            </div>
        </a>

        <!-- Website Desa -->
        <a href="<?= site_url('web_desa_kelurahan') ?>" class="group bg-slate-900 border border-slate-800 rounded-[2.5rem] p-10 hover:border-indigo-500/50 hover:bg-slate-900/50 transition-all shadow-2xl relative overflow-hidden no-underline">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-indigo-500/5 rounded-full blur-2xl group-hover:bg-indigo-500/10 transition-colors"></div>
            <div class="relative z-10 space-y-8">
                <div class="w-16 h-16 bg-indigo-500/10 border border-indigo-500/20 rounded-3xl flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-globe text-indigo-500 text-3xl"></i>
                </div>
                <div class="space-y-3">
                    <h3 class="text-2xl font-black text-slate-100 uppercase tracking-tight">Website Desa</h3>
                    <p class="text-slate-400 text-sm leading-relaxed font-medium">Pemantauan status keaktifan dan pengelolaan situs web Desa dan Kelurahan.</p>
                </div>
                <div class="pt-4 flex items-center text-xs font-black text-indigo-400 uppercase tracking-[0.2em] group-hover:translate-x-2 transition-transform">
                    Buka Monitoring <i class="fas fa-arrow-right ml-3"></i>
                </div>
            </div>
        </a>
    </div>
</div>
<?= $this->endSection() ?>
