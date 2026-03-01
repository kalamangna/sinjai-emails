<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= site_url('email') ?>" class="btn btn-outline no-underline">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Buat Akun Tunggal</h1>
    </div>

    <!-- Card Input -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Input Data</h3>
            <button type="button" class="btn btn-outline btn-xs" onclick="nameInput.value = nameInput.value.toUpperCase(); updateDraft();">
                <i class="fas fa-font mr-1.5"></i> Huruf Kapital
            </button>
        </div>
        <form id="create_single_form" class="p-6 space-y-6">
            <?= csrf_field() ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="name_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Nama Lengkap <span class="text-slate-700 font-normal">(Tanpa Gelar)</span></label>
                    <input type="text" id="name_input" name="name" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 uppercase transition-all" required placeholder="Contoh: BUDI SANTOSO">
                </div>
                
                <div>
                    <label for="nip_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">NIP</label>
                    <input type="text" id="nip_input" name="nip" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 font-mono transition-all" placeholder="Contoh: 198801082022031001" required maxlength="18">
                </div>

                <div>
                    <label for="nik_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">NIK <span class="text-slate-700 font-normal">(Opsional)</span></label>
                    <input type="text" id="nik_input" name="nik" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 font-mono transition-all" placeholder="Contoh: 730701XXXXXXXXXX" maxlength="16">
                </div>

                <div>
                    <label for="status_asn_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Status ASN</label>
                    <select id="status_asn_input" name="jenisFormasi" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 appearance-none cursor-pointer transition-all" required>
                        <option value="" disabled selected>Pilih Status...</option>
                        <?php foreach ($status_asn_options as $option): ?>
                            <option value="<?= esc($option['id']) ?>"><?= esc($option['nama_status_asn']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="unit_kerja_input" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Unit Kerja</label>
                    <select id="unit_kerja_input" name="unitKerja" class="choices-search block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 appearance-none cursor-pointer transition-all" required>
                        <option value="" disabled selected>Pilih Unit Kerja...</option>
                        <?php foreach ($unit_kerja_options as $unit): ?>
                            <option value="<?= esc($unit['nama_unit_kerja']) ?>"><?= esc($unit['nama_unit_kerja']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button type="button" id="preview_btn" class="btn btn-solid">
                    <i class="fas fa-eye mr-2 text-white/80"></i> Preview
                </button>
            </div>
        </form>
    </div>

    <!-- Preview Section -->
    <div id="preview_section" class="hidden bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Hasil Preview</h3>
        </div>
        <div class="overflow-x-auto">
            <table id="results_table" class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200 w-12">#</th>
                        <th class="px-6 py-3 border-b border-slate-200">NIP</th>
                        <th class="px-6 py-3 border-b border-slate-200">NIK</th>
                        <th class="px-6 py-3 border-b border-slate-200">Nama Lengkap</th>
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
    <div id="progress_section" class="hidden bg-white border border-slate-200 rounded-lg shadow-sm p-6 space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">Progres Eksekusi</h3>
            <span id="progress_text" class="text-[10px] font-bold text-slate-800 uppercase">MEMULAI</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-2">
            <div id="progress_bar" class="bg-slate-700 h-full rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
        <div id="results_log" class="p-4 bg-slate-800 text-white rounded-lg text-[10px] font-mono h-48 overflow-y-auto custom-scrollbar"></div>
    </div>

    <div class="flex justify-end">
        <button type="button" id="submit_btn" class="btn btn-solid btn-lg" disabled>
            <i class="fas fa-cloud-upload-alt mr-2 text-white/80"></i> Eksekusi
        </button>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const nameInput = document.getElementById('name_input');
    const nikInput = document.getElementById('nik_input');
    const nipInput = document.getElementById('nip_input');
    const statusAsnSelect = document.getElementById('status_asn_input');
    const unitKerjaSelect = document.getElementById('unit_kerja_input');
    
    const previewBtn = document.getElementById('preview_btn');
    const previewSection = document.getElementById('preview_section');
    const resultsTableBody = document.querySelector('#results_table tbody');
    
    const submitBtn = document.getElementById('submit_btn');
    const progressSection = document.getElementById('progress_section');
    const progressBar = document.getElementById('progress_bar');
    const progressText = document.getElementById('progress_text');
    const resultsLog = document.getElementById('results_log');

    let userBatch = []; // Wrap single user in an array to match batch logic

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
        resultsTableBody.innerHTML = '<tr><td colspan="8" class="px-10 py-12 text-center text-slate-700 font-bold uppercase tracking-widest text-[10px] animate-pulse">Sedang memproses dan memeriksa email...</td></tr>';
        
        // Base Draft Config
        const domain = "@sinjaikab.go.id";
        const cleanedName = name.replace(/[,.']/g, "");
        const originalUsername = cleanedName.toLowerCase().replace(/\s+/g, "").substring(0, 30 - domain.length);
        const password = generatePassword(name, nip);

        // Check NIK/NIP availability in DB
        const nikCheckResult = nik ? await checkNikOnServer(nik) : { exists: false };
        const nipCheckResult = await checkNipOnServer(nip);

        // Email Fallback Loop
        let currentUsername = originalUsername;
        let currentEmail = currentUsername + domain;
        let isAvailable = false;
        let attempts = 0;
        const maxAttempts = 10;

        while (attempts < maxAttempts) {
            attempts++;
            const check = await checkEmailAvailability(currentEmail);
            if (check.available) {
                isAvailable = true;
                break;
            }
            let suffix = "";
            if (attempts === 1) suffix = getNipPart(nip);
            else if (attempts === 2) suffix = getSecondNipPart(nip);
            else if (attempts === 3) suffix = getNikPart(nik);
            else {
                let base = getNipPart(nip) || getNikPart(nik);
                suffix = (base || "") + attempts;
            }
            if (!suffix) suffix = attempts;
            currentUsername = `${originalUsername}${suffix}`;
            currentEmail = `${currentUsername}${domain}`;
        }

        userBatch = [{
            name: cleanedName,
            nik: nik,
            nip: nip,
            jenisFormasi: statusAsn,
            unitKerja: unitKerja,
            generatedUsername: currentUsername,
            email: currentEmail,
            password: password,
            isAvailable: isAvailable,
            isNikInDb: nikCheckResult.exists,
            isNipInDb: nipCheckResult.exists,
            status: "pending"
        }];

        renderResults(userBatch);
        
        previewBtn.disabled = false;
        previewBtn.innerHTML = '<i class="fas fa-eye mr-2 text-white/80"></i> Preview';
        previewSection.classList.remove('hidden');
        previewSection.scrollIntoView({ behavior: 'smooth', block: 'center' });

        updateSubmitButtonState();
    });

    function updateSubmitButtonState() {
        const canExecute = userBatch.every(u => u.isAvailable && !u.isNipInDb);
        submitBtn.disabled = !canExecute;
    }

    function renderResults(batch) {
        resultsTableBody.innerHTML = "";
        batch.forEach((user, index) => {
            let statusBadge;
            const badgeBase = "inline-flex items-center px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border shadow-sm";

            if (user.status === "created") {
                statusBadge = `<span class="${badgeBase} bg-emerald-100 text-emerald-800 border-transparent">Created</span>`;
            } else if (user.status === "failed") {
                statusBadge = `<span class="${badgeBase} bg-red-100 text-red-700 border-transparent" title="${user.errorMessage || "Failed"}">Failed</span>`;
            } else if (user.isAvailable && !user.isNipInDb) {
                statusBadge = `<span class="${badgeBase} bg-blue-100 text-slate-700 border-transparent">Ready</span>`;
            } else {
                statusBadge = `<span class="${badgeBase} bg-amber-100 text-amber-700 border-transparent">Check Required</span>`;
            }

            const nameCellContent = `<span contenteditable="true" class="editable-name focus:outline-none focus:text-slate-700 transition-colors" data-name-index="${index}">${user.name}</span>`;
            const domain = "@sinjaikab.go.id";
            const username = user.email.substring(0, user.email.indexOf(domain));
            const emailCellContent = `<span contenteditable="true" class="editable-username focus:outline-none focus:text-slate-700 transition-colors" data-username-index="${index}">${username}</span><span class="text-slate-200 font-medium">${domain}</span>`;
            const passwordCellContent = `<span contenteditable="true" class="editable-password font-mono focus:outline-none focus:text-slate-700 transition-colors" data-password-index="${index}">${user.password}</span>`;

            const tagBase = "ml-1.5 px-1.5 py-0.5 rounded text-[8px] font-black uppercase";
            let nikDisplay = `<span class="font-mono text-slate-700">${user.nik || "-"}</span>`;
            if (user.isNikInDb) nikDisplay += `<span class="${tagBase} bg-red-50 text-red-600">DB</span>`;

            let nipDisplay = `<span class="font-mono text-slate-700">${user.nip || "-"}</span>`;
            if (user.isNipInDb) nipDisplay += `<span class="${tagBase} bg-red-50 text-red-600">DB</span>`;

            const row = `
                <tr class="hover:bg-slate-50 transition-colors group">
                    <td class="px-6 py-5 whitespace-nowrap text-[10px] font-black text-slate-700 font-mono">#${index + 1}</td>
                    <td class="px-6 py-5 whitespace-nowrap">${nipDisplay}</td>
                    <td class="px-6 py-5 whitespace-nowrap">${nikDisplay}</td>
                    <td class="px-6 py-5 font-black text-slate-800 tracking-tight whitespace-nowrap">${nameCellContent}</td>
                    <td class="px-6 py-5 whitespace-nowrap font-bold text-slate-700 tracking-tight lowercase">${emailCellContent}</td>
                    <td class="px-6 py-5 whitespace-nowrap">${passwordCellContent}</td>
                    <td class="px-6 py-5 text-center whitespace-nowrap">${statusBadge}</td>
                </tr>`;
            resultsTableBody.insertAdjacentHTML("beforeend", row);

            if (user.status === "failed" && user.errorMessage) {
                const errorRow = `<tr class="error-row"><td colspan="8" class="px-6 py-0"><div class="bg-red-50 text-red-600 px-4 py-2 border-x border-red-200 flex items-center"><i class="fas fa-exclamation-circle mr-3 text-xs opacity-50"></i><span class="text-[10px] font-black uppercase tracking-widest">${user.errorMessage}</span></div></td></tr>`;
                resultsTableBody.insertAdjacentHTML("beforeend", errorRow);
            }
        });
        addEditableListeners();
    }

    function addEditableListeners() {
        document.querySelectorAll(".editable-name, .editable-username, .editable-password").forEach((cell) => {
            cell.addEventListener("blur", handleCellEdit);
            cell.addEventListener("keydown", (e) => { if (e.key === "Enter") { e.preventDefault(); cell.blur(); } });
        });
    }

    async function handleCellEdit(event) {
        const cell = event.target;
        const index = parseInt(cell.dataset.nameIndex || cell.dataset.usernameIndex || cell.dataset.passwordIndex);
        const user = userBatch[index];
        const newContent = cell.textContent.trim();

        if (cell.classList.contains("editable-name")) {
            if (newContent === user.name) return;
            user.name = newContent;
            user.password = generatePassword(newContent, user.nip);
        } else if (cell.classList.contains("editable-username")) {
            const domain = "@sinjaikab.go.id";
            if (newContent + domain === user.email) return;
            user.email = newContent + domain;
        } else if (cell.classList.contains("editable-password")) {
            if (newContent === user.password) return;
            user.password = newContent;
        }

        const check = await checkEmailAvailability(user.email);
        user.isAvailable = check.available;
        renderResults(userBatch);
        updateSubmitButtonState();
    }

    submitBtn.addEventListener('click', async function() {
        if (!userBatch.length) return;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
        progressSection.classList.remove('hidden');
        progressSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        resultsLog.innerHTML = '';
        
        const user = userBatch[0];
        progressText.textContent = "100% (Processing 1 / 1)";
        progressBar.style.width = '100%';

        try {
            logResult(user.email, 'INFO', 'Mengirim data ke server...');
            const response = await fetch('<?= site_url('email/create_single') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ ...user, quota: 1024 })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                user.status = "created";
                logResult(user.email, 'SUCCESS', 'Akun berhasil dibuat.');
                renderResults(userBatch);
                setTimeout(() => { alert('Akun berhasil dibuat!'); window.location.href = '<?= site_url('email') ?>'; }, 1000);
            } else {
                const errorMsg = result.message || 'Gagal membuat akun.';
                user.status = "failed";
                user.errorMessage = errorMsg;
                
                if (errorMsg.toLowerCase().includes('strength') || errorMsg.toLowerCase().includes('weak')) {
                    const altPw = generatePassword(user.name, user.nip, true);
                    if (user.password === altPw) user.password = altPw + '*';
                    else user.password = altPw;
                    logResult(user.email, 'WEAK PW', 'Password diperbarui otomatis. Silakan klik Eksekusi lagi.');
                } else {
                    logResult(user.email, 'FAILURE', errorMsg);
                }
                renderResults(userBatch);
            }
        } catch (error) {
            user.status = "failed";
            user.errorMessage = error.message;
            logResult(user.email, 'FAILURE', error.message);
            renderResults(userBatch);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-cloud-upload-alt mr-2 text-white/80"></i> Eksekusi';
            updateSubmitButtonState();
        }
    });

    function logResult(email, status, message) {
        const colors = { 'SUCCESS': 'text-slate-500', 'FAILURE': 'text-red-500', 'WEAK PW': 'text-amber-500', 'INFO': 'text-slate-500' };
        const entry = `<div>[<span class="${colors[status] || 'text-white'} font-bold">${status}</span>] ${email}: ${message}</div>`;
        resultsLog.insertAdjacentHTML("beforeend", entry);
        resultsLog.scrollTop = resultsLog.scrollHeight;
    }

    async function checkNikOnServer(nik) {
        const r = await fetch('<?= site_url('user/check_niknip') ?>', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ nik }) });
        return await r.json();
    }

    async function checkNipOnServer(nip) {
        const r = await fetch('<?= site_url('user/check_niknip') ?>', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ nip }) });
        return await r.json();
    }

    async function checkEmailAvailability(email) {
        const r = await fetch('<?= site_url('user/check_email') ?>', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ email }) });
        return await r.json();
    }

    function getNipPart(nip) { return (typeof nip === "string" && nip.length >= 4) ? nip.substring(2, 4) : ""; }
    function getSecondNipPart(nip) { return (typeof nip === "string" && nip.length >= 8) ? nip.substring(6, 8) : ""; }
    function getNikPart(nik) { return (typeof nik === "string" && nik.length >= 12) ? nik.substring(10, 12) : ""; }

    function generatePassword(name, nip, useAltNipPart = false) {
        let suffix = new Date().getDate();
        if (nip && nip.length >= 8) suffix = useAltNipPart ? nip.substring(6, 8) : nip.substring(2, 4);
        else if (nip && nip.length >= 4) suffix = nip.substring(2, 4);
        const namePart = name.replace(/\s+/g, "").substring(0, 5).toLowerCase();
        return (namePart ? namePart.charAt(0).toUpperCase() + namePart.slice(1) : "") + `@${suffix}#`;
    }

    function updateDraft() { /* Handled by preview logic now */ }
</script>
<?= $this->endSection() ?>
