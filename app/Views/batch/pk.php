<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-tight">Edit PK Massal</h1>

        <button onclick="history.back()" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-50 transition-all text-xs uppercase tracking-widest no-underline shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </button>
    </div>

    <!-- Form Utama -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-tight">Konfigurasi PK</h3>
        </div>
        <div class="p-6">
            <form id="batch_update_form" class="space-y-6">
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-100 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3 uppercase tracking-tight">Identifikasi</label>
                        <div class="flex gap-6">
                            <label class="flex items-center cursor-pointer group">
                                <input type="radio" name="update_mode" id="mode_nik" value="nik" checked class="w-4 h-4 text-emerald-700 border-gray-200 focus:ring-emerald-700">
                                <span class="ml-2 text-sm text-gray-700 group-hover:text-gray-800 transition-colors">NIK</span>
                            </label>
                            <label class="flex items-center cursor-pointer group">
                                <input type="radio" name="update_mode" id="mode_email" value="email" class="w-4 h-4 text-emerald-700 border-gray-200 focus:ring-emerald-700">
                                <span class="ml-2 text-sm text-gray-700 group-hover:text-gray-800 transition-colors">Email</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="identifier_input" id="identifier_label" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Daftar NIK (Satu per baris)</label>
                        <textarea id="identifier_input" class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm font-medium text-gray-800 transition-all custom-scrollbar min-h-[300px]" placeholder="Satu pengenal per baris..."></textarea>
                    </div>
                </div>

                <!-- Detail PK Baru -->
                <div class="space-y-6">
                    <div class="flex items-center gap-2 border-b border-gray-100 pb-2">
                        <span class="text-[10px] font-bold text-gray-700 uppercase tracking-widest">Detail Perubahan</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="nomor_input" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Nomor PK</label>
                            <textarea id="nomor_input" class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm font-medium text-gray-800 transition-all custom-scrollbar min-h-[300px]" placeholder="Satu nomor per baris..."></textarea>
                        </div>
                        <div>
                            <label for="gaji_nominal_input" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Gaji (Nominal)</label>
                            <textarea id="gaji_nominal_input" class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm font-medium text-gray-800 transition-all custom-scrollbar min-h-[300px]" placeholder="Satu nominal per baris..."></textarea>
                        </div>
                    </div>

                    <div>
                        <label for="gaji_terbilang_input" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Gaji (Terbilang)</label>
                        <textarea id="gaji_terbilang_input" class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm font-medium text-gray-800 transition-all custom-scrollbar min-h-[300px]" placeholder="Satu terbilang per baris..."></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="tanggal_kontrak_awal_input" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Tanggal Awal</label>
                            <textarea id="tanggal_kontrak_awal_input" class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm font-medium text-gray-800 transition-all custom-scrollbar min-h-[300px]" placeholder="YYYY-MM-DD"></textarea>
                        </div>
                        <div>
                            <label for="tanggal_kontrak_akhir_input" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Tanggal Akhir</label>
                            <textarea id="tanggal_kontrak_akhir_input" class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm font-medium text-gray-800 transition-all custom-scrollbar min-h-[300px]" placeholder="YYYY-MM-DD"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button type="submit" id="update_btn" class="inline-flex items-center px-8 py-3 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg font-bold text-xs uppercase tracking-widest transition-all shadow-sm">
                        <i class="fas fa-save mr-2 text-white/80"></i> Update Data PK
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hasil Pemrosesan -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-tight">Hasil Pemrosesan</h3>
        </div>
        <div class="overflow-x-auto">
            <table id="results_table" class="w-full text-left text-sm">
                <thead class="bg-gray-100 text-gray-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-gray-200 w-12">#</th>
                        <th class="px-6 py-3 border-b border-gray-200">Pengenal</th>
                        <th class="px-6 py-3 border-b border-gray-200">Status</th>
                        <th class="px-6 py-3 border-b border-gray-200">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <tr>
                        <td colspan="4" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center mb-3">
                                    <i class="fas fa-layer-group text-gray-300 text-lg"></i>
                                </div>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest italic">Belum ada data yang diproses</span>
                            </div>
                        </td>
                    </tr>
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
            });
        });

        document.getElementById('batch_update_form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const mode = document.querySelector('input[name="update_mode"]:checked').value;
            const identifiers = identifierInput.value.split('\n').map(s => s.trim()).filter(s => s);
            if (!identifiers.length) return alert('Masukkan setidaknya satu identitas.');

            const mapInput = id => document.getElementById(id).value.split('\n').map(s => s.trim());

            updateBtn.disabled = true;
            updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
            resultsTableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-10 text-center italic text-gray-700">Sedang memproses pembaruan...</td></tr>';

            // Scroll ke tabel hasil
            resultsTableBody.closest('table').scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            try {
                const response = await fetch('<?= site_url('batch/process_update') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
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
                alert('Gagal memperbarui: ' + error.message);
            } finally {
                updateBtn.disabled = false;
                updateBtn.innerHTML = '<i class="fas fa-save mr-2 text-white/80"></i> Update Data PK';
            }
        });

        function renderResults(results) {
            resultsTableBody.innerHTML = results.length ? '' : '<tr><td colspan="4" class="px-6 py-10 text-center italic text-gray-700">Tidak ada data hasil pemrosesan.</td></tr>';
            results.forEach((res, i) => {
                const colorClass = res.success ? 'bg-emerald-100 text-emerald-800 border-transparent' : 'bg-red-100 text-red-700 border-transparent';
                resultsTableBody.insertAdjacentHTML('beforeend', `
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-mono text-gray-700">${i + 1}</td>
                        <td class="px-6 py-4 font-medium text-gray-800 lowercase">${res.identifier}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border ${colorClass}">${res.success ? 'BERHASIL' : 'GAGAL'}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-700">${res.message || '-'}</td>
                    </tr>
                `);
            });
        }
    });
</script>
<?= $this->endSection(); ?>