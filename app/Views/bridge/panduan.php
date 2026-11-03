<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, follow">
    <title><?= esc($title ?? 'Panduan Integrasi Bridge TTE') ?> | Sistem Identitas Digital</title>
    <link rel="icon" type="image/png" href="<?= esc($logoUrl) ?>">
    <link href="<?= esc($cssUrl) ?>" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        code, pre { font-family: 'JetBrains Mono', monospace; }
        summary::-webkit-details-marker { display: none; }
        details[open] .chevron-icon { transform: rotate(180deg); }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen p-4 sm:p-6">

    <div class="max-w-3xl mx-auto space-y-6">

        <!-- Header Navigasi -->
        <div class="flex items-center gap-3 pb-3 border-b border-slate-200">
            <img src="<?= esc($logoUrl) ?>" alt="Logo Kabupaten Sinjai" class="w-8 h-8 object-contain">
            <div>
                <h1 class="text-sm font-bold uppercase tracking-wider text-slate-900 leading-tight">Bridge TTE</h1>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Panduan Integrasi</p>
            </div>
        </div>

        <!-- 1. Autentikasi -->
        <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm space-y-2">
            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">1. Autentikasi</h2>
            <p class="text-xs text-slate-600 leading-relaxed">
                Wajib menyertakan header HTTP Basic Auth:
            </p>
            <div class="bg-slate-900 rounded-lg p-3.5 text-xs font-mono text-slate-200 overflow-x-auto">
                <span class="text-emerald-400">Authorization:</span> Basic &lt;base64_encoded_auth&gt;
            </div>
        </div>

        <!-- 2. Daftar Endpoint (Accordion) -->
        <div class="space-y-3">
            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider px-1">2. Daftar Endpoint</h2>

            <!-- Accordion 1: Status Service -->
            <details class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">
                <summary class="flex items-center justify-between p-4 cursor-pointer select-none bg-white hover:bg-slate-50 transition-colors list-none">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-slate-800 text-white">GET</span>
                        <span class="font-mono font-bold text-xs sm:text-sm text-slate-800">/api/status</span>
                        <span class="text-xs text-slate-500 hidden sm:inline">&bull; Status Server</span>
                    </div>
                    <svg class="chevron-icon w-4 h-4 text-slate-500 transition-transform duration-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </summary>
                <div class="p-4 sm:p-5 border-t border-slate-100 bg-slate-50/50 space-y-3">
                    <p class="text-xs text-slate-500">Tanpa parameter tambahan.</p>
                    <div class="bg-slate-900 rounded-lg p-3 text-xs font-mono text-slate-200 overflow-x-auto">
<span class="text-slate-400">curl -X GET "<?= esc($baseUrlExample) ?>/api/status" \
     -u "username:password"</span>
                    </div>
                </div>
            </details>

            <!-- Accordion 2: Cek Status Pengguna V2 -->
            <details class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">
                <summary class="flex items-center justify-between p-4 cursor-pointer select-none bg-white hover:bg-slate-50 transition-colors list-none">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-slate-800 text-white">POST</span>
                        <span class="font-mono font-bold text-xs sm:text-sm text-slate-800">/api/v2/user/check/status</span>
                        <span class="text-xs text-slate-500 hidden sm:inline">&bull; Status Sertifikat Versi 2</span>
                    </div>
                    <svg class="chevron-icon w-4 h-4 text-slate-500 transition-transform duration-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </summary>
                <div class="p-4 sm:p-5 border-t border-slate-100 bg-slate-50/50 space-y-3">
                    <div class="overflow-x-auto border border-slate-200 rounded-lg bg-white">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-900 font-semibold uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="px-3 py-2 border-b border-slate-200">Parameter</th>
                                    <th class="px-3 py-2 border-b border-slate-200">Wajib</th>
                                    <th class="px-3 py-2 border-b border-slate-200">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr>
                                    <td class="px-3 py-2 font-mono font-bold text-slate-900">email</td>
                                    <td class="px-3 py-2 text-amber-700 font-medium text-[11px]">Wajib salah satu</td>
                                    <td class="px-3 py-2 text-slate-600">Email dinas penandatangan jika NIK tidak diisi</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 font-mono font-bold text-slate-900">nik</td>
                                    <td class="px-3 py-2 text-amber-700 font-medium text-[11px]">Wajib salah satu</td>
                                    <td class="px-3 py-2 text-slate-600">16 digit NIK jika email tidak diisi</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-slate-900 rounded-lg p-3 text-xs font-mono text-slate-200 overflow-x-auto">
