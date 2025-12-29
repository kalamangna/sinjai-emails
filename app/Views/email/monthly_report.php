<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">Monthly Report</h1>
            <div>
                <a href="<?= site_url('email/export_monthly_report_pdf') ?>?month=<?= $month ?>&year=<?= $year ?>" class="btn btn-danger">
                    <i class="fas fa-file-pdf me-2"></i>Export PDF
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="<?= current_url() ?>" method="get" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="month" class="form-label">Month</label>
                <select name="month" id="month" class="form-select">
                    <?php
                    for ($m = 1; $m <= 12; $m++) {
                        $monthName = date('F', mktime(0, 0, 0, $m, 1));
                        $selected = ($m == $month) ? 'selected' : '';
                        echo "<option value='$m' $selected>$monthName</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="year" class="form-label">Year</label>
                <select name="year" id="year" class="form-select">
                    <?php
                    $currentYear = date('Y');
                    for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                        $selected = ($y == $year) ? 'selected' : '';
                        echo "<option value='$y' $selected>$y</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Apply Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Emails</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($total_emails) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-envelope fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">New This Month</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format(count($new_emails)) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active Accounts</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($active_emails) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Suspended</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($suspended_emails) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-ban fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Status ASN Breakdown -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Status ASN Distribution</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($status_asn_stats)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($status_asn_stats as $stat): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= esc($stat['nama_status_asn'] ?: 'Unknown') ?>
                                <span class="badge bg-primary rounded-pill"><?= $stat['count'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted text-center my-3">No data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- BSrE Status Breakdown -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">BSrE Status Summary</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($bsre_stats)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($bsre_stats as $label => $count): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= esc($label) ?>
                                <span class="badge bg-info rounded-pill"><?= $count ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted text-center my-3">No data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top Unit Kerja -->
    <div class="col-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Top 10 Unit Kerja by Email Count</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($unit_kerja_stats)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Unit Kerja</th>
                                    <th class="text-center" style="width: 150px;">Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($unit_kerja_stats as $stat): ?>
                                    <tr>
                                        <td><?= esc($stat['nama_unit_kerja'] ?: 'Unknown/Unassigned') ?></td>
                                        <td class="text-center"><?= $stat['count'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center my-3">No data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- New Emails Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">New Accounts Created in <?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></h6>
    </div>
    <div class="card-body">
        <?php if (!empty($new_emails)): ?>
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($new_emails as $email): ?>
                            <tr>
                                <td><?= esc($email['email']) ?></td>
                                <td><?= esc(strtoupper($email['name'])) ?></td>
                                <td><?= esc($email['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted text-center my-3">No new accounts created in this period.</p>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
