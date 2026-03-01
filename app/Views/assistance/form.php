<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<!-- Choices.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<style>
    .choices__inner {
        @apply bg-white border-gray-200 rounded-lg text-sm font-medium text-gray-800 !important;
        min-height: 38px !important;
        padding: 4px 12px !important;
    }

    .choices__list--dropdown {
        @apply bg-white border-gray-200 rounded-lg shadow-xl text-gray-800 !important;
    }

    .choices__list--dropdown .choices__item--selectable.is-highlighted {
        @apply bg-gray-100 !important;
    }

    .choices__input {
        @apply bg-transparent text-sm text-gray-800 !important;
    }

    .choices__placeholder {
        @apply text-gray-700 opacity-100 !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-tight"><?= esc($title) ?></h1>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="p-8">
            <form action="<?= isset($activity) ? site_url('assistance/update/' . $activity['id']) : site_url('assistance/store') ?>" method="post" class="space-y-6">
                <?= csrf_field() ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="tanggal_kegiatan" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Tanggal</label>
                        <input type="date" class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm font-medium text-gray-800 transition-all" id="tanggal_kegiatan" name="tanggal_kegiatan"
                            value="<?= isset($activity) ? $activity['tanggal_kegiatan'] : formatIsiInput('now') ?>" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Metode</label>
                        <div class="flex items-center gap-6 h-[38px]">
                            <label class="inline-flex items-center cursor-pointer group">
                                <input type="radio" class="w-4 h-4 text-emerald-700 border-gray-200 focus:ring-emerald-700" name="method" value="Online"
                                    <?= (isset($activity) && $activity['method'] == 'Online') ? 'checked' : '' ?> required>
                                <span class="ml-2 text-sm text-gray-700 group-hover:text-gray-800 transition-colors">Online</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer group">
                                <input type="radio" class="w-4 h-4 text-emerald-700 border-gray-200 focus:ring-emerald-700" name="method" value="Offline"
                                    <?= (isset($activity) && $activity['method'] == 'Offline') ? 'checked' : ((!isset($activity)) ? 'checked' : '') ?>>
                                <span class="ml-2 text-sm text-gray-700 group-hover:text-gray-800 transition-colors">Offline</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="agency_info" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Unit Kerja</label>
                    <select id="agency_info" name="agency_info" required>
                        <option value="">Pilih Unit Kerja...</option>
                        <?php
                        $groups = [];
                        foreach ($agencies as $agency) {
                            $groups[$agency->group][] = $agency;
                        }
                        foreach ($groups as $groupName => $items): ?>
                            <optgroup label="<?= strtoupper($groupName) ?>">
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Kategori</label>
                        <select class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm font-medium text-gray-800 appearance-none cursor-pointer transition-all" id="category" name="category" onchange="updateServicesDropdown()" required>
                            <option value="">Pilih Kategori...</option>
                            <?php foreach ($categoryMap as $id => $label): ?>
                                <option value="<?= $id ?>" <?= (isset($activity) && $activity['category'] == $id) ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="service" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Layanan</label>
                        <select class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm font-medium text-gray-800 appearance-none cursor-pointer transition-all" id="service" name="service" onchange="updateKeteranganOptions()" required>
                            <option value="">Pilih Layanan...</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Keterangan</label>
                    <select class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm font-medium text-gray-800 appearance-none cursor-pointer transition-all" id="keterangan" name="keterangan" required>
                        <option value="">Pilih Keterangan...</option>
                    </select>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t border-gray-100">
                    <a href="<?= site_url('assistance') ?>" class="w-full sm:w-auto px-6 py-2 bg-white border border-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-50 transition-all text-xs uppercase tracking-widest no-underline flex items-center justify-center shadow-sm">
                        <i class="fas fa-times mr-2 text-gray-700"></i> Batal
                    </a>
                    <button type="submit" class="w-full sm:w-auto px-8 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-lg shadow-sm transition-all text-xs uppercase tracking-widest flex items-center justify-center">
                        <i class="fas fa-save mr-2 text-white/80"></i> <?= isset($activity) ? 'Update Log' : 'Simpan Log' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
    const servicesMap = <?= json_encode($servicesMap) ?>;
    const keteranganByServiceMap = <?= json_encode($keteranganByServiceMap) ?>;

    const initialCategory = "<?= isset($activity) ? esc($activity['category']) : '' ?>";
    const initialService = "<?= isset($activity) && !empty($activity['services']) ? esc($activity['services'][0]) : '' ?>";
    const initialKeterangan = "<?= isset($activity) ? esc($activity['keterangan']) : '' ?>";

    function updateServicesDropdown() {
        const category = document.getElementById('category').value;
        const serviceSelect = document.getElementById('service');
        const currentService = serviceSelect.value || initialService;
        serviceSelect.innerHTML = '<option value="">Pilih Layanan...</option>';
        if (category && servicesMap[category]) {
            servicesMap[category].forEach(svc => {
                const opt = document.createElement('option');
                opt.value = svc;
                opt.textContent = svc;
                if (svc === currentService) opt.selected = true;
                serviceSelect.appendChild(opt);
            });
        }
        updateKeteranganOptions();
    }

    function updateKeteranganOptions() {
        const service = document.getElementById('service').value;
        const keteranganSelect = document.getElementById('keterangan');
        const currentKeterangan = keteranganSelect.value || initialKeterangan;
        keteranganSelect.innerHTML = '<option value="">Pilih Keterangan...</option>';
        if (service && keteranganByServiceMap[service]) {
            keteranganByServiceMap[service].forEach(opt => {
                const o = document.createElement('option');
                o.value = opt;
                o.textContent = opt;
                if (opt === currentKeterangan) o.selected = true;
                keteranganSelect.appendChild(o);
            });
        }
        const l = document.createElement('option');
        l.value = 'Lainnya';
        l.textContent = 'Lainnya';
        if (currentKeterangan === 'Lainnya') l.selected = true;
        keteranganSelect.appendChild(l);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const agencySelect = document.getElementById('agency_info');
        if (agencySelect) {
            new Choices(agencySelect, {
                searchEnabled: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: 'Pilih Unit Kerja...',
                searchPlaceholderValue: 'Cari Unit Kerja...',
                shouldSort: false
            });
        }
        if (document.getElementById('category').value) updateServicesDropdown();
    });
</script>
<?= $this->endSection() ?>