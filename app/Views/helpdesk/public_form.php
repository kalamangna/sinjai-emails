<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="robots" content="<?= $meta_robots ?? 'noindex, follow' ?>">
    <title><?= $title ?? 'Helpdesk' ?> | Sistem Identitas Digital</title>

    <meta name="description" content="<?= $meta_description ?? 'Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai' ?>">
    <link rel="canonical" href="<?= current_url() ?>">

    <!-- Meta Tags -->
    <meta property="og:site_name" content="Sistem Identitas Digital Sinjai">
    <meta property="og:title" content="<?= $title ?? 'Helpdesk' ?> | Sistem Identitas Digital">
    <meta property="og:description" content="<?= $meta_description ?? 'Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai' ?>">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:image" content="<?= $meta_image ?? base_url('meta.png') ?>">
    <meta property="og:type" content="<?= $meta_type ?? 'website' ?>">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title ?? 'Helpdesk' ?> | Sistem Identitas Digital">
    <meta name="twitter:description" content="<?= $meta_description ?? 'Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai' ?>">
    <meta name="twitter:image" content="<?= $meta_image ?? base_url('meta.png') ?>">

    <link rel="icon" type="image/png" href="<?= base_url('logo.png') ?>">
    <link href="<?= base_url('css/output.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Choices.js customization to match rounded-lg outline slate inputs */
        .choices {
            margin-bottom: 0 !important;
        }
        .choices__inner { 
            background-color: white !important; 
            border: 1px solid rgb(226, 232, 240) !important; 
            border-radius: 0.5rem !important; 
            font-size: 0.875rem !important; 
            font-weight: 500 !important; 
            color: rgb(30, 41, 59) !important; 
            min-height: 38px !important; 
            padding: 4px 12px !important; 
            display: flex;
            align-items: center;
            transition: all 0.2s ease-in-out;
        }
        .choices.is-focused .choices__inner {
            border-color: rgb(51, 65, 85) !important;
            box-shadow: 0 0 0 2px rgba(51, 65, 85, 0.15) !important;
        }
        .choices__list--dropdown { 
            background-color: white !important; 
            border: 1px solid rgb(226, 232, 240) !important; 
            border-radius: 0.5rem !important; 
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            overflow: hidden;
            z-index: 50 !important;
        }
        .choices__list--dropdown .choices__item--selectable.is-highlighted { 
            background-color: rgb(241, 245, 249) !important; 
            color: rgb(15, 23, 42) !important;
        }
        .choices__placeholder {
            opacity: 0.65;
            font-weight: 400 !important;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden flex flex-col my-auto">
        <!-- Header Identity -->
        <div class="bg-slate-800 p-8 text-center relative overflow-hidden shrink-0">
            <div class="absolute inset-0 opacity-10 pointer-events-none">
                <i class="fas fa-headset text-white text-[120px] absolute -right-8 -bottom-8 rotate-12"></i>
            </div>
            
            <div class="relative z-10 text-white">
                <img src="<?= base_url('logo.png') ?>" alt="Logo" class="w-16 h-16 object-contain mx-auto mb-4 drop-shadow-md">
                <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest mb-1">Sistem Identitas Digital</p>
                <h1 class="text-lg font-bold uppercase tracking-tight leading-tight">Helpdesk Layanan</h1>
            </div>
        </div>

        <!-- Form Body -->
        <div class="p-6 sm:p-8 space-y-6">
            <div class="flex items-start gap-3 p-4 bg-indigo-50 border border-indigo-100 rounded-lg text-left">
                <i class="fas fa-info-circle text-indigo-600 text-lg shrink-0 mt-0.5"></i>
                <div>
                    <h2 class="text-xs font-bold text-indigo-800 uppercase tracking-wider">Pusat Bantuan & Laporan</h2>
                    <p class="text-[10px] text-indigo-700 leading-normal mt-0.5">Silakan isi formulir di bawah ini untuk melaporkan kendala teknis atau pengajuan layanan TIK (Email, TTE/E-Sign, dll.). Tim kami akan segera menindaklanjuti laporan Anda.</p>
                </div>
            </div>

            <?php if (session()->has('errors')): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg relative shadow-sm" role="alert">
                    <strong class="font-bold text-xs uppercase tracking-wider flex items-center gap-2"><i class="fas fa-exclamation-triangle"></i> Terjadi Kesalahan!</strong>
                    <ul class="list-disc pl-5 mt-1 text-[10px] leading-relaxed">
                        <?php foreach (session('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('helpdesk/submit') ?>" method="post" class="space-y-6">
                <?= csrf_field() ?>

                <div class="space-y-6">
                    <!-- Sesi 1: Informasi Pemohon -->
                    <div class="space-y-4">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest border-b border-slate-100 pb-2 flex items-center gap-2">
                            <i class="fas fa-user-circle text-slate-500 text-sm"></i> Informasi Pemohon
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="nama_pemohon" class="block text-xs font-medium text-slate-700 mb-1 uppercase tracking-wider">Nama Lengkap</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                                        <i class="fas fa-user text-xs"></i>
                                    </span>
                                    <input type="text" class="block w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 transition-all uppercase placeholder:text-slate-400 placeholder:font-normal" id="nama_pemohon" name="nama_pemohon" value="<?= old('nama_pemohon') ?>" required placeholder="Contoh: BUDI SANTOSO, S.Kom">
                                </div>
                            </div>
                            <div>
                                <label for="nip_pemohon" class="block text-xs font-medium text-slate-700 mb-1 uppercase tracking-wider">NIP / NIK <span class="text-slate-400 font-normal">(Opsional)</span></label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                                        <i class="fas fa-id-card text-xs"></i>
                                    </span>
                                    <input type="text" class="block w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 transition-all font-mono placeholder:text-slate-400 placeholder:font-normal" id="nip_pemohon" name="nip_pemohon" value="<?= old('nip_pemohon') ?>" placeholder="19800101...">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="kontak_whatsapp" class="block text-xs font-medium text-slate-700 mb-1 uppercase tracking-wider">Nomor WhatsApp Aktif</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                                        <i class="fab fa-whatsapp text-xs"></i>
                                    </span>
                                    <input type="text" class="block w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 transition-all font-mono placeholder:text-slate-400 placeholder:font-normal" id="kontak_whatsapp" name="kontak_whatsapp" value="<?= old('kontak_whatsapp') ?>" required placeholder="Contoh: 08123456789">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1 uppercase tracking-wider">Unit Kerja / Instansi</label>
                                <div class="relative">
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
                            </div>
                        </div>
                    </div>

                    <!-- Sesi 2: Layanan & Kendala -->
                    <div class="space-y-4 pt-4 border-t border-slate-100">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest border-b border-slate-100 pb-2 flex items-center gap-2">
                            <i class="fas fa-exclamation-circle text-slate-500 text-sm"></i> Detail Kendala & Layanan
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="category" class="block text-xs font-medium text-slate-700 mb-1 uppercase tracking-wider">Kategori</label>
                                <select class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 cursor-pointer transition-all animate-none" id="category" name="category" onchange="updateServicesDropdown()" required>
                                    <option value="">Pilih Kategori...</option>
                                    <?php foreach ($categoryMap as $id => $label): ?>
                                        <option value="<?= $id ?>" <?= old('category') == $id ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="service" class="block text-xs font-medium text-slate-700 mb-1 uppercase tracking-wider">Layanan Spesifik</label>
                                <select class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 cursor-pointer transition-all animate-none" id="service" name="service" onchange="updateKeteranganOptions()" required>
                                    <option value="">Pilih Layanan...</option>
                                </select>
                            </div>
                            <div>
                                <label for="kategori_layanan" class="block text-xs font-medium text-slate-700 mb-1 uppercase tracking-wider">Jenis Masalah</label>
                                <select class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 cursor-pointer transition-all animate-none" id="kategori_layanan" name="kategori_layanan" required>
                                    <option value="">Pilih Kendala...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="w-full sm:w-auto btn btn-solid !px-8 !py-2.5 rounded-lg flex items-center justify-center gap-2 hover:scale-[1.01] active:scale-[0.99] transition-all">
                        <i class="fas fa-paper-plane text-xs text-white/80"></i> Kirim Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

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

</html>
