<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <!-- Dashboard Cards -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-primary border-4">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small fw-bold">Total Website</h6>
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
                    <i class="fas fa-globe me-2"></i>Website Desa & Kelurahan
                    <span class="badge bg-secondary ms-2 small" style="font-size: 0.7em;">Found: <?= $total_filtered ?></span>
                </h5>
                <div>
                    <form id="pdfExportForm" action="<?= site_url('web_desa_kelurahan/export_pdf') . '?' . $_SERVER['QUERY_STRING'] ?>" method="POST" class="d-inline" target="_blank">
                        <input type="hidden" name="statusChartData" id="statusChartData">
                        <input type="hidden" name="platformChartData" id="platformChartData">
                        <button type="submit" class="btn btn-danger btn-sm me-2" onclick="return preparePdfExport();">
                            <i class="fas fa-file-pdf me-2"></i>Export PDF
                        </button>
                    </form>
                    <button type="button" class="btn btn-warning text-dark btn-sm" id="batchSyncBtn" onclick="startBatchSync()">
                        <i class="fas fa-sync me-2"></i>Batch Sync Expiration
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Progress Bar -->
                <div id="syncProgressContainer" class="mb-4 d-none">
                    <h6 class="text-muted">Syncing expiration dates... <span id="syncStatusCount">0/0</span></h6>
                    <div class="progress">
                        <div id="syncProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Search and Filter Form -->
                <form method="GET" action="<?= site_url('web_desa_kelurahan') ?>" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Search by Desa, Kecamatan, or Domain..." value="<?= esc($search) ?>">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="kecamatan">
                                <option value="">All Kecamatan</option>
                                <?php foreach ($kecamatan_list as $k): ?>
                                    <option value="<?= esc($k['kecamatan']) ?>" <?= ($filterKecamatan === $k['kecamatan']) ? 'selected' : '' ?>>
                                        <?= esc($k['kecamatan']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="type">
                                <option value="">All Type</option>
                                <option value="DESA" <?= ($filterType === 'DESA') ? 'selected' : '' ?>>DESA</option>
                                <option value="KELURAHAN" <?= ($filterType === 'KELURAHAN') ? 'selected' : '' ?>>KELURAHAN</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="filter_platform">
                                <option value="">All Platform</option>
                                <option value="NULL" <?= ($filterPlatform === 'NULL') ? 'selected' : '' ?>>TIDAK TERDAFTAR</option>
                                <?php foreach ($platforms as $p): ?>
                                    <option value="<?= esc($p['nama_platform']) ?>" <?= ($filterPlatform === $p['nama_platform']) ? 'selected' : '' ?>>
                                        <?= esc($p['nama_platform']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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
                                <i class="fas fa-filter me-2"></i>Apply Filters
                            </button>
                            <a href="<?= site_url('web_desa_kelurahan') ?>" class="btn btn-outline-secondary">
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
                                    <th>Kecamatan</th>
                                    <th>Desa / Kelurahan</th>
                                    <th>Domain</th>
                                    <th>Platform</th>
                                    <th>Tanggal Berakhir</th>
                                    <th class="text-center">Kominfo?</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                    <th class="text-center" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($websites as $web): ?>
                                    <tr class="website-row" data-id="<?= $web['id'] ?>">
                                        <td><?= esc($web['kecamatan']) ?></td>
                                        <td><?= esc($web['desa_kelurahan']) ?></td>
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
                                            $platform = strtoupper($web['platform_name'] ?? '');
                                            if (empty($platform)): ?>
                                                <span class="text-muted">-</span>
                                            <?php else:
                                                $platformClass = 'bg-secondary';
                                                if ($platform === 'SIDEKA-NG') $platformClass = 'bg-primary';
                                                elseif ($platform === 'OPENSID') $platformClass = 'bg-info';
                                                elseif ($platform === 'PIHAK KETIGA') $platformClass = 'bg-warning text-dark';
                                            ?>
                                                <span class="badge <?= $platformClass ?>"><?= esc($web['platform_name']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td id="date-cell-<?= $web['id'] ?>">
                                            <?= $web['tanggal_berakhir'] ? date('d-m-Y', strtotime($web['tanggal_berakhir'])) : '-' ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (strtoupper($web['dikelola_kominfo']) === 'YA'): ?>
                                                <i class="fas fa-check-circle text-success"></i>
                                            <?php else: ?>
                                                <i class="fas fa-times-circle text-danger"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td id="status-cell-<?= $web['id'] ?>">
                                            <?php
                                            $status = strtoupper($web['status']);
                                            $statusClass = ($status === 'AKTIF') ? 'bg-success' : 'bg-danger';
                                            ?>
                                            <span class="badge <?= $statusClass ?>"><?= $status ?></span>
                                        </td>
                                        <td><?= esc($web['keterangan']) ?></td>
                                        <td class="text-center">
                                            <a href="<?= site_url('web_desa_kelurahan/edit/' . $web['id']) ?>" class="btn btn-sm btn-outline-primary" title="Edit">
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
                        <h5 class="text-muted">No websites found. Please seed the database.</h5>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4 mt-4">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small fw-bold">Status Website</h6>
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small fw-bold">Distribusi Platform</h6>
                <canvas id="platformChart"></canvas>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    Chart.register(ChartDataLabels);

    document.addEventListener("DOMContentLoaded", function() {
        // Data from PHP
        const stats = <?= json_encode($stats) ?>;
        const platformStats = <?= json_encode($platform_stats) ?>;

        // Status Chart (Pie)
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: ['AKTIF', 'NONAKTIF'],
                datasets: [{
                    label: 'Status Website',
                    data: [stats.aktif, stats.nonaktif],
                    backgroundColor: [
                        'rgba(25, 135, 84, 0.7)', // Green for Aktif
                        'rgba(220, 53, 69, 0.7)' // Red for Nonaktif
                    ],
                    borderColor: [
                        '#198754',
                        '#dc3545'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top' // Show legend for pie chart
                    },
                    datalabels: {
                        formatter: (value, context) => {
                            const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            if (total === 0) {
                                return '0 (0%)';
                            }
                            const percentage = (value / total * 100);
                            let percentageString;
                            if (percentage < 0.01 && percentage !== 0) {
                                percentageString = '<0.01%';
                            } else {
                                percentageString = percentage.toFixed(0) + '%';
                            }
                            return `${value} (${percentageString})`; // Combine raw value and percentage
                        },
                        color: '#fff', // White color for labels on colored slices
                        font: {
                            weight: 'bold'
                        }
                    }
                }
            }
        });

        // Platform Chart (Pie)
        const platformCtx = document.getElementById('platformChart').getContext('2d');
        const platformChart = new Chart(platformCtx, {
            type: 'pie',
            data: {
                labels: platformStats.map(p => p.nama_platform || 'TIDAK TERDAFTAR'),
                datasets: [{
                    label: 'Distribusi Platform',
                    data: platformStats.map(p => parseInt(p.count)),
                    backgroundColor: platformStats.map(p => {
                        const plat = (p.nama_platform || 'NOT REGISTERED').toUpperCase();
                        if (plat === 'SIDEKA-NG') return 'rgba(13, 110, 253, 0.7)'; // Primary
                        if (plat === 'OPENSID') return 'rgba(13, 202, 240, 0.7)'; // Info
                        if (plat === 'PIHAK KETIGA') return 'rgba(255, 193, 7, 0.7)'; // Warning
                        return 'rgba(108, 117, 125, 0.7)'; // Secondary
                    }),
                    borderColor: platformStats.map(p => {
                        const plat = (p.nama_platform || 'NOT REGISTERED').toUpperCase();
                        if (plat === 'SIDEKA-NG') return '#0d6efd'; // Primary
                        if (plat === 'OPENSID') return '#0dcaf0'; // Info
                        if (plat === 'PIHAK KETIGA') return '#ffc107'; // Warning
                        return '#6c757d'; // Secondary
                    }),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    datalabels: {
                        formatter: (value, context) => {
                            const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            if (total === 0) {
                                return '0 (0%)'; // Display raw count as 0 and percentage as 0%
                            }
                            const percentage = (value / total * 100);
                            let percentageString;
                            if (percentage < 0.01 && percentage !== 0) {
                                percentageString = '<0.01%';
                            } else {
                                percentageString = percentage.toFixed(0) + '%';
                            }
                            return `${value} (${percentageString})`; // Combine raw value and percentage
                        },
                        color: '#fff',
                        font: {
                            weight: 'bold'
                        }
                    }
                }
            }
        });

        // Store charts for PDF export
        window.charts = {
            statusChart: statusChart,
            platformChart: platformChart
        };
    });

    function preparePdfExport() {
        const statusChartB64 = window.charts.statusChart.toBase64Image();
        const platformChartB64 = window.charts.platformChart.toBase64Image();

        document.getElementById('statusChartData').value = statusChartB64;
        document.getElementById('platformChartData').value = platformChartB64;

        return true;
    }
</script>
<script>
    function syncExpiration(id, btn = null) {
        return new Promise((resolve, reject) => {
            var originalContent = '';
            if (btn) {
                originalContent = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            }

            var dateCell = document.getElementById('date-cell-' + id);
            if (dateCell) dateCell.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

            fetch('<?= site_url('web_desa_kelurahan/sync_expiration/') ?>' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        dateCell.innerText = data.date;
                        dateCell.classList.add('text-success', 'fw-bold');

                        var statusCell = document.getElementById('status-cell-' + id);
                        if (statusCell && data.web_status) {
                            var badgeClass = (data.web_status === 'AKTIF') ? 'bg-success' : 'bg-danger';
                            statusCell.innerHTML = '<span class="badge ' + badgeClass + '">' + data.web_status + '</span>';
                        }

                        setTimeout(() => dateCell.classList.remove('text-success', 'fw-bold'), 2000);
                        resolve(true);
                    } else {
                        console.warn('Sync failed for ID ' + id + ': ' + data.message);
                        if (dateCell) dateCell.innerText = '-';
                        resolve(false);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (dateCell) dateCell.innerText = 'Error';
                    resolve(false);
                })
                .finally(() => {
                    if (btn) {
                        btn.innerHTML = originalContent;
                        btn.disabled = false;
                    }
                });
        });
    }

    async function startBatchSync() {
        var batchBtn = document.getElementById('batchSyncBtn');
        var progressContainer = document.getElementById('syncProgressContainer');
        var progressBar = document.getElementById('syncProgressBar');
        var statusCount = document.getElementById('syncStatusCount');

        if (!confirm('Are you sure you want to sync expiration dates for all visible records? This process may take some time.')) {
            return;
        }

        batchBtn.disabled = true;
        progressContainer.classList.remove('d-none');

        var rows = document.querySelectorAll('.website-row');
        var total = rows.length;
        var current = 0;

        statusCount.innerText = `0/${total}`;
        progressBar.style.width = '0%';

        for (const row of rows) {
            var id = row.getAttribute('data-id');
            await syncExpiration(id);

            current++;
            var percentage = (current / total) * 100;
            progressBar.style.width = percentage + '%';
            statusCount.innerText = `${current}/${total}`;
        }

        setTimeout(() => {
            alert('Batch sync completed!');
            batchBtn.disabled = false;
            progressContainer.classList.add('d-none');
            location.reload(); // Reload to refresh dashboard stats
        }, 500);
    }
</script>
<?= $this->endSection() ?>