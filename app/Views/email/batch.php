<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Batch Email Generation</h1>
        <a href="javascript:void(0);" onclick="history.back();" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light py-3">
            <h5 class="card-title mb-0">
                <i class="fas fa-users me-2 text-primary"></i>
                Input Names
            </h5>
        </div>
        <div class="card-body">
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
                    Supported columns: name, nik, nip, jabatan, unit_kerja.
                </small>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nik_input" class="form-label">Enter one NIK per line.</label>
                        <textarea class="form-control" id="nik_input" rows="8" placeholder="e.g.&#10;1234567890123456&#10;0987654321098765"></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name_input" class="form-label">Enter one full name per line.</label>
                        <textarea class="form-control" id="name_input" rows="8" placeholder="e.g.&#10;John Doe&#10;Jane Smith"></textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nip_input" class="form-label">Enter one NIP per line.</label>
                        <textarea class="form-control" id="nip_input" rows="8" placeholder="e.g.&#10;199001012020011001"></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="jabatan_input" class="form-label">Enter one Jabatan per line.</label>
                        <textarea class="form-control" id="jabatan_input" rows="8" placeholder="e.g.&#10;Pranata Komputer Ahli Pertama"></textarea>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="status_asn_input" class="form-label">Status ASN (for all)</label>
                <select class="form-select" id="status_asn_input">
                    <option selected disabled value="">Choose...</option>
                    <?php foreach ($status_asn_options as $option): ?>
                        <option value="<?= esc($option['id']) ?>"><?= esc($option['nama_status_asn']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Unit Kerja Mode:</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="unitKerjaMode" id="mode_single" value="single" checked>
                    <label class="form-check-label" for="mode_single">Single Unit Kerja (for all)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="unitKerjaMode" id="mode_multiple" value="multiple">
                    <label class="form-check-label" for="mode_multiple">Multiple Unit Kerja (one per line)</label>
                </div>
            </div>

            <div id="single_unit_kerja_wrapper" class="mb-3">
                <label for="unit_kerja_input_single" class="form-label">Unit Kerja</label>
                <select class="form-select" id="unit_kerja_input_single">
                    <option selected disabled value="">Choose...</option>
                    <?php foreach ($unit_kerja as $unit) : ?>
                        <option value="<?= esc($unit['nama_unit_kerja']); ?>"><?= esc(strtoupper($unit['nama_unit_kerja'])); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="multiple_unit_kerja_wrapper" class="mb-3" style="display: none;">
                <label for="unit_kerja_input_multiple" class="form-label">Enter one Unit Kerja per line.</label>
                <textarea class="form-control" id="unit_kerja_input_multiple" rows="8" placeholder="e.g.&#10;Dinas Komunikasi dan Informatika&#10;Badan Perencanaan Pembangunan"></textarea>
            </div>

            <button id="generate_btn" class="btn btn-primary">
                <i class="fas fa-cogs me-2"></i>Generate
            </button>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light py-3">
            <h5 class="card-title mb-0">
                <i class="fas fa-list-alt me-2 text-primary"></i>
                Generated Emails
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="results_table" class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">NIK</th>
                            <th scope="col">NIP</th>
                            <th scope="col">Name</th>
                            <th scope="col">Jabatan</th>
                            <th scope="col">Unit Kerja</th>
                            <th scope="col">Generated Email</th>
                            <th scope="col">Password</th>
                            <th scope="col" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Results will be populated here by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Progress Bar and Results Log -->
    <div id="progress_section" class="card shadow-sm my-4" style="display: none;">
        <div class="card-header bg-light py-3">
            <h5 class="card-title mb-0">
                <i class="fas fa-tasks me-2 text-primary"></i>
                Batch Submission Progress
            </h5>
        </div>
        <div class="card-body">
            <div class="progress mb-3" style="height: 25px;">
                <div id="progress_bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
            <p id="progress_text" class="text-center mb-2">Processing 0 / 0</p>
            <div id="results_log" class="p-3 bg-light rounded" style="max-height: 300px; overflow-y: auto; font-family: monospace;">
                <!-- Log messages will be appended here -->
            </div>
        </div>
    </div>

    <div class="mt-4 text-end">
        <button id="submit_btn" class="btn btn-success btn-lg" disabled>
            <i class="fas fa-check-circle me-2"></i>Submit Batch
        </button>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const unitKerjaOptions = <?= json_encode(array_map(function($unit) { return ['id' => $unit['id'], 'nama_unit_kerja' => $unit['nama_unit_kerja']]; }, $unit_kerja)) ?>;
</script>
<script src="/js/batch.js"></script>
<?= $this->endSection() ?>