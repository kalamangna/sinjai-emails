<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="row">
  <div class="col-12">
    <div id="flash-message-container">
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
      <?php if (session()->getFlashdata('info')): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
          <i class="fas fa-info-circle me-2"></i>
          <?= session()->getFlashdata('info') ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
    </div>

    <!-- Top Actions -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
      <a href="javascript:void(0);" onclick="history.back();" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back
      </a>
      <?php if ($email['status_asn_id'] == 3): ?>
        <a href="<?= site_url('email/export_single_perjanjian_kerja_pdf/' . $email['user']) ?>" class="btn btn-info">
          <i class="fas fa-file-contract me-2"></i>Export PK
        </a>
      <?php endif; ?>
    </div>

    <!-- Email Header -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
          <h5 class="card-title mb-2 mb-md-0">
            <i class="fas fa-envelope me-2 text-primary"></i>Email Account Details
          </h5>
          <?php if (($email['suspended_login'] ?? 0) == 0): ?>
            <span class="badge bg-success fs-6">Active</span>
          <?php else: ?>
            <span class="badge bg-danger fs-6">Suspended</span>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-body py-4">
        <div class="row align-items-center">
          <div class="col-md-8">
            <div class="d-flex align-items-center mb-2">
              <h3 class="text-primary mb-0 me-3"><?= esc($email['email']) ?></h3>
              <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('<?= esc($email['email'], 'js') ?>', this)">
                <i class="fas fa-copy me-1"></i>Copy
              </button>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
              <div id="bsre-status-container">
                <span class="badge bg-secondary">Not Synced</span>
              </div> <button class="btn btn-sm btn-outline-secondary ms-2" onclick="syncBsreStatus('<?= esc($email['email'], 'js') ?>')" title="Sync Status to Database">
                <i class="fas fa-sync-alt"></i>
              </button>
            </div>
            <?php if (isset($email['mtime']) && $email['mtime'] > 0): ?>
              <p class="text-muted mb-0">
                <i class="fas fa-fw fa-calendar me-2"></i><small>Last Modified: <?= get_local_datetime($email['mtime']) ?> (<?= relative_local_time($email['mtime']) ?>)</small>
              </p>
            <?php endif; ?>
          </div>
          <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="https://<?= config('Cpanel')->cpanel_host ?>:2096" target="_blank" class="btn btn-primary">
              <i class="fas fa-fw fa-sign-in-alt me-2"></i>Login Webmail
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Disk Usage Information -->
    <div class="row">
      <div class="col-lg-12 mb-4">
        <div class="card shadow-sm">
          <div class="card-header bg-light py-3">
            <h5 class="card-title mb-0">
              <i class="fas fa-hdd me-2 text-info"></i>Disk Usage
            </h5>
          </div>
          <div class="card-body py-4">
            <?php
            // Cek apakah kuota unlimited berdasarkan respon API
            $is_unlimited = ($email['diskquota'] ?? '') == 'unlimited' ||
              ($email['txtdiskquota'] ?? '') == 'unlimited' ||
              ($email['humandiskquota'] ?? '') == 'None' ||
              empty($email['_diskquota']) ||
              ($email['_diskquota'] ?? 0) == 0;

            $disk_used = $email['humandiskused'] ?? '0 KB';
            $disk_quota = $is_unlimited ? '<i class="fas fa-infinity text-info"></i>' : ($email['humandiskquota'] ?? '0 GB');

            // Format persentase dengan 2 angka di belakang koma
            $usage_percent = round($email['diskusedpercent_float'] ?? 0, 2);

            // Hitung sisa kapasitas berdasarkan data API
            if ($is_unlimited) {
              $remaining_text = '<i class="fas fa-infinity text-info"></i>';
              $remaining_class = 'text-info';
            } else {
              // Gunakan data bytes dari API
              $disk_used_bytes = $email['_diskused'] ?? 0;
              $disk_quota_bytes = $email['_diskquota'] ?? 0;

              if ($disk_quota_bytes > 0 && $disk_used_bytes >= 0) {
                $remaining_bytes = $disk_quota_bytes - $disk_used_bytes;

                // Konversi ke satuan yang sesuai
                if ($remaining_bytes >= 1073741824) { // 1 GB = 1073741824 bytes
                  $remaining_text = number_format($remaining_bytes / 1073741824, 2) . ' GB';
                } elseif ($remaining_bytes >= 1048576) { // 1 MB = 1048576 bytes
                  $remaining_text = number_format($remaining_bytes / 1048576, 2) . ' MB';
                } elseif ($remaining_bytes >= 1024) { // 1 KB = 1024 bytes
                  $remaining_text = number_format($remaining_bytes / 1024, 2) . ' KB';
                } else {
                  $remaining_text = $remaining_bytes . ' bytes';
                }
              } else {
                $remaining_text = '0 MB';
              }

              $remaining_class = 'text-success';
            }

            // Tentukan warna progress bar
            if ($is_unlimited) {
              $progress_class = 'bg-info progress-bar-striped progress-bar-animated';
              $progress_width = 100;
            } else {
              $progress_class = ($usage_percent > 80) ? 'bg-danger' : (($usage_percent > 60) ? 'bg-warning' : 'bg-success');
              $progress_width = $usage_percent;
            }
            ?>

            <!-- Progress Bar -->
            <div class="mb-4">
              <div class="d-flex justify-content-between mb-2">
                <span class="fw-bold">Storage Capacity</span>
                <span class="fw-bold <?= $is_unlimited ? 'text-info' : 'text-primary' ?>">
                  <?= $is_unlimited ? 'Unlimited' : $usage_percent . '%' ?>
                </span>
              </div>
              <div class="progress" style="height: 12px;">
                <div class="progress-bar <?= $progress_class ?>"
                  style="width: <?= $progress_width ?>%">
                </div>
              </div>
            </div>

            <!-- Usage Details -->
            <div class="row text-center">
              <div class="col-4">
                <div class="border-end">
                  <h4 class="text-primary mb-1"><?= $disk_used ?></h4>
                  <small class="text-muted">Used</small>
                </div>
              </div>
              <div class="col-4">
                <div class="border-end">
                  <h4 class="<?= $remaining_class ?> mb-1">
                    <?= $remaining_text ?>
                  </h4>
                  <small class="text-muted">Remaining</small>
                </div>
              </div>
              <div class="col-4">
                <div>
                  <h4 class="<?= $is_unlimited ? 'text-success' : 'text-info' ?> mb-1">
                    <?= $disk_quota ?>
                  </h4>
                  <small class="text-muted">Capacity</small>
                </div>
              </div>
            </div>
            <hr>
            <div class="row mb-3">
              <div class="col-6">
                <strong class="text-muted">Quota Type:</strong>
              </div>
              <div class="col-6">
                <?php if ($is_unlimited): ?>
                  <span class="badge bg-info">Unlimited</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Limited (<?= $disk_quota ?>)</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Account Information -->
      <div class="col-lg-12 mb-4">
        <div class="card shadow-sm">
          <div class="card-header bg-light py-3">
            <h5 class="card-title mb-0">
              <i class="fas fa-info-circle me-2 text-primary"></i>Account Details
            </h5>
          </div>
          <div class="card-body py-4">
            <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#editModal">
              <i class="fas fa-pencil-alt me-2"></i>Edit Details
            </button>
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong class="text-muted d-block">Name:</strong>
                <?= esc(strtoupper($email['name'])) ?>
              </div>
              <div class="col-md-6 mb-3">
                <strong class="text-muted d-block">Email:</strong>
                <?= esc($email['email']) ?>
              </div>
              <div class="col-md-6 mb-3">
                <strong class="text-muted d-block">Gelar Depan:</strong>
                <?= esc($email['gelar_depan'] ?? '') ?>
              </div>
              <div class="col-md-6 mb-3">
                <strong class="text-muted d-block">Gelar Belakang:</strong>
                <?= esc($email['gelar_belakang'] ?? '') ?>
              </div>
              <div class="col-md-12 mb-3">
                <strong class="text-muted d-block">Password:</strong>
                <?= esc($email['password'] ?? '') ?>
              </div>
              <hr>
              <div class="col-md-6 mb-3">
                <strong class="text-muted d-block">NIK:</strong>
                <?= esc($email['nik']) ?>
              </div>
              <div class="col-md-6 mb-3">
                <strong class="text-muted d-block">NIP:</strong>
                <?= esc($email['nip']) ?>
              </div>
              <div class="col-md-6 mb-3">
                <strong class="text-muted d-block">Tempat Lahir:</strong>
                <?= esc($email['tempat_lahir']) ?>
              </div>
              <div class="col-md-6 mb-3">
                <strong class="text-muted d-block">Tanggal Lahir:</strong>
                <?= esc($email['tanggal_lahir']) ?>
              </div>
              <hr>
              <div class="col-md-6 mb-3">
                <strong class="text-muted d-block">Pendidikan:</strong>
                <?= esc($email['pendidikan']) ?>
              </div>
              <div class="col-md-6 mb-3">
                <strong class="text-muted d-block">Jabatan:</strong>
                <?= esc($email['jabatan']) ?>
              </div>
              <div class="col-md-6 mb-3">
                <strong class="text-muted d-block">Status ASN:</strong>
                <?php foreach ($status_asn_options as $option): ?>
                  <?php if ($email['status_asn_id'] == $option['id']): ?>
                    <?= esc($option['nama_status_asn']) ?>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
              <div class="col-md-6 mb-3">
                <strong class="text-muted d-block">Eselon:</strong>
                <?php foreach ($eselon_options as $option): ?>
                  <?php if ($email['eselon_id'] == $option['id']): ?>
                    <?= esc($option['nama_eselon']) ?>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
              <div class="col-md-6 mb-3">
                <strong class="text-muted d-block">Pimpinan:</strong>
                <?= ($email['pimpinan'] ?? 0) == 1 ? '<span class="badge bg-primary">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?>
              </div>
              <div class="col-md-6 mb-3">
                <strong class="text-muted d-block">Pimpinan Desa:</strong>
                <?= ($email['pimpinan_desa'] ?? 0) == 1 ? '<span class="badge bg-primary">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?>
              </div>
              <div class="col-md-6 mb-3">
                <strong class="text-muted d-block">Unit Kerja:</strong>
                <?php if (!empty($parent_unit_kerja)): ?>
                  <a href="<?= site_url('email/unit_kerja/' . $parent_unit_kerja['id']) ?>" class="btn btn-sm btn-outline-info">
                    <i class="fas fa-building me-1"></i><?= esc(strtoupper($parent_unit_kerja['nama_unit_kerja'])) ?>
                  </a>
                <?php elseif (!empty($current_unit_kerja)): ?>
                  <a href="<?= site_url('email/unit_kerja/' . $current_unit_kerja['id']) ?>" class="btn btn-sm btn-outline-info">
                    <i class="fas fa-building me-1"></i><?= esc(strtoupper($current_unit_kerja['nama_unit_kerja'])) ?>
                  </a>
                <?php else: ?>
                  N/A
                <?php endif; ?>
              </div>
              <?php if (!empty($parent_unit_kerja)): ?>
                <div class="col-md-6 mb-3">
                  <strong class="text-muted d-block">Sub Unit Kerja:</strong>
                  <a href="<?= site_url('email/unit_kerja/' . $current_unit_kerja['id']) ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-sitemap me-1"></i><?= esc(strtoupper($current_unit_kerja['nama_unit_kerja'])) ?>
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text).then(function() {
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';
      btn.classList.remove('btn-outline-primary');
      btn.classList.add('btn-success');

      setTimeout(function() {
        btn.innerHTML = originalText;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-primary');
      }, 2000);
    }).catch(function(err) {
      alert('Failed to copy email: ' + err);
    });
  }

  // Helper function to render status (Global Scope)
  function renderBsreStatus(status, keterangan = '', fromDb = false) {
    const bsreContainer = document.getElementById('bsre-status-container');
    if (!bsreContainer) return;

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

    bsreContainer.innerHTML = `
          <span class="badge ${badgeClass}">${badgeText}</span>${sourceText}
          ${descriptionText ? `<small class="text-muted d-block mt-1">${descriptionText}</small>` : ''}
          ${keterangan && keterangan !== descriptionText ? `<small class="text-muted d-block mt-1">${keterangan}</small>` : ''}
      `;
  }

  function syncBsreStatus(email) {
    const bsreContainer = document.getElementById('bsre-status-container');
    if (bsreContainer) {
      bsreContainer.innerHTML = '<span class="spinner-border spinner-border-sm text-secondary" role="status" aria-hidden="true"></span><span class="text-muted ms-2">Syncing...</span>';
    }

    fetch('<?= site_url('bsre/sync-status') ?>', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'email=' + encodeURIComponent(email)
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          renderBsreStatus(data.bsre_status, '', true);
        } else {
          if (bsreContainer) {
            bsreContainer.innerHTML = `<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>Sync Failed: ${data.message}</span>`;
          }
          console.error('Sync Error:', data.message);
        }
      })
      .catch(error => {
        if (bsreContainer) {
          bsreContainer.innerHTML = `<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>Network Error</span>`;
        }
        console.error('Sync Error:', error);
      });
  }
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Fetch Status TTE Logic
    const bsreContainer = document.getElementById('bsre-status-container');
    const emailAddress = '<?= esc($email['email'], 'js') ?>';

    // Initial load logic
    if (bsreContainer && emailAddress) {
      const initialBsreStatus = '<?= esc($email['bsre_status'] ?? '', 'js') ?>';
      if (initialBsreStatus) {
        renderBsreStatus(initialBsreStatus, '', true);
      } else {
        bsreContainer.innerHTML = `<span class="badge bg-secondary">Not Synced</span>`;
      }
    }
  });
</script>
<?= $this->include('email/_modal_edit') ?>
<?= $this->endSection() ?>