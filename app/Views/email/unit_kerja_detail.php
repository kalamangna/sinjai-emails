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
        <a href="<?= site_url('email/export_unit_kerja_csv/' . $unit_kerja['id']) ?>" class="btn btn-success">
          <i class="fas fa-file-csv me-2"></i>Export CSV
        </a>
        <a href="<?= site_url('email/export_unit_kerja_pdf/' . $unit_kerja['id']) ?>" class="btn btn-danger">
          <i class="fas fa-file-pdf me-2"></i>Export PDF
        </a>
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
                  <th><i class="fas fa-user me-2"></i>Name</th>
                  <?php if (!empty($child_units)): ?>
                    <th><i class="fas fa-building me-2"></i>Unit Kerja</th>
                  <?php endif; ?>
                  <th class="text-center"><i class="fas fa-chart-pie me-2"></i>Disk Usage</th>
                  <th class="text-center"><i class="fas fa-cog me-2"></i>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($emails as $email): ?>
                  <?php
                  $is_unlimited = ($email['diskquota'] ?? 0) == 0 || ($email['humandiskquota'] ?? '') == 'none' || ($email['humandiskquota'] ?? '') == 'unlimited';
                  $disk_used = $email['humandiskused'] ?? '0 KB';
                  $disk_quota = $is_unlimited ? '<i class="fas fa-infinity text-info"></i>' : ($email['humandiskquota'] ?? '0 GB');
                  $usage_percent = $email['diskusedpercent_float'] ?? 0;

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
                    <td class="align-middle"><?= esc($email['name']) ?></td>
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
                      <div class="d-flex flex-column align-items-center">
                        <?php if ($is_unlimited): ?>
                          <div class="progress w-100 mb-1" style="height: 8px; max-width: 120px;">
                            <div class="progress-bar <?= $progress_class ?> progress-bar-striped progress-bar-animated"
                              role="progressbar" style="width: 100%">
                            </div>
                          </div>
                          <small class="text-muted">
                            <?= $disk_used ?> / <span class="text-success fw-bold"><?= $disk_quota ?></span>
                            <br>
                            <span class="fw-bold text-info">Unlimited</span>
                          </small>
                        <?php else: ?>
                          <div class="progress w-100 mb-1" style="height: 8px; max-width: 120px;">
                            <div class="progress-bar <?= $progress_class ?>" role="progressbar"
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
                      <a href="<?= site_url('email/detail/' . $email['user']) ?>"
                        class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i>
                      </a>
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
<?= $this->endSection() ?>