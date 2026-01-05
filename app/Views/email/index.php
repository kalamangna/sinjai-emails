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

<!-- Action Buttons Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column align-items-end gap-2">
            <div class="d-flex gap-2">
                <a href="<?= site_url('email/check_nik') ?>" class="btn btn-info text-white">
                    <i class="fas fa-search me-2"></i>Check NIK
                </a>
                <button onclick="syncAllBsreStatus()" class="btn btn-warning">
                    <i class="fas fa-sync-alt me-2"></i>Batch Sync Status TTE
                </button>
                <a href="<?= site_url('email/sync') ?>" class="btn btn-primary" id="syncButton">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    <i class="fas fa-sync-alt me-2"></i>
                    <span class="button-text">Sync from cPanel</span>
                </a>
            </div>
            <small class="text-muted">
                <i class="fas fa-sync-alt me-1"></i>
                Last updated: <?php echo isset($last_sync_time) ? get_local_datetime(strtotime($last_sync_time)) : 'N/A'; ?>
            </small>
        </div>
    </div>
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

<!-- New Row for Status ASN and Status TTE Statistics -->
<div class="row mb-4">
    <div class="col-12">
        <div class="row g-3 row-cols-1 row-cols-md-2">
            <!-- Status ASN Statistics Card -->
            <div class="col">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-light py-3">
                        <h6 class="card-title mb-0"><i class="fas fa-user-tag me-2 text-primary"></i>Status ASN Statistics</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($status_asn_counts)): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($status_asn_counts as $status): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= esc($status['name']) ?>
                                        <span class="badge bg-secondary rounded-pill"><?= $status['count'] ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0">No Status ASN data available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Status TTE Statistics Card -->
            <div class="col">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-light py-3">
                        <h6 class="card-title mb-0"><i class="fas fa-fingerprint me-2 text-primary"></i>Status TTE Statistics</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($bsre_status_counts)): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($bsre_status_counts as $status): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= esc($status['label']) ?> (<?= esc($status['status']) ?>)
                                        <span class="badge bg-secondary rounded-pill"><?= $status['count'] ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0">No Status TTE data available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Unit Kerja and Eselon Lists Section -->
