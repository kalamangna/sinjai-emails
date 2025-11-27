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
        <a href="<?= site_url('email/export_perjanjian_kerja_pdf/' . $unit_kerja['id']) ?>" class="btn btn-info">
          <i class="fas fa-file-contract me-2"></i>Export Perjanjian Kerja PDF
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


    <!-- Search Form -->
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <form action="<?= current_url() ?>" method="get" class="row g-3 align-items-center">
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-search"></i></span>
              <input type="text" class="form-control" name="search" placeholder="Search by email or name..." value="<?= esc($search ?? '') ?>">
            </div>
          </div>
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-id-card"></i></span>
              <input type="text" class="form-control" id="nik" name="nik" placeholder="Enter NIK..." value="<?= esc($nik ?? '') ?>">
            </div>
          </div>
          <div class="col-md-12 mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="<?= current_url() ?>" class="btn btn-outline-secondary">
              <i class="fas fa-sync-alt me-2"></i>Reset
            </a>
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
                  <th><i class="fas fa-user me-2"></i>Name</th>
                  <th><i class="fas fa-id-card me-2"></i>NIK</th>
                  <?php if (!empty($child_units)): ?>
                    <th><i class="fas fa-building me-2"></i>Unit Kerja</th>
                  <?php endif; ?>
                  <th class="text-center"><i class="fas fa-cog me-2"></i>Action</th>
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
                          <small class="text-muted"><?= esc($email['domain']) ?></small>
                        </div>
                      </div>
                    </td>
                    <td class="align-middle"><?= esc($email['name']) ?></td>
                    <td class="align-middle"><?= esc($email['nik']) ?></td>
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
<?= $this->endSection() ?>