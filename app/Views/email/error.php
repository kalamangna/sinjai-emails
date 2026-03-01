<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="flex justify-center py-12">
    <div class="w-full max-w-2xl">
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden relative group">
            
            <!-- Card Header -->
            <div class="bg-red-600 px-10 py-6 flex items-center relative z-10">
                <i class="fas fa-exclamation-triangle text-white text-2xl mr-4 opacity-80"></i>
                <h5 class="text-white font-bold text-xl uppercase tracking-tight">Terjadi Kesalahan</h5>
            </div>
            
            <div class="p-12 text-center relative z-10">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-white border border-gray-200 border-l-4 border-l-emerald-700 rounded-2xl mb-10 text-red-600 shadow-sm">
                    <i class="fas fa-bug fa-4x"></i>
                </div>
                
                <p class="text-xl font-bold text-gray-800 mb-10 leading-relaxed uppercase tracking-tight">
                    <?= esc($error ?? 'AN UNKNOWN ERROR OCCURRED.') ?>
                </p>
                
                <?php if (!empty($back_url)): ?>
                    <div class="pt-10 border-t border-gray-100">
                        <a href="javascript:void(0);" onclick="history.back();" class="inline-flex items-center px-10 py-4 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg font-bold text-xs uppercase tracking-widest transition-all shadow-sm no-underline">
                            <i class="fas fa-arrow-left mr-3 text-white/80"></i> Kembali ke Halaman Sebelumnya
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>