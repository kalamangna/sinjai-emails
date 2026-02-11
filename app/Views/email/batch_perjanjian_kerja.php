<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-10">
    <!-- Header & Back -->
    <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-6 bg-slate-900/50 p-8 rounded-[2rem] border border-slate-800 shadow-xl">
        <div class="space-y-2">
            <h1 class="text-3xl font-black text-slate-100 uppercase tracking-tight">Batch Perjanjian Kerja</h1>
            <div class="flex items-center text-xs text-slate-500 font-black uppercase tracking-widest">
                <i class="fas fa-file-contract mr-2.5 text-blue-500/50"></i>
                Dokumen PK Massal
            </div>
        </div>
        <a href="javascript:void(0);" onclick="history.back();" class="inline-flex items-center px-8 py-4 bg-slate-800 border border-slate-700 rounded-2xl font-black text-xs text-slate-200 uppercase tracking-[0.2em] hover:bg-slate-700 transition-all shadow-xl no-underline group">
            <i class="fas fa-arrow-left mr-3 group-hover:-translate-x-1 transition-transform"></i>
            Kembali
        </a>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden relative group">
        <div class="bg-slate-800/30 px-10 py-8 border-b border-slate-800">
            <h5 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                <i class="fas fa-file-contract mr-3 text-blue-500 opacity-50"></i>Pembaruan Data PK Massal
            </h5>
        </div>
        <div class="p-10">
            <form id="batch_update_form" class="space-y-8">
                <div>
                    <label class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-4 ml-1">Metode Identifikasi</label>
                    <div class="flex gap-8">
                        <label class="inline-flex items-center cursor-pointer group">
                            <input type="radio" class="w-4 h-4 text-blue-600 bg-slate-950 border-slate-800 focus:ring-blue-500" name="update_mode" id="mode_email" value="email" checked>
                            <span class="ml-3 text-xs font-bold text-slate-400 group-hover:text-slate-200 transition-colors uppercase tracking-widest">Alamat Email</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer group">
                            <input type="radio" class="w-4 h-4 text-blue-600 bg-slate-950 border-slate-800 focus:ring-blue-500" name="update_mode" id="mode_nik" value="nik">
                            <span class="ml-3 text-xs font-bold text-slate-400 group-hover:text-slate-200 transition-colors uppercase tracking-widest">Nomor NIK</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="identifier_input" id="identifier_label" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Daftar Pengenal (Satu per baris)</label>
                    <textarea class="block w-full px-6 py-5 bg-slate-950 border border-slate-800 rounded-3xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all min-h-[150px] font-mono placeholder-slate-800 custom-scrollbar" id="identifier_input" placeholder="john.doe@sinjaikab.go.id"></textarea>
                </div>

                <div class="bg-slate-950 border border-slate-800 rounded-3xl overflow-hidden shadow-inner group/pk">
                    <div class="px-6 py-4 border-b border-slate-800 bg-slate-900/50 font-black text-[10px] text-slate-500 uppercase tracking-widest flex items-center">
                        <i class="fas fa-edit mr-3 text-blue-500 opacity-50"></i>Detail Perjanjian Kerja Baru
                    </div>
                    <div class="p-8 space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label for="nomor_input" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Daftar Nomor PK</label>
                                <textarea class="block w-full px-5 py-4 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all h-32 font-mono custom-scrollbar" id="nomor_input" placeholder="881"></textarea>
                            </div>
                            <div>
                                <label for="gaji_nominal_input" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Daftar Gaji Nominal</label>
                                <textarea class="block w-full px-5 py-4 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all h-32 font-mono custom-scrollbar" id="gaji_nominal_input" placeholder="3203600"></textarea>
                            </div>
                        </div>
                        <div>
                            <label for="gaji_terbilang_input" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Daftar Gaji Terbilang</label>
                            <textarea class="block w-full px-5 py-4 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all h-32 font-mono uppercase custom-scrollbar" id="gaji_terbilang_input" placeholder="TIGA JUTA..."></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label for="tanggal_kontrak_awal_input" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Tanggal Kontrak Awal (YYYY-MM-DD)</label>
                                <textarea class="block w-full px-5 py-4 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all h-32 font-mono custom-scrollbar" id="tanggal_kontrak_awal_input" placeholder="2024-01-01"></textarea>
                            </div>
                            <div>
                                <label for="tanggal_kontrak_akhir_input" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Tanggal Kontrak Akhir (YYYY-MM-DD)</label>
                                <textarea class="block w-full px-5 py-4 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all h-32 font-mono custom-scrollbar" id="tanggal_kontrak_akhir_input" placeholder="2024-12-31"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-right pt-4">
                    <button type="submit" id="update_btn" class="px-10 py-4 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-2xl shadow-xl shadow-blue-900/20 transition-all text-[10px] uppercase tracking-widest flex items-center ml-auto">
                        <i class="fas fa-sync-alt mr-3"></i> Perbarui Data PK Massal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
        <div class="bg-slate-800/30 px-10 py-8 border-b border-slate-800">
            <h5 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                <i class="fas fa-list-alt mr-3 text-blue-500 opacity-50"></i>Hasil Pemrosesan
            </h5>
        </div>
        <div class="p-0">
            <div class="overflow-x-auto">
                <table id="results_table" class="min-w-full divide-y divide-slate-800">
                    <thead class="bg-slate-950/50">
                        <tr>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] w-12">#</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Identitas</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Status</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Pesan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 bg-slate-900/30 text-xs font-bold text-slate-300 uppercase tracking-tight">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
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

        updateBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            const mode = document.querySelector('input[name="update_mode"]:checked').value;
            const identifiers = identifierInput.value.split('\n').map(s => s.trim()).filter(s => s);
            if (!identifiers.length) return alert('Masukkan setidaknya satu identitas untuk diperbarui.');

            const mapInput = id => document.getElementById(id).value.split('\n').map(s => s.trim());
            
            updateBtn.disabled = true;
            updateBtn.innerHTML = '<div class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full mr-3"></div> MEMPROSES...';
            resultsTableBody.innerHTML = '<tr><td colspan="4" class="px-8 py-10 text-center italic text-slate-500">Sedang memproses pembaruan...</td></tr>';

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
                resultsTableBody.innerHTML = `<tr><td colspan="4" class="px-8 py-10 text-center text-red-500">Error: ${error.message}</td></tr>`;
            } finally {
                updateBtn.disabled = false;
                updateBtn.innerHTML = '<i class="fas fa-sync-alt mr-3"></i> Perbarui Data PK Massal';
            }
        });

        function renderResults(results) {
            resultsTableBody.innerHTML = results.length ? '' : '<tr><td colspan="4" class="px-8 py-10 text-center italic text-slate-500">Tidak ada item yang diproses.</td></tr>';
            results.forEach((res, i) => {
                const badge = res.success ? 'bg-green-500/10 text-green-400 border-green-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20';
                resultsTableBody.insertAdjacentHTML('beforeend', `
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="px-8 py-5 whitespace-nowrap text-slate-600 font-mono">${i + 1}</td>
                        <td class="px-8 py-5 font-mono text-slate-200">${res.identifier}</td>
                        <td class="px-8 py-5 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] border shadow-sm ${badge}">${res.success ? 'BERHASIL' : 'GAGAL'}</span>
                        </td>
                        <td class="px-8 py-5 text-slate-500 italic">${res.message || ''}</td>
                    </tr>
                `);
            });
        }
    });
</script>
<?= $this->endSection(); ?>