<?= $this->extend('templates/layout') ?>

<?= $this->section('styles') ?>
<!-- Choices.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<style>
    .choices__inner {
        background-color: #fff;
        border-radius: 0.375rem;
        border: 1px solid #dee2e6;
        min-height: 38px;
    }

    .choices__list--single {
        padding: 2px 16px 2px 4px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><?= esc($title) ?></h5>
            </div>
            <div class="card-body">
                <form action="<?= isset($activity) ? site_url('assistance/update/' . $activity['id']) : site_url('assistance/store') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="tanggal_kegiatan" class="form-label">Tanggal Kegiatan</label>
                        <input type="date" class="form-control" id="tanggal_kegiatan" name="tanggal_kegiatan"
                            value="<?= isset($activity) ? $activity['tanggal_kegiatan'] : date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="agency_info" class="form-label">Unit Kerja</label>
                        <select class="form-select" id="agency_info" name="agency_info" required>
                            <option value="">Pilih Unit Kerja...</option>
                            <?php
                            $groups = [];
                            foreach ($agencies as $agency) {
                                $groups[$agency->group][] = $agency;
                            }
                            ?>

                            <?php foreach ($groups as $groupName => $items): ?>
                                <optgroup label="<?= $groupName ?>">
                                    <?php foreach ($items as $item): ?>
                                        <?php
                                        $selected = '';
                                        if (isset($activity)) {
                                            if ($activity['agency_id'] == explode('|', $item->value)[1] && $activity['agency_type'] == explode('|', $item->value)[0]) {
                                                $selected = 'selected';
                                            }
                                        }
                                        ?>
                                        <option value="<?= $item->value ?>" <?= $selected ?>><?= esc($item->label) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Metode</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="method" id="methodOnline" value="Online"
                                    <?= (isset($activity) && $activity['method'] == 'Online') ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="methodOnline">Online</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="method" id="methodOffline" value="Offline"
                                    <?= (isset($activity) && $activity['method'] == 'Offline') ? 'checked' : ((!isset($activity)) ? 'checked' : '') ?>>
                                <label class="form-check-label" for="methodOffline">Offline</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Kategori</label>
                        <select class="form-select" id="category" name="category" onchange="updateServicesDropdown()" required>
                            <option value="">Pilih Kategori...</option>
                            <?php foreach ($categoryMap as $id => $label): ?>
                                <option value="<?= $id ?>" <?= (isset($activity) && $activity['category'] == $id) ? 'selected' : '' ?>>
                                    <?= esc($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="service" class="form-label">Layanan</label>
                        <select class="form-select" id="service" name="service" onchange="updateKeteranganOptions()" required>
                            <option value="">Pilih Layanan...</option>
                            <!-- Options will be populated by JS -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <select class="form-select" id="keterangan" name="keterangan" required>
                            <option value="">Pilih Keterangan...</option>
                            <!-- Options will be populated by JS -->
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= site_url('assistance') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">Simpan Kegiatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
    const servicesMap = <?= json_encode($servicesMap) ?>;
    const keteranganByServiceMap = <?= json_encode($keteranganByServiceMap) ?>;

    // Handle initial values from PHP
    const initialCategory = "<?= isset($activity) ? esc($activity['category']) : '' ?>";
    const initialService = "<?= isset($activity) && !empty($activity['services']) ? esc($activity['services'][0]) : '' ?>";
    const initialKeterangan = "<?= isset($activity) ? esc($activity['keterangan']) : '' ?>";

    function updateServicesDropdown() {
        const category = document.getElementById('category').value;
        const serviceSelect = document.getElementById('service');
        const currentService = serviceSelect.value || initialService; // Preserve selection or use initial

        // Clear current options
        serviceSelect.innerHTML = '<option value="">Pilih Layanan...</option>';

        if (category && servicesMap[category]) {
            servicesMap[category].forEach(svc => {
                const option = document.createElement('option');
                option.value = svc;
                option.textContent = svc;
                // Auto-select if matches current or initial
                if (svc === currentService) {
                    option.selected = true;
                }
                serviceSelect.appendChild(option);
            });
        }

        // Trigger update for Keterangan
        updateKeteranganOptions();
    }

    function updateKeteranganOptions() {
        const service = document.getElementById('service').value;
        const keteranganSelect = document.getElementById('keterangan');
        const currentKeterangan = keteranganSelect.value || initialKeterangan;

        // Clear current options
        keteranganSelect.innerHTML = '<option value="">Pilih Keterangan...</option>';

        if (service && keteranganByServiceMap[service]) {
            keteranganByServiceMap[service].forEach(opt => {
                const option = document.createElement('option');
                option.value = opt;
                option.textContent = opt;
                if (opt === currentKeterangan) {
                    option.selected = true;
                }
                keteranganSelect.appendChild(option);
            });
        }

        // Always add "Lainnya"
        const lainnyaOpt = document.createElement('option');
        lainnyaOpt.value = 'Lainnya';
        lainnyaOpt.textContent = 'Lainnya';
        if (currentKeterangan === 'Lainnya') {
            lainnyaOpt.selected = true;
        }
        keteranganSelect.appendChild(lainnyaOpt);
    }

    // Initial update on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Choices.js for Agency selection
        const agencySelect = document.getElementById('agency_info');
        if (agencySelect) {
            new Choices(agencySelect, {
                searchEnabled: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: 'Pilih Unit Kerja...',
                searchPlaceholderValue: 'Cari Unit Kerja...',
                shouldSort: false,
            });
        }
        // If category is already selected (edit mode), trigger updates
        if (document.getElementById('category').value) {
            updateServicesDropdown();
        }
    });
</script><?= $this->endSection() ?>