<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">API Gateway Documentation</h1>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">
                Panduan integrasi data internal Pemerintah Kabupaten Sinjai
            </p>
        </div>
    </div>

    <!-- Informasi Otentikasi -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Otentikasi & Akses</h3>
        </div>
        <div class="p-6">
            <p class="text-xs text-slate-600 mb-4">Anda dapat mengakses API ini melalui dua cara:</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 space-y-2">
                    <span class="text-slate-800 uppercase tracking-widest text-[9px] font-black block">Metode 1: Akses Langsung (Browser)</span>
                    <p class="text-[10px] text-slate-600 leading-relaxed">Selama Anda <b>Login sebagai Admin</b> di browser ini, Anda dapat langsung mengklik link endpoint di bawah untuk melihat data JSON.</p>
                </div>

                <div class="bg-slate-900 rounded-lg p-4 font-mono text-[11px] text-slate-300 space-y-2 border border-slate-800">
                    <span class="text-slate-500 uppercase tracking-widest text-[9px] font-bold block border-b border-slate-800 pb-2 mb-2">Metode 2: Integrasi Aplikasi (Header)</span>
                    <div><span class="text-emerald-400">Authorization:</span> Bearer [LIHAT_DI_ENV]</div>
                    <div><span class="text-emerald-400">Accept:</span> application/json</div>
                </div>
            </div>
            
            <div class="mt-4 p-4 bg-amber-50 border border-amber-100 rounded-lg flex gap-3">
                <i class="fas fa-shield-alt text-amber-500 mt-0.5"></i>
                <div class="text-[10px] text-amber-800 leading-relaxed uppercase font-bold">
                    Jaga kerahasiaan token ini. Jangan membagikan token di dalam kode client-side (JavaScript) yang dapat dibaca publik.
                </div>
            </div>
        </div>
    </div>

    <!-- Parameter Query -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Parameter Query (Filter)</h3>
            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Opsional</span>
        </div>
        <div class="p-6">
            <p class="text-xs text-slate-600 mb-4">Anda dapat menambahkan parameter berikut pada URL untuk menyaring hasil pencarian:</p>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-[11px] text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-4 py-2 font-bold text-slate-700 uppercase tracking-tight w-1/4">Parameter</th>
                            <th class="px-4 py-2 font-bold text-slate-700 uppercase tracking-tight">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="px-4 py-3 font-mono text-emerald-600 font-bold">name</td>
                            <td class="px-4 py-3 text-slate-600">Filter berdasarkan nama pegawai (Partial match).</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-mono text-emerald-600 font-bold">email</td>
                            <td class="px-4 py-3 text-slate-600">Filter berdasarkan alamat email.</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-mono text-emerald-600 font-bold">nip</td>
                            <td class="px-4 py-3 text-slate-600">Filter berdasarkan NIP (Exact match).</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-mono text-emerald-600 font-bold">nik</td>
                            <td class="px-4 py-3 text-slate-600">Filter berdasarkan NIK (Exact match).</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-mono text-emerald-600 font-bold">jabatan</td>
                            <td class="px-4 py-3 text-slate-600">Filter berdasarkan nama jabatan.</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-mono text-emerald-600 font-bold">bsre_status</td>
                            <td class="px-4 py-3 text-slate-600">Filter status TTE (ISSUE, EXPIRED, dll).</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-mono text-emerald-600 font-bold">api_unit_id</td>
                            <td class="px-4 py-3 text-slate-600">Filter berdasarkan ID Unit Kerja API Pusat.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 p-3 bg-slate-50 border border-slate-100 rounded text-[10px] font-mono text-slate-500">
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
                'desc' => 'Mengambil daftar seluruh akun email aktif beserta informasi profil dasar dan status TTE.',
            ],
            [
                'name' => 'Data Pegawai PNS',
                'method' => 'GET',
                'path' => '/pns',
                'desc' => 'Mengambil daftar akun email yang berstatus sebagai Pegawai Negeri Sipil (PNS).',
            ],
            [
                'name' => 'Data PPPK',
                'method' => 'GET',
                'path' => '/pppk',
                'desc' => 'Mengambil daftar akun email untuk kategori PPPK Penuh Waktu.',
            ],
            [
                'name' => 'Data PPPK (Paruh Waktu)',
                'method' => 'GET',
                'path' => '/pppk-pw',
                'desc' => 'Mengambil daftar akun email untuk kategori PPPK Paruh Waktu.',
            ],
            [
                'name' => 'Filter Per Unit Kerja',
                'method' => 'GET',
                'path' => '/unit/{id}',
                'desc' => 'Mengambil daftar email yang terdaftar pada unit kerja tertentu berdasarkan ID Unit (Lihat daftar ID di bawah).',
            ],

        ];

        foreach ($endpoints as $api): ?>
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
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
                        <button onclick="copyToClipboard('<?= $base_url . $api['path'] ?>')" class="p-1.5 text-slate-400 hover:text-slate-800 transition-colors" title="Salin URL">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>

                    <div class="border-t border-slate-50 pt-4">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2 text-center italic">Example JSON Response</p>
                        <pre class="bg-slate-50 rounded-lg p-4 font-mono text-[10px] text-slate-600 border border-slate-100 overflow-x-auto custom-scrollbar">{
    "status": "success",
    "count": 1,
    "data": [
        {
            "email": "username@sinjaikab.go.id",
            "name": "NAMA LENGKAP",
            "nik": "730...",
            "nip": "199...",
            "jabatan": "JABATAN PEGAWAI",
            "bsre_status": "ISSUE"
        }
    ]
}</pre>
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
                <input type="text" id="unitSearch" onkeyup="filterUnits()" placeholder="Cari Nama Unit Kerja..." 
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[11px] font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-200 transition-all">
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
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('URL berhasil disalin ke clipboard!');
        });
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
