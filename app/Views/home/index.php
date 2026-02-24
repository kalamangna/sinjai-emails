<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-10">
    <!-- Welcome -->
    <div class="bg-slate-900 rounded-3xl p-8 lg:p-12 relative overflow-hidden shadow-2xl shadow-slate-200">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div class="max-w-2xl">
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/20 text-emerald-400 text-[10px] font-bold uppercase tracking-widest mb-6">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2 animate-pulse"></span> Sistem Aktif
                </div>
                <h1 class="text-3xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight mb-4">
                    Dashboard<br>
                    <span class="text-emerald-500">Identitas Digital</span>
                </h1>
                <p class="text-slate-400 text-sm lg:text-base leading-relaxed font-medium">
                    Kelola akun email, sertifikat elektronik, dan monitoring web dalam satu platform.
                </p>
            </div>
            
            <div class="flex-shrink-0 grid grid-cols-2 gap-4">
                <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6 text-center">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Total Email</p>
                    <p class="text-2xl font-black text-white"><?= number_format($total_emails ?? 0) ?></p>
                </div>
                <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6 text-center">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Web OPD</p>
                    <p class="text-2xl font-black text-white"><?= number_format($total_web_opd ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?= view('components/card_metric', [
            'label' => 'Email',
            'value' => number_format($total_emails ?? 0),
            'icon'  => 'fas fa-envelope',
            'color' => 'emerald',
            'link'  => site_url('email')
        ]) ?>
        <?= view('components/card_metric', [
            'label' => 'Sertifikat',
            'value' => number_format($total_bsre ?? 0),
            'icon'  => 'fas fa-shield-alt',
            'color' => 'blue',
            'link'  => site_url('email?bsre_status=ISSUE')
        ]) ?>
        <?= view('components/card_metric', [
            'label' => 'Web Desa',
            'value' => number_format($total_web_desa ?? 0),
            'icon'  => 'fas fa-globe',
            'color' => 'indigo',
            'link'  => site_url('web_desa_kelurahan')
        ]) ?>
        <?= view('components/card_metric', [
            'label' => 'Logs',
            'value' => number_format($total_assistance ?? 0),
            'icon'  => 'fas fa-history',
            'color' => 'amber',
            'link'  => site_url('assistance')
        ]) ?>
    </div>

    <!-- Quick Access -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-3xl overflow-hidden flex flex-col shadow-sm">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-tight">Modul Email</h3>
                    <p class="text-[11px] text-slate-500 font-medium">Akses cepat manajemen email</p>
                </div>
                <i class="fas fa-tools text-slate-300"></i>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 flex-grow">
                <a href="<?= site_url('email/batch_hub') ?>" class="group p-4 rounded-2xl border border-slate-100 bg-slate-50 hover:bg-emerald-600 hover:border-emerald-600 transition-all">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-emerald-600 group-hover:text-emerald-600 group-hover:bg-white shadow-sm">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900 group-hover:text-white">Batch</p>
                            <p class="text-[10px] text-slate-500 group-hover:text-emerald-100">Proses massal</p>
                        </div>
                    </div>
                </a>
                <a href="<?= site_url('email/pimpinan_hub') ?>" class="group p-4 rounded-2xl border border-slate-100 bg-slate-50 hover:bg-blue-600 hover:border-blue-600 transition-all">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-blue-600 group-hover:text-blue-600 group-hover:bg-white shadow-sm">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900 group-hover:text-white">Pimpinan</p>
                            <p class="text-[10px] text-slate-500 group-hover:text-blue-100">Data pejabat</p>
                        </div>
                    </div>
                </a>
                <a href="<?= site_url('email/unit_kerja') ?>" class="group p-4 rounded-2xl border border-slate-100 bg-slate-50 hover:bg-indigo-600 hover:border-indigo-600 transition-all">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-indigo-600 group-hover:text-indigo-600 group-hover:bg-white shadow-sm">
                            <i class="fas fa-building"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900 group-hover:text-white">Unit Kerja</p>
                            <p class="text-[10px] text-slate-500 group-hover:text-indigo-100">Struktur unit</p>
                        </div>
                    </div>
                </a>
                <a href="<?= site_url('unit_kerja/manage') ?>" class="group p-4 rounded-2xl border border-slate-100 bg-slate-50 hover:bg-slate-900 hover:border-slate-900 transition-all">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-slate-600 group-hover:text-slate-900 group-hover:bg-white shadow-sm">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900 group-hover:text-white">Settings</p>
                            <p class="text-[10px] text-slate-500 group-hover:text-slate-300">Pengaturan</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-tight">Status</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50 border border-emerald-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center text-white text-[10px]">
                            <i class="fas fa-check"></i>
                        </div>
                        <span class="text-xs font-bold text-emerald-900">Email Aktif</span>
                    </div>
                    <span class="text-xs font-black text-emerald-600">98.2%</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-blue-50 border border-blue-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white text-[10px]">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <span class="text-xs font-bold text-blue-900">Sertifikat</span>
                    </div>
                    <span class="text-xs font-black text-blue-600">85%</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-indigo-50 border border-indigo-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white text-[10px]">
                            <i class="fas fa-globe"></i>
                        </div>
                        <span class="text-xs font-bold text-indigo-900">Web Online</span>
                    </div>
                    <span class="text-xs font-black text-indigo-600">92%</span>
                </div>
            </div>
            <div class="px-6 pb-6">
                <a href="<?= site_url('assistance') ?>" class="block w-full py-2.5 rounded-xl bg-slate-900 text-white text-center text-[10px] font-bold uppercase tracking-widest hover:bg-emerald-600 transition-colors">
                    Lihat Logs
                </a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>