<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<style>
    .choices__inner { @apply bg-white border-slate-200 rounded-lg text-sm font-medium text-slate-800 !important; min-height: 38px !important; padding: 4px 12px !important; }
    .choices__list--dropdown { @apply bg-white border-slate-200 rounded-lg shadow-xl text-slate-800 !important; }
    .choices__list--dropdown .choices__item--selectable.is-highlighted { @apply bg-slate-100 !important; }
    .choices__input { @apply bg-transparent text-sm text-slate-800 !important; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto space-y-6 pb-12 pt-8">
    <div class="text-center space-y-2">
        <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Helpdesk Layanan</h1>
        <p class="text-sm font-medium text-slate-500 uppercase tracking-widest">Pusat Bantuan & Laporan Kendala TIK Kab. Sinjai</p>
    </div>

    <?php if (session()->has('errors')): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Terjadi Kesalahan!</strong>
            <ul class="list-disc pl-5 mt-1">
                <?php foreach (session('errors') as $error): ?>
                    <li class="text-sm"><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
        <div class="p-8">
            <form action="<?= site_url('helpdesk/submit') ?>" method="post" class="space-y-6">
                <?= csrf_field() ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nama_pemohon" class="block text-[10px] font-bold text-slate-700 mb-1.5 uppercase tracking-widest">Nama Lengkap</label>
                        <input type="text" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 text-sm font-medium text-slate-800 transition-all uppercase" id="nama_pemohon" name="nama_pemohon" value="<?= old('nama_pemohon') ?>" required placeholder="Contoh: Budi Santoso, S.Kom">
                    </div>
                    <div>
                        <label for="nip_pemohon" class="block text-[10px] font-bold text-slate-700 mb-1.5 uppercase tracking-widest">NIP / NIK <span class="text-slate-400 font-normal">(Opsional)</span></label>
                        <input type="text" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 text-sm font-medium text-slate-800 transition-all font-mono" id="nip_pemohon" name="nip_pemohon" value="<?= old('nip_pemohon') ?>" placeholder="19800101...">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="kontak_whatsapp" class="block text-[10px] font-bold text-slate-700 mb-1.5 uppercase tracking-widest">Nomor WhatsApp Aktif</label>
                        <input type="text" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 text-sm font-medium text-slate-800 transition-all font-mono" id="kontak_whatsapp" name="kontak_whatsapp" value="<?= old('kontak_whatsapp') ?>" required placeholder="08123456789">
                    </div>
                    <div>
                        <label for="category" class="block text-[10px] font-bold text-slate-700 mb-1.5 uppercase tracking-widest">Kategori Layanan</label>
                        <select class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 text-sm font-medium text-slate-800 cursor-pointer transition-all" id="category" name="category" onchange="updateServicesDropdown()" required>
                            <option value="">Pilih Kategori...</option>
                            <?php foreach ($categoryMap as $id => $label): ?>
                                <option value="<?= $id ?>" <?= old('category') == $id ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="service" class="block text-[10px] font-bold text-slate-700 mb-1.5 uppercase tracking-widest">Layanan Spesifik</label>
                        <select class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 text-sm font-medium text-slate-800 cursor-pointer transition-all" id="service" name="service" onchange="updateKeteranganOptions()" required>
                            <option value="">Pilih Layanan...</option>
                        </select>
                    </div>
                    <div>
                        <label for="kategori_layanan" class="block text-[10px] font-bold text-slate-700 mb-1.5 uppercase tracking-widest">Jenis Kendala / Bantuan</label>
                        <select class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 text-sm font-medium text-slate-800 cursor-pointer transition-all" id="kategori_layanan" name="kategori_layanan" required>
                            <option value="">Pilih Kendala...</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="agency_info" class="block text-[10px] font-bold text-slate-700 mb-1.5 uppercase tracking-widest">Unit Kerja / Instansi</label>
                    <select id="agency_info" name="agency_info" required>
                        <option value="">Pilih Unit Kerja Anda...</option>
                        <?php
                        $groups = [];
                        foreach ($agencies as $agency) {
                            $groups[$agency->group][] = $agency;
                        }
                        foreach ($groups as $groupName => $items): ?>
                            <optgroup label="<?= strtoupper($groupName) ?>">
                                <?php foreach ($items as $item): ?>
                                    <option value="<?= $item->value ?>" <?= old('agency_info') == $item->value ? 'selected' : '' ?>><?= esc($item->label) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="pt-6 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="w-full sm:w-auto btn btn-solid !px-8">
                        <i class="fas fa-paper-plane mr-2 text-white/80"></i> Kirim Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
    const servicesMap = <?= json_encode($servicesMap) ?>;
    const keteranganByServiceMap = <?= json_encode($keteranganByServiceMap) ?>;

    const oldCategory = "<?= old('category') ?>";
    const oldService = "<?= old('service') ?>";
    const oldKeterangan = "<?= old('kategori_layanan') ?>";

    function updateServicesDropdown() {
        const category = document.getElementById('category').value;
        const serviceSelect = document.getElementById('service');
        const currentService = serviceSelect.value || oldService;
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
        const keteranganSelect = document.getElementById('kategori_layanan');
        const currentKeterangan = keteranganSelect.value || oldKeterangan;
        keteranganSelect.innerHTML = '<option value="">Pilih Kendala...</option>';
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
                shouldSort: false
            });
        }
        if (document.getElementById('category').value) updateServicesDropdown();
    });
</script>
<?= $this->endSection() ?>
