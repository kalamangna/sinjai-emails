<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="flex justify-center">
    <div class="w-full max-w-2xl">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-slate-50 px-8 py-6 border-b border-slate-200">
                <h5 class="text-xs font-bold text-slate-700 uppercase tracking-widest flex items-center">
                    <i class="fas fa-edit mr-3 text-blue-500 opacity-60"></i><?= esc($title) ?>
                </h5>
            </div>

            <div class="p-8">
                <form action="<?= site_url('web_opd/update/' . $website['id']) ?>" method="POST" id="websiteForm" class="space-y-6">
                    <?= csrf_field() ?>

                    <!-- Unit Kerja Selection -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Organisasi Perangkat Daerah</label>
                        <div class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-bold text-sm uppercase">
                            <?= esc($unit_kerja_name) ?>
                        </div>
                        <input type="hidden" name="unit_kerja_id" value="<?= esc($website['unit_kerja_id']) ?>">
                    </div>

                    <!-- Domain -->
                    <div>
                        <label for="domain" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Domain</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <i class="fas fa-link text-xs"></i>
                            </span>
                            <input type="text" class="block w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium text-slate-700 transition-all lowercase placeholder-slate-300" id="domain" name="domain" value="<?= esc($website['domain'] ?? '') ?>" placeholder="e.g. dinas.sinjaikab.go.id">
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Status</label>
                        <select class="block w-full px-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium text-slate-700 transition-all cursor-pointer" id="status" name="status">
                            <option value="AKTIF" <?= (isset($website['status']) && strtoupper($website['status']) === 'AKTIF') ? 'selected' : '' ?>>AKTIF</option>
                            <option value="NONAKTIF" <?= (isset($website['status']) && strtoupper($website['status']) === 'NONAKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                        </select>
                    </div>

                    <!-- Keterangan -->
                    <div>
                        <label for="keterangan" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Keterangan</label>
                        <textarea class="block w-full px-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium text-slate-700 transition-all min-h-[100px] placeholder-slate-300" id="keterangan" name="keterangan" rows="3" placeholder="Tambahkan catatan jika diperlukan..."><?= esc($website['keterangan'] ?? '') ?></textarea>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t border-slate-100">
                        <a href="<?= site_url('web_opd') ?>" class="w-full sm:w-auto px-6 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-lg hover:bg-slate-50 hover:text-slate-900 transition-all text-xs uppercase tracking-widest no-underline flex items-center justify-center shadow-sm">
                            <i class="fas fa-arrow-left mr-2"></i> Batal
                        </a>
                        <button type="submit" class="w-full sm:w-auto px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm transition-all text-xs uppercase tracking-widest flex items-center justify-center" id="saveBtn">
                            <i class="fas fa-save mr-2"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('websiteForm').addEventListener('submit', function() {
        var btn = document.getElementById('saveBtn');
        if (btn.disabled) return;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> MEMPROSES...';
    });
</script>
<?= $this->endSection() ?>