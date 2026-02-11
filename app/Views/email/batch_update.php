<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-10">
    <!-- Header & Back -->
    <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-6 bg-slate-900/50 p-8 rounded-[2rem] border border-slate-800 shadow-xl">
        <div class="space-y-2">
            <h1 class="text-3xl font-black text-slate-100 uppercase tracking-tight">Batch Email Update</h1>
            <div class="flex items-center text-xs text-slate-500 font-black uppercase tracking-widest">
                <i class="fas fa-edit mr-2.5 text-blue-500/50"></i>
                Pembaruan Data Massal
            </div>
        </div>
        <a href="javascript:void(0);" onclick="history.back();" class="inline-flex items-center px-8 py-4 bg-slate-800 border border-slate-700 rounded-2xl font-black text-xs text-slate-200 uppercase tracking-[0.2em] hover:bg-slate-700 transition-all shadow-xl no-underline group">
            <i class="fas fa-arrow-left mr-3 group-hover:-translate-x-1 transition-transform"></i>
            Kembali
        </a>
    </div>

    <!-- Main Form Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
        <div class="bg-slate-800/30 px-10 py-6 border-b border-slate-800">
            <h5 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                <i class="fas fa-edit mr-3 text-blue-500 opacity-50"></i>Update Emails
            </h5>
        </div>
        <div class="p-10">
            <form id="batch_update_form" class="space-y-8">
                <div class="bg-slate-950 p-8 rounded-3xl border border-slate-800 space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-4 ml-1">Update By:</label>
                        <div class="flex gap-6">
                            <label class="flex items-center cursor-pointer group">
                                <input type="radio" name="update_mode" id="mode_email" value="email" checked class="w-4 h-4 text-blue-600 bg-slate-900 border-slate-700 focus:ring-blue-500 focus:ring-offset-slate-900">
                                <span class="ml-3 text-sm font-bold text-slate-400 group-hover:text-slate-200 transition-colors uppercase tracking-tight">Email Address</span>
                            </label>
                            <label class="flex items-center cursor-pointer group">
                                <input type="radio" name="update_mode" id="mode_nik" value="nik" class="w-4 h-4 text-blue-600 bg-slate-900 border-slate-700 focus:ring-blue-500 focus:ring-offset-slate-900">
                                <span class="ml-3 text-sm font-bold text-slate-400 group-hover:text-slate-200 transition-colors uppercase tracking-tight">NIK</span>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label for="identifier_input" id="identifier_label" class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">Enter one email address per line to update.</label>
                        <textarea id="identifier_input" rows="6" class="block w-full px-5 py-4 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all placeholder-slate-700 uppercase tracking-tight" placeholder="e.g.&#10;john.doe@example.com&#10;jane.smith@example.com"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-8">
                    <div class="bg-slate-950 p-8 rounded-3xl border border-slate-800 space-y-8">
                        <div class="flex items-center gap-3 border-b border-slate-800 pb-4">
                            <i class="fas fa-user text-blue-500 opacity-50 text-sm"></i>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Email Account & Personal Info</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <label for="name_input" class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">New Names</label>
                                <textarea id="name_input" rows="4" class="block w-full px-5 py-4 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all placeholder-slate-700 uppercase tracking-tight" placeholder="e.g. John Doe&#10;Jane Smith"></textarea>
                            </div>
                            <div class="space-y-3">
                                <label for="password_input" class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">New Passwords</label>
                                <textarea id="password_input" rows="4" class="block w-full px-5 py-4 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all placeholder-slate-700 uppercase tracking-tight" placeholder="Leave blank to keep current password"></textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <label for="gelar_depan_input" class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">New Gelar Depan</label>
                                <textarea id="gelar_depan_input" rows="3" class="block w-full px-5 py-4 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all placeholder-slate-700 uppercase tracking-tight" placeholder="e.g. Dr.&#10;H."></textarea>
                            </div>
                            <div class="space-y-3">
                                <label for="gelar_belakang_input" class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">New Gelar Belakang</label>
                                <textarea id="gelar_belakang_input" rows="3" class="block w-full px-5 py-4 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all placeholder-slate-700 uppercase tracking-tight" placeholder="e.g. S.Kom&#10;M.Si"></textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <label for="nik_input" class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">New NIKs</label>
                                <textarea id="nik_input" rows="3" class="block w-full px-5 py-4 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all placeholder-slate-700 uppercase tracking-tight" placeholder="e.g. 1234567890123456&#10;0987654321098765"></textarea>
                            </div>
                            <div class="space-y-3">
                                <label for="nip_input" class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">New NIPs</label>
                                <textarea id="nip_input" rows="3" class="block w-full px-5 py-4 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all placeholder-slate-700 uppercase tracking-tight" placeholder="e.g. 199001012020011001"></textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <label for="tempat_lahir_input" class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">New Tempat Lahir</label>
                                <textarea id="tempat_lahir_input" rows="2" class="block w-full px-5 py-4 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all placeholder-slate-700 uppercase tracking-tight" placeholder="e.g. Sinjai"></textarea>
                            </div>
                            <div class="space-y-3">
                                <label for="tanggal_lahir_input" class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">New Tanggal Lahir (YYYY-MM-DD)</label>
                                <textarea id="tanggal_lahir_input" rows="2" class="block w-full px-5 py-4 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all placeholder-slate-700 uppercase tracking-tight" placeholder="e.g. 1990-01-01"></textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <label for="pendidikan_input" class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">New Pendidikan</label>
                                <textarea id="pendidikan_input" rows="3" class="block w-full px-5 py-4 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all placeholder-slate-700 uppercase tracking-tight" placeholder="e.g. S1 Teknik Informatika"></textarea>
                            </div>
                            <div class="space-y-3">
                                <label for="jabatan_input" class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">New Jabatan</label>
                                <textarea id="jabatan_input" rows="3" class="block w-full px-5 py-4 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all placeholder-slate-700 uppercase tracking-tight" placeholder="e.g. Pranata Komputer Ahli Pertama"></textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="space-y-3">
                                <label for="status_asn_input" class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">New Status ASN</label>
                                <select id="status_asn_input" class="block w-full px-5 py-3.5 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase tracking-tight cursor-pointer transition-all">
                                    <option value="">Do not change</option>
                                    <?php foreach ($status_asn_options as $option): ?>
                                        <option value="<?= esc($option['id']) ?>"><?= esc(strtoupper($option['nama_status_asn'])) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="space-y-3">
                                <label for="pimpinan_input" class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">New Pimpinan OPD</label>
                                <select id="pimpinan_input" class="block w-full px-5 py-3.5 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase tracking-tight cursor-pointer transition-all">
                                    <option value="">Do not change</option>
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div class="space-y-3">
                                <label for="pimpinan_desa_input" class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">New Pimpinan Desa</label>
                                <select id="pimpinan_desa_input" class="block w-full px-5 py-3.5 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase tracking-tight cursor-pointer transition-all">
                                    <option value="">Do not change</option>
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div class="space-y-3">
                                <label for="unit_kerja_input" class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">New Unit Kerja (All)</label>
                                <select id="unit_kerja_input" class="block w-full px-5 py-3.5 bg-slate-900 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase tracking-tight cursor-pointer transition-all">
                                    <option selected value="">Do not change</option>
                                    <?php foreach ($unit_kerja as $unit) : ?>
                                        <option value="<?= esc($unit['nama_unit_kerja']); ?>"><?= esc(strtoupper($unit['nama_unit_kerja'])); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6">
                    <button type="submit" id="update_btn" class="inline-flex items-center px-10 py-5 bg-blue-600 border border-transparent rounded-[1.5rem] font-black text-xs text-white uppercase tracking-[0.2em] hover:bg-blue-700 transition-all shadow-xl shadow-blue-900/20 group">
                        <i class="fas fa-sync-alt mr-3 group-hover:rotate-180 transition-transform duration-500"></i>
                        Update Selected Emails
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Section -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
        <div class="bg-slate-800/30 px-10 py-6 border-b border-slate-800">
            <h5 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                <i class="fas fa-list-alt mr-3 text-blue-500 opacity-50"></i>Update Results
            </h5>
        </div>
        <div class="p-0">
            <div class="overflow-x-auto">
                <table id="results_table" class="min-w-full divide-y divide-slate-800">
                    <thead class="bg-slate-950/30">
                        <tr>
                            <th scope="col" class="px-10 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">#</th>
                            <th scope="col" class="px-10 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Identifier</th>
                            <th scope="col" class="px-10 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Status</th>
                            <th scope="col" class="px-10 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Message</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 bg-slate-900/30">
                        <!-- Results will be populated here -->
                        <tr>
                            <td colspan="4" class="px-10 py-12 text-center text-slate-500 italic font-medium uppercase tracking-widest text-[10px]">No data processed yet</td>
                        </tr>
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
        const nameInput = document.getElementById('name_input');
        const gelarDepanInput = document.getElementById('gelar_depan_input');
        const gelarBelakangInput = document.getElementById('gelar_belakang_input');
        const passwordInput = document.getElementById('password_input');
        const nikInput = document.getElementById('nik_input');
        const nipInput = document.getElementById('nip_input');
        const tempatLahirInput = document.getElementById('tempat_lahir_input');
        const tanggalLahirInput = document.getElementById('tanggal_lahir_input');
        const pendidikanInput = document.getElementById('pendidikan_input');
        const jabatanInput = document.getElementById('jabatan_input');
        const statusAsnInput = document.getElementById('status_asn_input');
        const pimpinanInput = document.getElementById('pimpinan_input');
        const pimpinanDesaInput = document.getElementById('pimpinan_desa_input');
        const unitKerjaInput = document.getElementById('unit_kerja_input');
        const updateBtn = document.getElementById('update_btn');
        const resultsTableBody = document.querySelector('#results_table tbody');

        document.querySelectorAll('input[name="update_mode"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'email') {
                    identifierLabel.textContent = 'Enter one email address per line to update.';
                    identifierInput.placeholder = 'e.g.\njohn.doe@example.com\njane.smith@example.com';
                } else {
                    identifierLabel.textContent = 'Enter one NIK per line to update.';
                    identifierInput.placeholder = 'e.g.\n1234567890123456\n0987654321098765';
                }
            });
        });

        updateBtn.addEventListener('click', async function(e) {
            e.preventDefault();

            const updateMode = document.querySelector('input[name="update_mode"]:checked').value;

            const rawIdentifiers = identifierInput.value.split('\n');
            const rawNames = nameInput.value.split('\n');
            const rawGelarDepans = gelarDepanInput.value.split('\n');
            const rawGelarBelakangs = gelarBelakangInput.value.split('\n');
            const rawPasswords = passwordInput.value.split('\n');
            const rawNiks = nikInput.value.split('\n');
            const rawNips = nipInput.value.split('\n');
            const rawTempatLahirs = tempatLahirInput.value.split('\n');
            const rawTanggalLahirs = tanggalLahirInput.value.split('\n');
            const rawPendidikans = pendidikanInput.value.split('\n');
            const rawJabatans = jabatanInput.value.split('\n');

            const identifiers = [];
            const newNames = [];
            const newGelarDepans = [];
            const newGelarBelakangs = [];
            const newPasswords = [];
            const newNiks = [];
            const newNips = [];
            const newTempatLahirs = [];
            const newTanggalLahirs = [];
            const newPendidikans = [];
            const newJabatans = [];

            for (let i = 0; i < rawIdentifiers.length; i++) {
                const id = rawIdentifiers[i].trim();
                if (id) {
                    identifiers.push(id);
                    newNames.push((rawNames[i] !== undefined) ? rawNames[i].trim() : '');
                    newGelarDepans.push((rawGelarDepans[i] !== undefined) ? rawGelarDepans[i].trim() : '');
                    newGelarBelakangs.push((rawGelarBelakangs[i] !== undefined) ? rawGelarBelakangs[i].trim() : '');
                    newPasswords.push((rawPasswords[i] !== undefined) ? rawPasswords[i].trim() : '');
                    newNiks.push((rawNiks[i] !== undefined) ? rawNiks[i].trim() : '');
                    newNips.push((rawNips[i] !== undefined) ? rawNips[i].trim() : '');
                    newTempatLahirs.push((rawTempatLahirs[i] !== undefined) ? rawTempatLahirs[i].trim() : '');
                    newTanggalLahirs.push((rawTanggalLahirs[i] !== undefined) ? rawTanggalLahirs[i].trim() : '');
                    newPendidikans.push((rawPendidikans[i] !== undefined) ? rawPendidikans[i].trim() : '');
                    newJabatans.push((rawJabatans[i] !== undefined) ? rawJabatans[i].trim() : '');
                }
            }

            const newUnitKerja = unitKerjaInput.value;
            const newStatusAsn = statusAsnInput.value;
            const newPimpinan = pimpinanInput.value;
            const newPimpinanDesa = pimpinanDesaInput.value;

            if (identifiers.length === 0) {
                alert('Please enter at least one identifier to update.');
                return;
            }

            updateBtn.disabled = true;
            updateBtn.innerHTML = `<div class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full mr-3"></div> Updating...`;
            resultsTableBody.innerHTML = '<tr><td colspan="4" class="px-10 py-12 text-center text-blue-400 font-bold uppercase tracking-widest text-[10px] animate-pulse">Processing updates...</td></tr>';

            try {
                const response = await fetch('<?= site_url('email/batch_update_process') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        mode: updateMode,
                        identifiers: identifiers,
                        names: newNames,
                        gelar_depans: newGelarDepans,
                        gelar_belakangs: newGelarBelakangs,
                        passwords: newPasswords,
                        niks: newNiks,
                        nips: newNips,
                        tempat_lahirs: newTempatLahirs,
                        tanggal_lahirs: newTanggalLahirs,
                        pendidikans: newPendidikans,
                        jabatans: newJabatans,
                        status_asn: newStatusAsn,
                        pimpinan: newPimpinan,
                        pimpinan_desa: newPimpinanDesa,
                        unit_kerja: newUnitKerja
                    })
                });

                if (!response.ok) {
                    throw new Error('Server responded with an error.');
                }

                const result = await response.json();
                renderResults(result.results);

            } catch (error) {
                console.error('Error during batch update:', error);
                alert('An unexpected error occurred during the update process.');
                resultsTableBody.innerHTML = `<tr><td colspan="4" class="px-10 py-12 text-center text-red-500 font-black uppercase tracking-widest text-[10px]">Error: ${error.message}</td></tr>`;
            } finally {
                updateBtn.disabled = false;
                updateBtn.innerHTML = `<i class="fas fa-sync-alt mr-3 group-hover:rotate-180 transition-transform duration-500"></i> Update Selected Emails`;
            }
        });

        function renderResults(results) {
            resultsTableBody.innerHTML = '';
            if (results.length === 0) {
                resultsTableBody.innerHTML = '<tr><td colspan="4" class="px-10 py-12 text-center text-slate-500 italic font-medium uppercase tracking-widest text-[10px]">No items processed.</td></tr>';
                return;
            }

            results.forEach((res, index) => {
                const statusBadgeClass = res.success ?
                    'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' :
                    'bg-red-500/10 text-red-400 border-red-500/20';
                
                const statusLabel = res.success ? 'Success' : 'Failed';

                const row = `
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="px-10 py-6 whitespace-nowrap text-sm font-bold text-slate-500">${index + 1}</td>
                        <td class="px-10 py-6 whitespace-nowrap text-sm font-black text-slate-200 tracking-tight lowercase">${res.identifier}</td>
                        <td class="px-10 py-6 whitespace-nowrap">
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border shadow-sm ${statusBadgeClass}">${statusLabel}</span>
                        </td>
                        <td class="px-10 py-6 text-sm font-medium text-slate-400 tracking-tight">${res.message || ''}</td>
                    </tr>
                `;
                resultsTableBody.insertAdjacentHTML('beforeend', row);
            });
        }
    });
</script>
<?= $this->endSection(); ?>