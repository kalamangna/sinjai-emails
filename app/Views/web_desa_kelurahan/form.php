<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-800">Perbarui Website Desa & Kelurahan</h1>
    </div>

    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
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
                        <input type="text" class="block w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 lowercase placeholder-slate-200 placeholder-slate-400" id="domain" name="domain" value="<?= esc($website['domain']) ?>" placeholder="desa.go.id">
                    </div>
                </div>

                <!-- Status & Pengelolaan -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm appearance-none cursor-pointer" id="status" name="status">
                            <option value="AKTIF" <?= strtoupper($website['status']) === 'AKTIF' ? 'selected' : '' ?>>AKTIF</option>
                            <option value="NONAKTIF" <?= (strtoupper($website['status']) === 'NONAKTIF' || strtoupper($website['status']) === 'NON AKTIF' || strtoupper($website['status']) === 'TIDAK AKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-3">Dikelola Kominfo</label>
                        <input type="hidden" name="dikelola_kominfo" value="TIDAK">
                        <label class="inline-flex items-center cursor-pointer mt-1">
                            <input type="checkbox" name="dikelola_kominfo" value="YA" class="sr-only peer" <?= strtoupper($website['dikelola_kominfo']) === 'YA' ? 'checked' : '' ?>>
                            <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-slate-400 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-slate-700"></div>
                            <span class="ms-3 text-xs font-bold text-slate-700 uppercase tracking-wider">Ya, dikelola Kominfo</span>
                        </label>
                    </div>
                </div>

                <!-- Platform -->
                <div>
                    <label for="platform_id" class="block text-sm font-medium text-slate-700 mb-1">Platform</label>
                    <select class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm appearance-none cursor-pointer" id="platform_id" name="platform_id">
                        <option value="">-- PILIH PLATFORM --</option>
                        <?php foreach ($platforms as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($website['platform_id'] == $p['id']) ? 'selected' : '' ?>><?= esc($p['nama_platform']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Informasi Server & Hosting (Read-Only) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-4 rounded-lg border border-slate-100">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-widest mb-1">IP Address Server</label>
                        <span class="text-xs font-semibold text-slate-700"><?= esc($website['ip_address'] ?: '-') ?></span>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-widest mb-1">Hosting Provider (ISP)</label>
                        <span class="text-xs font-semibold text-slate-700"><?= esc($website['hosting_provider'] ?: '-') ?></span>
                    </div>
                </div>

                <!-- Keterangan -->
                <div>
                    <label for="keterangan" class="block text-sm font-medium text-slate-700 mb-1">Keterangan</label>
                    <textarea class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm min-h-[100px] placeholder-slate-200 custom-scrollbar placeholder-slate-400" id="keterangan" name="keterangan" rows="3" placeholder="Tambahkan catatan teknis jika diperlukan..."><?= esc($website['keterangan']) ?></textarea>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t border-slate-100">
                    <a href="<?= site_url('web_desa_kelurahan') ?>" class="w-full sm:w-auto btn btn-outline no-underline">
                        <i class="fas fa-times mr-2"></i> Batal
                    </a>
                    <button type="submit" class="w-full sm:w-auto btn btn-solid" id="saveBtn">
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