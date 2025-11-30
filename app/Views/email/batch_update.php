<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
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
                <div class="mb-4 border-bottom pb-3">
                    <label class="form-label fw-bold">Import Data:</label>
                    <div class="d-flex gap-2">
                        <button type="button" id="import_csv_btn" class="btn btn-outline-success">
                            <i class="fas fa-file-csv me-2"></i>Import from CSV
                        </button>
                        <input type="file" id="csv_file_input" accept=".csv" style="display: none;">
                        <a href="#" id="download_template_btn" class="btn btn-link text-decoration-none">Download Template</a>
                    </div>
                    <small class="text-muted d-block mt-1">
                        Supported columns: identifier, name, nik, nip, gelar_depan, gelar_belakang, password, tempat_lahir, tanggal_lahir, pendidikan, jabatan, sub_unit_kerja, nomor_sk, gaji_nominal, gaji_terbilang, tanggal_kontrak_awal, tanggal_kontrak_akhir, status_asn.
                    </small>
                </div>

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
                                    <label for="status_asn_input" class="form-label">New Status ASN</label>
                                    <select class="form-select" id="status_asn_input">
                                        <option value="">Do not change</option>
                                        <?php foreach ($status_asn_options as $option): ?>
                                            <option value="<?= esc($option['id']) ?>"><?= esc($option['nama_status_asn']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
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
                                    <textarea class="form-control" id="nomor_input" rows="4" placeholder="e.g. 881"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="gaji_nominal_input" class="form-label">New Gaji Nominal</label>
                                    <textarea class="form-control" id="gaji_nominal_input" rows="4" placeholder="e.g. 3203600"></textarea>
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
<?= $this->endSection() ?>

<?= $this->section('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
        const jenisFormasiInput = document.getElementById('jenis_formasi_input'); // Added
        const unitKerjaInput = document.getElementById('unit_kerja_input');
        const subUnitKerjaInput = document.getElementById('sub_unit_kerja_input');
        const updateBtn = document.getElementById('update_btn');
        const resultsTableBody = document.querySelector('#results_table tbody');

        // CSV Import Elements
        const importCsvBtn = document.getElementById('import_csv_btn');
        const csvFileInput = document.getElementById('csv_file_input');
        const downloadTemplateBtn = document.getElementById('download_template_btn');

        importCsvBtn.addEventListener('click', () => csvFileInput.click());

        downloadTemplateBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const headers = [
                'identifier', 'name', 'nik', 'nip', 'gelar_depan', 'gelar_belakang', 
                'password', 'tempat_lahir', 'tanggal_lahir', 'pendidikan', 
                'jabatan', 'sub_unit_kerja', 'nomor_sk', 'gaji_nominal', 
                'gaji_terbilang', 'tanggal_kontrak_awal', 'tanggal_kontrak_akhir'
            ];
            const csvContent = headers.join(',') + '\n';
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'batch_update_template.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        });

        csvFileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (event) => {
                const text = event.target.result;
                const data = parseCSV(text);
                populateFields(data);
                csvFileInput.value = ''; // Reset
            };
            reader.readAsText(file);
        });

        function parseCSV(text) {
            // Normalize line endings
            const normalizedText = text.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
            const lines = normalizedText.split('\n').filter(l => l.trim() !== '');
            if (lines.length < 2) return []; // No data

            // Detect delimiter
            const firstLine = lines[0];
            const commaCount = (firstLine.match(/,/g) || []).length;
            const semicolonCount = (firstLine.match(/;/g) || []).length;
            const delimiter = semicolonCount > commaCount ? ';' : ',';

            const headers = lines[0].split(delimiter).map(h => h.trim().toLowerCase().replace(/^"|"$/g, ''));
            const result = [];

            for (let i = 1; i < lines.length; i++) {
                // Simple CSV split (doesn't handle complex quoted strings with delimiters inside)
                const currentLine = lines[i].split(delimiter); 
                const obj = {};
                
                headers.forEach((header, index) => {
                    let value = currentLine[index] ? currentLine[index].trim().replace(/^"|"$/g, '') : '';
                    obj[header] = value;
                });
                result.push(obj);
            }
            return result;
        }

        function populateFields(data) {
            if (data.length === 0) return;

            const fieldMap = {
                'identifier': identifierInput,
                'email': identifierInput, // Alias
                'old_email': identifierInput, // Alias
                'old_nik': identifierInput, // Alias if in nik mode
                'name': nameInput,
                'nama': nameInput, // Alias
                'nik': nikInput,
                'new_nik': nikInput,
                'nip': nipInput,
                'new_nip': nipInput,
                'gelar_depan': gelarDepanInput,
                'gelar_belakang': gelarBelakangInput,
                'password': passwordInput,
                'tempat_lahir': tempatLahirInput,
                'tanggal_lahir': tanggalLahirInput,
                'pendidikan': pendidikanInput,
                'jabatan': jabatanInput,
                'sub_unit_kerja': subUnitKerjaInput,
                'nomor_sk': nomorInput,
                'nomor': nomorInput,
                'gaji_nominal': gajiNominalInput,
                'gaji_terbilang': gajiTerbilangInput,
                'tanggal_kontrak_awal': tanggalKontrakAwalInput,
                'tanggal_kontrak_akhir': tanggalKontrakAkhirInput
            };

            // Clear existing (or append? Usually import replaces or we can append. Let's replace for simplicity or check if empty)
            // Let's just overwrite for a clean import flow.
            
            // Initialize arrays for each mapped field
            const values = {};
            Object.values(fieldMap).forEach(input => values[input.id] = []);

            data.forEach(row => {
                // Identifier is required for alignment
                // Find the identifier column
                let idVal = row['identifier'] || row['email'] || row['old_email'] || row['old_nik'] || '';
                if (!idVal) return; // Skip rows without identifier

                // Push identifier
                values[identifierInput.id].push(idVal);

                // Push other fields
                Object.keys(row).forEach(header => {
                    if (fieldMap[header] && fieldMap[header] !== identifierInput) {
                        const inputId = fieldMap[header].id;
                        // If we have multiple aliases mapping to same input (e.g. nik and new_nik), 
                        // only one should be present in a row ideally. 
                        // But if present, last one wins in this loop logic or we check.
                        // Since we iterate row keys, let's just use what we find.
                        // Ideally we push to the array corresponding to the row.
                    }
                });
            });

            // Actually, we need to iterate the *targets* and find *source* in row to ensure alignment
            const inputs = [
                { input: nameInput, keys: ['name', 'nama'] },
                { input: nikInput, keys: ['nik', 'new_nik'] },
                { input: nipInput, keys: ['nip', 'new_nip'] },
                { input: gelarDepanInput, keys: ['gelar_depan'] },
                { input: gelarBelakangInput, keys: ['gelar_belakang'] },
                { input: passwordInput, keys: ['password'] },
                { input: tempatLahirInput, keys: ['tempat_lahir'] },
                { input: tanggalLahirInput, keys: ['tanggal_lahir'] },
                { input: pendidikanInput, keys: ['pendidikan'] },
                { input: jabatanInput, keys: ['jabatan'] },
                { input: subUnitKerjaInput, keys: ['sub_unit_kerja'] },
                { input: nomorInput, keys: ['nomor_sk', 'nomor'] },
                { input: gajiNominalInput, keys: ['gaji_nominal'] },
                { input: gajiTerbilangInput, keys: ['gaji_terbilang'] },
                { input: tanggalKontrakAwalInput, keys: ['tanggal_kontrak_awal'] },
                { input: tanggalKontrakAkhirInput, keys: ['tanggal_kontrak_akhir'] }
            ];

            // Reset all inputs
            identifierInput.value = '';
            inputs.forEach(item => item.input.value = '');

            const newIdValues = [];
            const otherValues = {};
            inputs.forEach(item => otherValues[item.input.id] = []);

            data.forEach(row => {
                let idVal = row['identifier'] || row['email'] || row['old_email'] || row['old_nik'] || '';
                if (!idVal) return; // Skip empty identifiers

                // Auto-detect mode if not set by user (or override?)
                // Let's override based on first valid identifier found
                if (newIdValues.length === 0) {
                    if (idVal.includes('@')) {
                        document.getElementById('mode_email').checked = true;
                        document.getElementById('mode_email').dispatchEvent(new Event('change'));
                    } else if (/^\d+$/.test(idVal)) { // Simple NIK check
                        document.getElementById('mode_nik').checked = true;
                        document.getElementById('mode_nik').dispatchEvent(new Event('change'));
                    }
                }

                newIdValues.push(idVal);

                inputs.forEach(item => {
                    let val = '';
                    for (const key of item.keys) {
                        if (row[key] !== undefined) {
                            val = row[key];
                            break;
                        }
                    }
                    otherValues[item.input.id].push(val);
                });
            });

            identifierInput.value = newIdValues.join('\n');
            inputs.forEach(item => {
                item.input.value = otherValues[item.input.id].join('\n');
            });

            alert(`Imported ${newIdValues.length} rows successfully.`);
        }

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
            const rawNomors = nomorInput.value.split('\n');
            const rawGajiNominals = gajiNominalInput.value.split('\n');
            const rawGajiTerbilangs = gajiTerbilangInput.value.split('\n');
            const rawTanggalKontrakAwals = tanggalKontrakAwalInput.value.split('\n');
            const rawTanggalKontrakAkhirs = tanggalKontrakAkhirInput.value.split('\n');
            const rawTempatLahirs = tempatLahirInput.value.split('\n');
            const rawTanggalLahirs = tanggalLahirInput.value.split('\n');
            const rawPendidikans = pendidikanInput.value.split('\n');
            const rawJabatans = jabatanInput.value.split('\n');
            const rawSubUnitKerja = subUnitKerjaInput.value.split('\n');

            const identifiers = [];
            const newNames = [];
            const newGelarDepans = [];
            const newGelarBelakangs = [];
            const newPasswords = [];
            const newNiks = [];
            const newNips = [];
            const newNomors = [];
            const newGajiNominals = [];
            const newGajiTerbilangs = [];
            const newTanggalKontrakAwals = [];
            const newTanggalKontrakAkhirs = [];
            const newTempatLahirs = [];
            const newTanggalLahirs = [];
            const newPendidikans = [];
            const newJabatans = [];
            const newSubUnitKerja = [];

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
                    newNomors.push((rawNomors[i] !== undefined) ? rawNomors[i].trim() : '');
                    newGajiNominals.push((rawGajiNominals[i] !== undefined) ? rawGajiNominals[i].trim() : '');
                    newGajiTerbilangs.push((rawGajiTerbilangs[i] !== undefined) ? rawGajiTerbilangs[i].trim() : '');
                    newTanggalKontrakAwals.push((rawTanggalKontrakAwals[i] !== undefined) ? rawTanggalKontrakAwals[i].trim() : '');
                    newTanggalKontrakAkhirs.push((rawTanggalKontrakAkhirs[i] !== undefined) ? rawTanggalKontrakAkhirs[i].trim() : '');
                    newTempatLahirs.push((rawTempatLahirs[i] !== undefined) ? rawTempatLahirs[i].trim() : '');
                    newTanggalLahirs.push((rawTanggalLahirs[i] !== undefined) ? rawTanggalLahirs[i].trim() : '');
                    newPendidikans.push((rawPendidikans[i] !== undefined) ? rawPendidikans[i].trim() : '');
                    newJabatans.push((rawJabatans[i] !== undefined) ? rawJabatans[i].trim() : '');
                    newSubUnitKerja.push((rawSubUnitKerja[i] !== undefined) ? rawSubUnitKerja[i].trim() : '');
                }
            }

            const newUnitKerja = unitKerjaInput.value;
            const newJenisFormasi = jenisFormasiInput.value; // Added

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
                        jenis_formasi: newJenisFormasi, // Added
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