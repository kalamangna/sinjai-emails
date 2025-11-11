<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Batch Email Generation</h1>
        <a href="<?= site_url('email') ?>" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Back to Email List
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
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nik_nip_input" class="form-label">Enter one NIK/NIP per line.</label>
                        <textarea class="form-control" id="nik_nip_input" rows="8" placeholder="e.g.&#10;1234567890123456&#10;0987654321098765"></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name_input" class="form-label">Enter one full name per line.</label>
                        <textarea class="form-control" id="name_input" rows="8" placeholder="e.g.&#10;John Doe&#10;Jane Smith"></textarea>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="unit_kerja_input" class="form-label">Unit Kerja</label>
                <select class="form-select" id="unit_kerja_input">
                    <option selected disabled value="">Choose...</option>
                    <?php foreach ($unit_kerja as $unit) : ?>
                        <option value="<?= esc($unit['nama_unit_kerja']); ?>"><?= esc($unit['nama_unit_kerja']); ?></option>
                    <?php endforeach; ?>
                    <option value="Lainnya">Lainnya</option>
                </select>
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
                            <th scope="col">NIK/NIP</th>
                            <th scope="col">Name</th>
                            <th scope="col">Unit Kerja</th>
                            <th scope="col">Generated Email</th>
                            <th scope="col">Password</th>
                            <th scope="col" class="text-center">Availability</th>
                            <th scope="col" class="message-column d-none">Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Results will be populated here by JavaScript -->
                    </tbody>
                </table>
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
<script src="/js/batch.js"></script>
<?= $this->endSection() ?>