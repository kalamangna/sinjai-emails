<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Buat Unit Kerja Massal</h1>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-8">
            <form action="<?= site_url('unit_kerja/batch_store') ?>" method="post" class="space-y-6">
                <?= csrf_field() ?>

                <div>
                    <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">Unit Induk <span class="text-gray-400 font-normal">(Opsional)</span></label>
                    <select name="parent_id" id="parent_id" class="choices-search block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm font-medium text-gray-900 appearance-none cursor-pointer">
                        <option value="">-- TANPA UNIT INDUK (ROOT) --</option>
                        <?php foreach ($parent_options as $parent): ?>
                            <option value="<?= $parent['id'] ?>"><?= esc(strtoupper($parent['nama_unit_kerja'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="unit_kerja_names" class="block text-sm font-medium text-gray-700 mb-1">Daftar Nama Unit Kerja</label>
                    <textarea class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm font-medium text-gray-900 min-h-[250px] font-mono uppercase placeholder-gray-300 custom-scrollbar" id="unit_kerja_names" name="unit_kerja_names" placeholder="CONTOH:&#10;DINAS KESEHATAN&#10;DINAS PENDIDIKAN" required></textarea>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t border-gray-100">
                    <a href="<?= site_url('unit_kerja/manage') ?>" class="w-full sm:w-auto px-6 py-2 bg-white border border-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-50 transition-all text-xs uppercase tracking-widest no-underline flex items-center justify-center shadow-sm">
                        <i class="fas fa-times mr-2"></i> Batal
                    </a>
                    <button type="submit" class="w-full sm:w-auto px-8 py-2 bg-gray-900 hover:bg-gray-800 text-white font-bold rounded-lg shadow-sm transition-all text-xs uppercase tracking-widest flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
