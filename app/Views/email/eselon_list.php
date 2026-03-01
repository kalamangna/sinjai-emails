<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Eselon</h1>

        <a href="<?= site_url('email') ?>" class="btn btn-outline no-underline">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <!-- Grid Eselon -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (!empty($eselons)): ?>
            <?php foreach ($eselons as $eselon): ?>
                <a href="<?= site_url('email/eselon_detail/' . $eselon['id']) ?>" class="group bg-white border border-slate-200 rounded-lg p-6 hover:border-slate-800 hover:shadow-md transition-all no-underline flex flex-col">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-slate-50 rounded-lg flex items-center justify-center text-slate-700 group-hover:bg-slate-800 group-hover:text-white transition-all duration-300">
                            <i class="fas fa-layer-group text-xl"></i>
                        </div>
                        <div class="flex-grow">
                            <h3 class="text-lg font-bold text-slate-800 uppercase tracking-tight">Eselon <?= esc($eselon['nama_eselon']) ?></h3>
                            <p class="text-xs text-slate-700 uppercase font-medium mt-0.5">Lihat Detail</p>
                        </div>
                        <i class="fas fa-chevron-right text-[10px] text-slate-700 group-hover:text-slate-800 group-hover:translate-x-1 transition-all"></i>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full bg-white border border-slate-200 rounded-lg p-12 text-center">
                <p class="text-slate-700 italic text-sm">Tidak ada data eselon yang tersedia.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>