<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <!-- Header & Back -->
    <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-4 bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div class="space-y-1">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Batch Email Generation</h1>
            <div class="flex items-center text-[11px] text-slate-500 font-medium uppercase tracking-wider">
                <i class="fas fa-plus-circle mr-2 text-blue-500 opacity-50"></i>
                Pembuatan Akun Massal
            </div>
        </div>
        <a href="<?= site_url('email/batch_hub') ?>" class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all shadow-sm no-underline group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> Kembali
        </a>
    </div>

    <!-- Input Section -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center">
                <i class="fas fa-users mr-2 text-blue-500 opacity-50"></i>Input Data Massal
            </h5>
            <button class="inline-flex items-center px-3 py-1 bg-white border border-slate-200 rounded-md text-[10px] font-bold text-slate-500 hover:text-blue-600 hover:border-blue-200 transition-colors uppercase tracking-widest" onclick="document.getElementById('name_input').value = document.getElementById('name_input').value.toUpperCase()">
                <i class="fas fa-arrow-up mr-1.5 text-[8px]"></i> Make Uppercase
            </button>
        </div>
        <div class="p-6 space-y-6">
            <div>
                <label for="name_input" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Daftar Nama Lengkap</label>
                <textarea id="name_input" rows="5" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all placeholder-slate-300 custom-scrollbar" placeholder="Satu nama per baris..."></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nip_input" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Daftar NIP</label>
                    <textarea id="nip_input" rows="5" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all placeholder-slate-300 custom-scrollbar" placeholder="Satu NIP per baris..."></textarea>
                </div>
                <div>
                    <label for="nik_input" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Daftar NIK (Opsional)</label>
                    <textarea id="nik_input" rows="5" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all placeholder-slate-300 custom-scrollbar" placeholder="Satu NIK per baris..."></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <div>
                    <label for="status_asn_input" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Status Kepegawaian</label>
                    <select id="status_asn_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                        <option selected disabled value="">Pilih Status...</option>
                        <?php foreach ($status_asn_options as $option): ?>
                            <option value="<?= esc($option['id']) ?>"><?= esc($option['nama_status_asn']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="unit_kerja_input_single" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Unit Kerja</label>
                    <select id="unit_kerja_input_single" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                        <option selected disabled value="">Pilih Unit Kerja...</option>
                        <?php foreach ($unit_kerja as $unit) : ?>
                            <option value="<?= esc($unit['nama_unit_kerja']); ?>"><?= esc($unit['nama_unit_kerja']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button id="generate_btn" class="inline-flex items-center px-6 py-2.5 bg-blue-600 text-white rounded-lg font-bold text-xs uppercase tracking-wider hover:bg-blue-700 shadow-sm transition-all">
                    <i class="fas fa-magic mr-2"></i> Generate Preview
                </button>
            </div>
        </div>
    </div>

    <!-- Preview Section -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
            <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center">
                <i class="fas fa-list-alt mr-2 text-blue-500 opacity-50"></i>Pratinjau Generasi
            </h5>
        </div>
        <div class="overflow-x-auto">
            <table id="results_table" class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest w-12">#</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">NIP</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">NIK</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nama</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Unit Kerja</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Email</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Password</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest w-24">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <!-- Populated by batch.js -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Execution Section -->
    <div id="progress_section" class="hidden bg-white border border-slate-200 rounded-xl shadow-sm p-6 space-y-4">
        <div class="flex justify-between items-center">
            <h5 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Eksekusi Batch...</h5>
            <span id="progress_text" class="text-[10px] font-bold text-blue-600 uppercase">0 / 0</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-2">
            <div id="progress_bar" class="bg-blue-600 h-full rounded-full transition-all duration-300 shadow-sm" style="width: 0%"></div>
        </div>
        <div id="results_log" class="p-4 bg-slate-900 text-emerald-400 rounded-lg text-[10px] font-mono h-48 overflow-y-auto custom-scrollbar"></div>
    </div>

    <div class="flex justify-end">
        <button id="submit_btn" class="inline-flex items-center px-8 py-3 bg-emerald-600 text-white rounded-lg font-bold text-xs uppercase tracking-wider hover:bg-emerald-700 shadow-md transition-all disabled:opacity-40 disabled:cursor-not-allowed group" disabled>
            <i class="fas fa-cloud-upload-alt mr-2 group-hover:scale-110 transition-transform"></i> Eksekusi Pembuatan Akun
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