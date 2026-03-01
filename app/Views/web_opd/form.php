<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-800">Perbarui Website OPD</h1>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="p-8">
            <form action="<?= site_url('web_opd/update/' . $website['id']) ?>" method="POST" id="websiteForm" class="space-y-6">
                <?= csrf_field() ?>

                <!-- Informasi Unit Kerja -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">OPD</label>
                    <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-800 font-bold text-sm uppercase">
                        <?= esc($unit_kerja_name) ?>
                    </div>
                    <input type="hidden" name="unit_kerja_id" value="<?= esc($website['unit_kerja_id']) ?>">
                </div>

                <!-- Domain -->
                <div>
                    <label for="domain" class="block text-sm font-medium text-gray-700 mb-1">Domain</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-700">
                            <i class="fas fa-link text-xs"></i>
                        </span>
                        <input type="text" class="block w-full pl-9 pr-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm font-medium text-gray-800 lowercase placeholder-gray-200" id="domain" name="domain" value="<?= esc($website['domain'] ?? '') ?>" placeholder="contoh: dinas.sinjaikab.go.id">
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm appearance-none cursor-pointer" id="status" name="status">
                        <option value="AKTIF" <?= (isset($website['status']) && strtoupper($website['status']) === 'AKTIF') ? 'selected' : '' ?>>AKTIF</option>
                        <option value="NONAKTIF" <?= (isset($website['status']) && strtoupper($website['status']) === 'NONAKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                    </select>
                </div>

                <!-- Keterangan -->
                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                    <textarea class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm min-h-[100px] placeholder-gray-200 custom-scrollbar" id="keterangan" name="keterangan" rows="3" placeholder="Tambahkan catatan teknis jika diperlukan..."><?= esc($website['keterangan'] ?? '') ?></textarea>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t border-gray-100">
                    <a href="<?= site_url('web_opd') ?>" class="w-full sm:w-auto px-6 py-2 bg-white border border-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-50 transition-all text-xs uppercase tracking-widest no-underline flex items-center justify-center shadow-sm">
                        <i class="fas fa-times mr-2"></i> Batal
                    </a>
                    <button type="submit" class="w-full sm:w-auto px-8 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-lg shadow-sm transition-all text-xs uppercase tracking-widest flex items-center justify-center" id="saveBtn">
                        <i class="fas fa-save mr-2"></i> Simpan Website
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