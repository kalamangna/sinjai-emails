<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <!-- Dashboard Charts -->
        <div class="row mb-4">
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

            <!-- Platform Chart -->
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light py-3">
                        <h6 class="card-title mb-0"><i class="fas fa-microchip me-2 text-primary"></i>Distribusi Platform</h6>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-sm-5">
                                <div style="height: 180px; position: relative;">
                                    <canvas id="platformChart"></canvas>
                                </div>
                            </div>
                            <div class="col-sm-7">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0" style="font-size: 0.85rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Platform</th>
                                                <th class="text-end">Jumlah</th>
                                                <th class="text-end">%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($platform_stats as $index => $ps): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge me-2" style="background-color: var(--platform-color-<?= $index ?>) !important;">&nbsp;</span>
                                                        <?= esc($ps['nama_platform']) ?: '-' ?>
                                                    </td>
                                                    <td class="text-end fw-bold"><?= number_format($ps['count']) ?></td>
                                                    <td class="text-end"><?= $stats['total'] > 0 ? (int)(($ps['count'] / $stats['total']) * 100) : 0 ?>%</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
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
                                        <td><?= esc($web['kecamatan']) ?: '-' ?></td>
                                        <td><?= esc($web['desa_kelurahan']) ?: '-' ?></td>
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
                                            <?php if (strtoupper($web['dikelola_kominfo'] ?? '') === 'YA'): ?>
                                                <i class="fas fa-check-circle text-success"></i>
                                            <?php else: ?>
                                                <i class="fas fa-times-circle text-danger"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td id="status-cell-<?= $web['id'] ?>">
                                            <?php
                                            $status = strtoupper($web['status'] ?? 'NONAKTIF');
                                            $statusClass = ($status === 'AKTIF') ? 'bg-success' : 'bg-danger';
                                            ?>
                                            <span class="badge <?= $statusClass ?>"><?= $status ?></span>
                                        </td>
                                        <td><?= esc($web['keterangan']) ?: '-' ?></td>
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    Chart.register(ChartDataLabels);

    document.addEventListener("DOMContentLoaded", function() {
        const stats = <?= json_encode($stats) ?>;
        const platformStats = <?= json_encode($platform_stats) ?>;

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

        // Platform Chart
        const platformCtx = document.getElementById('platformChart').getContext('2d');
        const platformColors = ['#0d6efd', '#0dcaf0', '#ffc107', '#6610f2', '#6c757d', '#d63384', '#20c997', '#fd7e14'];
        
        const platformData = platformStats.map(p => parseInt(p.count));
        const platformLabels = platformStats.map(p => p.nama_platform);
        const platformBg = platformStats.map((_, i) => platformColors[i % platformColors.length]);

        // Set CSS variables for legend colors
        platformBg.forEach((color, i) => {
            document.documentElement.style.setProperty('--platform-color-' + i, color);
        });

        const platformChart = new Chart(platformCtx, {
            type: 'doughnut',
            data: {
                labels: platformLabels,
                datasets: [{
                    data: platformData,
                    backgroundColor: platformBg,
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