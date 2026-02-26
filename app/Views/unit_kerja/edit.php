<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Perbarui Unit Kerja</h1>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-8">
            <form action="<?= site_url('unit_kerja/update/' . $unit_kerja['id']) ?>" method="post" class="space-y-6">
                    <?= csrf_field() ?>
                    <div>
                        <label for="nama_unit_kerja" class="block text-sm font-medium text-gray-700 mb-1">Nama Unit Kerja</label>
                        <input type="text" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm font-medium text-gray-900 uppercase placeholder-gray-300" id="nama_unit_kerja" name="nama_unit_kerja" value="<?= esc($unit_kerja['nama_unit_kerja']) ?>" required>
                    </div>
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">Unit Induk <span class="text-gray-400 font-normal">(Opsional)</span></label>
                        <select class="choices-search block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm font-medium text-gray-900 appearance-none cursor-pointer" id="parent_id" name="parent_id">
                            <option value="">-- TANPA UNIT INDUK (ROOT) --</option>
                            <?php foreach ($parent_options as $option): ?>
                                <option value="<?= $option['id'] ?>" <?= ($option['id'] == $unit_kerja['parent_id']) ? 'selected' : '' ?>>
                                    <?= esc(strtoupper($option['nama_unit_kerja'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
</div>
<?= $this->endSection() ?>
