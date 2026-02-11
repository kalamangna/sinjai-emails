<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<!-- Choices.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<style>
    .choices__inner {
        background-color: #020617 !important; /* bg-slate-950 */
        border-radius: 1rem !important;
        border: 1px solid #1e293b !important; /* border-slate-800 */
        min-height: 48px !important;
        padding: 6px 12px !important;
        color: #f1f5f9 !important; /* text-slate-100 */
    }
    .choices__list--dropdown {
        background-color: #0f172a !important; /* bg-slate-900 */
        border: 1px solid #1e293b !important;
        border-radius: 1rem !important;
        color: #f1f5f9 !important;
    }
    .choices__list--dropdown .choices__item--selectable.is-highlighted {
        background-color: #1e293b !important; /* bg-slate-800 */
    }
    .choices__input {
        background-color: transparent !important;
        color: #f1f5f9 !important;
    }
    .choices__placeholder {
        opacity: 0.5;
        color: #94a3b8 !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="flex justify-center">
    <div class="w-full max-w-2xl">
        <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
            <div class="bg-slate-800/30 px-10 py-8 border-b border-slate-800">
                <h5 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                    <i class="fas fa-edit mr-3 text-blue-500 opacity-50"></i><?= esc($title) ?>
                </h5>
            </div>
            <div class="p-10">
                <form action="<?= isset($activity) ? site_url('assistance/update/' . $activity['id']) : site_url('assistance/store') ?>" method="post" class="space-y-8">
                    <?= csrf_field() ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label for="tanggal_kegiatan" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Tanggal Kegiatan</label>
                            <input type="date" class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all uppercase" id="tanggal_kegiatan" name="tanggal_kegiatan"
                                value="<?= isset($activity) ? $activity['tanggal_kegiatan'] : date('Y-m-d') ?>" required>
                        </div>

                        <div>
                            <label class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Metode Pendampingan</label>
                            <div class="flex items-center gap-6 h-[48px]">
                                <label class="inline-flex items-center cursor-pointer group">
                                    <input type="radio" class="w-4 h-4 text-blue-600 bg-slate-950 border-slate-800 focus:ring-blue-500 focus:ring-offset-slate-900" name="method" value="Online"
                                        <?= (isset($activity) && $activity['method'] == 'Online') ? 'checked' : '' ?> required>
                                    <span class="ml-3 text-xs font-bold text-slate-400 group-hover:text-slate-200 transition-colors uppercase tracking-widest">Online</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer group">
                                    <input type="radio" class="w-4 h-4 text-blue-600 bg-slate-950 border-slate-800 focus:ring-blue-500 focus:ring-offset-slate-900" name="method" value="Offline"
                                        <?= (isset($activity) && $activity['method'] == 'Offline') ? 'checked' : ((!isset($activity)) ? 'checked' : '') ?>>
                                    <span class="ml-3 text-xs font-bold text-slate-400 group-hover:text-slate-200 transition-colors uppercase tracking-widest">Offline</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="agency_info" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Unit Kerja / Agency</label>
                        <select id="agency_info" name="agency_info" required>
                            <option value="">PILIH UNIT KERJA...</option>
                            <?php
                            $groups = [];
                            foreach ($agencies as $agency) { $groups[$agency->group][] = $agency; }
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
                                        <option value="<?= $item->value ?>" <?= $selected ?>><?= esc(strtoupper($item->label)) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label for="category" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Kategori Utama</label>
                            <select class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all" id="category" name="category" onchange="updateServicesDropdown()" required>
                                <option value="">PILIH KATEGORI...</option>
                                <?php foreach ($categoryMap as $id => $label): ?>
                                    <option value="<?= $id ?>" <?= (isset($activity) && $activity['category'] == $id) ? 'selected' : '' ?>><?= esc(strtoupper($label)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="service" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Jenis Layanan</label>
                            <select class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all" id="service" name="service" onchange="updateKeteranganOptions()" required>
                                <option value="">PILIH LAYANAN...</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="keterangan" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Keterangan / Tindakan</label>
                        <select class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all" id="keterangan" name="keterangan" required>
                            <option value="">PILIH KETERANGAN...</option>
                        </select>
                    </div>

                    <div class="flex flex-col md:flex-row justify-between items-center gap-4 pt-8 border-t border-slate-800">
                        <a href="<?= site_url('assistance') ?>" class="w-full md:w-auto px-10 py-4 bg-slate-800 hover:bg-slate-700 text-slate-300 font-black rounded-2xl shadow-sm transition-all text-[10px] uppercase tracking-widest no-underline flex items-center justify-center">
                            <i class="fas fa-arrow-left mr-3"></i> Kembali
                        </a>
                        <button type="submit" class="w-full md:w-auto px-10 py-4 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-2xl shadow-xl shadow-blue-900/20 transition-all text-[10px] uppercase tracking-widest flex items-center justify-center">
                            Simpan Log Kegiatan
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
        serviceSelect.innerHTML = '<option value="">PILIH LAYANAN...</option>';
        if (category && servicesMap[category]) {
            servicesMap[category].forEach(svc => {
                const opt = document.createElement('option');
                opt.value = svc; opt.textContent = svc.toUpperCase();
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
        keteranganSelect.innerHTML = '<option value="">PILIH KETERANGAN...</option>';
        if (service && keteranganByServiceMap[service]) {
            keteranganByServiceMap[service].forEach(opt => {
                const o = document.createElement('option');
                o.value = opt; o.textContent = opt.toUpperCase();
                if (opt === currentKeterangan) o.selected = true;
                keteranganSelect.appendChild(o);
            });
        }
        const l = document.createElement('option');
        l.value = 'Lainnya'; l.textContent = 'LAINNYA';
        if (currentKeterangan === 'Lainnya') l.selected = true;
        keteranganSelect.appendChild(l);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const agencySelect = document.getElementById('agency_info');
        if (agencySelect) {
            new Choices(agencySelect, { searchEnabled: true, itemSelectText: '', placeholder: true, placeholderValue: 'PILIH UNIT KERJA...', searchPlaceholderValue: 'CARI UNIT KERJA...', shouldSort: false });
        }
        if (document.getElementById('category').value) updateServicesDropdown();
    });
</script>
<?= $this->endSection() ?>