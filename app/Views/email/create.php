<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= site_url('email') ?>" class="inline-flex items-center justify-center w-10 h-10 bg-white border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition-all shadow-sm no-underline">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Buat Akun Tunggal</h1>
    </div>

    <!-- Card Utama -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <form id="create_single_form" class="p-8 space-y-8">
            <?= csrf_field() ?>
            
            <div class="space-y-6">
                <div class="flex justify-between items-center border-b border-slate-100 pb-1">
                    <h4 class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">Data Minimal</h4>
                    <button type="button" class="text-[10px] font-bold text-slate-700 hover:text-slate-800 uppercase tracking-widest transition-colors flex items-center" onclick="nameInput.value = nameInput.value.toUpperCase(); updateDraft();">
                        <i class="fas fa-font mr-1.5"></i> Huruf Kapital
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Nama Lengkap <span class="text-slate-700 font-normal">(Tanpa Gelar)</span></label>
                        <input type="text" id="name" name="name" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 uppercase transition-all" required placeholder="Contoh: BUDI SANTOSO">
                    </div>
                    
                    <div>
                        <label for="nip" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">NIP</label>
                        <div class="relative">
                            <input type="text" id="nip" name="nip" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 font-mono transition-all" placeholder="Contoh: 198801082022031001" required maxlength="18">
                            <div id="nip_status" class="absolute right-3 top-2.5"></div>
                        </div>
                    </div>

                    <div>
                        <label for="nik" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">NIK <span class="text-slate-700 font-normal">(Opsional)</span></label>
                        <div class="relative">
                            <input type="text" id="nik" name="nik" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 font-mono transition-all" placeholder="Contoh: 730701XXXXXXXXXX" maxlength="16">
                            <div id="nik_status" class="absolute right-3 top-2.5"></div>
                        </div>
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
            </div>

            <div class="space-y-6">
                <h4 class="text-[10px] font-bold text-slate-700 uppercase tracking-widest border-b border-slate-100 pb-1">Konfigurasi Akun</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Username Email</label>
                        <div class="relative">
                            <input type="text" id="username" name="username" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 transition-all" required placeholder="Contoh: budisans">
                            <span class="absolute right-3 top-2 text-sm text-slate-400 font-medium">@sinjaikab.go.id</span>
                            <div id="email_status" class="absolute -bottom-5 left-0"></div>
                        </div>
                        <input type="hidden" id="email" name="email">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Password</label>
                        <div class="relative">
                            <input type="text" id="password" name="password" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 font-mono transition-all" required>
                            <button type="button" onclick="regeneratePassword()" class="absolute right-2 top-1.5 p-1 text-slate-400 hover:text-blue-600 transition-colors" title="Generate Ulang">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="execution_section" class="hidden space-y-4 pt-6 border-t border-slate-100">
                <div class="flex justify-between items-center">
                    <h3 class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">Progres Eksekusi</h3>
                    <span id="status_badge" class="px-2 py-0.5 rounded text-[9px] font-bold border">SEDANG MEMPROSES</span>
                </div>
                <div id="results_log" class="p-4 bg-slate-800 text-white rounded-lg text-[10px] font-mono h-32 overflow-y-auto custom-scrollbar"></div>
            </div>

            <div class="bg-slate-50 px-8 py-4 flex flex-col sm:flex-row justify-end gap-3 border-t border-slate-200 -mx-8 -mb-8 mt-10">
                <a href="<?= site_url('email') ?>" class="order-2 sm:order-1 inline-flex items-center justify-center px-6 py-2 bg-white border border-slate-200 rounded-lg font-bold text-xs text-slate-700 uppercase tracking-widest hover:bg-slate-50 shadow-sm transition-all no-underline">
                    <i class="fas fa-times mr-2"></i> Batal
                </a>
                <button type="submit" id="submit_btn" class="order-1 sm:order-2 inline-flex items-center justify-center px-8 py-2 bg-slate-800 text-white rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-slate-700 shadow-sm transition-all">
                    <i class="fas fa-save mr-2 text-white/80"></i> Simpan Akun
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const nameInput = document.getElementById('name');
    const nikInput = document.getElementById('nik');
    const nipInput = document.getElementById('nip');
    const usernameInput = document.getElementById('username');
    const emailHidden = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const statusAsnSelect = document.getElementById('status_asn');
    const form = document.getElementById('create_single_form');
    const submitBtn = document.getElementById('submit_btn');
    const executionSection = document.getElementById('execution_section');
    const resultsLog = document.getElementById('results_log');
    const statusBadge = document.getElementById('status_badge');

    const nikStatus = document.getElementById('nik_status');
    const nipStatus = document.getElementById('nip_status');
    const emailStatus = document.getElementById('email_status');

    nameInput.addEventListener('input', updateDraft);
    nipInput.addEventListener('input', updateDraft);
    
    usernameInput.addEventListener('input', () => {
        const val = usernameInput.value.trim().toLowerCase();
        emailHidden.value = val ? val + '@sinjaikab.go.id' : '';
        if (val) checkEmail(emailHidden.value);
        else emailStatus.innerHTML = '';
    });

    nikInput.addEventListener('change', () => {
        if (nikInput.value.length >= 10) checkNikNip('nik', nikInput.value, nikStatus);
    });

    nipInput.addEventListener('change', () => {
        if (nipInput.value.length >= 10) checkNikNip('nip', nipInput.value, nipStatus);
    });

    async function checkNikNip(type, value, indicator) {
        indicator.innerHTML = '<i class="fas fa-spinner fa-spin text-slate-400"></i>';
        try {
            const response = await fetch('<?= site_url('user/check_niknip') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ [type]: value })
            });
            const result = await response.json();
            if (result.exists) {
                indicator.innerHTML = '<i class="fas fa-exclamation-circle text-red-500" title="Data sudah ada di database"></i>';
            } else {
                indicator.innerHTML = '<i class="fas fa-check-circle text-emerald-500" title="Data tersedia"></i>';
            }
        } catch (e) {
            indicator.innerHTML = '';
        }
    }

    async function checkEmail(email) {
        emailStatus.innerHTML = '<span class="text-[9px] text-slate-400 uppercase font-bold"><i class="fas fa-spinner fa-spin mr-1"></i> Checking...</span>';
        try {
            const response = await fetch('<?= site_url('user/check_email') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ email: email })
            });
            const result = await response.json();
            if (result.available) {
                emailStatus.innerHTML = '<span class="text-[9px] text-emerald-600 uppercase font-bold"><i class="fas fa-check-circle mr-1"></i> Email Tersedia</span>';
            } else {
                emailStatus.innerHTML = '<span class="text-[9px] text-red-500 uppercase font-bold"><i class="fas fa-exclamation-circle mr-1"></i> Email Sudah Digunakan</span>';
            }
        } catch (e) {
            emailStatus.innerHTML = '';
        }
    }

    function updateDraft() {
        const name = nameInput.value.trim();
        const nip = nipInput.value.trim();
        
        if (name) {
            const domain = "@sinjaikab.go.id";
            const maxUsernameLength = 30 - domain.length;
            const cleanedName = name.replace(/[,.']/g, "");
            const username = cleanedName
                .toLowerCase()
                .replace(/\s+/g, "")
                .substring(0, maxUsernameLength);
            
            if (!usernameInput.value) {
                usernameInput.value = username;
                emailHidden.value = username + domain;
                checkEmail(emailHidden.value);
            }
            
            if (!passwordInput.value) {
                passwordInput.value = generatePassword(name, nip);
            }
        }
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

    function regeneratePassword() {
        passwordInput.value = generatePassword(nameInput.value, nipInput.value);
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
        executionSection.classList.remove('hidden');
        resultsLog.innerHTML = '';
        statusBadge.className = 'px-2 py-0.5 rounded text-[9px] font-bold border bg-blue-50 text-blue-600 border-blue-200';
        statusBadge.innerText = 'SEDANG MEMPROSES';

        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => data[key] = value);
        data['quota'] = 1024;

        try {
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
                logResult('SUCCESS', 'Akun berhasil dibuat.');
                statusBadge.className = 'px-2 py-0.5 rounded text-[9px] font-bold border bg-emerald-50 text-emerald-600 border-emerald-200';
                statusBadge.innerText = 'BERHASIL';
                alert('Akun berhasil dibuat!');
                window.location.href = '<?= site_url('email') ?>';
            } else {
                const errorMsg = result.message || 'Gagal membuat akun.';
                logResult('FAILURE', errorMsg);
                
                if (errorMsg.toLowerCase().includes('strength') || errorMsg.toLowerCase().includes('weak')) {
                    const altPw = generatePassword(data.name, data.nip, true);
                    if (passwordInput.value === altPw) {
                        passwordInput.value = altPw + '*';
                    } else {
                        passwordInput.value = altPw;
                    }
                    logResult('INFO', 'Password diperbarui ke varian lebih kuat. Silakan klik Simpan lagi.');
                }
                
                statusBadge.className = 'px-2 py-0.5 rounded text-[9px] font-bold border bg-red-50 text-red-600 border-red-200';
                statusBadge.innerText = 'GAGAL';
            }
        } catch (error) {
            logResult('FAILURE', 'Terjadi kesalahan jaringan atau server.');
            statusBadge.className = 'px-2 py-0.5 rounded text-[9px] font-bold border bg-red-50 text-red-600 border-red-200';
            statusBadge.innerText = 'ERROR';
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2 text-white/80"></i> Simpan Akun';
        }
    });

    function logResult(status, message) {
        const colors = {
            'SUCCESS': 'text-emerald-500',
            'FAILURE': 'text-red-500',
            'INFO': 'text-blue-500'
        };
        const color = colors[status] || 'text-white';
        const entry = `<div>[<span class="${color} font-bold">${status}</span>] ${message}</div>`;
        resultsLog.insertAdjacentHTML('beforeend', entry);
        resultsLog.scrollTop = resultsLog.scrollHeight;
    }
</script>
<?= $this->endSection() ?>
