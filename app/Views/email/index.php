<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div id="flash-message-container">
    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>

<!-- Statistics Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="row g-3">
            <!-- Total Accounts Card -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-envelope fa-3x text-primary"></i>
                        </div>
                        <h3 class="card-title text-primary"><?= $total_emails ?></h3>
                        <p class="card-text text-muted mb-0">Total Accounts</p>
                    </div>
                </div>
            </div>

            <!-- Active Accounts Card -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-check-circle fa-3x text-success"></i>
                        </div>
                        <h3 class="card-title text-success"><?= $active_count ?></h3>
                        <p class="card-text text-muted mb-0">Active Accounts</p>
                    </div>
                </div>
            </div>

            <!-- Suspended Accounts Card -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-times-circle fa-3x text-danger"></i>
                        </div>
                        <h3 class="card-title text-danger"><?= $suspended_count ?></h3>
                        <p class="card-text text-muted mb-0">Suspended Accounts</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2 justify-content-end">
            <a href="<?= site_url('email/sync') ?>" class="btn btn-primary" id="syncButton">
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                <i class="fas fa-sync-alt me-2"></i>
                <span class="button-text">Sync from cPanel</span>
            </a>
            <a href="<?= site_url('email/batch') ?>" class="btn btn-info">
                <i class="fas fa-plus-circle me-2"></i>Batch Create Emails
            </a>
            <a href="<?= site_url('email/batch_update') ?>" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Batch Update Emails
            </a>
            <a href="<?= site_url('unit_kerja/manage') ?>" class="btn btn-secondary">
                <i class="fas fa-building me-2"></i>Manage Unit Kerja
            </a>
        </div>
        <div class="text-end mt-2">
            <small class="text-muted">
                <i class="fas fa-sync-alt me-1"></i>
                Last updated: <?php echo isset($last_sync_time) ? get_local_datetime(strtotime($last_sync_time)) : 'N/A'; ?>
            </small>
        </div>
    </div>
</div>

