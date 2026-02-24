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
                <!-- Entity Info -->
                <div class="mb-8 p-5 bg-slate-50 border border-slate-200 rounded-xl relative overflow-hidden">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 relative z-10">Entitas Website Desa & Kelurahan</span>
                    <h6 class="text-lg font-bold text-slate-900 uppercase tracking-tight relative z-10"><?= esc($website['kecamatan']) ?> &mdash; <?= esc($website['desa_kelurahan']) ?></h6>
                </div>

                <form action="<?= site_url('web_desa_kelurahan/update/' . $website['id']) ?>" method="POST" id="websiteForm" class="space-y-6">
                    <?= csrf_field() ?>

                    <!-- Domain -->
                    <div>
                        <label for="domain" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Domain</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <i class="fas fa-link text-xs"></i>
                            </span>
                            <input type="text" class="block w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium text-slate-700 transition-all lowercase placeholder-slate-300" id="domain" name="domain" value="<?= esc($website['domain']) ?>" placeholder="e.g. desa.go.id">
                        </div>
                    </div>

                    <!-- Status & Kominfo -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="status" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Status</label>
                            <select class="block w-full px-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium text-slate-700 transition-all cursor-pointer" id="status" name="status">
                                <option value="AKTIF" <?= strtoupper($website['status']) === 'AKTIF' ? 'selected' : '' ?>>AKTIF</option>
                                <option value="NONAKTIF" <?= (strtoupper($website['status']) === 'NONAKTIF' || strtoupper($website['status']) === 'NON AKTIF' || strtoupper($website['status']) === 'TIDAK AKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                            </select>
                        </div>
                        <div>
                            <label for="dikelola_kominfo" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Dikelola Kominfo?</label>
                            <select class="block w-full px-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium text-slate-700 transition-all cursor-pointer" id="dikelola_kominfo" name="dikelola_kominfo">
                                <option value="YA" <?= strtoupper($website['dikelola_kominfo']) === 'YA' ? 'selected' : '' ?>>YA</option>
                                <option value="TIDAK" <?= strtoupper($website['dikelola_kominfo']) === 'TIDAK' ? 'selected' : '' ?>>TIDAK</option>
                            </select>
                        </div>
                    </div>

                    <!-- Platform -->
                    <div>
                        <label for="platform_id" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Platform</label>
                        <select class="block w-full px-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium text-slate-700 transition-all cursor-pointer" id="platform_id" name="platform_id">
                            <option value="">-- PILIH PLATFORM --</option>
                            <?php foreach ($platforms as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ($website['platform_id'] == $p['id']) ? 'selected' : '' ?>><?= esc($p['nama_platform']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Keterangan -->
                    <div>
                        <label for="keterangan" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Keterangan</label>
                        <textarea class="block w-full px-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium text-slate-700 transition-all min-h-[100px] placeholder-slate-300" id="keterangan" name="keterangan" rows="3" placeholder="Tambahkan catatan jika diperlukan..."><?= esc($website['keterangan']) ?></textarea>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t border-slate-100">
                        <a href="<?= site_url('web_desa_kelurahan') ?>" class="w-full sm:w-auto px-6 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-lg hover:bg-slate-50 hover:text-slate-900 transition-all text-xs uppercase tracking-widest no-underline flex items-center justify-center shadow-sm">
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