<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-tight">Buat Akun Massal</h1>

        <button onclick="history.back()" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-50 transition-all text-xs uppercase tracking-widest no-underline shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </button>
    </div>

    <!-- Input Section -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-tight">Input Data</h3>
            <button class="text-[10px] font-bold text-gray-700 hover:text-gray-800 uppercase tracking-widest transition-colors flex items-center" onclick="document.getElementById('name_input').value = document.getElementById('name_input').value.toUpperCase()">
                <i class="fas fa-font mr-1.5"></i> Huruf Kapital
            </button>
        </div>
        <div class="p-6 space-y-6">
            <div>
                <label for="name_input" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Nama Lengkap</label>
                <textarea id="name_input" class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm font-medium text-gray-800 transition-all custom-scrollbar min-h-[300px]" placeholder="Satu nama per baris..."></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nip_input" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">NIP</label>
                    <textarea id="nip_input" class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm font-medium text-gray-800 transition-all custom-scrollbar min-h-[300px]" placeholder="Satu NIP per baris..."></textarea>
                </div>
                <div>
                    <label for="nik_input" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">NIK <span class="text-gray-700 font-normal">(Opsional)</span></label>
                    <textarea id="nik_input" class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm font-medium text-gray-800 transition-all custom-scrollbar min-h-[300px]" placeholder="Satu NIK per baris..."></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="status_asn_input" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Status ASN</label>
                    <select id="status_asn_input" class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm appearance-none cursor-pointer transition-all">
                        <option selected disabled value="">Pilih Status ASN...</option>
                        <?php foreach ($status_asn_options as $option): ?>
                            <option value="<?= esc($option['id']) ?>"><?= esc($option['nama_status_asn']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="unit_kerja_input_single" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Unit Kerja</label>
                    <select id="unit_kerja_input_single" class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm appearance-none cursor-pointer choices-search transition-all">
                        <option selected disabled value="">Pilih Unit Kerja...</option>
                        <?php foreach ($unit_kerja as $unit) : ?>
                            <option value="<?= esc($unit['nama_unit_kerja']); ?>"><?= esc($unit['nama_unit_kerja']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-100">
                <button id="generate_btn" class="inline-flex items-center px-6 py-2 bg-gray-800 text-white rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-gray-700 transition-all shadow-sm">
                    <i class="fas fa-eye mr-2 text-white/80"></i> Preview
                </button>
            </div>
        </div>
    </div>

    <!-- Preview Section -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-tight">Hasil Preview</h3>
        </div>
        <div class="overflow-x-auto">
            <table id="results_table" class="w-full text-left text-sm">
                <thead class="bg-gray-100 text-gray-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-gray-200 w-12">#</th>
                        <th class="px-6 py-3 border-b border-gray-200">NIP</th>
                        <th class="px-6 py-3 border-b border-gray-200">NIK</th>
                        <th class="px-6 py-3 border-b border-gray-200">Nama Lengkap</th>
                        <th class="px-6 py-3 border-b border-gray-200">Email</th>
                        <th class="px-6 py-3 border-b border-gray-200">Password</th>
                        <th class="px-6 py-3 border-b border-gray-200 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <!-- Populated by batch.js -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Execution Section -->
    <div id="progress_section" class="hidden bg-white border border-gray-200 rounded-lg shadow-sm p-6 space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="text-[10px] font-bold text-gray-700 uppercase tracking-widest">Progres Eksekusi</h3>
            <span id="progress_text" class="text-[10px] font-bold text-gray-800 uppercase">0 / 0</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-2">
            <div id="progress_bar" class="bg-emerald-700 h-full rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
        <div id="results_log" class="p-4 bg-gray-800 text-white rounded-lg text-[10px] font-mono h-96 overflow-y-auto custom-scrollbar"></div>
    </div>

    <div class="flex justify-end">
        <button id="submit_btn" class="inline-flex items-center px-8 py-3 bg-gray-800 text-white rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-gray-700 shadow-sm transition-all disabled:opacity-40 disabled:cursor-not-allowed group" disabled>
            <i class="fas fa-cloud-upload-alt mr-2 text-white/80"></i> Eksekusi
        </button>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const unitKerjaOptions = <?= json_encode(array_map(function ($unit) {
                                    return ['id' => $unit['id'], 'nama_unit_kerja' => $unit['nama_unit_kerja']];
                                }, $unit_kerja)) ?>;
</script>
<script src="<?= base_url('js/batch.js') ?>"></script>
<?= $this->endSection() ?>