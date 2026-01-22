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
        $csvUrl = site_url('email/export_unit_kerja_csv/' . $unit_kerja['id']) . ($queryString ? '?' . $queryString : '');
        $pdfUrl = site_url('email/export_unit_kerja_pdf/' . $unit_kerja['id']) . ($queryString ? '?' . $queryString : '');
        ?>
        <a href="<?= $csvUrl ?>" class="btn btn-success">
          <i class="fas fa-file-csv me-2"></i>Export CSV
        </a>
        <a href="<?= $pdfUrl ?>" class="btn btn-danger">
          <i class="fas fa-file-pdf me-2"></i>Export PDF
        </a>
        <a href="<?= site_url('email/export_account_detail_pdf/' . $unit_kerja['id']) . ($queryString ? '?' . $queryString : '') ?>" class="btn btn-dark">
          <i class="fas fa-file-pdf me-2"></i>Export Akun PDF
        </a>
        <button onclick="openExportModal(<?= $unit_kerja['id'] ?>)" class="btn btn-info">
          <i class="fas fa-file-contract me-2"></i>Export Perjanjian Kerja PDF
        </button>
        <button onclick="syncAllBsreStatus()" class="btn btn-warning">
          <i class="fas fa-sync-alt me-2"></i>Batch Sync Status TTE
        </button>
      </div>
    </div>

    <!-- Unit Kerja Header -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light py-3">
        <h5 class="card-title mb-0">
          <i class="fas fa-building me-2 text-primary"></i>Unit Kerja: <?= esc(strtoupper($unit_kerja['nama_unit_kerja'])) ?>
        </h5>
      </div>
      <div class="card-body">
        <?php if ($parent_unit): ?>
          <p class="card-text">
            This is a sub-unit of:
            <a href="<?= site_url('email/unit_kerja/' . $parent_unit['id']) ?>"><?= esc(strtoupper($parent_unit['nama_unit_kerja'])) ?></a>
          </p>
        <?php else: ?>
          <p class="card-text">Daftar akun email yang terkait dengan unit kerja ini dan semua sub-unit di bawahnya.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Child Units List -->
    <?php if (!empty($child_units)): ?>
      <div class="accordion shadow-sm mb-4" id="subUnitKerjaAccordion">
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingSubUnits">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSubUnits" aria-expanded="false" aria-controls="collapseSubUnits">
              <i class="fas fa-sitemap me-2 text-info"></i>Sub Unit Kerja
            </button>
          </h2>
          <div id="collapseSubUnits" class="accordion-collapse collapse" aria-labelledby="headingSubUnits" data-bs-parent="#subUnitKerjaAccordion">
            <div class="accordion-body">
              <div class="row">
                <?php foreach ($child_units as $child): ?>
                  <div class="col-md-6 mb-2">
                    <a href="<?= site_url('email/unit_kerja/' . $child['id']) ?>" class="list-group-item list-group-item-action">
                      <?= esc(strtoupper($child['nama_unit_kerja'])) ?>
                    </a>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>


    <!-- Search Form -->
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <form action="<?= current_url() ?>" method="get" class="row g-3 align-items-center">
          <?php 
            $isKecamatan = stripos($unit_kerja['nama_unit_kerja'], 'Kecamatan') !== false;
            $middleColClass = $isKecamatan ? 'col-md-2' : 'col-md-3';
          ?>
          <div class="col-md-3">
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-search"></i></span>
              <input type="text" class="form-control" name="search" placeholder="Search..." value="<?= esc($search ?? '') ?>">
            </div>
          </div>
          <div class="<?= $middleColClass ?>">
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-users-cog"></i></span>
              <select name="status_asn" class="form-select">
                <option value="" <?= empty($status_asn) ? 'selected' : '' ?>>All Status ASN</option>
                <?php foreach ($status_asn_options as $option): ?>
                  <option value="<?= esc($option['id']) ?>" <?= ($status_asn == $option['id']) ? 'selected' : '' ?>>
                    <?= esc($option['nama_status_asn']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="<?= $middleColClass ?>">
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
          
          <?php if ($isKecamatan): ?>
            <div class="col-md-2">
                <div class="form-check bg-light rounded p-2 border d-flex align-items-center justify-content-center">
                    <input type="hidden" name="pimpinan_desa" value="0">
                    <input class="form-check-input me-2 mt-0" type="checkbox" name="pimpinan_desa" value="1" id="pimpinanDesaCheck" <?= ($pimpinan_desa ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label user-select-none" for="pimpinanDesaCheck">
                        Pimpinan Desa
                    </label>
                </div>
            </div>
          <?php endif; ?>

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

    <!-- Email List for Unit Kerja -->
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
                  <th><i class="fas fa-user-tag me-2"></i>Status ASN / Jabatan</th>
                  <th><i class="fas fa-fingerprint me-2"></i>Status TTE</th>
                  <?php if (!empty($child_units)): ?>
                    <th><i class="fas fa-building me-2"></i>Unit Kerja</th>
                  <?php endif; ?>
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
                      <?php if (!empty($email['status_asn'])): ?>
                        <div><?= esc($email['status_asn']) ?></div>
                      <?php endif; ?>
                      <?php if (!empty($email['jabatan'])): ?>
                        <small class="text-muted"><?= esc($email['jabatan']) ?></small>
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
                    <?php if (!empty($child_units)): ?>
                      <td class="align-middle">
                        <?php if (!empty($email['parent_unit_kerja_name'])): ?>
                          <small class="d-block"><?= esc(strtoupper($email['parent_unit_kerja_name'])) ?></small>
                          <small class="d-block text-muted">
                            (<?= esc(strtoupper($email['unit_kerja_name'])) ?>)
                          </small>
                        <?php else: ?>
                          <small>
                            <?= esc(strtoupper($email['unit_kerja_name'])) ?>
                          </small>
                        <?php endif; ?>
                      </td>
                    <?php endif; ?>
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
            <h5 class="text-muted">No email accounts found for this Unit Kerja.</h5>
            <p class="text-muted">There are no email accounts currently assigned to "<?= esc(strtoupper($unit_kerja['nama_unit_kerja'])) ?>".</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Export Progress Modal -->
<div class="modal fade" id="exportProgressModal" tabindex="-1" aria-labelledby="exportProgressModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exportProgressModalLabel">Generating PDFs...</h5>
      </div>
      <div class="modal-body">
        <div class="progress mb-3">
          <div id="exportProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
        </div>
        <p id="exportStatusText" class="text-center">Starting...</p>
      </div>
    </div>
  </div>
</div>

<script>
  function openExportModal(unitId) {
    const exportModal = new bootstrap.Modal(document.getElementById('exportProgressModal'));
    const progressBar = document.getElementById('exportProgressBar');
    const statusText = document.getElementById('exportStatusText');

    exportModal.show();
    progressBar.style.width = '0%';
    progressBar.innerText = '0%';
    statusText.innerText = 'Fetching email list...';

    // First, get the list of emails to export, passing current filters
    const currentQuery = window.location.search;
    fetch(`<?= site_url('email/api_unit_emails/') ?>${unitId}${currentQuery}`)
      .then(response => response.json())
      .then(data => {
        if (!data.success) {
          statusText.innerText = 'Error: ' + (data.message || 'Failed to fetch emails.');
          return;
        }
        
        if (data.emails.length === 0) {
          statusText.innerText = 'No emails found matching the current filters.';
          return;
        }

        const emails = data.emails;
        const totalEmails = emails.length;
        let processedCount = 0;

        statusText.innerText = `Found ${totalEmails} emails. Starting PDF generation...`;

        // Process each email
        const processNext = () => {
          if (processedCount >= totalEmails) {
            statusText.innerText = 'All PDFs generated. Creating ZIP files...';
            
            fetch(`<?= site_url('email/api_download_zip/') ?>${unitId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.files.length > 0) {
                        statusText.innerText = `Created ${data.files.length} ZIP file(s). Downloading...`;
                        
                        data.files.forEach((filename, index) => {
                            setTimeout(() => {
                                const link = document.createElement('a');
                                link.href = `<?= site_url('email/download_zip_file/') ?>${filename}`;
                                link.style.display = 'none';
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                            }, index * 2500); // 2.5 second delay between downloads
                        });

                        setTimeout(() => exportModal.hide(), (data.files.length * 2500) + 1000);
                    } else {
                        statusText.innerText = 'Error creating ZIP: ' + (data.message || 'Unknown error');
                    }
                })
                .catch(error => {
                    console.error('Error zipping:', error);
                    statusText.innerText = 'Error creating ZIP file.';
                });
            return;
          }

          const email = emails[processedCount];
          const formData = new FormData();
          formData.append('unit_id', unitId);
          formData.append('email_id', email.id);


          fetch(`<?= site_url('email/api_generate_pdf') ?>`, {
            method: 'POST',
            body: new URLSearchParams(formData)
          })
          .then(response => response.json())
          .then(result => {
            processedCount++;
            const progress = Math.round((processedCount / totalEmails) * 100);
            progressBar.style.width = `${progress}%`;
            progressBar.innerText = `${progress}%`;
            statusText.innerText = `Generated PDF for ${email.name}... (${processedCount}/${totalEmails})`;
            
            // Process the next email
            setTimeout(processNext, 100); // Small delay
          })
          .catch(error => {
            console.error('Error generating PDF for:', email, error);
            statusText.innerText = `Error generating PDF for ${email.name}.`;
            // Decide if you want to stop or continue
            setTimeout(processNext, 100);
          });
        };

        processNext();
      })
      .catch(error => {
        console.error('Error fetching email list:', error);
        statusText.innerText = 'Error fetching email list.';
      });
  }

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
      let sourceText = ''; // Removed fromDb text

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