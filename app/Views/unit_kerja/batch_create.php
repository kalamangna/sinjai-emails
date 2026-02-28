<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-800">Buat Unit Kerja Massal</h1>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
        <div class="p-8">
            <form action="<?= site_url('unit_kerja/batch_store') ?>" method="post" class="space-y-6">
                <?= csrf_field() ?>

                <div>
                    <label for="parent_id" class="block text-sm font-medium text-slate-700 mb-1">Unit Induk <span class="text-slate-700 font-normal">(Opsional)</span></label>
                    <select name="parent_id" id="parent_id" class="choices-search block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 appearance-none cursor-pointer">
                        <option value="">-- TANPA UNIT INDUK (ROOT) --</option>
                        <?php foreach ($parent_options as $parent): ?>
                            <option value="<?= $parent['id'] ?>"><?= esc(strtoupper($parent['nama_unit_kerja'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="unit_kerja_names" class="block text-sm font-medium text-slate-700 mb-1">Daftar Nama Unit Kerja</label>
                    <textarea class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 min-h-[300px] font-mono uppercase placeholder-slate-200 custom-scrollbar" id="unit_kerja_names" name="unit_kerja_names" placeholder="CONTOH:&#10;DINAS KESEHATAN&#10;DINAS PENDIDIKAN" required></textarea>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t border-slate-100">
                    <a href="<?= site_url('unit_kerja/manage') ?>" class="w-full sm:w-auto px-6 py-2 bg-white border border-slate-200 text-slate-700 font-bold rounded-lg hover:bg-slate-50 transition-all text-xs uppercase tracking-widest no-underline flex items-center justify-center shadow-sm">
                        <i class="fas fa-times mr-2"></i> Batal
                    </a>
                    <button type="submit" class="w-full sm:w-auto px-8 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-lg shadow-sm transition-all text-xs uppercase tracking-widest flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
