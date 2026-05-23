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
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Otentikasi (Bearer Token)</h3>
        </div>
        <div class="p-6">
            <p class="text-xs text-slate-600 mb-4">Seluruh request ke API Gateway wajib menyertakan token otentikasi. Anda dapat memilih salah satu metode di bawah ini:</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-slate-900 rounded-lg p-4 font-mono text-[11px] text-slate-300 space-y-2 border border-slate-800">
                    <div class="flex justify-between items-center mb-2 border-b border-slate-800 pb-2">
                        <span class="text-slate-500 uppercase tracking-widest text-[9px] font-bold">Metode 1: Header (Recommended)</span>
                    </div>
                    <div><span class="text-emerald-400">Authorization:</span> Bearer <?= esc($token) ?></div>
                </div>

                <div class="bg-slate-900 rounded-lg p-4 font-mono text-[11px] text-slate-300 space-y-2 border border-slate-800">
                    <div class="flex justify-between items-center mb-2 border-b border-slate-800 pb-2">
                        <span class="text-slate-500 uppercase tracking-widest text-[9px] font-bold">Metode 2: URL Parameter (Browser)</span>
                    </div>
                    <div class="truncate"><span class="text-emerald-400">URL:</span> ...?token=<?= esc($token) ?></div>
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
                'name' => 'Data Pegawai PPPK',
                'method' => 'GET',
                'path' => '/pppk',
                'desc' => 'Mengambil daftar akun email untuk kategori PPPK (Penuh Waktu dan Paruh Waktu).',
            ],
            [
                'name' => 'Filter Per Unit Kerja',
                'method' => 'GET',
                'path' => '/unit/{id}',
                'desc' => 'Mengambil daftar email yang terdaftar pada unit kerja tertentu berdasarkan ID Unit.',
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
</div>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('URL berhasil disalin ke clipboard!');
        });
    }
</script>
<?php $this->endSection() ?>
