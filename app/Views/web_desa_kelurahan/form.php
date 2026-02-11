<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="flex justify-center">
    <div class="w-full max-w-2xl">
        <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden relative group">
            <div class="bg-slate-800/30 px-10 py-8 border-b border-slate-800">
                <h5 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                    <i class="fas fa-edit mr-3 text-blue-500 opacity-50"></i><?= esc($title) ?>
                </h5>
            </div>
            
            <div class="p-10">
                <!-- Entity Info -->
                <div class="mb-10 p-6 bg-slate-950 border border-slate-800 rounded-3xl shadow-inner relative overflow-hidden group/info">
                    <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-500/5 rounded-full blur-2xl group-hover/info:bg-blue-500/10 transition-colors"></div>
                    <span class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-2 relative z-10">Entitas Website Desa/Kelurahan</span>
                    <h6 class="text-lg font-black text-slate-200 uppercase tracking-tight relative z-10"><?= esc($website['kecamatan']) ?> &mdash; <?= esc($website['desa_kelurahan']) ?></h6>
                </div>

                <form action="<?= site_url('web_desa_kelurahan/update/' . $website['id']) ?>" method="POST" id="websiteForm" class="space-y-8">
                    <?= csrf_field() ?>
                    
                    <!-- Domain -->
                    <div>
                        <label for="domain" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Alamat Domain</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-5 flex items-center text-slate-600">
                                <i class="fas fa-link text-xs"></i>
                            </span>
                            <input type="text" class="block w-full pl-12 pr-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all lowercase placeholder-slate-800" id="domain" name="domain" value="<?= esc($website['domain']) ?>" placeholder="e.g. desa.go.id">
                        </div>
                    </div>

                    <!-- Status & Kominfo -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label for="status" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Status Keaktifan</label>
                            <select class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all" id="status" name="status">
                                <option value="AKTIF" <?= strtoupper($website['status']) === 'AKTIF' ? 'selected' : '' ?>>AKTIF</option>
                                <option value="NONAKTIF" <?= (strtoupper($website['status']) === 'NONAKTIF' || strtoupper($website['status']) === 'NON AKTIF' || strtoupper($website['status']) === 'TIDAK AKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                            </select>
                        </div>
                        <div>
                            <label for="dikelola_kominfo" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Dikelola Kominfo?</label>
                            <select class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all" id="dikelola_kominfo" name="dikelola_kominfo">
                                <option value="YA" <?= strtoupper($website['dikelola_kominfo']) === 'YA' ? 'selected' : '' ?>>YA</option>
                                <option value="TIDAK" <?= strtoupper($website['dikelola_kominfo']) === 'TIDAK' ? 'selected' : '' ?>>TIDAK</option>
                            </select>
                        </div>
                    </div>

                    <!-- Platform -->
                    <div>
                        <label for="platform_id" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Platform CMS</label>
                        <select class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all" id="platform_id" name="platform_id">
                            <option value="">PILIH PLATFORM...</option>
                            <?php foreach ($platforms as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ($website['platform_id'] == $p['id']) ? 'selected' : '' ?>><?= esc(strtoupper($p['nama_platform'])) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Keterangan -->
                    <div>
                        <label for="keterangan" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Keterangan Tambahan</label>
                        <textarea class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all min-h-[120px] placeholder-slate-800" id="keterangan" name="keterangan" rows="3" placeholder="CATATAN..."><?= esc($website['keterangan']) ?></textarea>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4 pt-8 border-t border-slate-800">
                        <a href="<?= site_url('web_desa_kelurahan') ?>" class="w-full md:w-auto px-10 py-4 bg-slate-800 hover:bg-slate-700 text-slate-300 font-black rounded-2xl shadow-sm transition-all text-[10px] uppercase tracking-widest no-underline flex items-center justify-center">
                            <i class="fas fa-arrow-left mr-3"></i> Batal
                        </a>
                        <button type="submit" class="w-full md:w-auto px-10 py-4 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-2xl shadow-xl shadow-blue-900/20 transition-all text-[10px] uppercase tracking-widest flex items-center justify-center" id="saveBtn">
                            <i class="fas fa-save mr-3"></i> Simpan Perubahan
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
        btn.innerHTML = '<div class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full mr-3"></div> MEMPROSES...';
    });
</script>
<?= $this->endSection() ?>