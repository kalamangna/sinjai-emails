<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12 mb-4">
            <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Input NIKs</h6>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('email/check_nik') ?>" method="post">
                        <div class="mb-3">
                            <label for="nik_list" class="form-label">Enter NIKs (one per line)</label>
                            <textarea class="form-control" id="nik_list" name="nik_list" rows="15" placeholder="Enter NIK list here..."><?= esc($input_niks) ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Check NIKs
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Results</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($results) && empty($input_niks)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-search fa-3x mb-3"></i>
                            <p>Enter NIKs in the form to see results.</p>
                        </div>
                    <?php elseif (empty($results) && !empty($input_niks)): ?>
                        <div class="alert alert-warning">
                            No valid NIKs found in input.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Searched NIK</th>
                                        <th>Status</th>
                                        <th>Found Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $row): ?>
                                        <tr>
                                            <td class="align-middle font-monospace"><?= esc($row['searched_nik']) ?></td>
                                            <td class="align-middle text-center">
                                                <?php if ($row['found']): ?>
                                                    <span class="badge bg-success">Found (<?= count($row['emails']) ?>)</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Not Found</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($row['found']): ?>
                                                    <ul class="list-group list-group-flush mb-0">
                                                        <?php foreach ($row['emails'] as $email): ?>
                                                            <li class="list-group-item bg-transparent p-2">
                                                                <div class="fw-bold"><?= esc($email['email']) ?></div>
                                                                <div class="small text-muted">
                                                                    <i class="fas fa-user me-1"></i><?= esc($email['name']) ?>
                                                                </div>
                                                                <?php if (!empty($email['unit_kerja_name'])): ?>
                                                                    <div class="small text-muted">
                                                                        <i class="fas fa-building me-1"></i><?= esc($email['unit_kerja_name']) ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <?php if (!empty($email['nik']) && $email['nik'] !== $row['searched_nik']): ?>
                                                                    <div class="small text-danger">
                                                                        <i class="fas fa-exclamation-circle me-1"></i>Matched via LIKE: <?= esc($email['nik']) ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
