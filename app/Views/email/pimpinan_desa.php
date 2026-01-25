<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="row">
  <div class="col-12">
    <!-- Back Button -->
    <div class="mb-4 d-flex justify-content-between">
      <a href="javascript:void(0);" onclick="history.back();" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back
      </a>
      <div class="d-flex gap-2">
        <?php
        $queryString = \Config\Services::request()->getUri()->getQuery();
        $pdfUrl = site_url('email/export_pimpinan_desa_pdf') . ($queryString ? '?' . $queryString : '');
        ?>
        <a href="<?= $pdfUrl ?>" class="btn btn-danger">
          <i class="fas fa-file-pdf me-2"></i>Export PDF
        </a>
        <button onclick="syncAllBsreStatus()" class="btn btn-warning">
          <i class="fas fa-sync-alt me-2"></i>Sync Status TTE
        </button>
      </div>
    </div>

    <!-- Header -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light py-3">
        <h5 class="card-title mb-0">
          <i class="fas fa-users me-2 text-primary"></i>Daftar Email Pimpinan Desa
        </h5>
      </div>
      <div class="card-body">
        <p class="card-text">Daftar semua akun email yang ditandai sebagai Pimpinan Desa.</p>
      </div>
    </div>

    <!-- Search Form -->
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <form action="<?= current_url() ?>" method="get" class="row g-3 align-items-center">
          <div class="col-md-4">
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-search"></i></span>
              <input type="text" class="form-control" name="search" placeholder="Search..." value="<?= esc($search ?? '') ?>">
            </div>
          </div>
          <div class="col-md-4">
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-fingerprint"></i></span>
              <select name="bsre_status" class="form-select">
                <option value="" <?= empty($bsre_status) ? 'selected' : '' ?>>All Status TTE</option>
                <?php foreach ($bsre_status_options as $key => $label): ?>
                  <option value="<?= esc($key) ?>" <?= ($bsre_status === $key) ? 'selected' : '' ?>>
                    <?= esc($key === 'not_synced' ? $label : $key) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-4">
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

    <!-- Email List -->
    <div class="card shadow-sm">
      <div class="card-header bg-light py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
          <h5 class="card-title mb-2 mb-md-0">
            <i class="fas fa-list me-2 text-primary"></i>Email Accounts
          </h5>
          <span class="badge bg-primary fs-6">
            <?= $total_emails ?> Accounts
          </span>
        </div>
      </div>
      <div class="card-body p-0">
        <?php if (!empty($emails)): ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-4"><i class="fas fa-envelope me-2"></i>Email Address</th>
                  <th><i class="fas fa-briefcase me-2"></i>Jabatan</th>
                  <th><i class="fas fa-building me-2"></i>Unit Kerja</th>
                  <th><i class="fas fa-fingerprint me-2"></i>Status TTE</th>
                  <th class="text-center" style="width: 120px;"><i class="fas fa-cog me-2"></i>Action</th>
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
                          <small class="d-block text-muted"><?= esc(strtoupper($email['name'])) ?></small>
                        </div>
                      </div>
                    </td>
                    <td class="align-middle">
                      <?php if (!empty($email['jabatan'])): ?>
                        <div class="text-dark"><?= esc($email['jabatan']) ?></div>
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                    <td class="align-middle">
                      <small>
                        <?= esc(strtoupper($email['unit_kerja_name'])) ?>
                      </small>
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
                      <form action="<?= site_url('email/delete/' . $email['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this email?');">
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
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
                $total = $total_emails;

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
            <h5 class="text-muted">No pimpinan desa email accounts found.</h5>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
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
    let sourceText = '';

    if (status === 'ISSUE') {
      badgeClass = 'bg-success';
    } else if (status === 'EXPIRED' || status === 'REVOKE' || status === 'SUSPEND') {
      badgeClass = 'bg-danger';
    } else if (status === 'RENEW' || status === 'WAITING_FOR_VERIFICATION' || status === 'NEW') {
      badgeClass = 'bg-info text-dark';
    } else if (status === 'NO_CERTIFICATE' || status === 'NOT_REGISTERED' || status === 'UNKNOWN' || !status) {
      badgeClass = 'bg-warning text-dark';
    }

    bsreStatusDiv.innerHTML = `<span class="badge ${badgeClass}">${badgeText}</span>${sourceText}`;
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