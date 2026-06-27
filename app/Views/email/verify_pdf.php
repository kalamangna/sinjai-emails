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
                    
                    let badgeClass = 'bg-slate-100 text-slate-700';
                    let conclusionText = conclusion;

                    if (conclusion === 'NO_SIGNATURE') {
                        badgeClass = 'bg-red-50 text-red-700 border border-red-200';
                        conclusionText = 'Tidak Memiliki Tanda Tangan Elektronik';
                    } else if (conclusion === 'SUCCESS') {
                        badgeClass = 'bg-emerald-50 text-emerald-800 border border-emerald-200';
                        conclusionText = 'Tanda Tangan Elektronik Valid';
                    } else {
                        badgeClass = 'bg-amber-50 text-amber-800 border border-amber-200';
                    }

                    // Integrity & Trust Badges
                    const integrityBadge = bsreData.integrityValid 
                        ? '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 uppercase tracking-wider">Keutuhan Dokumen Terjamin (Utuh)</span>'
                        : '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-700 border border-red-200 uppercase tracking-wider">Dokumen Rusak / Telah Dimodifikasi</span>';

                    const trustBadge = bsreData.certificateTrusted 
                        ? '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 uppercase tracking-wider">Sertifikat Terpercaya</span>'
                        : '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 uppercase tracking-wider">Sertifikat Tidak Terpercaya / Lokal</span>';

                    let signersListHtml = '';
                    if (bsreData.signatureInformations && bsreData.signatureInformations.length > 0) {
                        signersListHtml += `
                            <div class="space-y-4">
                                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-widest border-b border-slate-100 pb-2">Daftar Penandatangan</h4>
                                <div class="space-y-3">
                        `;
                        
                        bsreData.signatureInformations.forEach((info, index) => {
                            const dateSigned = info.signatureDate ? info.signatureDate : '-';
                            const location = info.location ? info.location : '-';
                            const reason = info.reason ? info.reason : '-';
                            
                            signersListHtml += `
                                <div class="p-4 bg-slate-50 border border-slate-200 rounded-lg flex flex-col md:flex-row justify-between gap-4">
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-xs font-bold text-slate-800">${info.signerName}</span>
                                            <span class="px-1.5 py-0.5 rounded bg-slate-200 text-slate-700 text-[8px] font-bold uppercase">${info.signatureFormat || 'PKCS7'}</span>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1 text-[11px] text-slate-600">
                                            <div><span class="font-semibold text-slate-400 uppercase tracking-widest text-[9px] mr-1">Waktu:</span> ${dateSigned}</div>
                                            <div><span class="font-semibold text-slate-400 uppercase tracking-widest text-[9px] mr-1">Lokasi:</span> ${location}</div>
                                            <div><span class="font-semibold text-slate-400 uppercase tracking-widest text-[9px] mr-1">Alasan:</span> ${reason}</div>
                                            <div><span class="font-semibold text-slate-400 uppercase tracking-widest text-[9px] mr-1">Field TTE:</span> ${info.fieldName}</div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-1 items-start md:items-end justify-center shrink-0">
                                        <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-widest">Penerbit Sertifikat:</span>
                                        <span class="text-[11px] font-medium text-slate-700 truncate max-w-xs md:text-right" title="${info.certificateDetails?.[0]?.issuerName || '-'}">
                                            ${info.certificateDetails?.[0]?.commonName || 'BSrE BSSN'}
                                        </span>
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
                        <div class="border-b border-slate-100 pb-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div>
                                <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-widest">Hasil Analisis Dokumen</span>
                                <div class="flex items-center gap-3 mt-1 flex-wrap">
                                    <span class="px-2.5 py-1 rounded text-xs font-black uppercase tracking-wider ${badgeClass}">${conclusionText}</span>
                                    <span class="text-xs text-slate-600">Terdeteksi <strong>${count}</strong> Tanda Tangan Elektronik</span>
                                </div>
                            </div>
                            <div class="flex gap-2 flex-wrap">
                                ${integrityBadge}
                                ${trustBadge}
                            </div>
                        </div>
                        
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
