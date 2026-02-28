<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="flex justify-center py-12">
    <div class="w-full max-w-2xl">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden relative group">
            
            <!-- Card Header -->
            <div class="bg-red-600 px-10 py-6 flex items-center relative z-10">
                <i class="fas fa-exclamation-triangle text-white text-2xl mr-4 opacity-80"></i>
                <h5 class="text-white font-bold text-xl uppercase tracking-tight">Terjadi Kesalahan</h5>
            </div>
            
            <div class="p-12 text-center relative z-10">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-red-50 border border-red-200 rounded-2xl mb-10 text-red-600 shadow-sm">
                    <i class="fas fa-bug fa-4x"></i>
                </div>
                
                <p class="text-xl font-bold text-slate-800 mb-10 leading-relaxed uppercase tracking-tight">
                    <?= esc($error ?? 'AN UNKNOWN ERROR OCCURRED.') ?>
                </p>
                
                <?php if (!empty($back_url)): ?>
                    <div class="pt-10 border-t border-slate-100">
                        <a href="javascript:void(0);" onclick="history.back();" class="inline-flex items-center px-10 py-4 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-bold text-xs uppercase tracking-widest transition-all shadow-sm no-underline">
                            <i class="fas fa-arrow-left mr-3 text-white/80"></i> Kembali ke Halaman Sebelumnya
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>