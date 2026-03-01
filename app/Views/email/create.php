<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= site_url('email') ?>" class="inline-flex items-center justify-center w-10 h-10 bg-white border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition-all shadow-sm no-underline">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Buat Akun Tunggal</h1>
    </div>

    <!-- Card Input -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Input Data</h3>
            <button type="button" class="text-[10px] font-bold text-slate-700 hover:text-slate-800 uppercase tracking-widest transition-colors flex items-center" onclick="nameInput.value = nameInput.value.toUpperCase(); updateDraft();">
                <i class="fas fa-font mr-1.5"></i> Huruf Kapital
            </button>
        </div>
        <form id="create_single_form" class="p-6 space-y-6">
            <?= csrf_field() ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Nama Lengkap <span class="text-slate-700 font-normal">(Tanpa Gelar)</span></label>
                    <input type="text" id="name" name="name" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 uppercase transition-all" required placeholder="Contoh: BUDI SANTOSO">
                </div>
                
                <div>
                    <label for="nip" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">NIP</label>
                    <input type="text" id="nip" name="nip" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 font-mono transition-all" placeholder="Contoh: 198801082022031001" required maxlength="18">
                </div>

                <div>
                    <label for="nik" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">NIK <span class="text-slate-700 font-normal">(Opsional)</span></label>
                    <input type="text" id="nik" name="nik" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 font-mono transition-all" placeholder="Contoh: 730701XXXXXXXXXX" maxlength="16">
                </div>

                <div>
                    <label for="status_asn" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Status ASN</label>
                    <select id="status_asn" name="jenisFormasi" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 appearance-none cursor-pointer transition-all" required>
                        <option value="" disabled selected>Pilih Status...</option>
                        <?php foreach ($status_asn_options as $option): ?>
                            <option value="<?= esc($option['id']) ?>"><?= esc($option['nama_status_asn']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="unit_kerja" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Unit Kerja</label>
                    <select id="unit_kerja" name="unitKerja" class="choices-search block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 appearance-none cursor-pointer transition-all" required>
                        <option value="" disabled selected>Pilih Unit Kerja...</option>
                        <?php foreach ($unit_kerja_options as $unit): ?>
                            <option value="<?= esc($unit['nama_unit_kerja']) ?>"><?= esc($unit['nama_unit_kerja']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button type="button" id="preview_btn" class="inline-flex items-center px-6 py-2 bg-slate-800 text-white rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-slate-700 transition-all shadow-sm">
                    <i class="fas fa-eye mr-2 text-white/80"></i> Preview
                </button>
            </div>
        </form>
    </div>

    <!-- Preview Section -->
    <div id="preview_section" class="hidden bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Hasil Preview</h3>
        </div>
        <div class="overflow-x-auto">
            <table id="preview_table" class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200">NIP / NIK</th>
                        <th class="px-6 py-3 border-b border-slate-200">Nama</th>
                        <th class="px-6 py-3 border-b border-slate-200">Email</th>
                        <th class="px-6 py-3 border-b border-slate-200">Password</th>
                        <th class="px-6 py-3 border-b border-slate-200 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <!-- Populated by JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Execution Progress Section -->
    <div id="execution_section" class="hidden bg-white border border-slate-200 rounded-xl shadow-sm p-6 space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">Progres Eksekusi</h3>
            <span id="status_badge" class="px-2 py-0.5 rounded text-[9px] font-bold border">MEMULAI</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-2">
            <div id="progress_bar" class="bg-blue-600 h-full rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
        <div id="results_log" class="p-4 bg-slate-800 text-white rounded-lg text-[10px] font-mono h-48 overflow-y-auto custom-scrollbar"></div>
    </div>

    <div class="flex justify-end">
        <button type="button" id="submit_btn" class="inline-flex items-center px-8 py-3 bg-slate-800 text-white rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-slate-700 shadow-sm transition-all disabled:opacity-40 disabled:cursor-not-allowed" disabled>
            <i class="fas fa-cloud-upload-alt mr-2 text-white/80"></i> Eksekusi
        </button>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const nameInput = document.getElementById('name');
    const nikInput = document.getElementById('nik');
    const nipInput = document.getElementById('nip');
    const statusAsnSelect = document.getElementById('status_asn');
    const unitKerjaSelect = document.getElementById('unit_kerja');
    const form = document.getElementById('create_single_form');
    
    const previewBtn = document.getElementById('preview_btn');
    const previewSection = document.getElementById('preview_section');
    const previewTableBody = document.querySelector('#preview_table tbody');
    
    const submitBtn = document.getElementById('submit_btn');
    const executionSection = document.getElementById('execution_section');
    const progressBar = document.getElementById('progress_bar');
    const resultsLog = document.getElementById('results_log');
    const statusBadge = document.getElementById('status_badge');

    let currentDraft = null;

    previewBtn.addEventListener('click', async function() {
        const name = nameInput.value.trim();
        const nip = nipInput.value.trim();
        const nik = nikInput.value.trim();
        const statusAsn = statusAsnSelect.value;
        const unitKerja = unitKerjaSelect.value;

        if (!name || !nip || !statusAsn || !unitKerja) {
            alert('Silakan lengkapi data wajib (Nama, NIP, Status ASN, dan Unit Kerja).');
            return;
        }

        previewBtn.disabled = true;
        previewBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
        
        // Generate Draft
        const domain = "@sinjaikab.go.id";
        const cleanedName = name.replace(/[,.']/g, "");
        const username = cleanedName.toLowerCase().replace(/\s+/g, "").substring(0, 30 - domain.length);
        const email = username + domain;
        const password = generatePassword(name, nip);

        // Check availability
        const emailCheck = await checkEmailAvailability(email);
        const nikCheck = nik ? await checkNikNip('nik', nik) : { exists: false };
        const nipCheck = await checkNikNip('nip', nip);

        currentDraft = {
            name: cleanedName,
            nik: nik,
            nip: nip,
            jenisFormasi: statusAsn,
            unitKerja: unitKerja,
            username: username,
            email: email,
            password: password,
            isAvailable: emailCheck.available,
            nikExists: nikCheck.exists,
            nipExists: nipCheck.exists
        };

        renderPreview(currentDraft);
        
        previewBtn.disabled = false;
        previewBtn.innerHTML = '<i class="fas fa-eye mr-2 text-white/80"></i> Preview';
        previewSection.classList.remove('hidden');
        previewSection.scrollIntoView({ behavior: 'smooth', block: 'center' });

        // Enable execution if no critical errors
        submitBtn.disabled = !(currentDraft.isAvailable && !currentDraft.nipExists);
    });

    function renderPreview(draft) {
        previewTableBody.innerHTML = "";
        
        let statusBadge;
        const badgeBase = "inline-flex items-center px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border shadow-sm";

        if (draft.isAvailable && !draft.nipExists) {
            statusBadge = `<span class="${badgeBase} bg-emerald-50 text-emerald-600 border-emerald-200">Ready</span>`;
        } else if (draft.nipExists) {
            statusBadge = `<span class="${badgeBase} bg-red-50 text-red-600 border-red-200">NIP Exists</span>`;
        } else {
            statusBadge = `<span class="${badgeBase} bg-amber-50 text-amber-600 border-amber-200">Email Used</span>`;
        }

        const tagBase = "ml-1.5 px-1.5 py-0.5 rounded text-[8px] font-black uppercase";
        let nipDisplay = `<span class="font-mono text-slate-700">${draft.nip}</span>`;
        if (draft.nipExists) nipDisplay += `<span class="${tagBase} bg-red-50 text-red-600">DB</span>`;

        let nikDisplay = `<span class="font-mono text-slate-700">${draft.nik || "-"}</span>`;
        if (draft.nikExists) nikDisplay += `<span class="${tagBase} bg-red-50 text-red-600">DB</span>`;

        const row = `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-5 whitespace-nowrap">
                    <div class="flex flex-col gap-1">
                        ${nipDisplay}
                        ${nikDisplay}
                    </div>
                </td>
                <td class="px-6 py-5 font-black text-slate-800 tracking-tight whitespace-nowrap">${draft.name}</td>
                <td class="px-6 py-5 whitespace-nowrap font-bold text-slate-700 lowercase">${draft.email}</td>
                <td class="px-6 py-5 whitespace-nowrap font-mono text-slate-600">${draft.password}</td>
                <td class="px-6 py-5 text-center whitespace-nowrap">${statusBadge}</td>
            </tr>`;
        
        previewTableBody.insertAdjacentHTML("beforeend", row);
    }

    async function checkNikNip(type, value) {
        try {
            const response = await fetch('<?= site_url('user/check_niknip') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ [type]: value })
            });
            return await response.json();
        } catch (e) { return { exists: false }; }
    }

    async function checkEmailAvailability(email) {
        try {
            const response = await fetch('<?= site_url('user/check_email') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ email: email })
            });
            return await response.json();
        } catch (e) { return { available: false }; }
    }

    function generatePassword(name, nip, useAltNipPart = false) {
        let suffix = new Date().getDate();
        if (nip && nip.length >= 8) {
            suffix = useAltNipPart ? nip.substring(6, 8) : nip.substring(2, 4);
        } else if (nip && nip.length >= 4) {
            suffix = nip.substring(2, 4);
        }
        const namePart = name.replace(/\s+/g, "").substring(0, 5).toLowerCase();
        if (!namePart) return `@${suffix}#`;
        const capitalizedNamePart = namePart.charAt(0).toUpperCase() + namePart.slice(1);
        return `${capitalizedNamePart}@${suffix}#`;
    }

    submitBtn.addEventListener('click', async function() {
        if (!currentDraft) return;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
        executionSection.classList.remove('hidden');
        executionSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        resultsLog.innerHTML = '';
        progressBar.style.width = '10%';
        statusBadge.className = 'px-2 py-0.5 rounded text-[9px] font-bold border bg-blue-50 text-blue-600 border-blue-200';
        statusBadge.innerText = 'SEDANG MEMPROSES';

        const data = { ...currentDraft, quota: 1024 };

        try {
            logResult('INFO', 'Mengirim data ke server...');
            progressBar.style.width = '40%';

            const response = await fetch('<?= site_url('email/create_single') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                progressBar.style.width = '100%';
                logResult('SUCCESS', 'Akun berhasil dibuat di server dan database.');
                statusBadge.className = 'px-2 py-0.5 rounded text-[9px] font-bold border bg-emerald-50 text-emerald-600 border-emerald-200';
                statusBadge.innerText = 'BERHASIL';
                setTimeout(() => {
                    alert('Akun berhasil dibuat!');
                    window.location.href = '<?= site_url('email') ?>';
                }, 1000);
            } else {
                const errorMsg = result.message || 'Gagal membuat akun.';
                logResult('FAILURE', errorMsg);
                
                if (errorMsg.toLowerCase().includes('strength') || errorMsg.toLowerCase().includes('weak')) {
                    const altPw = generatePassword(data.name, data.nip, true);
                    if (currentDraft.password === altPw) {
                        currentDraft.password = altPw + '*';
                    } else {
                        currentDraft.password = altPw;
                    }
                    logResult('WARN', 'Password terlalu lemah. Sistem telah memperbarui ke varian lebih kuat.');
                    logResult('INFO', 'Silakan klik "Eksekusi" sekali lagi.');
                    renderPreview(currentDraft);
                }
                
                statusBadge.className = 'px-2 py-0.5 rounded text-[9px] font-bold border bg-red-50 text-red-600 border-red-200';
                statusBadge.innerText = 'GAGAL';
                progressBar.style.width = '0%';
            }
        } catch (error) {
            logResult('FAILURE', 'Error: ' + error.message);
            statusBadge.className = 'px-2 py-0.5 rounded text-[9px] font-bold border bg-red-50 text-red-600 border-red-200';
            statusBadge.innerText = 'ERROR';
            progressBar.style.width = '0%';
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-cloud-upload-alt mr-2 text-white/80"></i> Eksekusi';
        }
    });

    function logResult(status, message) {
        const colors = {
            'SUCCESS': 'text-emerald-500',
            'FAILURE': 'text-red-500',
            'WARN': 'text-amber-500',
            'INFO': 'text-blue-400'
        };
        const color = colors[status] || 'text-white';
        const entry = `<div>[<span class="${color} font-bold">${status}</span>] ${message}</div>`;
        resultsLog.insertAdjacentHTML('beforeend', entry);
        resultsLog.scrollTop = resultsLog.scrollHeight;
    }
</script>
<?= $this->endSection() ?>
