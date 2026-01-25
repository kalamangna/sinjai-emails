<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Batch Perjanjian Kerja Update</h1>
    <a href="javascript:void(0);" onclick="history.back();" class="btn btn-outline-primary">
        <i class="fas fa-arrow-left me-2"></i>Back
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light py-3">
        <h5 class="card-title mb-0">
            <i class="fas fa-file-contract me-2 text-primary"></i>
            Update PK Details
        </h5>
    </div>
    <div class="card-body">
        <form id="batch_update_form">
            <div class="mb-3">
                <label class="form-label">Update By:</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="update_mode" id="mode_email" value="email" checked>
                    <label class="form-check-label" for="mode_email">Email Address</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="update_mode" id="mode_nik" value="nik">
                    <label class="form-check-label" for="mode_nik">NIK</label>
                </div>
            </div>

            <div class="mb-3">
                <label for="identifier_input" id="identifier_label" class="form-label">Enter one email address per line to update.</label>
                <textarea class="form-control" id="identifier_input" rows="8" placeholder="e.g.
john.doe@example.com
jane.smith@example.com"></textarea>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light fw-bold">
                    <i class="fas fa-file-contract me-2"></i>Perjanjian Kerja (PK) Details
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nomor_input" class="form-label">New Nomor PK</label>
                            <textarea class="form-control" id="nomor_input" rows="4" placeholder="e.g. 881"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gaji_nominal_input" class="form-label">New Gaji Nominal</label>
                            <textarea class="form-control" id="gaji_nominal_input" rows="4" placeholder="e.g. 3203600"></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="gaji_terbilang_input" class="form-label">New Gaji Terbilang</label>
                        <textarea class="form-control" id="gaji_terbilang_input" rows="4" placeholder="e.g. Tiga Juta Dua Ratus Tiga Ribu Enam Ratus"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_kontrak_awal_input" class="form-label">New Tanggal Kontrak Awal (YYYY-MM-DD)</label>
                            <textarea class="form-control" id="tanggal_kontrak_awal_input" rows="4" placeholder="e.g. 2024-01-01"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_kontrak_akhir_input" class="form-label">New Tanggal Kontrak Akhir (YYYY-MM-DD)</label>
                            <textarea class="form-control" id="tanggal_kontrak_akhir_input" rows="4" placeholder="e.g. 2024-12-31"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" id="update_btn" class="btn btn-primary">
                    <i class="fas fa-sync-alt me-2"></i>Update Perjanjian Kerja
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-light py-3">
        <h5 class="card-title mb-0">
            <i class="fas fa-list-alt me-2 text-primary"></i>
            Update Results
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="results_table" class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Identifier</th>
                        <th scope="col">Status</th>
                        <th scope="col">Message</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Results will be populated here by JavaScript -->
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
        const nomorInput = document.getElementById('nomor_input');
        const gajiNominalInput = document.getElementById('gaji_nominal_input');
        const gajiTerbilangInput = document.getElementById('gaji_terbilang_input');
        const tanggalKontrakAwalInput = document.getElementById('tanggal_kontrak_awal_input');
        const tanggalKontrakAkhirInput = document.getElementById('tanggal_kontrak_akhir_input');
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
            const rawNomors = nomorInput.value.split('\n');
            const rawGajiNominals = gajiNominalInput.value.split('\n');
            const rawGajiTerbilangs = gajiTerbilangInput.value.split('\n');
            const rawTanggalKontrakAwals = tanggalKontrakAwalInput.value.split('\n');
            const rawTanggalKontrakAkhirs = tanggalKontrakAkhirInput.value.split('\n');

            const identifiers = [];
            const newNomors = [];
            const newGajiNominals = [];
            const newGajiTerbilangs = [];
            const newTanggalKontrakAwals = [];
            const newTanggalKontrakAkhirs = [];

            for (let i = 0; i < rawIdentifiers.length; i++) {
                const id = rawIdentifiers[i].trim();
                if (id) {
                    identifiers.push(id);
                    newNomors.push((rawNomors[i] !== undefined) ? rawNomors[i].trim() : '');
                    newGajiNominals.push((rawGajiNominals[i] !== undefined) ? rawGajiNominals[i].trim() : '');
                    newGajiTerbilangs.push((rawGajiTerbilangs[i] !== undefined) ? rawGajiTerbilangs[i].trim() : '');
                    newTanggalKontrakAwals.push((rawTanggalKontrakAwals[i] !== undefined) ? rawTanggalKontrakAwals[i].trim() : '');
                    newTanggalKontrakAkhirs.push((rawTanggalKontrakAkhirs[i] !== undefined) ? rawTanggalKontrakAkhirs[i].trim() : '');
                }
            }

            if (identifiers.length === 0) {
                alert('Please enter at least one identifier to update.');
                return;
            }

            updateBtn.disabled = true;
            updateBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...`;
            resultsTableBody.innerHTML = '<tr><td colspan="4" class="text-center">Processing updates...</td></tr>';

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
                        nomors: newNomors,
                        gaji_nominals: newGajiNominals,
                        gaji_terbilangs: newGajiTerbilangs,
                        tanggal_kontrak_awals: newTanggalKontrakAwals,
                        tanggal_kontrak_akhirs: newTanggalKontrakAkhirs,
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
                resultsTableBody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">Error: ${error.message}</td></tr>`;
            } finally {
                updateBtn.disabled = false;
                updateBtn.innerHTML = `<i class="fas fa-sync-alt me-2"></i>Update Perjanjian Kerja`;
            }
        });

        function renderResults(results) {
            resultsTableBody.innerHTML = '';
            if (results.length === 0) {
                resultsTableBody.innerHTML = '<tr><td colspan="4" class="text-center">No items processed.</td></tr>';
                return;
            }

            results.forEach((res, index) => {
                const statusBadge = res.success ?
                    '<span class="badge bg-success">Success</span>' :
                    `<span class="badge bg-danger">Failed</span>`;

                const row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${res.identifier}</td>
                        <td>${statusBadge}</td>
                        <td>${res.message || ''}</td>
                    </tr>
                `;
                resultsTableBody.insertAdjacentHTML('beforeend', row);
            });
        }
    });
</script>
<?= $this->endSection(); ?>