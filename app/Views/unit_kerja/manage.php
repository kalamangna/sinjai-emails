<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-building me-2"></i>Manage Unit Kerja
                </h5>
                <a href="<?= site_url('unit_kerja/add') ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Unit Kerja
                </a>
            </div>
            <div class="card-body">
                <!-- Search Form -->
                <form method="GET" action="" class="mb-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search by name or parent..." value="<?= isset($search) ? esc($search) : '' ?>">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                        <a href="<?= site_url('unit_kerja/manage') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-sync-alt me-2"></i>Reset
                        </a>
                    </div>
                </form>

                <!-- Unit Kerja List -->
                <?php if (!empty($unit_kerja_list)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Parent</th>
                                    <th class="text-center" style="width: 120px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($unit_kerja_list as $unit): ?>
                                    <tr>
                                        <td><?= $unit['id'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-building text-primary me-3"></i>
                                                <div>
                                                    <div class="fw-bold"><?= strtoupper(esc($unit['nama_unit_kerja'])) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?= !empty($unit['parent_name']) ? strtoupper(esc($unit['parent_name'])) : '<span class="text-muted">-</span>' ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= site_url('unit_kerja/edit/' . $unit['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= site_url('unit_kerja/delete/' . $unit['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this item?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-building fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No unit kerjas found.</h5>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>