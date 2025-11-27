<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Batch Email Update</h1>
        <a href="javascript:void(0);" onclick="history.back();" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light py-3">
            <h5 class="card-title mb-0">
                <i class="fas fa-edit me-2 text-primary"></i>
                Update Emails
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

                <div class="row">
                    <!-- Email Table Column -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light fw-bold">
                                <i class="fas fa-user me-2"></i>Email Account & Personal Info
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="name_input" class="form-label">New Names</label>
                                    <textarea class="form-control" id="name_input" rows="4" placeholder="e.g. John Doe
Jane Smith"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="gelar_depan_input" class="form-label">New Gelar Depan</label>
                                        <textarea class="form-control" id="gelar_depan_input" rows="4" placeholder="e.g. Dr.
H."></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="gelar_belakang_input" class="form-label">New Gelar Belakang</label>
                                        <textarea class="form-control" id="gelar_belakang_input" rows="4" placeholder="e.g. S.Kom
M.Si"></textarea>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="password_input" class="form-label">New Passwords</label>
                                    <textarea class="form-control" id="password_input" rows="4" placeholder="Leave blank to keep current password"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nik_input" class="form-label">New NIKs</label>
                                        <textarea class="form-control" id="nik_input" rows="4" placeholder="e.g. 1234567890123456
