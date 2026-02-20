<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto space-y-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="space-y-1">
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight uppercase">Monitoring Website</h2>
            <p class="text-slate-500 text-xs font-medium uppercase tracking-wider">Pemantauan ketersediaan website daerah</p>
        </div>
        <a href="<?= site_url('/') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all shadow-sm no-underline group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> Kembali
        </a>
    </div>

    <!-- Grid Menu -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Website OPD -->
        <a href="<?= site_url('web_opd') ?>" class="group bg-white border border-slate-200 rounded-2xl p-10 hover:border-indigo-300 hover:shadow-md transition-all no-underline">
            <div class="space-y-8 text-center sm:text-left">
                <div class="w-16 h-16 bg-indigo-50 border border-indigo-100 rounded-2xl flex items-center justify-center mx-auto sm:mx-0 group-hover:bg-indigo-600 transition-colors duration-300">
                    <i class="fas fa-building text-indigo-600 text-3xl group-hover:text-white transition-colors"></i>
                </div>
                <div class="space-y-3">
                    <h3 class="text-xl font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">Website OPD</h3>
                    <p class="text-slate-500 text-sm leading-relaxed font-medium">Monitoring ketersediaan dan masa berlaku domain website Perangkat Daerah.</p>
                </div>
                <div class="pt-2 flex items-center justify-center sm:justify-start text-[11px] font-bold text-indigo-600 uppercase tracking-wider">
                    Buka Monitoring <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
        </a>

        <!-- Website Desa & Kelurahan -->
        <a href="<?= site_url('web_desa_kelurahan') ?>" class="group bg-white border border-slate-200 rounded-2xl p-10 hover:border-indigo-300 hover:shadow-md transition-all no-underline">
            <div class="space-y-8 text-center sm:text-left">
                <div class="w-16 h-16 bg-indigo-50 border border-indigo-100 rounded-2xl flex items-center justify-center mx-auto sm:mx-0 group-hover:bg-indigo-600 transition-colors duration-300">
                    <i class="fas fa-globe text-indigo-600 text-3xl group-hover:text-white transition-colors"></i>
                </div>
                <div class="space-y-3">
                    <h3 class="text-xl font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">Website Desa</h3>
                    <p class="text-slate-500 text-sm leading-relaxed font-medium">Pemantauan status keaktifan dan pengelolaan website Desa & Kelurahan.</p>
                </div>
                <div class="pt-2 flex items-center justify-center sm:justify-start text-[11px] font-bold text-indigo-600 uppercase tracking-wider">
                    Buka Monitoring <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
        </a>
    </div>
</div>
<?= $this->endSection() ?>