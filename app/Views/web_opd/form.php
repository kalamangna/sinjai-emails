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
                <?php 
                    $isEdit = isset($website['id']);
                    $action = $isEdit ? site_url('web_opd/update/' . $website['id']) : site_url('web_opd/store'); 
                ?>
                <form action="<?= $action ?>" method="POST" id="websiteForm">
                    <?= csrf_field() ?>
                    
                    <div class="mb-3">
                        <label for="unit_kerja_id" class="form-label">Unit Kerja (OPD)</label>
                        <select class="form-select" id="unit_kerja_id" name="unit_kerja_id" required>
                            <option value="">Select Unit Kerja</option>
                            <?php foreach ($unit_kerja as $uk): ?>
                                <option value="<?= $uk['id'] ?>" <?= (isset($website['unit_kerja_id']) && $website['unit_kerja_id'] == $uk['id']) ? 'selected' : '' ?>>
                                    <?= esc(strtoupper($uk['nama_unit_kerja'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="domain" class="form-label">Domain</label>
                        <input type="text" class="form-control" id="domain" name="domain" value="<?= esc($website['domain'] ?? '') ?>" placeholder="e.g. dinas.sinjaikab.go.id">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="AKTIF" <?= (isset($website['status']) && strtoupper($website['status']) === 'AKTIF') ? 'selected' : '' ?>>AKTIF</option>
                                <option value="NONAKTIF" <?= (isset($website['status']) && strtoupper($website['status']) === 'NONAKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="dikelola_kominfo" class="form-label">Dikelola Kominfo?</label>
                            <select class="form-select" id="dikelola_kominfo" name="dikelola_kominfo">
                                <option value="YA" <?= (isset($website['dikelola_kominfo']) && strtoupper($website['dikelola_kominfo']) === 'YA') ? 'selected' : '' ?>>YA</option>
                                <option value="TIDAK" <?= (isset($website['dikelola_kominfo']) && strtoupper($website['dikelola_kominfo']) === 'TIDAK') ? 'selected' : '' ?>>TIDAK</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="platform_id" class="form-label">Platform</label>
                        <select class="form-select" id="platform_id" name="platform_id">
                            <option value="">Select Platform</option>
                            <?php foreach ($platforms as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= (isset($website['platform_id']) && $website['platform_id'] == $p['id']) ? 'selected' : '' ?>>
                                    <?= esc($p['nama_platform']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?= esc($website['keterangan'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= site_url('web_opd') ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <i class="fas fa-save me-2"></i>Save Website
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
