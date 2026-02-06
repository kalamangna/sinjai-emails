<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="text-uppercase">Assistance Activities</h4>
            <div>
                <a href="<?= site_url('assistance/export_pdf') . '?' . $_SERVER['QUERY_STRING'] ?>" class="btn btn-danger me-2" target="_blank">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
                <a href="<?= site_url('assistance/create') ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New
                </a>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="<?= site_url('assistance') ?>" method="get" class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label for="category" class="col-form-label fw-bold">Filter Category:</label>
                    </div>
                    <div class="col-auto">
                        <select name="category" id="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categoryMap as $id => $label): ?>
                                <option value="<?= $id ?>" <?= ($filterCategory == $id) ? 'selected' : '' ?>>
                                    <?= esc($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-secondary">Filter</button>
                        <?php if ($filterCategory): ?>
                            <a href="<?= site_url('assistance') ?>" class="btn btn-outline-secondary">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <?php if (session()->getFlashdata('message')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('message') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">No</th>
                                <th>Tanggal</th>
                                <th>Unit Kerja</th>
                                <th>Kategori</th>
                                <th>Metode</th>
                                <th>Layanan</th>
                                <th>Keterangan</th>
                                <th style="width: 10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($activities)): ?>
                                <?php foreach ($activities as $key => $item): ?>
                                    <tr>
                                        <td><?= $key + 1 ?></td>
                                        <td><?= date('d M Y', strtotime($item['tanggal_kegiatan'])) ?></td>
                                        <td>
                                            <strong><?= esc($item['agency_name']) ?></strong><br>
                                            <small class="text-muted"><?= esc($item['agency_type']) ?></small>
                                        </td>
                                        <td><?= esc($categoryMap[$item['category']] ?? 'Unknown') ?></td>
                                        <td>
                                            <span class="badge <?= $item['method'] == 'Online' ? 'bg-info' : 'bg-warning' ?>">
                                                <?= esc($item['method']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $services = json_decode($item['services'], true);
                                            if (!empty($services)) {
                                                echo '<ul class="mb-0 ps-3">';
                                                foreach ($services as $svc) {
                                                    echo '<li>' . esc($svc) . '</li>';
                                                }
                                                echo '</ul>';
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td><?= esc($item['keterangan']) ?></td>
                                        <td>
                                            <a href="<?= site_url('assistance/edit/' . $item['id']) ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= site_url('assistance/delete/' . $item['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No activities found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>