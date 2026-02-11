<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="flex justify-center">
    <div class="w-full max-w-2xl">
        <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden relative group">
            <div class="bg-slate-800/30 px-10 py-8 border-b border-slate-800">
                <h5 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                    <i class="fas fa-edit mr-3 text-blue-500 opacity-50"></i>Edit Unit Kerja
                </h5>
            </div>
            <div class="p-10">
                <form action="<?= site_url('unit_kerja/update/' . $unit_kerja['id']) ?>" method="post" class="space-y-8">
                    <?= csrf_field() ?>
                    <div>
                        <label for="nama_unit_kerja" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Nama Unit Kerja</label>
                        <input type="text" class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all uppercase placeholder-slate-800" id="nama_unit_kerja" name="nama_unit_kerja" value="<?= esc($unit_kerja['nama_unit_kerja']) ?>" required>
                    </div>
                    <div>
                        <label for="parent_id" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Induk Unit Kerja (Opsional)</label>
                        <select class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all" id="parent_id" name="parent_id">
                            <option value="">PILIH UNIT INDUK...</option>
                            <?php foreach ($parent_options as $option): ?>
                                <option value="<?= $option['id'] ?>" <?= ($option['id'] == $unit_kerja['parent_id']) ? 'selected' : '' ?>>
                                    <?= esc(strtoupper($option['nama_unit_kerja'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4 pt-8 border-t border-slate-800">
                        <a href="javascript:void(0);" onclick="history.back();" class="w-full md:w-auto px-10 py-4 bg-slate-800 hover:bg-slate-700 text-slate-300 font-black rounded-2xl shadow-sm transition-all text-[10px] uppercase tracking-widest no-underline flex items-center justify-center">
                            <i class="fas fa-arrow-left mr-3"></i> Batal
                        </a>
                        <button type="submit" class="w-full md:w-auto px-10 py-4 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-2xl shadow-xl shadow-blue-900/20 transition-all text-[10px] uppercase tracking-widest flex items-center justify-center">
                            <i class="fas fa-save mr-3"></i> Perbarui Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>