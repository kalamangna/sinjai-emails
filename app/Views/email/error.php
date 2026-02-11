<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="flex justify-center py-12">
    <div class="w-full max-w-2xl">
        <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden relative group">
            <div class="absolute -right-10 -top-10 w-64 h-64 bg-red-500/5 rounded-full blur-3xl transition-colors"></div>
            
            <!-- Card Header -->
            <div class="bg-red-600 px-10 py-6 flex items-center relative z-10">
                <i class="fas fa-exclamation-triangle text-white text-2xl mr-4 opacity-80"></i>
                <h5 class="text-white font-black text-xl uppercase tracking-tighter">Terjadi Kesalahan</h5>
            </div>
            
            <div class="p-12 text-center relative z-10">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-red-500/10 border border-red-500/20 rounded-3xl mb-10 text-red-500 shadow-lg shadow-red-900/20">
                    <i class="fas fa-bug fa-4x"></i>
                </div>
                
                <p class="text-xl font-black text-slate-200 mb-10 leading-relaxed uppercase tracking-tight">
                    <?= esc($error ?? 'AN UNKNOWN ERROR OCCURRED.') ?>
                </p>
                
                <?php if (!empty($back_url)): ?>
                    <div class="pt-10 border-t border-slate-800">
                        <a href="javascript:void(0);" onclick="history.back();" class="inline-flex items-center px-10 py-4 bg-slate-800 hover:bg-slate-700 border border-transparent rounded-2xl font-black text-xs text-slate-300 uppercase tracking-[0.2em] transition-all shadow-sm no-underline">
                            <i class="fas fa-arrow-left mr-3"></i> Kembali ke Halaman Sebelumnya
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>