<!-- Unit Kerja List Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="accordion shadow-sm" id="unitKerjaAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                        <i class="fas fa-building me-2 text-primary"></i>Unit Kerja List
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#unitKerjaAccordion">
                    <div class="accordion-body">
                        <?php if (!empty($unit_kerja_list)): ?>
                            <div class="list-group">
                                <?php foreach ($unit_kerja_list as $unit): ?>
                                    <a href="<?= site_url('email/unit_kerja/' . $unit['id']) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <?= esc($unit['nama_unit_kerja']) ?>
                                        <span class="badge bg-primary rounded-pill"><?= $unit['email_count'] ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No Unit Kerja found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter and Search Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light py-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <h5 class="card-title mb-3 mb-md-0">
                        <i class="fas fa-filter me-2 text-primary"></i>Filter & Search
                    </h5>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <!-- Search Input -->
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="search" name="search"
                                placeholder="Enter email..." value="<?= isset($search) ? esc($search) : '' ?>">
                        </div>
                    </div>
                    <!-- Status Filter -->
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="active" <?= (isset($status) && $status == 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="suspended" <?= (isset($status) && $status == 'suspended') ? 'selected' : '' ?>>Suspended</option>
                        </select>
                    </div>

                    <!-- Sorting -->
                    <div class="col-md-2">
                        <label for="sort" class="form-label">Sort by</label>
                        <select class="form-select" id="sort" name="sort">
                            <option value="newest" <?= (isset($sort) && $sort == 'newest') ? 'selected' : '' ?>>Newest</option>
                            <option value="oldest" <?= (isset($sort) && $sort == 'oldest') ? 'selected' : '' ?>>Oldest</option>
                            <option value="email_asc" <?= (isset($sort) && $sort == 'email_asc') ? 'selected' : '' ?>>A-Z Email</option>
                            <option value="email_desc" <?= (isset($sort) && $sort == 'email_desc') ? 'selected' : '' ?>>Z-A Email</option>
                            <option value="unit_kerja_asc" <?= (isset($sort) && $sort == 'unit_kerja_asc') ? 'selected' : '' ?>>A-Z Unit Kerja</option>
                            <option value="unit_kerja_desc" <?= (isset($sort) && $sort == 'unit_kerja_desc') ? 'selected' : '' ?>>Z-A Unit Kerja</option>
                            <option value="usage_asc" <?= (isset($sort) && $sort == 'usage_asc') ? 'selected' : '' ?>>Usage Asc</option>
                            <option value="usage_desc" <?= (isset($sort) && $sort == 'usage_desc') ? 'selected' : '' ?>>Usage Desc</option>
                        </select>
                    </div>

                    <!-- Items Per Page -->
                    <div class="col-md-2">
                        <label for="per_page" class="form-label">Items per Page</label>
                        <select class="form-select" id="per_page" name="per_page">
                            <option value="50" <?= (isset($per_page) && $per_page == 50) ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= (isset($per_page) && $per_page == 100) ? 'selected' : '' ?>>100</option>
                            <option value="250" <?= (isset($per_page) && $per_page == 250) ? 'selected' : '' ?>>250</option>
                            <option value="500" <?= (isset($per_page) && $per_page == 500) ? 'selected' : '' ?>>500</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-md-3">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Apply Filter
                            </button>
                            <a href="<?= current_url() ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-sync-alt me-2"></i>Reset
                            </a>
                            <a href="<?= site_url('email/export_csv') ?>" class="btn btn-outline-success">
                                <i class="fas fa-file-csv me-2"></i>Export to CSV
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Email Table Section -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light py-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <h5 class="card-title mb-2 mb-md-0">
                        <i class="fas fa-list me-2 text-primary"></i>Email Account List
                    </h5>
                    <span class="badge bg-primary fs-6">
                        <?= $filtered_count ?> Accounts
                    </span>
                </div>
            </div>

            <div class="card-body p-0">
                <?php if (!empty($emails)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">
                                        <i class="fas fa-envelope me-2"></i>Email Address
                                        <?php if (isset($sort) && in_array($sort, ['email_asc', 'email_desc'])): ?>
                                            <i class="fas fa-sort-<?= $sort == 'email_asc' ? 'down' : 'up' ?> text-primary"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th class="text-center">
                                        <i class="fas fa-building me-2"></i>Unit Kerja
                                    </th>
                                    <th class="text-center">
                                        <i class="fas fa-chart-pie me-2"></i>Disk Usage
                                        <?php if (isset($sort) && in_array($sort, ['usage_asc', 'usage_desc'])): ?>
                                            <i class="fas fa-sort-<?= $sort == 'usage_asc' ? 'down' : 'up' ?> text-primary"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th class="text-center">
                                        <i class="fas fa-calendar me-2"></i>Modified
                                        <?php if (isset($sort) && in_array($sort, ['newest', 'oldest'])): ?>
                                            <i class="fas fa-sort-<?= $sort == 'newest' ? 'down' : 'up' ?> text-primary"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th class="text-center">
                                        <i class="fas fa-cog me-2"></i>Action
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($emails as $email): ?>
                                    <?php
                                    // Cek apakah kuota unlimited/tidak terbatas
                                    $is_unlimited = ($email['diskquota'] ?? 0) == 0 || ($email['humandiskquota'] ?? '') == 'none' || ($email['humandiskquota'] ?? '') == 'unlimited';
                                    $disk_used = $email['humandiskused'] ?? '0 KB';
                                    $disk_quota = $is_unlimited ? '<i class="fas fa-infinity text-info"></i>' : ($email['humandiskquota'] ?? '0 GB');
                                    $usage_percent = $email['diskusedpercent_float'] ?? 0;

                                    // Tentukan warna progress bar
                                    if ($is_unlimited) {
                                        $progress_class = 'bg-info';
                                    } else {
                                        $progress_class = ($usage_percent > 80) ? 'bg-danger' : (($usage_percent > 60) ? 'bg-warning' : 'bg-success');
                                    }
                                    ?>

                                    <tr>
                                        <td class="ps-4 align-middle">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-envelope text-primary me-3"></i>
                                                <div>
                                                    <div class="fw-bold"><?= esc($email['email']) ?></div>
                                                    <small class="text-muted"><?= esc($email['domain']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <small><?= esc($email['unit_kerja']) ?: '-' ?></small>
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex flex-column align-items-center">
                                                <?php if ($is_unlimited): ?>
                                                    <!-- Tampilan untuk kuota unlimited -->
                                                    <div class="progress w-100 mb-1" style="height: 8px; max-width: 120px;">
                                                        <div class="progress-bar <?= $progress_class ?> progress-bar-striped progress-bar-animated"
                                                            role="progressbar"
                                                            style="width: 100%">
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= $disk_used ?> / <span class="text-success fw-bold"><?= $disk_quota ?></span>
                                                        <br>
                                                        <span class="fw-bold text-info">Unlimited</span>
                                                    </small>
                                                <?php else: ?>
                                                    <!-- Tampilan untuk kuota terbatas -->
                                                    <div class="progress w-100 mb-1" style="height: 8px; max-width: 120px;">
                                                        <div class="progress-bar <?= $progress_class ?>"
                                                            role="progressbar"
                                                            style="width: <?= $usage_percent ?>%">
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= $disk_used ?> / <?= $disk_quota ?>
                                                        <br>
                                                        <span class="fw-bold"><?= round($usage_percent, 2) ?>%</span>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <small class="text-muted">
                                                <?php if (isset($email['mtime']) && $email['mtime'] > 0): ?>
                                                    <?= get_local_date($email['mtime']) ?>
                                                    <br>
                                                    <small><?= get_local_time($email['mtime']) ?></small>
                                                    <br>
                                                    <small class="text-info"><?= relative_local_time($email['mtime']) ?></small>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                        <td class="text-center align-middle">
                                            <a href="<?= site_url('email/detail/' . $email['user']) ?>"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-email-id="<?= $email['id'] ?>" data-email-address="<?= esc($email['email']) ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination): ?>
                        <nav class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3" aria-label="Page navigation">
                            <div class="mb-2 mb-md-0">
                                <?php
                                $currentPage = $pagination->getCurrentPage();
                                $perPage = $pagination->getPerPage();
                                $total = $filtered_count;

                                $start_entry = ($currentPage - 1) * $perPage + 1;
                                $end_entry = min($currentPage * $perPage, $total);
                                ?>
                                <span class="text-muted">
                                    Showing <strong><?= $start_entry ?></strong> to <strong><?= $end_entry ?></strong> of <strong><?= $total ?></strong> entries
                                </span>
                            </div>
                            <?= $pagination->links() ?>
                        </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No email accounts found</h5>
                        <p class="text-muted">
                            <?= (isset($search) || isset($status)) ?
                                'Try changing your search filter.' :
                                'No email accounts are registered for this domain yet'; ?>
                        </p>
                        <?php if (isset($search) || isset($status)): ?>
                            <a href="<?= current_url() ?>" class="btn btn-primary">
                                <i class="fas fa-sync-alt me-2"></i>Reset Filter
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($emails)): ?>
                <div class="card-footer bg-light">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Showing <?= count($emails) ?> email accounts
                            </small>
                        </div>
                        <div class="col-md-6 text-md-end">
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the email account <strong id="emailToDelete"></strong>? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" action="" method="post" class="d-inline">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const emailId = button.getAttribute('data-email-id');
                const emailAddress = button.getAttribute('data-email-address');

                const modalBody = deleteModal.querySelector('.modal-body #emailToDelete');
                const deleteForm = deleteModal.querySelector('#deleteForm');

                modalBody.textContent = emailAddress;
                deleteForm.action = '<?= site_url('email/delete/') ?>' + emailId;
            });
        }
    });

    document.getElementById('syncButton').addEventListener('click', function(e) {
        e.preventDefault(); // Prevent default link navigation

        const button = this;
        if (button.classList.contains('disabled')) {
            return;
        }

        button.classList.add('disabled');
        button.setAttribute('aria-disabled', 'true');

        const spinner = button.querySelector('.spinner-border');
        const icon = button.querySelector('i');
        const text = button.querySelector('.button-text');

        spinner.classList.remove('d-none');
        icon.classList.add('d-none');
        text.textContent = 'Syncing...';

        const flashContainer = document.getElementById('flash-message-container');

        fetch('<?= site_url('email/sync') ?>')
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || 'Server error')
                    });
                }
                return response.json();
            })
            .then(data => {
                let alert;
                if (data.success) {
                    alert = `<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;
                    flashContainer.innerHTML = alert;

                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);

                } else {
                    alert = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;
                    flashContainer.innerHTML = alert;
                    resetButton();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const alert = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${error.message || 'An unexpected error occurred during synchronization.'}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
                flashContainer.innerHTML = alert;
                resetButton();
            });

        function resetButton() {
            button.classList.remove('disabled');
            button.removeAttribute('aria-disabled');
            spinner.classList.add('d-none');
            icon.classList.remove('d-none');
            text.textContent = 'Sync from cPanel';
        }
    });
</script>
<?= $this->endSection() ?>