<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light py-3">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-csv me-2"></i>Batch Create Unit Kerja
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= site_url('unit_kerja/batch_store') ?>" method="post">
                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Parent Unit Kerja (Optional)</label>
                        <select name="parent_id" id="parent_id" class="form-select">
                            <option value="">-- No Parent --</option>
                            <?php foreach ($parent_options as $parent): ?>
                                <option value="<?= $parent['id'] ?>"><?= esc(strtoupper($parent['nama_unit_kerja'])) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Select a parent unit if these new units are sub-units. Leave blank if they are top-level units.</div>
                    </div>

                    <div class="mb-3">
                        <label for="unit_kerja_names" class="form-label">Unit Kerja Names</label>
                        <textarea class="form-control" id="unit_kerja_names" name="unit_kerja_names" rows="10" placeholder="Enter unit kerja names, one per line." required></textarea>
                        <div class="form-text">Enter one unit kerja name per line.</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= site_url('unit_kerja/manage') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Create Units
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>