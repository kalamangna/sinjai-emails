<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Batch Email Update</h1>
        <a href="<?= site_url('email') ?>" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Back to Email List
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
                        <input class="form-check-input" type="radio" name="update_mode" id="mode_nik_nip" value="nik_nip">
                        <label class="form-check-label" for="mode_nik_nip">NIK/NIP</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="identifier_input" id="identifier_label" class="form-label">Enter one email address per line to update.</label>
                    <textarea class="form-control" id="identifier_input" rows="8" placeholder="e.g.
john.doe@example.com
jane.smith@example.com"></textarea>
                </div>
                <div class="mb-3">
                    <label for="name_input" class="form-label">New Names (optional - one per line, must match identifier count)</label>
                    <textarea class="form-control" id="name_input" rows="4" placeholder="e.g. John Doe
Jane Smith"></textarea>
                </div>
                <div class="mb-3">
                    <label for="password_input" class="form-label">New Passwords (optional - one per line, must match identifier count)</label>
                    <textarea class="form-control" id="password_input" rows="4" placeholder="Leave blank to keep current password"></textarea>
                </div>
                <div class="mb-3">
                    <label for="nik_nip_input" class="form-label">New NIK/NIPs (optional - one per line, must match identifier count)</label>
                    <textarea class="form-control" id="nik_nip_input" rows="4" placeholder="e.g. 1234567890123456
0987654321098765"></textarea>
                </div>
                <div class="mb-3">
                    <label for="unit_kerja_input" class="form-label">New Unit Kerja (optional - applies to all)</label>
                    <select class="form-select" id="unit_kerja_input">
                        <option selected value="">Do not change</option>
                        <?php foreach ($unit_kerja as $unit) : ?>
                            <option value="<?= esc($unit['nama_unit_kerja']); ?>"><?= esc(strtoupper($unit['nama_unit_kerja'])); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="sub_unit_kerja_input" class="form-label">New Sub Unit Kerja (optional - one per line, must match identifier count)</label>
                    <textarea class="form-control" id="sub_unit_kerja_input" rows="4" placeholder="Enter a new sub unit kerja per line"></textarea>
                </div>
                <button type="submit" id="update_btn" class="btn btn-primary">
                    <i class="fas fa-sync-alt me-2"></i>Update Selected Emails
                </button>
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
        const passwordInput = document.getElementById('password_input');
        const nikNipInput = document.getElementById('nik_nip_input');
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
                    identifierLabel.textContent = 'Enter one NIK/NIP per line to update.';
                    identifierInput.placeholder = 'e.g.\n1234567890123456\n0987654321098765';
                }
            });
        });

        updateBtn.addEventListener('click', async function (e) {
            e.preventDefault();

            const updateMode = document.querySelector('input[name="update_mode"]:checked').value;
            const identifiers = identifierInput.value.trim().split('\n').filter(id => id.trim() !== '');
            const newNames = nameInput.value.trim().split('\n').filter(name => name.trim() !== '');
            const newPasswords = passwordInput.value.split('\n').filter(password => password.trim() !== '');
            const newNikNips = nikNipInput.value.trim().split('\n').filter(nikNip => nikNip.trim() !== '');
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
            if (newPasswords.length > 0 && newPasswords.length !== identifiers.length) {
                alert('The number of new passwords must match the number of identifiers.');
                return;
            }
            if (newNikNips.length > 0 && newNikNips.length !== identifiers.length) {
                alert('The number of new NIK/NIPs must match the number of identifiers.');
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
                        passwords: newPasswords,
                        nik_nips: newNikNips,
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