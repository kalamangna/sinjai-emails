<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light py-3">
                <h5 class="card-title mb-0">
                    <i class="fas fa-plus me-2"></i>Add Unit Kerja
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= site_url('unit_kerja/store') ?>" method="post">
                    <div class="mb-3">
                        <label for="nama_unit_kerja" class="form-label">Name</label>
                        <input type="text" class="form-control" id="nama_unit_kerja" name="nama_unit_kerja" value="<?= old('nama_unit_kerja') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Parent Unit Kerja</label>
                        <select class="form-select" id="parent_id" name="parent_id">
                            <option value="">Select Parent Unit Kerja (Optional)</option>
                            <?php foreach ($parent_options as $option): ?>
                                <option value="<?= $option['id'] ?>"><?= esc($option['nama_unit_kerja']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save
                    </button>
                    <a href="javascript:void(0);" onclick="history.back();" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>