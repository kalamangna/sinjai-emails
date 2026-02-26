<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <h1 class="text-2xl font-semibold text-gray-900">Eselon</h1>

        <a href="<?= site_url('email') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-50 transition-all text-xs uppercase tracking-widest no-underline shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <!-- Grid Eselon -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (!empty($eselons)): ?>
            <?php foreach ($eselons as $eselon): ?>
                <a href="<?= site_url('email/eselon_detail/' . $eselon['id']) ?>" class="group bg-white border border-gray-200 rounded-xl p-6 hover:border-gray-400 hover:shadow-md transition-all no-underline flex flex-col">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center text-gray-400 group-hover:bg-gray-900 group-hover:text-white transition-all duration-300">
                            <i class="fas fa-layer-group text-xl"></i>
                        </div>
                        <div class="flex-grow">
                            <h3 class="text-lg font-bold text-gray-900 uppercase tracking-tight">Eselon <?= esc($eselon['nama_eselon']) ?></h3>
                            <p class="text-xs text-gray-500 uppercase font-medium mt-0.5">Lihat Detail</p>
                        </div>
                        <i class="fas fa-chevron-right text-[10px] text-gray-300 group-hover:text-gray-900 group-hover:translate-x-1 transition-all"></i>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full bg-white border border-gray-200 rounded-xl p-12 text-center">
                <p class="text-gray-400 italic text-sm">Tidak ada data eselon yang tersedia.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>