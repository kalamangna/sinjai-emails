<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-800">Perbarui Website Desa & Kelurahan</h1>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-8">
            <form action="<?= site_url('web_desa_kelurahan/update/' . $website['id']) ?>" method="POST" id="websiteForm" class="space-y-6">
                <?= csrf_field() ?>

                <!-- Domain -->
                <div>
                    <label for="domain" class="block text-sm font-medium text-slate-700 mb-1">Domain</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                            <i class="fas fa-link text-xs"></i>
                        </span>
                        <input type="text" class="block w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 lowercase placeholder-slate-200" id="domain" name="domain" value="<?= esc($website['domain']) ?>" placeholder="contoh: desa.go.id">
                    </div>
                </div>

                <!-- Status & Pengelolaan -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm appearance-none cursor-pointer" id="status" name="status">
                            <option value="AKTIF" <?= strtoupper($website['status']) === 'AKTIF' ? 'selected' : '' ?>>AKTIF</option>
                            <option value="NONAKTIF" <?= (strtoupper($website['status']) === 'NONAKTIF' || strtoupper($website['status']) === 'NON AKTIF' || strtoupper($website['status']) === 'TIDAK AKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                        </select>
                    </div>
                    <div>
                        <label for="dikelola_kominfo" class="block text-sm font-medium text-slate-700 mb-1">Dikelola Kominfo</label>
                        <select class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm appearance-none cursor-pointer" id="dikelola_kominfo" name="dikelola_kominfo">
                            <option value="YA" <?= strtoupper($website['dikelola_kominfo']) === 'YA' ? 'selected' : '' ?>>YA</option>
                            <option value="TIDAK" <?= strtoupper($website['dikelola_kominfo']) === 'TIDAK' ? 'selected' : '' ?>>TIDAK</option>
                        </select>
                    </div>
                </div>

                <!-- Platform -->
                <div>
                    <label for="platform_id" class="block text-sm font-medium text-slate-700 mb-1">Platform</label>
                    <select class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm appearance-none cursor-pointer" id="platform_id" name="platform_id">
                        <option value="">-- PILIH PLATFORM --</option>
                        <?php foreach ($platforms as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($website['platform_id'] == $p['id']) ? 'selected' : '' ?>><?= esc($p['nama_platform']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Keterangan -->
                <div>
                    <label for="keterangan" class="block text-sm font-medium text-slate-700 mb-1">Keterangan</label>
                    <textarea class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm min-h-[100px] placeholder-slate-200 custom-scrollbar" id="keterangan" name="keterangan" rows="3" placeholder="Tambahkan catatan teknis jika diperlukan..."><?= esc($website['keterangan']) ?></textarea>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t border-slate-100">
                    <a href="<?= site_url('web_desa_kelurahan') ?>" class="w-full sm:w-auto px-6 py-2 bg-white border border-slate-200 text-slate-700 font-bold rounded-lg hover:bg-slate-50 transition-all text-xs uppercase tracking-widest no-underline flex items-center justify-center shadow-sm">
                        <i class="fas fa-times mr-2"></i> Batal
                    </a>
                    <button type="submit" class="w-full sm:w-auto px-8 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-lg shadow-sm transition-all text-xs uppercase tracking-widest flex items-center justify-center" id="saveBtn">
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