<div class="row mb-4">
    <div class="col-12 mb-4">
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
                                        <?= esc(strtoupper($unit['nama_unit_kerja'])) ?>
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
    <div class="col-12">
        <div class="accordion shadow-sm" id="eselonAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        <i class="fas fa-layer-group me-2 text-primary"></i>Eselon List
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#eselonAccordion">
                    <div class="accordion-body">
                        <?php if (!empty($eselon_counts)): ?>
                            <div class="list-group">
                                <?php foreach ($eselon_counts as $eselon): ?>
                                    <a href="<?= site_url('email/eselon/' . $eselon['id']) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <?= esc(strtoupper($eselon['name'])) ?>
                                        <span class="badge bg-primary rounded-pill"><?= $eselon['count'] ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No Eselon data found.</p>
                        <?php endif; ?>
                    </div>
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
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="search" name="search"
                                placeholder="Search by Email, Name, NIK, or NIP..." value="<?= isset($search) ? esc($search) : '' ?>">
                        </div>
                    </div>

                    <!-- Status TTE Filter -->
                    <div class="col-md-3">
                        <label for="bsre_status" class="form-label">Status TTE</label>
                        <select class="form-select" id="bsre_status" name="bsre_status">
                            <option value="" <?= empty($bsre_status) ? 'selected' : '' ?>>All Status</option>
                            <?php foreach ($bsre_status_options as $key => $label): ?>
                                <option value="<?= esc($key) ?>" <?= ($bsre_status === $key) ? 'selected' : '' ?>>
                                    <?= esc($key === 'not_synced' ? $label : $key) ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="not_synced" <?= ($bsre_status === 'not_synced') ? 'selected' : '' ?>>Not Synced</option>
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
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                            <a href="<?= current_url() ?>" class="btn btn-outline-secondary flex-grow-1">
                                <i class="fas fa-sync-alt me-2"></i>Reset
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
                                    </th>
                                    <th>
                                        <i class="fas fa-building me-2"></i>Unit Kerja
                                    </th>
                                    <th>
                                        <i class="fas fa-fingerprint me-2"></i>Status TTE
                                    </th>
                                    <th class="text-center" style="width: 120px;">
                                        <i class="fas fa-cog me-2"></i>Action
                                    </th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($emails as $email): ?>

                                    <tr>

                                        <td class="ps-4 align-middle">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-envelope text-primary me-3"></i>
                                                <div>
                                                    <div class="fw-bold"><?= esc($email['email']) ?></div>
                                                    <small class="d-block text-muted">
                                                        <?= esc(strtoupper($email['name'])) ?>
                                                    </small>
                                                    <small class="text-info" style="font-size: 12px;">
                                                        Last Modified: <?= get_local_datetime($email['mtime']) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="align-middle">
                                            <?php if (!empty($email['parent_unit_kerja_name'])): ?>
                                                <small class="d-block"><?= esc(strtoupper($email['parent_unit_kerja_name'])) ?></small>
                                                <small class="d-block text-muted"><?= esc(strtoupper($email['unit_kerja_name'])) ?></small>
                                            <?php else: ?>
                                                <small><?= esc(strtoupper($email['unit_kerja_name'])) ?></small>
                                            <?php endif; ?>
                                        </td>

                                        <td class="align-middle">
                                            <div id="bsre-status-<?= esc($email['user']) ?>" 
                                                 data-user="<?= esc($email['user']) ?>" 
                                                 data-email="<?= esc($email['email']) ?>"
                                                 class="bsre-status-container d-flex align-items-center">
                                                <?php
                                                    $status = $email['bsre_status'] ?? '';
                                                    $badgeClass = 'bg-secondary';
                                                    $badgeText = $status ?: 'Not Synced';
                                                    
                                                    if ($status === 'ISSUE') {
                                                        $badgeClass = 'bg-success';
                                                    } else if (in_array($status, ['EXPIRED', 'REVOKE', 'SUSPEND'])) {
                                                        $badgeClass = 'bg-danger';
                                                    } else if (in_array($status, ['RENEW', 'WAITING_FOR_VERIFICATION', 'NEW'])) {
                                                        $badgeClass = 'bg-info text-dark';
                                                    } else if (in_array($status, ['NO_CERTIFICATE', 'NOT_REGISTERED', 'UNKNOWN'])) {
                                                        $badgeClass = 'bg-warning text-dark';
                                                    }
                                                ?>
                                                <span class="badge <?= $badgeClass ?>"><?= esc($badgeText) ?></span>
                                            </div>
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

        fetch('<?= site_url('email/sync') ?>?t=' + new Date().getTime())
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

  function updateBsreStatusElement(emailUser, status, keterangan) {
      const bsreStatusDiv = document.getElementById(`bsre-status-${emailUser}`);
      if (!bsreStatusDiv) return;

      const statusMapping = {
          'ISSUE': 'Sertifikat Aktif / Siap TTE',
          'EXPIRED': 'Masa Berlaku Habis',
          'RENEW': 'Proses Pembaruan',
          'WAITING_FOR_VERIFICATION': 'Menunggu Verifikasi',
          'NEW': 'Belum Aktivasi',
          'NO_CERTIFICATE': 'Belum Ada Sertifikat',
          'NOT_REGISTERED': 'Pengguna Tidak Terdaftar',
          'SUSPEND': 'Akun Ditangguhkan',
          'REVOKE': 'Sertifikat Dicabut'
      };

      let badgeClass = 'bg-secondary';
      let badgeText = status || 'UNKNOWN';
      let descriptionText = statusMapping[status] || 'Status Tidak Dikenali';

      if (status === 'ISSUE') {
          badgeClass = 'bg-success';
      } else if (status === 'EXPIRED' || status === 'REVOKE' || status === 'SUSPEND') {
          badgeClass = 'bg-danger';
      } else if (status === 'RENEW' || status === 'WAITING_FOR_VERIFICATION' || status === 'NEW') {
          badgeClass = 'bg-info text-dark';
      } else if (status === 'NO_CERTIFICATE' || status === 'NOT_REGISTERED' || status === 'UNKNOWN' || !status) {
          badgeClass = 'bg-warning text-dark';
      }
      
      bsreStatusDiv.innerHTML = `<span class="badge ${badgeClass}">${badgeText}</span>`;
  }

  function syncBsreStatus(emailUser, emailAddress) {
      const bsreStatusDiv = document.getElementById(`bsre-status-${emailUser}`);
      if (!bsreStatusDiv) return;

      // Show syncing state
      bsreStatusDiv.innerHTML = '<span class="spinner-border spinner-border-sm text-info" role="status" aria-hidden="true"></span><small class="text-muted ms-1">Syncing...</small>';

      fetch('<?= site_url('bsre/sync-status') ?>', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest'
          },
          body: 'email=' + encodeURIComponent(emailAddress)
      })
      .then(response => response.json())
      .then(data => {
          if (data.status === 'success') {
              // Update display with new status
              updateBsreStatusElement(emailUser, data.bsre_status, '');
          } else {
              bsreStatusDiv.innerHTML = `<span class="badge bg-danger">Sync Error</span><br><small class="d-block text-danger mt-1">${data.message}</small>`;
              console.error(`Error syncing Status TTE for ${emailAddress}:`, data.message);
          }
      })
      .catch(error => {
          bsreStatusDiv.innerHTML = `<span class="badge bg-danger">Network Error</span>`;
          console.error(`Network error syncing Status TTE for ${emailAddress}:`, error);
      });
  }

  function syncAllBsreStatus() {
      // Logic to find all sync buttons or rows and trigger syncBsreStatus
      // We can iterate over IDs starting with bsre-status-
      const statusContainers = document.querySelectorAll('[id^="bsre-status-"]');
      
      if (statusContainers.length === 0) {
          alert('No emails to sync.');
          return;
      }

      if (!confirm(`Are you sure you want to sync Status TTE for ${statusContainers.length} displayed emails? This might take a moment.`)) {
          return;
      }

      statusContainers.forEach((container, index) => {
          const emailUser = container.id.replace('bsre-status-', '');
          const emailAddress = container.getAttribute('data-email'); // Get email from data attribute

          if (emailUser && emailAddress) {
              setTimeout(() => {
                  syncBsreStatus(emailUser, emailAddress);
              }, index * 200); // 200ms delay per request
          }
      });
  }
</script>
<?= $this->endSection() ?>