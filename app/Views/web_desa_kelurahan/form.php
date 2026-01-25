<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-globe me-2"></i><?= $title ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h6 class="fw-bold text-muted">Entity: <?= esc($website['kecamatan']) ?> - <?= esc($website['desa_kelurahan']) ?></h6>
                </div>

                <?php $action = site_url('web_desa_kelurahan/update/' . $website['id']); ?>
                <form action="<?= $action ?>" method="POST" id="websiteForm">
                    <?= csrf_field() ?>
                    
                    <div class="mb-3">
                        <label for="domain" class="form-label">Domain</label>
                        <input type="text" class="form-control" id="domain" name="domain" value="<?= esc($website['domain']) ?>">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="AKTIF" <?= strtoupper($website['status']) === 'AKTIF' ? 'selected' : '' ?>>AKTIF</option>
                                <option value="NONAKTIF" <?= (strtoupper($website['status']) === 'NONAKTIF' || strtoupper($website['status']) === 'NON AKTIF' || strtoupper($website['status']) === 'TIDAK AKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="dikelola_kominfo" class="form-label">Dikelola Kominfo?</label>
                            <select class="form-select" id="dikelola_kominfo" name="dikelola_kominfo">
                                <option value="YA" <?= strtoupper($website['dikelola_kominfo']) === 'YA' ? 'selected' : '' ?>>YA</option>
                                <option value="TIDAK" <?= strtoupper($website['dikelola_kominfo']) === 'TIDAK' ? 'selected' : '' ?>>TIDAK</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="platform_id" class="form-label">Platform</label>
                        <select class="form-select" id="platform_id" name="platform_id">
                            <option value="">Select Platform</option>
                            <?php foreach ($platforms as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ($website['platform_id'] == $p['id']) ? 'selected' : '' ?>>
                                    <?= esc($p['nama_platform']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?= esc($website['keterangan']) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= site_url('web_desa_kelurahan') ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <i class="fas fa-save me-2"></i>Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('websiteForm').addEventListener('submit', function() {
        var btn = document.getElementById('saveBtn');
        if (btn.disabled) return;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
    });
</script>
<?= $this->endSection() ?>