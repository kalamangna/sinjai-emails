<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <!-- Dashboard Cards -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-primary border-4">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small fw-bold">Total Websites</h6>
                <h2 class="mb-0"><?= $stats['total'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-success border-4">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small fw-bold">Status AKTIF</h6>
                <h2 class="mb-0 text-success"><?= $stats['aktif'] ?> <small class="text-muted">(<?= $stats['aktif_percentage'] ?>%)</small></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-danger border-4">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small fw-bold">Status NONAKTIF</h6>
                <h2 class="mb-0 text-danger"><?= $stats['nonaktif'] ?> <small class="text-muted">(<?= $stats['nonaktif_percentage'] ?>%)</small></h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php if (session()->getFlashdata('message')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('message') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-globe me-2"></i>Website OPD
                    <span class="badge bg-secondary ms-2 small" style="font-size: 0.7em;">Found: <?= $total_filtered ?></span>
                </h5>
                <div>
                    <a href="<?= site_url('web_opd/export_pdf') . '?' . $_SERVER['QUERY_STRING'] ?>" class="btn btn-danger btn-sm me-2" target="_blank">
                        <i class="fas fa-file-pdf me-2"></i>Export PDF
                    </a>
                    <a href="<?= site_url('web_opd/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-2"></i>Add Website
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Search and Filter Form -->
                <form method="GET" action="<?= site_url('web_opd') ?>" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="search" placeholder="Search by OPD or Domain..." value="<?= esc($search) ?>">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">All Status</option>
                                <option value="AKTIF" <?= ($filterStatus === 'AKTIF') ? 'selected' : '' ?>>AKTIF</option>
                                <option value="NONAKTIF" <?= ($filterStatus === 'NONAKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                            </select>
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="submit" class="btn btn-info w-100">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Website List -->
                <?php if (!empty($websites)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>OPD</th>
                                    <th>Domain</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                    <th class="text-center" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($websites as $web): ?>
                                    <tr>
                                        <td class="fw-bold"><?= esc(strtoupper($web['nama_unit_kerja'] ?? 'N/A')) ?></td>
                                        <td>
                                            <?php if (!empty($web['domain'])): ?>
                                                <a href="http://<?= esc($web['domain']) ?>" target="_blank" class="text-decoration-none">
                                                    <?= esc($web['domain']) ?> <i class="fas fa-external-link-alt small ms-1"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status = strtoupper($web['status'] ?? 'NONAKTIF');
                                            $statusClass = ($status === 'AKTIF') ? 'bg-success' : 'bg-danger';
                                            ?>
                                            <span class="badge <?= $statusClass ?>"><?= $status ?></span>
                                        </td>
                                        <td><?= esc($web['keterangan'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <a href="<?= site_url('web_opd/edit/' . $web['id']) ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-globe fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No websites found.</h5>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>