<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">API Gateway</h1>
        </div>
    </div>

    <!-- Informasi Otentikasi -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Otentikasi & Akses</h3>
            <span class="text-[10px] font-bold text-slate-500 font-mono">Bearer Token</span>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1">
                    <span class="text-slate-700 text-[10px] font-bold uppercase tracking-wider block">Akses Browser</span>
                    <p class="text-xs text-slate-600">Sesi login admin aktif dapat langsung membuka endpoint di browser.</p>
                </div>

                <div class="bg-slate-900 rounded-xl p-4 font-mono text-xs text-slate-300 space-y-2 border border-slate-800">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                        <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Header Request</span>
                        <button type="button" onclick="copyToken('<?= esc($token) ?>', this)" class="text-slate-400 hover:text-emerald-400 transition-colors text-[10px] flex items-center gap-1 font-sans font-bold uppercase tracking-wider focus:outline-none" title="Salin Header">
                            <i class="fas fa-copy"></i> <span>Salin Header</span>
                        </button>
                    </div>
                    <div class="flex items-center justify-between gap-2 pt-0.5">
                        <div class="truncate text-[11px]">
                            <span class="text-emerald-400">Authorization:</span> Bearer 
                            <span id="token-masked" class="text-slate-500 tracking-widest select-none">••••••••••••••••</span>
                            <span id="token-revealed" class="text-white hidden select-all"><?= esc($token) ?></span>
                        </div>
                        <button type="button" onclick="toggleTokenReveal()" class="text-slate-400 hover:text-white transition-colors p-1 focus:outline-none shrink-0" title="Lihat/Sembunyikan">
                            <i id="token-eye-icon" class="fas fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Parameter Query -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Parameter Filter</h3>
            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Opsional</span>
        </div>
        <div class="p-6 space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-[11px] text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-4 py-2 font-bold text-slate-700 uppercase tracking-tight w-1/4">Parameter</th>
                            <th class="px-4 py-2 font-bold text-slate-700 uppercase tracking-tight">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="px-4 py-2.5 font-mono text-emerald-600 font-bold">name</td>
                            <td class="px-4 py-2.5 text-slate-600">Nama pegawai (parsial)</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5 font-mono text-emerald-600 font-bold">email</td>
                            <td class="px-4 py-2.5 text-slate-600">Alamat email</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5 font-mono text-emerald-600 font-bold">nip</td>
                            <td class="px-4 py-2.5 text-slate-600">NIP (eksak)</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5 font-mono text-emerald-600 font-bold">nik</td>
                            <td class="px-4 py-2.5 text-slate-600">NIK (eksak)</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5 font-mono text-emerald-600 font-bold">jabatan</td>
                            <td class="px-4 py-2.5 text-slate-600">Nama jabatan</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5 font-mono text-emerald-600 font-bold">bsre_status</td>
                            <td class="px-4 py-2.5 text-slate-600">Status TTE (ISSUE, EXPIRED, dll.)</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5 font-mono text-emerald-600 font-bold">api_unit_id</td>
                            <td class="px-4 py-2.5 text-slate-600">ID Unit Kerja API Pusat</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-3 bg-slate-50 border border-slate-100 rounded-lg text-[10px] font-mono text-slate-500 truncate">
                <span class="font-bold text-slate-700">Contoh:</span> <?= $base_url ?>/emails?name=SUDARMIN&bsre_status=ISSUE
            </div>
        </div>
    </div>

    <!-- Daftar Endpoint -->
    <div class="grid grid-cols-1 gap-6">
        <?php
        $endpoints = [
            [
                'name' => 'Daftar Seluruh Email',
                'method' => 'GET',
                'path' => '/emails',
                'desc' => 'Seluruh akun email aktif beserta profil dan status TTE.',
                'example' => '{
    "status": "success",
    "count": 8052,
    "data": [
        {
            "email": "username@sinjaikab.go.id",
            "name": "NAMA LENGKAP",
            "nik": "7307...",
            "nip": "1990...",
            "jabatan": "JABATAN PEGAWAI",
            "bsre_status": "ISSUE",
            "api_unit_id": "730701"
        }
    ]
}'
            ],
            [
                'name' => 'Data Pegawai PNS',
                'method' => 'GET',
                'path' => '/pns',
                'desc' => 'Daftar akun email berstatus Pegawai Negeri Sipil (PNS).',
                'example' => '{
    "status": "success",
    "count": 1163,
    "data": [
        {
            "email": "username@sinjaikab.go.id",
            "name": "NAMA LENGKAP PNS",
            "nik": "7307...",
            "nip": "1985...",
            "jabatan": "JABATAN PNS",
            "bsre_status": "ISSUE",
            "api_unit_id": "730706"
        }
    ]
}'
            ],
            [
                'name' => 'Data PPPK',
                'method' => 'GET',
                'path' => '/pppk',
                'desc' => 'Daftar akun email kategori PPPK Penuh Waktu.',
                'example' => '{
    "status": "success",
    "count": 467,
    "data": [
        {
            "email": "username@sinjaikab.go.id",
            "name": "NAMA LENGKAP PPPK",
            "nik": "7307...",
            "nip": "1992...",
            "jabatan": "AHLI PERTAMA - GURU KELAS",
            "bsre_status": "ISSUE",
            "api_unit_id": "730722"
        }
    ]
}'
            ],
            [
                'name' => 'Data PPPK (Paruh Waktu)',
                'method' => 'GET',
                'path' => '/pppk-pw',
                'desc' => 'Daftar akun email kategori PPPK Paruh Waktu.',
                'example' => '{
    "status": "success",
    "count": 3948,
    "data": [
        {
            "email": "username@sinjaikab.go.id",
            "name": "NAMA LENGKAP PPPK PARUH WAKTU",
            "nik": "7307...",
            "nip": "1995...",
            "jabatan": "TENAGA ADMINISTRASI",
            "bsre_status": "ISSUE",
            "api_unit_id": "730711"
        }
    ]
}'
            ],
            [
                'name' => 'Filter Per Unit Kerja',
                'method' => 'GET',
                'path' => '/unit/{id}',
                'desc' => 'Daftar akun email per unit kerja (termasuk unit turunan).',
                'example' => '{
    "status": "success",
    "count": 42,
    "unit_id": "339",
    "api_unit_id": "730722",
    "nama_unit_kerja": "DINAS KESEHATAN",
    "data": [
        {
            "email": "username@sinjaikab.go.id",
            "name": "NAMA PEGAWAI",
            "nik": "7307...",
            "nip": "1988...",
            "jabatan": "EPIDEMIOLOG KESEHATAN",
            "bsre_status": "ISSUE",
            "api_unit_id": "730722"
        }
    ]
}'
            ],
        ];

        foreach ($endpoints as $api): ?>
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-white flex justify-between items-center">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight"><?= $api['name'] ?></h3>
                    <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded text-[9px] font-black uppercase leading-none">V1</span>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-xs text-slate-600"><?= $api['desc'] ?></p>
                    
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1.5 bg-slate-800 text-white rounded text-[10px] font-bold font-mono"><?= $api['method'] ?></span>
                        <div class="flex-1 bg-slate-50 border border-slate-200 rounded px-3 py-1.5 font-mono text-[11px] text-slate-700 truncate">
                            <?= $base_url . $api['path'] ?>
                        </div>
                        <button onclick="copyToClipboard('<?= $base_url . $api['path'] ?>', this)" class="p-1.5 text-slate-400 hover:text-slate-800 transition-colors focus:outline-none" title="Salin URL">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>

                    <div class="border-t border-slate-50 pt-4">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2 text-center italic">Example JSON Response</p>
                        <pre class="bg-slate-50 rounded-lg p-4 font-mono text-[10px] text-slate-600 border border-slate-100 overflow-x-auto custom-scrollbar"><?= esc($api['example']) ?></pre>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Tombol Modal Unit Kerja -->
    <div class="flex justify-center mt-4">
        <button type="button" onclick="openModal('unitModal')" class="btn btn-outline text-[10px] font-bold uppercase tracking-widest">
            <i class="fas fa-list-numeric mr-2"></i> Lihat Daftar ID Unit Kerja
        </button>
    </div>

    <!-- Modal ID Unit Kerja -->
    <?php
    $modalContent = '
        <div class="mb-6">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                <input type="text" id="unitSearch" onkeyup="filterUnits()" placeholder="Cari..." 
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[11px] font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-200 transition-all placeholder-slate-400">
            </div>
        </div>

        <div id="unitList" class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3">
    ';
    
    foreach ($units as $unit) {
        if ($unit['api_unit_id']) {
            $modalContent .= '
                <div class="unit-item flex justify-between items-center border border-slate-200 bg-white p-3 hover:border-slate-800 hover:shadow-sm transition-all rounded-lg group">
                    <span class="unit-name text-[10px] font-bold text-slate-700 truncate mr-2" title="' . esc($unit['nama_unit_kerja']) . '">' . esc($unit['nama_unit_kerja']) . '</span>
                    <div class="flex flex-col items-end shrink-0">
                        <span class="text-[10px] font-black text-slate-800 font-mono">ID: ' . $unit['api_unit_id'] . '</span>
                    </div>
                </div>
            ';
        }
    }

    $modalContent .= '
        </div>
        <div id="noUnitFound" class="hidden text-center py-12">
            <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-search text-slate-200"></i>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Unit kerja tidak ditemukan</span>
        </div>
    ';

    $modalFooter = '
        <button onclick="closeModal(\'unitModal\')" class="px-8 py-2.5 bg-slate-800 text-white text-[10px] font-bold uppercase tracking-widest rounded-lg hover:bg-slate-700 transition-colors shadow-lg shadow-slate-200 focus:outline-none">
            Tutup
        </button>
    ';

    echo view('components/modal', [
        'id' => 'unitModal',
        'title' => 'Daftar ID Unit Kerja (API Sinjai)',
        'size' => 'xl',
        'content' => $modalContent,
        'footer' => $modalFooter
    ], ['saveData' => false]);
    ?>
