<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>Edit Unit Kerja
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('unit_kerja/update/' . $unit_kerja['id']) ?>" method="post">
                        <div class="mb-3">
                            <label for="nama_unit_kerja" class="form-label">Name</label>
                            <input type="text" class="form-control" id="nama_unit_kerja" name="nama_unit_kerja" value="<?= esc($unit_kerja['nama_unit_kerja']) ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update
                        </button>
                        <a href="<?= site_url('unit_kerja/manage') ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>