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

    <!-- Back Button -->
    <div class="mb-4">
      <a href="<?= $back_url ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Email List
      </a>
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
            <h3 class="text-primary mb-2"><?= esc($email['email']) ?></h3>
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
            <div class="row mb-3">
              <div class="col-6">
                <strong class="text-muted">Name:</strong>
              </div>
              <div class="col-6">
                <form action="<?= site_url('email/update_name/' . $email['user']) ?>" method="post" id="name-form">
                    <div class="input-group">
                        <input type="text" name="name" id="name-input" value="<?= esc($email['name']) ?>" class="form-control" readonly>
                        <button type="button" id="edit-name-btn" class="btn btn-outline-secondary"><i class="fas fa-pencil-alt"></i></button>
                        <button type="submit" id="save-name-btn" class="btn btn-primary d-none"><i class="fas fa-save"></i></button>
                        <button type="button" id="cancel-name-btn" class="btn btn-outline-secondary d-none"><i class="fas fa-times"></i></button>
                    </div>
                </form>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-6">
                <strong class="text-muted">Email:</strong>
              </div>
              <div class="col-6">
                <?= esc($email['email']) ?>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-6">
                <strong class="text-muted">Password:</strong>
              </div>
              <div class="col-6">
                <?= esc($email['password']) ?>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-6">
                <strong class="text-muted">NIK/NIP:</strong>
              </div>
              <div class="col-6">
                <?= esc($email['nik_nip']) ?>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-6">
                <strong class="text-muted">Domain:</strong>
              </div>
              <div class="col-6">
                <?= esc($email['domain']) ?>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-6">
                <strong class="text-muted">Status:</strong>
              </div>
              <div class="col-6">
                <?php if (($email['suspended_login'] ?? 0) == 0): ?>
                  <span class="badge bg-success">Active</span>
                <?php else: ?>
                  <span class="badge bg-danger">Suspended</span>
                <?php endif; ?>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-6">
                <strong class="text-muted">Unit Kerja:</strong>
              </div>
              <div class="col-6">
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
            </div>
            <?php if (!empty($parent_unit_kerja)): ?>
            <div class="row mb-3">
              <div class="col-6">
                <strong class="text-muted">Sub Unit Kerja:</strong>
              </div>
              <div class="col-6">
                <a href="<?= site_url('email/unit_kerja/' . $current_unit_kerja['id']) ?>" class="btn btn-sm btn-outline-secondary">
                  <i class="fas fa-sitemap me-1"></i><?= esc(strtoupper($current_unit_kerja['nama_unit_kerja'])) ?>
                </a>
              </div>
            </div>
            <?php endif; ?>
            <div class="row mb-3">
              <div class="col-6">
                <strong class="text-muted">Webmail:</strong>
              </div>
              <div class="col-6">
                <a href="https://<?= config('Cpanel')->cpanel_host ?>:2096" target="_blank" class="btn btn-sm btn-outline-primary">
                  <i class="fas fa-external-link-alt me-1"></i>Login
                </a>
              </div>
            </div>
            <hr>
            <form action="<?= site_url('email/update_unit_kerja/' . $email['user']) ?>" method="post" class="mt-3">
              <div class="row align-items-center">
                <div class="col-md-4">
                  <label for="unit_kerja_id" class="form-label fw-bold"><i class="fas fa-building me-2"></i>Change Unit Kerja:</label>
                </div>
                <div class="col-md-8">
                  <div class="input-group">
                    <select class="form-select" id="unit_kerja_id" name="unit_kerja_id">
                      <option value="">--Pilih Unit Kerja--</option>
                      <?php foreach ($unit_kerja_options as $unit): ?>
                        <option value="<?= esc($unit['id']) ?>" <?= ($unit['id'] == ($parent_unit_kerja['id'] ?? $current_unit_kerja['id'])) ? 'selected' : '' ?>>
                          <?= esc(strtoupper($unit['nama_unit_kerja'])) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save</button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-header bg-light py-3">
            <h5 class="card-title mb-0">
              <i class="fas fa-bolt me-2 text-danger"></i>Quick Actions
            </h5>
          </div>
          <div class="card-body py-4">
            <div class="row g-3">
              <div class="col-md-3 col-sm-6">
                <button class="btn btn-outline-primary w-100" onclick="copyToClipboard('<?= esc($email['email'], 'js') ?>')">
                  <i class="fas fa-copy me-2"></i>Copy Email
                </button>
              </div>
              <div class="col-md-3 col-sm-6">
                <a href="mailto:<?= esc($email['email']) ?>" class="btn btn-outline-success w-100">
                  <i class="fas fa-paper-plane me-2"></i>Send Email
                </a>
              </div>
              <div class="col-md-3 col-sm-6">
                <a href="https://<?= config('Cpanel')->cpanel_host ?>:2096" target="_blank" class="btn btn-outline-info w-100">
                  <i class="fas fa-inbox me-2"></i>Open Webmail
                </a>
              </div>
              <div class="col-md-3 col-sm-6">
                <a href="<?= $back_url ?>" class="btn btn-outline-secondary w-100">
                  <i class="fas fa-arrow-left me-2"></i>Back
                </a>
              </div>
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
  function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
      const originalText = event.target.innerHTML;
      event.target.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';
      event.target.classList.remove('btn-outline-primary');
      event.target.classList.add('btn-success');

      setTimeout(function() {
        event.target.innerHTML = originalText;
        event.target.classList.remove('btn-success');
        event.target.classList.add('btn-outline-primary');
      }, 2000);
    }).catch(function(err) {
      alert('Failed to copy email: ' + err);
    });
  }
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('edit-name-btn');
    const saveBtn = document.getElementById('save-name-btn');
    const cancelBtn = document.getElementById('cancel-name-btn');
    const nameInput = document.getElementById('name-input');
    const originalName = nameInput.value;

    editBtn.addEventListener('click', function() {
        nameInput.removeAttribute('readonly');
        nameInput.focus();
        editBtn.classList.add('d-none');
        saveBtn.classList.remove('d-none');
        cancelBtn.classList.remove('d-none');
    });

    cancelBtn.addEventListener('click', function() {
        nameInput.setAttribute('readonly', true);
        nameInput.value = originalName;
        editBtn.classList.remove('d-none');
        saveBtn.classList.add('d-none');
        cancelBtn.classList.add('d-none');
    });
});
</script>
<?= $this->endSection() ?>