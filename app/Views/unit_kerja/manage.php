<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light py-3">
                <h5 class="card-title mb-0">
                    <i class="fas fa-building me-2"></i>Manage Unit Kerja
                </h5>
            </div>
            <div class="card-body">
                <!-- Search Form -->
                <form method="GET" action="" class="mb-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search by name..." value="<?= isset($search) ? esc($search) : '' ?>">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                        <a href="<?= site_url('unit_kerja/manage') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-sync-alt me-2"></i>Reset
                        </a>
                    </div>
                </form>

                <!-- Add Unit Kerja Form -->
                <form action="<?= site_url('unit_kerja/add') ?>" method="post" class="mb-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="nama_unit_kerja" placeholder="Enter new Unit Kerja name" required>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add Unit Kerja
                        </button>
                    </div>
                </form>

                <!-- Unit Kerja List -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($unit_kerja_list as $unit): ?>
                                <tr>
                                    <td><?= $unit['id'] ?></td>
                                    <td><?= strtoupper(esc($unit['nama_unit_kerja'])) ?></td>
                                    <td>
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
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>