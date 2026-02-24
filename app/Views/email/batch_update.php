<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <!-- Header & Back -->
    <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-4 bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div class="space-y-1">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Update Massal</h1>
            <div class="flex items-center text-[11px] text-slate-500 font-medium uppercase tracking-wider">
                <i class="fas fa-edit mr-2 text-blue-500 opacity-50"></i>
                Batch Update
            </div>
        </div>
        <a href="<?= site_url('email/batch_hub') ?>" class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all shadow-sm no-underline group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> Kembali
        </a>
    </div>

    <!-- Main Form -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
            <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center">
                <i class="fas fa-edit mr-2 text-blue-500 opacity-50"></i>Update Emails
            </h5>
        </div>
        <div class="p-6">
            <form id="batch_update_form" class="space-y-8">
                <div class="bg-slate-50 p-6 rounded-xl border border-slate-100 space-y-6">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1">Update By:</label>
                        <div class="flex gap-6">
                            <label class="flex items-center cursor-pointer group">
                                <input type="radio" name="update_mode" id="mode_email" value="email" checked class="w-4 h-4 text-blue-600 bg-white border-slate-300 focus:ring-blue-500 focus:ring-offset-white">
                                <span class="ml-2.5 text-xs font-bold text-slate-600 group-hover:text-slate-900 transition-colors uppercase tracking-tight">Email Address</span>
                            </label>
                            <label class="flex items-center cursor-pointer group">
                                <input type="radio" name="update_mode" id="mode_nik" value="nik" class="w-4 h-4 text-blue-600 bg-white border-slate-300 focus:ring-blue-500 focus:ring-offset-white">
                                <span class="ml-2.5 text-xs font-bold text-slate-600 group-hover:text-slate-900 transition-colors uppercase tracking-tight">NIK</span>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="identifier_input" id="identifier_label" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Daftar Pengenal (Satu per baris)</label>
                        <textarea id="identifier_input" rows="4" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all placeholder-slate-300" placeholder="Masukkan identifier..."></textarea>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden space-y-6">
                    <div class="flex items-center gap-2 px-6 pt-6">
                        <i class="fas fa-info-circle text-blue-500 text-sm"></i>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Detail Data Baru</span>
                    </div>

                    <div class="px-6 pb-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="name_input" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">New Names</label>
                                <textarea id="name_input" rows="3" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 text-sm font-medium" placeholder="Satu nama per baris..."></textarea>
                            </div>
                            <div class="space-y-2">
                                <label for="password_input" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">New Passwords</label>
                                <textarea id="password_input" rows="3" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 text-sm font-medium" placeholder="Kosongkan jika tidak diubah..."></textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="space-y-2">
                                <label for="status_asn_input" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Status ASN</label>
                                <select id="status_asn_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 text-sm font-medium uppercase transition-all">
                                    <option value="">No change</option>
                                    <?php foreach ($status_asn_options as $option): ?>
                                        <option value="<?= esc($option['id']) ?>"><?= esc($option['nama_status_asn']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="unit_kerja_input" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Unit Kerja</label>
                                <select id="unit_kerja_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 text-sm font-medium transition-all uppercase">
                                    <option selected value="">No change</option>
                                    <?php foreach ($unit_kerja as $unit) : ?>
                                        <option value="<?= esc($unit['nama_unit_kerja']); ?>"><?= esc($unit['nama_unit_kerja']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="pimpinan_input" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Pimpinan OPD</label>
                                <select id="pimpinan_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 text-sm font-medium transition-all">
                                    <option value="">No change</option>
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="pimpinan_desa_input" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Pimpinan Desa</label>
                                <select id="pimpinan_desa_input" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 text-sm font-medium transition-all">
                                    <option value="">No change</option>
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" id="update_btn" class="inline-flex items-center px-8 py-3 bg-blue-600 text-white rounded-lg font-bold text-xs uppercase tracking-wider hover:bg-blue-700 shadow-md transition-all group">
                        <i class="fas fa-sync-alt mr-2 group-hover:rotate-180 transition-transform duration-500"></i>
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Section -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
            <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center">
                <i class="fas fa-list-alt mr-2 text-blue-500 opacity-50"></i>Update Results
            </h5>
        </div>
        <div class="overflow-x-auto">
            <table id="results_table" class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">#</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Identifier</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Message</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-slate-400 text-xs font-medium italic">No data processed yet.</td>
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
            updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Updating...';
            resultsTableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-10 text-center italic text-slate-400">Processing...</td></tr>';

            try {
                const response = await fetch('<?= site_url('email/batch_update_process') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({
                        mode: mode,
                        identifiers: identifiers,
                        names: mapInput('name_input'),
                        passwords: mapInput('password_input'),
                        status_asn: document.getElementById('status_asn_input').value,
                        unit_kerja: document.getElementById('unit_kerja_input').value,
                        pimpinan: document.getElementById('pimpinan_input').value,
                        pimpinan_desa: document.getElementById('pimpinan_desa_input').value,
                    })
                });
                const result = await response.json();
                renderResults(result.results);
            } catch (error) {
                alert('Update failed: ' + error.message);
            } finally {
                updateBtn.disabled = false;
                updateBtn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> Update Emails';
            }
        });

        function renderResults(results) {
            resultsTableBody.innerHTML = results.length ? '' : '<tr><td colspan="4" class="px-6 py-10 text-center italic text-slate-400">No data processed.</td></tr>';
            results.forEach((res, i) => {
                const cls = res.success ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100';
                resultsTableBody.insertAdjacentHTML('beforeend', `
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 text-xs font-bold text-slate-400 font-mono">${i + 1}</td>
                        <td class="px-6 py-4 text-[13px] font-bold text-slate-700 lowercase">${res.identifier}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold border ${cls}">${res.success ? 'SUCCESS' : 'FAILED'}</span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 font-medium">${res.message || '-'}</td>
                    </tr>
                `);
            });
        }
    });
</script>
<?= $this->endSection(); ?>