<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-10">
    <!-- Header & Back -->
    <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-6 bg-slate-900/50 p-8 rounded-[2rem] border border-slate-800 shadow-xl">
        <div class="space-y-2">
            <h1 class="text-3xl font-black text-slate-100 uppercase tracking-tight">Batch Email Generation</h1>
            <div class="flex items-center text-xs text-slate-500 font-black uppercase tracking-widest">
                <i class="fas fa-plus-circle mr-2.5 text-blue-500/50"></i>
                Pembuatan Akun Massal
            </div>
        </div>
        <a href="javascript:void(0);" onclick="history.back();" class="inline-flex items-center px-8 py-4 bg-slate-800 border border-slate-700 rounded-2xl font-black text-xs text-slate-200 uppercase tracking-[0.2em] hover:bg-slate-700 transition-all shadow-xl no-underline group">
            <i class="fas fa-arrow-left mr-3 group-hover:-translate-x-1 transition-transform"></i>
            Kembali
        </a>
    </div>

    <!-- Input Section -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden relative group">
        <div class="bg-slate-800/30 px-10 py-8 border-b border-slate-800 flex justify-between items-center">
            <h5 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                <i class="fas fa-users mr-3 text-blue-500 opacity-50"></i>Input Data Massal
            </h5>
            <button class="inline-flex items-center px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-[10px] font-black text-slate-500 hover:text-slate-200 transition-colors uppercase tracking-widest" onclick="document.getElementById('name_input').value = document.getElementById('name_input').value.toUpperCase()">
                <i class="fas fa-arrow-up mr-2 text-xs"></i> Make Uppercase
            </button>
        </div>
        <div class="p-10 space-y-8">
            <div>
                <label for="name_input" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Daftar Nama Lengkap (Satu per baris)</label>
                <textarea class="block w-full px-6 py-5 bg-slate-950 border border-slate-800 rounded-3xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all min-h-[200px] font-mono uppercase placeholder-slate-800 custom-scrollbar" id="name_input" placeholder="CONTOH:&#10;ANDI AHMAD&#10;SITI AISYAH"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label for="nip_input" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Daftar NIP (Satu per baris)</label>
                    <textarea class="block w-full px-6 py-5 bg-slate-950 border border-slate-800 rounded-3xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all min-h-[200px] font-mono placeholder-slate-800 custom-scrollbar" id="nip_input" placeholder="CONTOH:&#10;199001012020011001"></textarea>
                </div>
                <div>
                    <label for="nik_input" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Daftar NIK (Opsional, Satu per baris)</label>
                    <textarea class="block w-full px-6 py-5 bg-slate-950 border border-slate-800 rounded-3xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all min-h-[200px] font-mono placeholder-slate-800 custom-scrollbar" id="nik_input" placeholder="CONTOH:&#10;730701..."></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label for="status_asn_input" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Status Kepegawaian (Berlaku Semua)</label>
                    <select class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all" id="status_asn_input">
                        <option selected disabled value="">PILIH STATUS...</option>
                        <?php foreach ($status_asn_options as $option): ?>
                            <option value="<?= esc($option['id']) ?>"><?= esc(strtoupper($option['nama_status_asn'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="unit_kerja_input_single" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Unit Kerja (Berlaku Semua)</label>
                    <select class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all" id="unit_kerja_input_single">
                        <option selected disabled value="">PILIH UNIT KERJA...</option>
                        <?php foreach ($unit_kerja as $unit) : ?>
                            <option value="<?= esc($unit['nama_unit_kerja']); ?>"><?= esc(strtoupper($unit['nama_unit_kerja'])); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <input type="hidden" name="unitKerjaMode" id="mode_single" value="single">
            <input type="hidden" id="unit_kerja_input_multiple">

            <div class="flex justify-end pt-8 border-t border-slate-800">
                <button id="generate_btn" class="px-10 py-4 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-2xl shadow-xl shadow-blue-900/20 transition-all text-[10px] uppercase tracking-widest flex items-center">
                    <i class="fas fa-magic mr-3"></i> Generate Preview
                </button>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
        <div class="bg-slate-800/30 px-10 py-8 border-b border-slate-800">
            <h5 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                <i class="fas fa-list-alt mr-3 text-blue-500 opacity-50"></i>Pratinjau Hasil Generasi
            </h5>
        </div>
        <div class="p-0">
            <div class="overflow-x-auto">
                <table id="results_table" class="min-w-full divide-y divide-slate-800">
                    <thead class="bg-slate-950/50">
                        <tr>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">#</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">NIP / NIK</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Nama Pengguna</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Unit Kerja</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Email & Pass</th>
                            <th class="px-8 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 bg-slate-900/30 text-xs font-bold text-slate-300 uppercase tracking-tight">
                        <!-- Results populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Progress Section -->
    <div id="progress_section" class="hidden bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden p-10 space-y-8">
        <div class="flex justify-between items-center">
            <h5 class="text-xs font-black text-blue-400 uppercase tracking-[0.2em]">Proses Pengiriman Batch...</h5>
            <span id="progress_text" class="text-xs font-black text-slate-500 uppercase tracking-widest">Processing 0 / 0</span>
        </div>
        
        <div class="w-full bg-slate-950 rounded-full h-5 p-1 border border-slate-800 shadow-inner">
            <div id="progress_bar" class="bg-blue-600 h-full rounded-full transition-all duration-300 shadow-lg shadow-blue-900/40 text-[9px] font-black text-white flex items-center justify-center" style="width: 0%">0%</div>
        </div>

        <div id="results_log" class="p-6 bg-slate-950 text-emerald-400 rounded-3xl text-[11px] font-mono h-64 overflow-y-auto border border-slate-800 custom-scrollbar shadow-inner">
            <!-- Log messages appended here -->
        </div>
    </div>

    <div class="flex justify-end">
        <button id="submit_btn" class="px-12 py-5 bg-green-600 hover:bg-green-700 text-white font-black rounded-[2rem] shadow-2xl shadow-green-900/40 transition-all text-[11px] uppercase tracking-[0.2em] disabled:opacity-30 disabled:cursor-not-allowed group" disabled>
            <i class="fas fa-cloud-upload-alt mr-3 group-hover:scale-110 transition-transform"></i> Eksekusi Pembuatan Akun
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
<script src="/js/batch.js"></script>
<?= $this->endSection() ?>