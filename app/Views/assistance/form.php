<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<!-- Choices.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<style>
    .choices__inner {
        background-color: #ffffff !important;
        border-radius: 0.5rem !important;
        border: 1px solid #e2e8f0 !important;
        min-height: 42px !important;
        padding: 4px 12px !important;
        color: #1e293b !important;
        font-size: 14px !important;
        font-weight: 500 !important;
    }

    .choices__list--dropdown {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.5rem !important;
        color: #1e293b !important;
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
    }

    .choices__list--dropdown .choices__item--selectable.is-highlighted {
        background-color: #f8fafc !important;
    }

    .choices__input {
        background-color: transparent !important;
        color: #1e293b !important;
        font-size: 14px !important;
    }

    .choices__placeholder {
        opacity: 1;
        color: #94a3b8 !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="flex justify-center">
    <div class="w-full max-w-2xl">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-slate-50 px-8 py-6 border-b border-slate-200">
                <h5 class="text-xs font-bold text-slate-700 uppercase tracking-widest flex items-center">
                    <i class="fas fa-edit mr-3 text-blue-500 opacity-60"></i><?= esc($title) ?>
                </h5>
            </div>
            <div class="p-8">
                <form action="<?= isset($activity) ? site_url('assistance/update/' . $activity['id']) : site_url('assistance/store') ?>" method="post" class="space-y-6">
                    <?= csrf_field() ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="tanggal_kegiatan" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Tanggal</label>
                            <input type="date" class="block w-full px-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium text-slate-700 transition-all" id="tanggal_kegiatan" name="tanggal_kegiatan"
                                value="<?= isset($activity) ? $activity['tanggal_kegiatan'] : date('Y-m-d') ?>" required>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Metode</label>
                            <div class="flex items-center gap-6 h-[42px]">
                                <label class="inline-flex items-center cursor-pointer group">
                                    <input type="radio" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500" name="method" value="Online"
                                        <?= (isset($activity) && $activity['method'] == 'Online') ? 'checked' : '' ?> required>
                                    <span class="ml-2.5 text-xs font-bold text-slate-500 group-hover:text-slate-900 transition-colors uppercase tracking-wider">Online</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer group">
                                    <input type="radio" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500" name="method" value="Offline"
                                        <?= (isset($activity) && $activity['method'] == 'Offline') ? 'checked' : ((!isset($activity)) ? 'checked' : '') ?>>
                                    <span class="ml-2.5 text-xs font-bold text-slate-500 group-hover:text-slate-900 transition-colors uppercase tracking-wider">Offline</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="agency_info" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Unit Kerja</label>
                        <select id="agency_info" name="agency_info" required>
                            <option value="">-- PILIH UNIT KERJA --</option>
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
                            <label for="category" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Kategori</label>
                            <select class="block w-full px-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium text-slate-700 transition-all cursor-pointer" id="category" name="category" onchange="updateServicesDropdown()" required>
                                <option value="">-- PILIH KATEGORI --</option>
                                <?php foreach ($categoryMap as $id => $label): ?>
                                    <option value="<?= $id ?>" <?= (isset($activity) && $activity['category'] == $id) ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="service" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Layanan</label>
                            <select class="block w-full px-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium text-slate-700 transition-all cursor-pointer" id="service" name="service" onchange="updateKeteranganOptions()" required>
                                <option value="">-- PILIH LAYANAN --</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="keterangan" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Keterangan</label>
                        <select class="block w-full px-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium text-slate-700 transition-all cursor-pointer" id="keterangan" name="keterangan" required>
                            <option value="">-- PILIH KETERANGAN --</option>
                        </select>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t border-slate-100">
                        <a href="<?= site_url('assistance') ?>" class="w-full sm:w-auto px-6 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-lg hover:bg-slate-50 hover:text-slate-900 transition-all text-xs uppercase tracking-widest no-underline flex items-center justify-center shadow-sm">
                            <i class="fas fa-arrow-left mr-2"></i> Batal
                        </a>
                        <button type="submit" class="w-full sm:w-auto px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm transition-all text-xs uppercase tracking-widest flex items-center justify-center">
                            <i class="fas fa-save mr-2"></i> Simpan
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
        serviceSelect.innerHTML = '<option value="">-- PILIH LAYANAN --</option>';
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
        keteranganSelect.innerHTML = '<option value="">-- PILIH KETERANGAN --</option>';
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
                placeholderValue: '-- PILIH UNIT KERJA --',
                searchPlaceholderValue: 'CARI UNIT KERJA...',
                shouldSort: false
            });
        }
        if (document.getElementById('category').value) updateServicesDropdown();
    });
</script>
<?= $this->endSection() ?>