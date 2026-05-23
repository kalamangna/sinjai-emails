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
            "nip": "199...",
            "jabatan": "JABATAN PEGAWAI",
            "humandiskquota": "1 GB",
            "humandiskused": "150 MB",
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
        <button type="button" onclick="openUnitModal()" class="btn btn-outline text-[10px] font-bold uppercase tracking-widest">
            <i class="fas fa-list-numeric mr-2"></i> Lihat Daftar ID Unit Kerja
        </button>
    </div>

    <!-- Modal ID Unit Kerja -->
    <div id="unitModal" class="fixed inset-0 z-[99] hidden flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[80vh]">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center shrink-0">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Daftar ID Unit Kerja</h3>
                <button onclick="closeUnitModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            
            <div class="p-6 shrink-0 border-b border-slate-50">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                    <input type="text" id="unitSearch" onkeyup="filterUnits()" placeholder="Cari Nama Unit Kerja..." 
                           class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[11px] font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-200 transition-all">
                </div>
            </div>

            <div class="p-6 overflow-y-auto custom-scrollbar flex-grow">
                <div id="unitList" class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1">
                    <?php foreach ($units as $unit): ?>
                        <div class="unit-item flex justify-between items-center border-b border-slate-50 py-1.5 hover:bg-slate-50 transition-colors px-2 rounded">
                            <span class="unit-name text-[10px] font-medium text-slate-600 truncate mr-2" title="<?= esc($unit['nama_unit_kerja']) ?>"><?= esc($unit['nama_unit_kerja']) ?></span>
                            <span class="text-[10px] font-black text-slate-800 font-mono shrink-0">ID: <?= $unit['id'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div id="noUnitFound" class="hidden text-center py-8 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Unit kerja tidak ditemukan
                </div>
            </div>

            <div class="bg-slate-50 p-4 border-t border-slate-100 flex justify-end shrink-0">
                <button onclick="closeUnitModal()" class="px-6 py-2 bg-slate-800 text-white text-[10px] font-bold uppercase tracking-widest rounded-lg hover:bg-slate-700 transition-colors focus:outline-none">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('URL berhasil disalin ke clipboard!');
        });
    }

    const modal = document.getElementById('unitModal');
    const searchInput = document.getElementById('unitSearch');
    const unitList = document.getElementById('unitList');
    const noResult = document.getElementById('noUnitFound');

    function openUnitModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        searchInput.focus();
    }

    function closeUnitModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        searchInput.value = '';
        filterUnits();
    }

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

    // Close on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeUnitModal();
        }
    });

    // Close on click outside
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeUnitModal();
        }
    });
</script>
<?php $this->endSection() ?>
