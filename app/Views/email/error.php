<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card shadow-sm text-center border-danger">
      <div class="card-header bg-danger text-white">
        <h5 class="card-title mb-0">
          <i class="fas fa-exclamation-triangle me-2"></i>An Error Occurred
        </h5>
      </div>
      <div class="card-body py-5">
        <p class="card-text fs-5 text-danger">
          <?= esc($error ?? 'An unknown error occurred.') ?>
        </p>
        <?php if (!empty($back_url)): ?>
          <a href="javascript:void(0);" onclick="history.back();" class="btn btn-primary mt-4">
            <i class="fas fa-arrow-left me-2"></i>Go Back
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