<span class="text-slate-400">curl -X POST "<?= esc($baseUrlExample) ?>/api/v2/user/check/status" \
     -u "username:password" \
     -H "Content-Type: application/json" \
     -d '{"email": "nama@sinjaikab.go.id"}'</span>
                    </div>
                </div>
            </details>

            <!-- Accordion 3: Cek Status Pengguna V1 (NIK) -->
            <details class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">
                <summary class="flex items-center justify-between p-4 cursor-pointer select-none bg-white hover:bg-slate-50 transition-colors list-none">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-slate-800 text-white">GET</span>
                        <span class="font-mono font-bold text-xs sm:text-sm text-slate-800">/api/user/status/{nik}</span>
                        <span class="text-xs text-slate-500 hidden sm:inline">&bull; Status Sertifikat Versi 1</span>
                    </div>
                    <svg class="chevron-icon w-4 h-4 text-slate-500 transition-transform duration-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </summary>
                <div class="p-4 sm:p-5 border-t border-slate-100 bg-slate-50/50 space-y-3">
                    <div class="overflow-x-auto border border-slate-200 rounded-lg bg-white">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-900 font-semibold uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="px-3 py-2 border-b border-slate-200">Parameter</th>
                                    <th class="px-3 py-2 border-b border-slate-200">Wajib</th>
                                    <th class="px-3 py-2 border-b border-slate-200">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="px-3 py-2 font-mono font-bold text-slate-900">{nik}</td>
                                    <td class="px-3 py-2 text-emerald-700 font-semibold">Ya</td>
                                    <td class="px-3 py-2 text-slate-600">16 digit NIK pemilik sertifikat pada URL</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-slate-900 rounded-lg p-3 text-xs font-mono text-slate-200 overflow-x-auto">
<span class="text-slate-400">curl -X GET "<?= esc($baseUrlExample) ?>/api/user/status/7302xxxxxxxxxxxx" \
     -u "username:password"</span>
                    </div>
                </div>
            </details>

            <!-- Accordion 4: Penandatanganan Dokumen PDF -->
            <details class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm" open>
                <summary class="flex items-center justify-between p-4 cursor-pointer select-none bg-white hover:bg-slate-50 transition-colors list-none">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-slate-800 text-white">POST</span>
                        <span class="font-mono font-bold text-xs sm:text-sm text-slate-800">/api/sign/pdf</span>
                        <span class="text-xs text-slate-500 hidden sm:inline">&bull; Penandatanganan PDF</span>
                    </div>
                    <svg class="chevron-icon w-4 h-4 text-slate-500 transition-transform duration-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </summary>
                <div class="p-4 sm:p-5 border-t border-slate-100 bg-slate-50/50 space-y-3">
                    <div class="overflow-x-auto border border-slate-200 rounded-lg bg-white">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-900 font-semibold uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="px-3 py-2 border-b border-slate-200">Parameter</th>
                                    <th class="px-3 py-2 border-b border-slate-200">Wajib</th>
                                    <th class="px-3 py-2 border-b border-slate-200">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr>
                                    <td class="px-3 py-2 font-mono font-bold text-slate-900">file</td>
                                    <td class="px-3 py-2 text-emerald-700 font-semibold">Ya</td>
                                    <td class="px-3 py-2 text-slate-600">Berkas dokumen PDF</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 font-mono font-bold text-slate-900">nik</td>
                                    <td class="px-3 py-2 text-emerald-700 font-semibold">Ya</td>
                                    <td class="px-3 py-2 text-slate-600">16 digit NIK penandatangan</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 font-mono font-bold text-slate-900">passphrase</td>
                                    <td class="px-3 py-2 text-emerald-700 font-semibold">Ya</td>
                                    <td class="px-3 py-2 text-slate-600">Kata sandi sertifikat TTE</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 font-mono font-bold text-slate-900">tampilan</td>
                                    <td class="px-3 py-2 text-emerald-700 font-semibold">Ya</td>
                                    <td class="px-3 py-2 text-slate-600"><code class="font-mono bg-slate-100 px-1 py-0.5 rounded">visible</code> atau <code class="font-mono bg-slate-100 px-1 py-0.5 rounded">invisible</code></td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 font-mono font-bold text-slate-900">halaman</td>
                                    <td class="px-3 py-2 text-amber-700 font-medium text-[11px]">Wajib jika visible</td>
                                    <td class="px-3 py-2 text-slate-600">Nomor halaman penempatan tanda tangan seperti 1 atau terakhir</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 font-mono font-bold text-slate-900">xAxis, yAxis</td>
                                    <td class="px-3 py-2 text-amber-700 font-medium text-[11px]">Wajib jika visible</td>
                                    <td class="px-3 py-2 text-slate-600">Titik koordinat posisi visual tanda tangan</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 font-mono font-bold text-slate-900">width, height</td>
                                    <td class="px-3 py-2 text-slate-500">Opsional</td>
                                    <td class="px-3 py-2 text-slate-600">Ukuran dimensi visual tanda tangan, standar 100 sampai 150</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-slate-900 rounded-lg p-3 text-xs font-mono text-slate-200 overflow-x-auto">
