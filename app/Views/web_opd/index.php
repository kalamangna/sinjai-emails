<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <!-- Dashboard Charts -->
        <div class="row mb-4 justify-content-center">
            <!-- Status Chart -->
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light py-3">
                        <h6 class="card-title mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Status Website</h6>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-sm-5">
                                <div style="height: 180px; position: relative;">
                                    <canvas id="statusChart"></canvas>
                                </div>
                            </div>
                            <div class="col-sm-7">
                                <table class="table table-sm table-bordered mb-0" style="font-size: 0.85rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Status</th>
                                            <th class="text-end">Jumlah</th>
                                            <th class="text-end">%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="badge bg-success me-2">&nbsp;</span> AKTIF</td>
                                            <td class="text-end fw-bold"><?= number_format($stats['aktif']) ?></td>
                                            <td class="text-end"><?= (int)$stats['aktif_percentage'] ?>%</td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-danger me-2">&nbsp;</span> NONAKTIF</td>
                                            <td class="text-end fw-bold"><?= number_format($stats['nonaktif']) ?></td>
                                            <td class="text-end"><?= (int)$stats['nonaktif_percentage'] ?>%</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td>Total</td>
                                            <td class="text-end"><?= number_format($stats['total']) ?></td>
                                            <td class="text-end">100%</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                    <form id="pdfExportForm" action="<?= site_url('web_opd/export_pdf') . '?' . $_SERVER['QUERY_STRING'] ?>" method="POST" class="d-inline" target="_blank">
                        <input type="hidden" name="statusChartData" id="statusChartData">
                        <button type="submit" class="btn btn-danger btn-sm me-2" onclick="return preparePdfExport();">
                            <i class="fas fa-file-pdf me-2"></i>Export PDF
                        </button>
                    </form>
                    <a href="<?= site_url('web_opd/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-2"></i>Add Website
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Search and Filter Form -->
                <form method="GET" action="<?= site_url('web_opd') ?>" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-10">
                            <input type="text" class="form-control" name="search" placeholder="Search by OPD or Domain..." value="<?= esc($search) ?>">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">All Status</option>
                                <option value="AKTIF" <?= ($filterStatus === 'AKTIF') ? 'selected' : '' ?>>AKTIF</option>
                                <option value="NONAKTIF" <?= ($filterStatus === 'NONAKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                            </select>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-info me-2">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <a href="<?= site_url('web_opd') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-sync-alt me-2"></i>Reset
                            </a>
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
                                        <td class="fw-bold"><?= esc(strtoupper($web['nama_unit_kerja'] ?? '')) ?: '-' ?></td>
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
                                        <td><?= esc($web['keterangan'] ?? '') ?: '-' ?></td>
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

<?= $this->section('scripts') ?>
<script>
    Chart.register(ChartDataLabels);

    document.addEventListener("DOMContentLoaded", function() {
        const stats = <?= json_encode($stats) ?>;

        // Common Chart Labels Formatter
        const labelFormatter = (value, ctx) => {
            const total = ctx.dataset.data.reduce((acc, curr) => acc + curr, 0);
            const percentage = Math.trunc(value * 100 / total) + "%";
            return value > (total * 0.05) ? percentage : '';
        };

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['AKTIF', 'NONAKTIF'],
                datasets: [{
                    data: [stats.aktif, stats.nonaktif],
                    backgroundColor: ['#198754', '#dc3545'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        display: true,
                        color: '#fff',
                        font: { weight: 'bold' },
                        formatter: labelFormatter
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        window.charts = {
            statusChart: statusChart,
        };
    });

    function preparePdfExport() {
        const statusChartB64 = window.charts.statusChart.toBase64Image();
        document.getElementById('statusChartData').value = statusChartB64;
        return true;
    }
</script>
<?= $this->endSection() ?>