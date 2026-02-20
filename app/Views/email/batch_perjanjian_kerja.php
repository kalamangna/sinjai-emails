<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <!-- Header & Back -->
    <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-4 bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div class="space-y-1">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Batch Perjanjian Kerja</h1>
            <div class="flex items-center text-[11px] text-slate-500 font-medium uppercase tracking-wider">
                <i class="fas fa-file-contract mr-2 text-blue-500 opacity-50"></i>
                Dokumen PK Massal
            </div>
        </div>
        <a href="<?= site_url('email/batch_hub') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all shadow-sm no-underline group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> Kembali
        </a>
    </div>

    <!-- Main Form -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
            <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center">
                <i class="fas fa-file-contract mr-2 text-blue-500 opacity-50"></i>Pembaruan Data PK Massal
            </h5>
        </div>
        <div class="p-6">
            <form id="batch_update_form" class="space-y-8">
                <div class="bg-slate-50 p-6 rounded-xl border border-slate-100 space-y-6">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1">Metode Identifikasi</label>
                        <div class="flex gap-6">
                            <label class="inline-flex items-center cursor-pointer group">
                                <input type="radio" class="w-4 h-4 text-blue-600 bg-white border-slate-300 focus:ring-blue-500" name="update_mode" id="mode_email" value="email" checked>
                                <span class="ml-2.5 text-xs font-bold text-slate-600 group-hover:text-slate-900 transition-colors uppercase tracking-tight">Alamat Email</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer group">
                                <input type="radio" class="w-4 h-4 text-blue-600 bg-white border-slate-300 focus:ring-blue-500" name="update_mode" id="mode_nik" value="nik">
                                <span class="ml-2.5 text-xs font-bold text-slate-600 group-hover:text-slate-900 transition-colors uppercase tracking-tight">Nomor NIK</span>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="identifier_input" id="identifier_label" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Daftar Pengenal (Satu per baris)</label>
                        <textarea id="identifier_input" rows="4" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all placeholder-slate-300 custom-scrollbar" placeholder="john.doe@sinjaikab.go.id"></textarea>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="px-6 py-3 border-b border-slate-100 bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-widest flex items-center">
                        <i class="fas fa-edit mr-2 text-blue-500 opacity-50"></i>Detail PK Baru
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="nomor_input" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nomor PK</label>
                                <textarea id="nomor_input" rows="3" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 text-sm font-medium custom-scrollbar" placeholder="Contoh: 881"></textarea>
                            </div>
                            <div class="space-y-2">
                                <label for="gaji_nominal_input" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Gaji Nominal</label>
                                <textarea id="gaji_nominal_input" rows="3" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 text-sm font-medium custom-scrollbar" placeholder="Contoh: 3203600"></textarea>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="gaji_terbilang_input" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Gaji Terbilang</label>
                            <textarea id="gaji_terbilang_input" rows="3" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 text-sm font-medium custom-scrollbar" placeholder="Contoh: TIGA JUTA..."></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="tanggal_kontrak_awal_input" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Kontrak Awal (YYYY-MM-DD)</label>
                                <textarea id="tanggal_kontrak_awal_input" rows="3" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 text-sm font-medium custom-scrollbar" placeholder="2024-01-01"></textarea>
                            </div>
                            <div class="space-y-2">
                                <label for="tanggal_kontrak_akhir_input" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Kontrak Akhir (YYYY-MM-DD)</label>
                                <textarea id="tanggal_kontrak_akhir_input" rows="3" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 text-sm font-medium custom-scrollbar" placeholder="2024-12-31"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <button type="submit" id="update_btn" class="inline-flex items-center justify-center px-6 py-2.5 bg-blue-600 text-white rounded-lg font-bold text-xs uppercase tracking-wider hover:bg-blue-700 active:bg-blue-800 transition-all shadow-md group focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        <i class="fas fa-sync-alt mr-2 group-hover:rotate-180 transition-transform duration-500"></i> Update PK Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
            <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center">
                <i class="fas fa-list-alt mr-2 text-blue-500 opacity-50"></i>Hasil Pemrosesan
            </h5>
        </div>
        <div class="overflow-x-auto">
            <table id="results_table" class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest w-12">#</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Identitas</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pesan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <!-- Populated by JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const identifierInput = document.getElementById('identifier_input');
        const identifierLabel = document.getElementById('identifier_label');
        const updateBtn = document.getElementById('update_btn');
        const resultsTableBody = document.querySelector('#results_table tbody');

        document.querySelectorAll('input[name="update_mode"]').forEach(radio => {
            radio.addEventListener('change', function() {
                identifierLabel.textContent = (this.value === 'email') ? 'Daftar Alamat Email (Satu per baris)' : 'Daftar NIK (Satu per baris)';
                identifierInput.placeholder = (this.value === 'email') ? 'john.doe@example.com' : '730701...';
            });
        });

        document.getElementById('batch_update_form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const mode = document.querySelector('input[name="update_mode"]:checked').value;
            const identifiers = identifierInput.value.split('\n').map(s => s.trim()).filter(s => s);
            if (!identifiers.length) return alert('Masukkan setidaknya satu identitas.');

            const mapInput = id => document.getElementById(id).value.split('\n').map(s => s.trim());
            
            updateBtn.disabled = true;
            updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> MEMPROSES...';
            resultsTableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-10 text-center italic text-slate-400 text-xs">Sedang memproses pembaruan...</td></tr>';

            try {
                const response = await fetch('<?= site_url('email/batch_update_process') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({
                        mode: mode,
                        identifiers: identifiers,
                        nomors: mapInput('nomor_input'),
                        gaji_nominals: mapInput('gaji_nominal_input'),
                        gaji_terbilangs: mapInput('gaji_terbilang_input'),
                        tanggal_kontrak_awals: mapInput('tanggal_kontrak_awal_input'),
                        tanggal_kontrak_akhirs: mapInput('tanggal_kontrak_akhir_input'),
                    })
                });
                const result = await response.json();
                renderResults(result.results);
            } catch (error) {
                resultsTableBody.innerHTML = `<tr><td colspan="4" class="px-6 py-10 text-center text-rose-500 font-bold">Error: ${error.message}</td></tr>`;
            } finally {
                updateBtn.disabled = false;
                updateBtn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> Perbarui Data PK Massal';
            }
        });

        function renderResults(results) {
            resultsTableBody.innerHTML = results.length ? '' : '<tr><td colspan="4" class="px-6 py-10 text-center italic text-slate-400">Tidak ada item yang diproses.</td></tr>';
            results.forEach((res, i) => {
                const badge = res.success ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100';
                resultsTableBody.insertAdjacentHTML('beforeend', `
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-slate-400 font-mono">${i + 1}</td>
                        <td class="px-6 py-4 font-mono text-[13px] text-slate-700">${res.identifier}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold border ${badge}">${res.success ? 'SUCCESS' : 'FAILED'}</span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 italic">${res.message || '-'}</td>
                    </tr>
                `);
            });
        }
    });
</script>
<?= $this->endSection(); ?>