<span class="text-slate-400">curl -X POST "<?= esc($baseUrlExample) ?>/api/sign/pdf" \
     -u "username:password" \
     -F "file=@/lokasi/dokumen.pdf" \
     -F "nik=7302xxxxxxxxxxxx" \
     -F "passphrase=RahasiaPassphrase123" \
     -F "tampilan=visible" \
     -F "halaman=1" \
     -F "xAxis=100" \
     -F "yAxis=150" \
     -F "width=120" \
     -F "height=120" \
     --output "dokumen_signed.pdf"</span>
                    </div>
                </div>
            </details>

            <!-- Accordion 5: Verifikasi Dokumen -->
            <details class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">
                <summary class="flex items-center justify-between p-4 cursor-pointer select-none bg-white hover:bg-slate-50 transition-colors list-none">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-slate-800 text-white">POST</span>
                        <span class="font-mono font-bold text-xs sm:text-sm text-slate-800">/api/sign/verify</span>
                        <span class="text-xs text-slate-500 hidden sm:inline">&bull; Verifikasi Dokumen PDF</span>
                    </div>
                    <svg class="chevron-icon w-4 h-4 text-slate-500 transition-transform duration-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </summary>
                <div class="p-4 sm:p-5 border-t border-slate-100 bg-slate-50/50 space-y-3">
                    <div class="overflow-x-auto border border-slate-200 rounded-lg bg-white">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-900 font-semibold uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="px-3 py-2 border-b border-slate-200">Parameter</th>
                                    <th class="px-3 py-2 border-b border-slate-200">Wajib</th>
                                    <th class="px-3 py-2 border-b border-slate-200">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="px-3 py-2 font-mono font-bold text-slate-900">file</td>
                                    <td class="px-3 py-2 text-emerald-700 font-semibold">Ya</td>
                                    <td class="px-3 py-2 text-slate-600">Berkas dokumen PDF</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-slate-900 rounded-lg p-3 text-xs font-mono text-slate-200 overflow-x-auto">
<span class="text-slate-400">curl -X POST "<?= esc($baseUrlExample) ?>/api/sign/verify" \
     -u "username:password" \
     -F "file=@/lokasi/dokumen_terverifikasi.pdf"</span>
                    </div>
                </div>
            </details>

        </div>

    </div>

</body>
</html>