</div>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<script>
    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text).then(function() {
            if (btn) {
                const originalIcon = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check text-emerald-600"></i>';
                setTimeout(() => {
                    btn.innerHTML = originalIcon;
                }, 2000);
            }
        });
    }

    function copyToken(token, btn) {
        const fullHeader = `Bearer ${token}`;
        navigator.clipboard.writeText(fullHeader).then(function() {
            if (btn) {
                const originalContent = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check text-emerald-400 text-[10px]"></i> <span class="text-[9px] uppercase font-bold text-emerald-400">Tersalin</span>';
                setTimeout(() => {
                    btn.innerHTML = originalContent;
                }, 2000);
            }
        });
    }

    function toggleTokenReveal() {
        const masked = document.getElementById('token-masked');
        const revealed = document.getElementById('token-revealed');
        const icon = document.getElementById('token-eye-icon');
        if (masked && revealed && icon) {
            const isHidden = revealed.classList.contains('hidden');
            if (isHidden) {
                masked.classList.add('hidden');
                revealed.classList.remove('hidden');
                icon.className = 'fas fa-eye-slash text-xs text-emerald-400';
            } else {
                masked.classList.remove('hidden');
                revealed.classList.add('hidden');
                icon.className = 'fas fa-eye text-xs';
            }
        }
    }

    const searchInput = document.getElementById('unitSearch');
    const unitList = document.getElementById('unitList');
    const noResult = document.getElementById('noUnitFound');

    function filterUnits() {
        const filter = searchInput.value.toLowerCase();
        const items = unitList.getElementsByClassName('unit-item');
        let visibleCount = 0;

        for (let i = 0; i < items.length; i++) {
            const name = items[i].querySelector('.unit-name').textContent.toLowerCase();
            if (name.includes(filter)) {
                items[i].style.display = "";
                visibleCount++;
            } else {
                items[i].style.display = "none";
            }
        }

        noResult.classList.toggle('hidden', visibleCount > 0);
    }
</script>
<?php $this->endSection() ?>
