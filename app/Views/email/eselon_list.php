<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="space-y-1">
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight uppercase">Daftar Eselon</h2>
            <p class="text-slate-500 text-xs font-medium uppercase tracking-wider">Pilih eselon untuk melihat daftar email pimpinan</p>
        </div>
        <a href="<?= site_url('email/pimpinan_hub') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all shadow-sm no-underline group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> Kembali
        </a>
    </div>

    <!-- Eselon List -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (!empty($eselons)): ?>
            <?php foreach ($eselons as $eselon): ?>
                <a href="<?= site_url('email/eselon_detail/' . $eselon['id']) ?>" class="group bg-white border border-slate-200 rounded-lg p-6 hover:border-blue-300 hover:shadow-sm transition-all no-underline">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-blue-50 border border-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-600 transition-colors duration-300">
                            <i class="fas fa-building text-blue-600 text-xl group-hover:text-white transition-colors"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-bold text-slate-800 group-hover:text-blue-600 transition-colors"><?= esc($eselon['nama_eselon']) ?></h4>
                            <p class="text-slate-500 text-sm">Lihat Pimpinan</p>
                        </div>
                        <i class="fas fa-arrow-right text-slate-400 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-10 text-slate-400 italic">Tidak ada data eselon.</div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>