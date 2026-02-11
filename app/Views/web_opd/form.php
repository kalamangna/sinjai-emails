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
                <?php 
                    $isEdit = isset($website['id']);
                    $action = $isEdit ? site_url('web_opd/update/' . $website['id']) : site_url('web_opd/store'); 
                ?>
                <form action="<?= $action ?>" method="POST" id="websiteForm" class="space-y-8">
                    <?= csrf_field() ?>
                    
                    <!-- Unit Kerja Selection -->
                    <div>
                        <label class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Organisasi Perangkat Daerah</label>
                        <?php if ($isEdit): ?>
                            <div class="px-6 py-4 bg-slate-950 border border-slate-800 rounded-2xl text-slate-200 font-black uppercase tracking-tight shadow-inner">
                                <?= esc(strtoupper($unit_kerja_name)) ?>
                            </div>
                            <input type="hidden" name="unit_kerja_id" value="<?= esc($website['unit_kerja_id']) ?>">
                        <?php else: ?>
                            <select class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all" id="unit_kerja_id" name="unit_kerja_id" required>
                                <option value="">PILIH UNIT KERJA...</option>
                                <?php foreach ($unit_kerja as $uk): ?>
                                    <option value="<?= $uk['id'] ?>" <?= (isset($website['unit_kerja_id']) && $website['unit_kerja_id'] == $uk['id']) ? 'selected' : '' ?>>
                                        <?= esc(strtoupper($uk['nama_unit_kerja'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <!-- Domain -->
                    <div>
                        <label for="domain" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Alamat Domain</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-5 flex items-center text-slate-600">
                                <i class="fas fa-link text-xs"></i>
                            </span>
                            <input type="text" class="block w-full pl-12 pr-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all lowercase placeholder-slate-800" id="domain" name="domain" value="<?= esc($website['domain'] ?? '') ?>" placeholder="e.g. dinas.sinjaikab.go.id">
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Status Keaktifan</label>
                        <select class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all" id="status" name="status">
                            <option value="AKTIF" <?= (isset($website['status']) && strtoupper($website['status']) === 'AKTIF') ? 'selected' : '' ?>>AKTIF</option>
                            <option value="NONAKTIF" <?= (isset($website['status']) && strtoupper($website['status']) === 'NONAKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                        </select>
                    </div>

                    <!-- Keterangan -->
                    <div>
                        <label for="keterangan" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Keterangan Tambahan</label>
                        <textarea class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all min-h-[120px] placeholder-slate-800" id="keterangan" name="keterangan" rows="3" placeholder="CATATAN..."><?= esc($website['keterangan'] ?? '') ?></textarea>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4 pt-8 border-t border-slate-800">
                        <a href="<?= site_url('web_opd') ?>" class="w-full md:w-auto px-10 py-4 bg-slate-800 hover:bg-slate-700 text-slate-300 font-black rounded-2xl shadow-sm transition-all text-[10px] uppercase tracking-widest no-underline flex items-center justify-center">
                            <i class="fas fa-arrow-left mr-3"></i> Batal
                        </a>
                        <button type="submit" class="w-full md:w-auto px-10 py-4 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-2xl shadow-xl shadow-blue-900/20 transition-all text-[10px] uppercase tracking-widest flex items-center justify-center" id="saveBtn">
                            <i class="fas fa-save mr-3"></i> Simpan Website
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