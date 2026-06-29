<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="robots" content="<?= $meta_robots ?? 'noindex, follow' ?>">
    <title><?= $title ?? 'Verifikasi PDF' ?> | Sistem Identitas Digital</title>

    <meta name="description" content="<?= $meta_description ?? 'Portal Verifikasi Dokumen Elektronik & Sertifikat Elektronik Pemerintah Kabupaten Sinjai' ?>">
    <link rel="canonical" href="<?= current_url() ?>">

    <!-- Meta Tags -->
    <meta property="og:site_name" content="Sistem Identitas Digital Sinjai">
    <meta property="og:title" content="<?= $title ?? 'Verifikasi PDF' ?> | Sistem Identitas Digital">
    <meta property="og:description" content="<?= $meta_description ?? 'Portal Verifikasi Dokumen Elektronik & Sertifikat Elektronik Pemerintah Kabupaten Sinjai' ?>">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:image" content="<?= $meta_image ?? base_url('meta.png') ?>">
    <meta property="og:type" content="<?= $meta_type ?? 'website' ?>">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title ?? 'Verifikasi PDF' ?> | Sistem Identitas Digital">
    <meta name="twitter:description" content="<?= $meta_description ?? 'Portal Verifikasi Dokumen Elektronik & Sertifikat Elektronik Pemerintah Kabupaten Sinjai' ?>">
    <meta name="twitter:image" content="<?= $meta_image ?? base_url('meta.png') ?>">

    <link rel="icon" type="image/png" href="<?= base_url('logo.png') ?>">
    <link href="<?= base_url('css/output.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden flex flex-col my-auto">
        <!-- Header Identity -->
        <div class="bg-slate-800 p-8 text-center relative overflow-hidden shrink-0">
            <div class="absolute inset-0 opacity-10 pointer-events-none">
                <i class="fas fa-shield-alt text-white text-[120px] absolute -right-8 -bottom-8 rotate-12"></i>
            </div>
            
            <div class="relative z-10 text-white">
                <img src="<?= base_url('logo.png') ?>" alt="Logo" class="w-16 h-16 object-contain mx-auto mb-4 drop-shadow-md">
                <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest mb-1">Sistem Identitas Digital</p>
                <h1 class="text-lg font-bold uppercase tracking-tight leading-tight">Verifikasi Dokumen Elektronik</h1>
            </div>
        </div>

        <!-- Form & Hasil -->
        <div class="p-6 sm:p-6 space-y-6">
            <form id="form-verify" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-2">Pilih File PDF</label>
                    <div id="dropzone" class="border-2 border-dashed border-slate-200 hover:border-slate-400 rounded-xl py-6 px-4 flex flex-col items-center justify-center cursor-pointer transition-all bg-slate-50/50">
                        <i class="fas fa-file-pdf text-3xl text-slate-400 mb-2"></i>
                        <span class="text-xs font-semibold text-slate-700 text-center">Tarik & lepas file PDF di sini atau klik untuk memilih</span>
                        <span class="text-[9px] text-slate-500 mt-0.5">Hanya mendukung format PDF (Maks. 10MB)</span>
                        <input type="file" id="pdf-file" name="file" accept="application/pdf" class="hidden">
                    </div>
                    <div id="selected-file-info" class="hidden mt-3 p-3 bg-slate-50 border border-slate-200 rounded-lg flex items-center justify-between">
                        <div class="flex items-center gap-2 min-w-0">
                            <i class="fas fa-file-pdf text-red-600 text-sm shrink-0"></i>
                            <span id="file-name" class="text-xs font-medium text-slate-700 truncate"></span>
                            <span id="file-size" class="text-[10px] text-slate-500 shrink-0"></span>
                        </div>
                        <button type="button" onclick="resetFile()" class="text-slate-400 hover:text-red-600 text-xs transition-colors">
                            <i class="fas fa-times-circle"></i> Batal
                        </button>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 items-end">
                    <div class="flex-grow w-full">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-2">Sandi Dokumen (Opsional)</label>
                        <input type="password" id="password" name="password" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm transition-all" placeholder="Masukkan sandi jika PDF terenkripsi...">
                    </div>
                    <button type="submit" class="w-full sm:w-auto btn btn-solid flex items-center justify-center gap-2 h-[38px] shrink-0">
                        <i class="fas fa-shield-alt text-xs"></i> Uji Keaslian Dokumen
                    </button>
                </div>
            </form>

            <!-- Area Hasil Verifikasi -->
            <div id="result-verify" class="hidden border-t border-slate-200 pt-6 space-y-6">
            </div>
        </div>

        <!-- Footer inside card -->
        <div class="bg-slate-50 p-4 border-t border-slate-100 text-center shrink-0">
            <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">
                &copy; <?= tahunSekarang() ?> Diskominfo-SP Sinjai
            </p>
        </div>
    </div>

    <!-- Global Loading Overlay -->
    <div id="global-loading" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-all duration-300">
        <div class="bg-white p-8 rounded-2xl shadow-2xl flex flex-col items-center gap-4 max-w-xs w-full border border-white/20">
            <div class="relative">
                <div class="w-12 h-12 border-4 border-slate-100 rounded-full"></div>
                <div class="w-12 h-12 border-4 border-slate-700 border-t-transparent rounded-full animate-spin absolute inset-0"></div>
            </div>
            <div class="text-center">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-tight">Mohon Tunggu</h3>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Sedang Memproses...</p>
            </div>
        </div>
    </div>

    <!-- Global Error Modal -->
    <?php
    $errorContent = '<div id="error-modal-message" class="text-xs font-medium text-slate-600 leading-relaxed max-h-48 overflow-y-auto custom-scrollbar p-2 bg-slate-50 rounded-lg text-center"></div>';
    $errorFooter = '
        <button onclick="closeModal(\'global-error-modal\')" class="px-6 py-2 bg-slate-800 text-white text-[10px] font-bold uppercase tracking-widest rounded-lg hover:bg-slate-700 transition-colors focus:outline-none">
            Tutup
        </button>
    ';
    echo view('components/modal', [
        'id' => 'global-error-modal',
        'title' => 'Terjadi Kesalahan',
        'size' => 'sm',
        'content' => $errorContent,
        'footer' => $errorFooter
    ]);
    ?>

    <script>
        // File Drag & Drop Handlers
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('pdf-file');
        const selectedFileInfo = document.getElementById('selected-file-info');
        const fileNameEl = document.getElementById('file-name');
        const fileSizeEl = document.getElementById('file-size');

        dropzone.addEventListener('click', () => fileInput.click());

        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('border-slate-400', 'bg-slate-100');
        });

        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('border-slate-400', 'bg-slate-100');
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('border-slate-400', 'bg-slate-100');
            if (e.dataTransfer.files.length > 0) {
                handleFileSelection(e.dataTransfer.files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelection(e.target.files[0]);
            }
        });

        function handleFileSelection(file) {
            if (file.type !== 'application/pdf') {
                showGlobalError('Format File Salah', 'Hanya diperbolehkan mengunggah file berekstensi PDF.');
                return;
            }
            fileNameEl.innerText = file.name;
            const sizeInMb = (file.size / (1024 * 1024)).toFixed(2);
            fileSizeEl.innerText = `(${sizeInMb} MB)`;
            
            dropzone.classList.add('hidden');
            selectedFileInfo.classList.remove('hidden');
        }

        function resetFile() {
            fileInput.value = '';
            dropzone.classList.remove('hidden');
            selectedFileInfo.classList.add('hidden');
        }

        // Global loading helper
        function showGlobalLoading(show = true) {
            const overlay = document.getElementById('global-loading');
            if (!overlay) return;
            if (show) {
                overlay.classList.remove('hidden');
            } else {
                overlay.classList.add('hidden');
            }
        }

        // Global error modal helpers
        function showGlobalError(title, message) {
            const titleEl = document.getElementById('global-error-modal-title');
            const messageEl = document.getElementById('error-modal-message');
            if (titleEl) titleEl.innerText = title || 'Terjadi Kesalahan';
            if (messageEl) messageEl.innerText = message || 'Gagal memproses permintaan.';
            openModal('global-error-modal');
        }

        // Ajax Form Submit: Verify PDF
        document.getElementById('form-verify').addEventListener('submit', async function (e) {
            e.preventDefault();
            const resultContainer = document.getElementById('result-verify');
            resultContainer.classList.add('hidden');
            resultContainer.innerHTML = '';

            if (!fileInput.files.length) {
                showGlobalError('File Kosong', 'Harap pilih file PDF terlebih dahulu.');
                return;
            }

            showGlobalLoading(true);

            const formData = new FormData(this);
            
            try {
                const response = await fetch('<?= site_url('verifikasi-pdf') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();
                showGlobalLoading(false);

                if (response.ok && result.status === 'success') {
                    resultContainer.classList.remove('hidden');
                    
                    const bsreData = result.data;
                    const conclusion = bsreData.conclusion || 'NO_SIGNATURE';
                    const count = bsreData.signatureCount || 0;
                    
                    // 1. Status Card Html
                    let statusCardHtml = '';
                    if (conclusion === 'SUCCESS' && bsreData.integrityValid) {
                        statusCardHtml = `
                            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-5 flex items-start gap-4 shadow-sm">
                                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-600 shrink-0">
                                    <i class="fas fa-check-double text-lg"></i>
                                </div>
                                <div class="space-y-1">
                                    <h3 class="text-xs font-bold text-emerald-900 uppercase tracking-tight leading-tight">Dokumen Valid & Asli</h3>
                                    <p class="text-[11px] text-emerald-700 leading-relaxed">Tanda tangan elektronik valid, diterbitkan oleh otoritas terpercaya, dan isi dokumen dijamin utuh (tidak mengalami perubahan sejak ditandatangani).</p>
                                </div>
                            </div>
                        `;
                    } else if (conclusion === 'NO_SIGNATURE') {
                        statusCardHtml = `
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 flex items-start gap-4 shadow-sm">
                                <div class="w-10 h-10 rounded-lg bg-slate-500/10 flex items-center justify-center text-slate-500 shrink-0">
                                    <i class="fas fa-file-excel text-lg"></i>
                                </div>
                                <div class="space-y-1">
                                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight leading-tight">Tidak Ada Tanda Tangan</h3>
                                    <p class="text-[11px] text-slate-600 leading-relaxed">Dokumen PDF ini tidak memiliki tanda tangan elektronik (TTE) tersertifikasi yang terdeteksi.</p>
                                </div>
                            </div>
                        `;
                    } else {
                        let title = 'TTE Terdeteksi (Perlu Perhatian)';
                        let desc = 'Dokumen memiliki tanda tangan elektronik, namun keutuhan berkas atau status sertifikat tidak sepenuhnya terverifikasi.';
                        let bgClass = 'bg-amber-50 border-amber-200 text-amber-700';
                        let titleClass = 'text-amber-900';
                        let iconClass = 'bg-amber-500/10 text-amber-600';
                        let icon = 'fas fa-exclamation-triangle';

                        if (!bsreData.integrityValid) {
                            title = 'Dokumen Telah Dimodifikasi';
                            desc = 'PENTING: Isi dokumen ini telah mengalami perubahan atau modifikasi setelah ditandatangani secara elektronik.';
                            bgClass = 'bg-red-50 border-red-200 text-red-700';
                            titleClass = 'text-red-900';
                            iconClass = 'bg-red-500/10 text-red-600';
                            icon = 'fas fa-file-signature';
                        }

                        statusCardHtml = `
                            <div class="${bgClass} border rounded-xl p-5 flex items-start gap-4 shadow-sm">
                                <div class="w-10 h-10 rounded-lg ${iconClass} flex items-center justify-center shrink-0">
                                    <i class="${icon} text-lg"></i>
                                </div>
                                <div class="space-y-1">
                                    <h3 class="text-xs font-bold ${titleClass} uppercase tracking-tight leading-tight">${title}</h3>
                                    <p class="text-[11px] leading-relaxed">${desc}</p>
                                </div>
                            </div>
                        `;
                    }

                    // 2. Integrity & Trust Badges in Grid
                    const integrityBadge = bsreData.integrityValid 
                        ? '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 uppercase tracking-wider"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Utuh (Original)</span>'
                        : '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200 uppercase tracking-wider"><span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>Telah Dimodifikasi</span>';

                    const trustBadge = bsreData.certificateTrusted 
                        ? '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 uppercase tracking-wider"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Terpercaya (BSSN)</span>'
                        : '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 uppercase tracking-wider"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Lokal / Tidak Terpercaya</span>';

                    const summaryGridHtml = `
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Jumlah TTE</span>
                                <span class="text-xs font-bold text-slate-700 block">${count} Tanda Tangan</span>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Keutuhan Berkas</span>
                                <div class="block mt-0.5">${integrityBadge}</div>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Kepercayaan Sertifikat</span>
                                <div class="block mt-0.5">${trustBadge}</div>
                            </div>
                        </div>
                    `;

                    // 3. Signers List Html
                    let signersListHtml = '';
                    if (bsreData.signatureInformations && bsreData.signatureInformations.length > 0) {
                        signersListHtml += `
                            <div class="space-y-4">
                                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-widest border-b border-slate-100 pb-2 flex items-center gap-2">
                                    <i class="fas fa-file-signature text-slate-500"></i> Detail Penandatangan
                                </h4>
                                <div class="space-y-3">
                        `;
                        
                        bsreData.signatureInformations.forEach((info, index) => {
                            const dateSigned = info.signatureDate ? info.signatureDate : '-';
                            const location = info.location ? info.location : '-';
                            const reason = info.reason ? info.reason : '-';
                            const format = info.signatureFormat || 'PKCS7';
                            const issuerName = info.certificateDetails?.[0]?.issuerName || '-';
                            const commonName = info.certificateDetails?.[0]?.commonName || 'BSrE BSSN';
                            const serialNumber = info.certificateDetails?.[0]?.serialNumber || '-';
                            
                            signersListHtml += `
                                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden hover:border-slate-300 transition-all shadow-sm">
                                    <!-- Header Card -->
                                    <div class="bg-slate-50/50 px-4 py-3 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <div class="w-6 h-6 rounded-lg bg-slate-800 text-white flex items-center justify-center text-xs shrink-0 font-bold">
                                                ${index + 1}
                                            </div>
                                            <div class="min-w-0">
                                                <span class="block text-xs font-bold text-slate-800 truncate">${info.signerName}</span>
                                            </div>
                                        </div>
                                        <div class="flex gap-1.5 shrink-0">
                                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-[8px] font-bold uppercase tracking-wider border border-slate-200">${format}</span>
                                            <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[8px] font-bold uppercase tracking-wider border border-indigo-100">${info.fieldName}</span>
                                        </div>
                                    </div>

                                    <!-- Content Card -->
                                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-[11px] leading-relaxed">
                                        <!-- Kiri: Informasi Tanda Tangan -->
                                        <div class="space-y-2">
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block border-b border-slate-50 pb-1">Detail Transaksi</span>
                                            <div class="space-y-1">
                                                <div class="flex justify-between md:justify-start gap-4">
                                                    <span class="text-slate-500 font-medium w-20 shrink-0">Waktu TTE:</span>
                                                    <span class="text-slate-800 font-semibold break-all">${dateSigned}</span>
                                                </div>
                                                <div class="flex justify-between md:justify-start gap-4">
                                                    <span class="text-slate-500 font-medium w-20 shrink-0">Lokasi:</span>
                                                    <span class="text-slate-800">${location}</span>
                                                </div>
                                                <div class="flex justify-between md:justify-start gap-4">
                                                    <span class="text-slate-500 font-medium w-20 shrink-0">Alasan:</span>
                                                    <span class="text-slate-700 italic">${reason}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Kanan: Sertifikat Elektronik -->
                                        <div class="space-y-2">
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block border-b border-slate-50 pb-1">Sertifikat Digital</span>
                                            <div class="space-y-1">
                                                <div class="flex justify-between md:justify-start gap-4">
                                                    <span class="text-slate-500 font-medium w-20 shrink-0">Common Name:</span>
                                                    <span class="text-slate-800 font-semibold truncate" title="${commonName}">${commonName}</span>
                                                </div>
                                                <div class="flex justify-between md:justify-start gap-4">
                                                    <span class="text-slate-500 font-medium w-20 shrink-0">Penerbit:</span>
                                                    <span class="text-slate-700 truncate" title="${issuerName}">${issuerName}</span>
                                                </div>
                                                <div class="flex justify-between md:justify-start gap-4">
                                                    <span class="text-slate-500 font-medium w-20 shrink-0">Serial:</span>
                                                    <span class="text-slate-600 font-mono text-[10px] break-all">${serialNumber}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
 
                        signersListHtml += `
                                </div>
                            </div>
                        `;
                    }

                    resultContainer.innerHTML = `
                        ${statusCardHtml}
                        ${summaryGridHtml}
                        ${signersListHtml}
                    `;
                    
                    // Scroll to result smoothly
                    resultContainer.scrollIntoView({ behavior: 'smooth' });
                } else {
                    showGlobalError('Gagal Verifikasi', result.message || 'Terjadi kesalahan sistem saat memproses berkas PDF.');
                }
            } catch (err) {
                showGlobalLoading(false);
                showGlobalError('Error Jaringan', 'Gagal menghubungi server aplikasi.');
            }
        });
    </script>
</body>

</html>
