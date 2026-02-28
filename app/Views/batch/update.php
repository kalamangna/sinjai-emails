<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Edit Akun</h1>

        <a href="<?= site_url('email') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 text-slate-700 font-bold rounded-lg hover:bg-slate-50 transition-all text-xs uppercase tracking-widest no-underline shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <!-- Form Utama -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Konfigurasi Update</h3>
        </div>
        <div class="p-6">
            <form id="batch_update_form" class="space-y-6">
                <div class="bg-slate-50 p-6 rounded-lg border border-slate-100 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-3 uppercase tracking-tight">Identifikasi</label>
                        <div class="flex gap-6">
                            <label class="flex items-center cursor-pointer group">
                                <input type="radio" name="update_mode" id="mode_email" value="email" checked class="w-4 h-4 text-blue-600 border-slate-200 focus:ring-blue-600">
                                <span class="ml-2 text-sm text-slate-700 group-hover:text-slate-800 transition-colors">Email</span>
                            </label>
                            <label class="flex items-center cursor-pointer group">
                                <input type="radio" name="update_mode" id="mode_nik" value="nik" class="w-4 h-4 text-blue-600 border-slate-200 focus:ring-blue-600">
                                <span class="ml-2 text-sm text-slate-700 group-hover:text-slate-800 transition-colors">NIK</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="identifier_input" id="identifier_label" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Daftar Pengenal</label>
                        <textarea id="identifier_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 transition-all custom-scrollbar min-h-[300px]" placeholder="Satu pengenal per baris..."></textarea>
                    </div>
                </div>

                <!-- Detail Data Baru -->
                <div class="space-y-6">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
                        <span class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">Detail Perubahan (Satu per baris, biarkan kosong jika tidak ada perubahan)</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Nama Baru</label>
                            <textarea id="name_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 transition-all custom-scrollbar min-h-[300px]" placeholder="Nama Lengkap..."></textarea>
                        </div>
                        <div>
                            <label for="nik_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">NIK Baru</label>
                            <textarea id="nik_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 transition-all custom-scrollbar min-h-[300px]" placeholder="16 digit NIK..."></textarea>
                        </div>
                        <div>
                            <label for="nip_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">NIP Baru</label>
                            <textarea id="nip_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 transition-all custom-scrollbar min-h-[300px]" placeholder="18 digit NIP..."></textarea>
                        </div>
                        <div>
                            <label for="jabatan_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Jabatan Baru</label>
                            <textarea id="jabatan_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 transition-all custom-scrollbar min-h-[300px]" placeholder="Nama Jabatan..."></textarea>
                        </div>
                        <div>
                            <label for="golongan_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Golongan Baru</label>
                            <textarea id="golongan_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 transition-all custom-scrollbar min-h-[300px]" placeholder="IX, VII, etc..."></textarea>
                        </div>
                        <div>
                            <label for="pendidikan_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Pendidikan</label>
                            <textarea id="pendidikan_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 transition-all custom-scrollbar min-h-[300px]" placeholder="S1 - Teknik Informatika..."></textarea>
                        </div>
                        <div>
                            <label for="gelar_depan_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Gelar Depan</label>
                            <textarea id="gelar_depan_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 transition-all custom-scrollbar min-h-[300px]" placeholder="H., Dra., etc..."></textarea>
                        </div>
                        <div>
                            <label for="gelar_belakang_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Gelar Belakang</label>
                            <textarea id="gelar_belakang_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 transition-all custom-scrollbar min-h-[300px]" placeholder="S.Kom, M.Si, etc..."></textarea>
                        </div>
                        <div>
                            <label for="tempat_lahir_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Tempat Lahir</label>
                            <textarea id="tempat_lahir_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 transition-all custom-scrollbar min-h-[300px]" placeholder="Sinjai, Makassar, etc..."></textarea>
                        </div>
                        <div>
                            <label for="tanggal_lahir_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Tgl Lahir</label>
                            <textarea id="tanggal_lahir_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 transition-all custom-scrollbar min-h-[300px]" placeholder="YYYY-MM-DD"></textarea>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="status_asn_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Status ASN</label>
                            <select id="status_asn_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm appearance-none cursor-pointer transition-all">
                                <option value="">Tidak Ada Perubahan</option>
                                <?php foreach ($status_asn_options as $option): ?>
                                    <option value="<?= esc($option['id']) ?>"><?= esc($option['nama_status_asn']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="unit_kerja_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Unit Kerja</label>
                            <select id="unit_kerja_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm appearance-none cursor-pointer choices-search transition-all">
                                <option selected value="">Tidak Ada Perubahan</option>
                                <?php foreach ($unit_kerja as $unit) : ?>
                                    <option value="<?= esc($unit['nama_unit_kerja']); ?>"><?= esc($unit['nama_unit_kerja']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="eselon_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Eselon</label>
                            <select id="eselon_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm appearance-none cursor-pointer transition-all">
                                <option value="">Tidak Ada Perubahan</option>
                                <?php foreach ($eselon_options as $option): ?>
                                    <option value="<?= esc($option['id']) ?>"><?= esc($option['nama_eselon']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="pimpinan_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Pimpinan</label>
                            <select id="pimpinan_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm appearance-none cursor-pointer transition-all">
                                <option value="">Tidak Ada Perubahan</option>
                                <option value="0">Bukan Pimpinan</option>
                                <option value="1">Pimpinan</option>
                            </select>
                        </div>
                        <div>
                            <label for="pimpinan_desa_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Kades</label>
                            <select id="pimpinan_desa_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm appearance-none cursor-pointer transition-all">
                                <option value="">Tidak Ada Perubahan</option>
                                <option value="0">Bukan Kades</option>
                                <option value="1">Kepala Desa</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <button type="submit" id="update_btn" class="inline-flex items-center px-8 py-3 bg-slate-800 text-white rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-slate-700 transition-all shadow-sm">
                        <i class="fas fa-save mr-2 text-white/80"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hasil Pemrosesan -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Hasil Pemrosesan</h3>
        </div>
        <div class="overflow-x-auto">
            <table id="results_table" class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200 w-12">#</th>
                        <th class="px-6 py-3 border-b border-slate-200">Pengenal</th>
                        <th class="px-6 py-3 border-b border-slate-200">Status</th>
                        <th class="px-6 py-3 border-b border-slate-200">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-slate-700 italic">Belum ada data yang diproses.</td>
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
                identifierLabel.textContent = (this.value === 'email') ? 'Daftar Email' : 'Daftar NIK';
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
            resultsTableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-10 text-center italic text-slate-700">Sedang memproses data...</td></tr>';

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
                        names: mapInput('name_input'),
                        niks: mapInput('nik_input'),
                        nips: mapInput('nip_input'),
                        gelar_depans: mapInput('gelar_depan_input'),
                        gelar_belakangs: mapInput('gelar_belakang_input'),
                        tempat_lahirs: mapInput('tempat_lahir_input'),
                        tanggal_lahirs: mapInput('tanggal_lahir_input'),
                        pendidikans: mapInput('pendidikan_input'),
                        jabatans: mapInput('jabatan_input'),
                        golongans: mapInput('golongan_input'),
                        status_asn: document.getElementById('status_asn_input').value,
                        unit_kerja: document.getElementById('unit_kerja_input').value,
                        eselon_id: document.getElementById('eselon_input').value,
                        pimpinan: document.getElementById('pimpinan_input').value,
                        pimpinan_desa: document.getElementById('pimpinan_desa_input').value,
                    })
                });
                const result = await response.json();
                renderResults(result.results);
            } catch (error) {
                alert('Gagal memperbarui: ' + error.message);
            } finally {
                updateBtn.disabled = false;
                updateBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Update';
            }
        });

        function renderResults(results) {
            resultsTableBody.innerHTML = results.length ? '' : '<tr><td colspan="4" class="px-6 py-10 text-center italic text-slate-700">Tidak ada data hasil pemrosesan.</td></tr>';
            results.forEach((res, i) => {
                const colorClass = res.success ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-red-50 text-red-600 border-red-200';
                resultsTableBody.insertAdjacentHTML('beforeend', `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-mono text-slate-700">${i + 1}</td>
                        <td class="px-6 py-4 font-medium text-slate-800 lowercase">${res.identifier}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border ${colorClass}">${res.success ? 'BERHASIL' : 'GAGAL'}</span>
                        </td>
                        <td class="px-6 py-4 text-slate-700">${res.message || '-'}</td>
                    </tr>
                `);
            });
        }
    });
</script>
<?= $this->endSection(); ?>