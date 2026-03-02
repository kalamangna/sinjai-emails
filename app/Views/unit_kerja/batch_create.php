<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Buat Unit Kerja Massal</h1>

        <button onclick="history.back()" class="btn btn-outline">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </button>
    </div>

    <!-- Import Section -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Impor dari Excel (XLSX)</h3>
        </div>
        <div class="p-6 space-y-4">
            <div class="bg-slate-50 p-4 rounded-lg border border-slate-100 text-[10px] text-slate-600 space-y-1 mb-4 font-medium uppercase tracking-tight">
                <p><i class="fas fa-info-circle mr-1.5 text-slate-700"></i> Tips Impor:</p>
                <ul class="list-disc ml-5 space-y-0.5">
                    <li>Kolom pertama: <strong class="text-slate-800">nama_unit_kerja</strong></li>
                    <li>Kolom kedua: <strong class="text-slate-800">parent_id</strong> (Kosongkan untuk root)</li>
                </ul>
            </div>
            <form id="spreadsheet_import_form" method="post" enctype="multipart/form-data">
                <div>
                    <label for="spreadsheet_file" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">File Excel</label>
                    <input type="file" id="spreadsheet_file" name="spreadsheet_file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100 transition-colors">
                </div>
            </form>
            <div class="text-xs text-slate-700">
                <a href="<?= site_url('batch/download_unit_kerja_template') ?>" class="text-slate-800 font-bold hover:underline">
                    <i class="fas fa-download mr-1.5"></i> Unduh file template
                </a>
            </div>
        </div>
    </div>

    <!-- Preview Section -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Hasil Preview</h3>
            <button id="submit_btn" class="btn btn-solid btn-xs" disabled>
                <i class="fas fa-save mr-1.5 text-white/80"></i> Simpan Batch
            </button>
        </div>
        <div class="overflow-x-auto">
            <table id="results_table" class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200 w-12">#</th>
                        <th class="px-6 py-3 border-b border-slate-200">Nama Unit Kerja</th>
                        <th class="px-6 py-3 border-b border-slate-200">Parent ID</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td colspan="3" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                    <i class="fas fa-file-excel text-slate-300 text-lg"></i>
                                </div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Silakan impor file Excel terlebih dahulu</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Manual Input (Original) -->
    <div x-data="{ showManual: false }">
        <button @click="showManual = !showManual" class="text-xs font-bold text-slate-500 uppercase tracking-widest hover:text-slate-800 transition-colors focus:outline-none">
            <i class="fas" :class="showManual ? 'fa-minus-square' : 'fa-plus-square'"></i> 
            <span x-text="showManual ? 'Sembunyikan Input Manual' : 'Tampilkan Input Manual'"></span>
        </button>

        <div x-show="showManual" x-collapse class="mt-4">
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="p-8">
                    <form action="<?= site_url('unit_kerja/batch_store') ?>" method="post" class="space-y-6">
                        <?= csrf_field() ?>

                        <div>
                            <label for="parent_id" class="block text-sm font-medium text-slate-700 mb-1">Unit Induk <span class="text-slate-700 font-normal">(Opsional)</span></label>
                            <select name="parent_id" id="parent_id" class="choices-search block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 appearance-none cursor-pointer">
                                <option value="">-- TANPA UNIT INDUK (ROOT) --</option>
                                <?php foreach ($parent_options as $parent): ?>
                                    <option value="<?= $parent['id'] ?>"><?= esc(strtoupper($parent['nama_unit_kerja'])) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="unit_kerja_names" class="block text-sm font-medium text-slate-700 mb-1">Daftar Nama Unit Kerja</label>
                            <textarea class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 min-h-[300px] font-mono uppercase placeholder-slate-200 custom-scrollbar" id="unit_kerja_names" name="unit_kerja_names" placeholder="CONTOH:&#10;DINAS KESEHATAN&#10;DINAS PENDIDIKAN" required></textarea>
                        </div>

                        <div class="flex justify-end pt-6 border-t border-slate-100">
                            <button type="submit" class="btn btn-solid">
                                <i class="fas fa-save mr-2 text-white/80"></i> Simpan Batch Manual
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/unit-kerja-batch.js') ?>"></script>
<?= $this->endSection() ?>