0987654321098765"></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="nip_input" class="form-label">New NIPs</label>
                                        <textarea class="form-control" id="nip_input" rows="4" placeholder="e.g. 199001012020011001"></textarea>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="tempat_lahir_input" class="form-label">New Tempat Lahir</label>
                                    <textarea class="form-control" id="tempat_lahir_input" rows="4" placeholder="e.g. Sinjai"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="tanggal_lahir_input" class="form-label">New Tanggal Lahir (YYYY-MM-DD)</label>
                                    <textarea class="form-control" id="tanggal_lahir_input" rows="4" placeholder="e.g. 1990-01-01"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="pendidikan_input" class="form-label">New Pendidikan</label>
                                    <textarea class="form-control" id="pendidikan_input" rows="4" placeholder="e.g. S1 Teknik Informatika"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="jabatan_input" class="form-label">New Jabatan</label>
                                    <textarea class="form-control" id="jabatan_input" rows="4" placeholder="e.g. Pranata Komputer Ahli Pertama"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="unit_kerja_input" class="form-label">New Unit Kerja (All)</label>
                                    <select class="form-select" id="unit_kerja_input">
                                        <option selected value="">Do not change</option>
                                        <?php foreach ($unit_kerja as $unit) : ?>
                                            <option value="<?= esc($unit['nama_unit_kerja']); ?>"><?= esc(strtoupper($unit['nama_unit_kerja'])); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="sub_unit_kerja_input" class="form-label">New Sub Unit Kerja</label>
                                    <textarea class="form-control" id="sub_unit_kerja_input" rows="4" placeholder="Enter a new sub unit kerja per line"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PK Table Column -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light fw-bold">
                                <i class="fas fa-file-contract me-2"></i>Perjanjian Kerja (PK) Details
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="nomor_input" class="form-label">New Nomor SK</label>
                                    <textarea class="form-control" id="nomor_input" rows="4" placeholder="e.g. 800.1.2.5/29.881/BKPSDMA"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="gaji_nominal_input" class="form-label">New Gaji Nominal</label>
                                    <textarea class="form-control" id="gaji_nominal_input" rows="4" placeholder="e.g. Rp. 3.203.600"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="gaji_terbilang_input" class="form-label">New Gaji Terbilang</label>
                                    <textarea class="form-control" id="gaji_terbilang_input" rows="4" placeholder="e.g. Tiga Juta Dua Ratus Tiga Ribu Enam Ratus Rupiah"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="tanggal_kontrak_awal_input" class="form-label">New Tanggal Kontrak Awal (YYYY-MM-DD)</label>
                                    <textarea class="form-control" id="tanggal_kontrak_awal_input" rows="4" placeholder="e.g. 2024-01-01"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="tanggal_kontrak_akhir_input" class="form-label">New Tanggal Kontrak Akhir (YYYY-MM-DD)</label>
                                    <textarea class="form-control" id="tanggal_kontrak_akhir_input" rows="4" placeholder="e.g. 2024-12-31"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-end">
                    <button type="submit" id="update_btn" class="btn btn-primary">
                        <i class="fas fa-sync-alt me-2"></i>Update Selected Emails
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
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const identifierInput = document.getElementById('identifier_input');
        const identifierLabel = document.getElementById('identifier_label');
        const nameInput = document.getElementById('name_input');
        const gelarDepanInput = document.getElementById('gelar_depan_input'); // Added
        const gelarBelakangInput = document.getElementById('gelar_belakang_input'); // Added
        const passwordInput = document.getElementById('password_input');
        const nikInput = document.getElementById('nik_input');
        const nipInput = document.getElementById('nip_input');
        const nomorInput = document.getElementById('nomor_input');
        const gajiNominalInput = document.getElementById('gaji_nominal_input');
        const gajiTerbilangInput = document.getElementById('gaji_terbilang_input');
        const tanggalKontrakAwalInput = document.getElementById('tanggal_kontrak_awal_input'); // Added
        const tanggalKontrakAkhirInput = document.getElementById('tanggal_kontrak_akhir_input'); // Added
        const tempatLahirInput = document.getElementById('tempat_lahir_input');
        const tanggalLahirInput = document.getElementById('tanggal_lahir_input');
        const pendidikanInput = document.getElementById('pendidikan_input');
        const jabatanInput = document.getElementById('jabatan_input');
        const unitKerjaInput = document.getElementById('unit_kerja_input');
        const subUnitKerjaInput = document.getElementById('sub_unit_kerja_input');
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

        updateBtn.addEventListener('click', async function (e) {
            e.preventDefault();

            const updateMode = document.querySelector('input[name="update_mode"]:checked').value;
            const identifiers = identifierInput.value.trim().split('\n').filter(id => id.trim() !== '');
            const newNames = nameInput.value.trim().split('\n').filter(name => name.trim() !== '');
            const newGelarDepans = gelarDepanInput.value.trim().split('\n').filter(gd => gd.trim() !== ''); // Added
            const newGelarBelakangs = gelarBelakangInput.value.trim().split('\n').filter(gb => gb.trim() !== ''); // Added
            const newPasswords = passwordInput.value.split('\n').filter(password => password.trim() !== '');
            const newNiks = nikInput.value.trim().split('\n').filter(nik => nik.trim() !== '');
            const newNips = nipInput.value.trim().split('\n').filter(nip => nip.trim() !== '');
            const newNomors = nomorInput.value.trim().split('\n').filter(nomor => nomor.trim() !== '');
            const newGajiNominals = gajiNominalInput.value.trim().split('\n').filter(gn => gn.trim() !== '');
            const newGajiTerbilangs = gajiTerbilangInput.value.trim().split('\n').filter(gt => gt.trim() !== '');
            const newTanggalKontrakAwals = tanggalKontrakAwalInput.value.trim().split('\n').filter(tka => tka.trim() !== ''); // Added
            const newTanggalKontrakAkhirs = tanggalKontrakAkhirInput.value.trim().split('\n').filter(tkk => tkk.trim() !== ''); // Added
            const newTempatLahirs = tempatLahirInput.value.trim().split('\n').filter(tl => tl.trim() !== '');
            const newTanggalLahirs = tanggalLahirInput.value.trim().split('\n').filter(tgl => tgl.trim() !== '');
            const newPendidikans = pendidikanInput.value.trim().split('\n').filter(p => p.trim() !== '');
            const newJabatans = jabatanInput.value.trim().split('\n').filter(j => j.trim() !== '');
            const newUnitKerja = unitKerjaInput.value;
            const newSubUnitKerja = subUnitKerjaInput.value.trim().split('\n').filter(sub => sub.trim() !== '');

            if (identifiers.length === 0) {
                alert('Please enter at least one identifier to update.');
                return;
            }

            // Validate counts if optional fields are provided
            if (newNames.length > 0 && newNames.length !== identifiers.length) {
                alert('The number of new names must match the number of identifiers.');
                return;
            }
            if (newGelarDepans.length > 0 && newGelarDepans.length !== identifiers.length) { // Added
                alert('The number of new Gelar Depan entries must match the number of identifiers.');
                return;
            }
            if (newGelarBelakangs.length > 0 && newGelarBelakangs.length !== identifiers.length) { // Added
                alert('The number of new Gelar Belakang entries must match the number of identifiers.');
                return;
            }
            if (newPasswords.length > 0 && newPasswords.length !== identifiers.length) {
                alert('The number of new passwords must match the number of identifiers.');
                return;
            }
            if (newNiks.length > 0 && newNiks.length !== identifiers.length) {
                alert('The number of new NIKs must match the number of identifiers.');
                return;
            }
            if (newNips.length > 0 && newNips.length !== identifiers.length) {
                alert('The number of new NIPs must match the number of identifiers.');
                return;
            }
            if (newNomors.length > 0 && newNomors.length !== identifiers.length) {
                alert('The number of new Nomor SK entries must match the number of identifiers.');
                return;
            }
            if (newGajiNominals.length > 0 && newGajiNominals.length !== identifiers.length) {
                alert('The number of new Gaji Nominal entries must match the number of identifiers.');
                return;
            }
            if (newGajiTerbilangs.length > 0 && newGajiTerbilangs.length !== identifiers.length) {
                alert('The number of new Gaji Terbilang entries must match the number of identifiers.');
                return;
            }
            if (newTanggalKontrakAwals.length > 0 && newTanggalKontrakAwals.length !== identifiers.length) { // Added
                alert('The number of new Tanggal Kontrak Awal entries must match the number of identifiers.');
                return;
            }
            if (newTanggalKontrakAkhirs.length > 0 && newTanggalKontrakAkhirs.length !== identifiers.length) { // Added
                alert('The number of new Tanggal Kontrak Akhir entries must match the number of identifiers.');
                return;
            }
            if (newTempatLahirs.length > 0 && newTempatLahirs.length !== identifiers.length) {
                alert('The number of new Tempat Lahir entries must match the number of identifiers.');
                return;
            }
            if (newTanggalLahirs.length > 0 && newTanggalLahirs.length !== identifiers.length) {
                alert('The number of new Tanggal Lahir entries must match the number of identifiers.');
                return;
            }
            if (newPendidikans.length > 0 && newPendidikans.length !== identifiers.length) {
                alert('The number of new Pendidikan entries must match the number of identifiers.');
                return;
            }
            if (newJabatans.length > 0 && newJabatans.length !== identifiers.length) {
                alert('The number of new Jabatan entries must match the number of identifiers.');
                return;
            }
            if (newSubUnitKerja.length > 0 && newSubUnitKerja.length !== identifiers.length) {
                alert('The number of new Sub Unit Kerja entries must match the number of identifiers.');
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
                        names: newNames,
                        gelar_depans: newGelarDepans, // Added
                        gelar_belakangs: newGelarBelakangs, // Added
                        passwords: newPasswords,
                        niks: newNiks,
                        nips: newNips,
                        nomors: newNomors,
                        gaji_nominals: newGajiNominals,
                        gaji_terbilangs: newGajiTerbilangs,
                        tanggal_kontrak_awals: newTanggalKontrakAwals, // Added
                        tanggal_kontrak_akhirs: newTanggalKontrakAkhirs, // Added
                        tempat_lahirs: newTempatLahirs,
                        tanggal_lahirs: newTanggalLahirs,
                        pendidikans: newPendidikans,
                        jabatans: newJabatans,
                        unit_kerja: newUnitKerja,
                        sub_unit_kerja: newSubUnitKerja
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
                updateBtn.innerHTML = `<i class="fas fa-sync-alt me-2"></i>Update Selected Emails`;
            }
        });

        function renderResults(results) {
            resultsTableBody.innerHTML = '';
            if (results.length === 0) {
                resultsTableBody.innerHTML = '<tr><td colspan="4" class="text-center">No items processed.</td></tr>';
                return;
            }

            results.forEach((res, index) => {
                const statusBadge = res.success
                    ? '<span class="badge bg-success">Success</span>'
                    : `<span class="badge bg-danger">Failed</span>`;
                
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