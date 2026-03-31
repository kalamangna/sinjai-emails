<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Navigasi dan Aksi -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-4">
            <button onclick="history.back()" class="btn btn-outline !w-10 !h-10 shrink-0">
                <i class="fas fa-arrow-left"></i>
            </button>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 uppercase tracking-tight truncate">Detail Akun</h1>
        </div>
        <div class="grid grid-cols-2 gap-2 w-full sm:flex sm:w-auto">
            <?php if ($showPk = in_array($email['status_asn_id'] ?? 0, [2, 3])): ?>
                <a href="<?= site_url('email/export_single_perjanjian_kerja_pdf/' . $email['user']) ?>" class="btn btn-outline no-underline justify-center flex-1 sm:flex-none">
                    <i class="fas fa-file-contract mr-2 text-slate-700"></i> Export PK
                </a>
            <?php endif; ?>
            <a href="https://<?= config('Cpanel')->cpanel_host ?>:2096" target="_blank" class="btn btn-solid no-underline justify-center <?= !$showPk ? 'col-span-2' : 'flex-1 sm:flex-none' ?>">
                <i class="fas fa-external-link-alt mr-2 text-white/80"></i> Webmail
            </a>
        </div>
    </div>

    <!-- Informasi Utama -->
    <div class="bg-white border border-slate-200 rounded-lg p-6 lg:p-8 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div class="flex flex-col md:flex-row items-center gap-6">
                <div class="w-24 h-24 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-700 border border-slate-200 shadow-sm shrink-0">
                    <i class="fas fa-user-circle text-5xl"></i>
                </div>

                <div class="text-center md:text-left space-y-2">
                    <div class="flex flex-col md:flex-row md:items-center gap-2">
                        <p class="text-2xl font-bold text-slate-800 break-all"><?= esc($email['email']) ?></p>
                        <button class="text-slate-700 hover:text-slate-800 transition-colors p-1" onclick="copyToClipboard('<?= esc($email['email'], 'js') ?>', this)" title="Salin Email">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <p class="text-sm font-medium text-slate-700 uppercase tracking-tight"><?= esc($email['name']) ?></p>

                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 mt-4">
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg">
                            <span class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">Status TTE:</span>
                            <div id="bsre-status-container" class="flex items-center">
                                <span class="text-[10px] font-bold text-slate-700 animate-pulse uppercase">Memeriksa...</span>
                            </div>
                            <?php if (in_array(session()->get('role'), ['super_admin', 'admin'])): ?>
                                <button onclick="syncBsreStatus('<?= esc($email['email'], 'js') ?>')" class="btn btn-solid btn-xs ml-2" title="Sinkronisasi">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if (($email['pimpinan'] ?? 0) == 1): ?>
                            <span class="px-2.5 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                <i class="fas fa-user-tie mr-1"></i> Pimpinan OPD
                            </span>
                        <?php endif; ?>

                        <?php if (($email['pimpinan_desa'] ?? 0) == 1): ?>
                            <span class="px-2.5 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                <i class="fas fa-landmark mr-1"></i> Kepala Desa
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="flex-shrink-0 flex flex-col items-center lg:items-end gap-2 pt-6 lg:pt-0 lg:border-l lg:border-slate-100 lg:pl-8">
                <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">Status Akun</p>
                <?php if (($email['suspended_login'] ?? 0) == 0): ?>
                    <span class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest bg-emerald-100 text-emerald-800 border-transparent">Aktif</span>
                <?php else: ?>
                    <span class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest bg-red-100 text-red-700 border-transparent">Ditangguhkan</span>
                <?php endif; ?>

                <p class="text-[9px] font-bold text-slate-700 uppercase tracking-tight mt-1">
                    Terakhir diperbarui: <span class="text-slate-800"><?= formatTanggalWaktu($email['mtime'] ?? 'now') ?></span>
                </p>
            </div>
        </div>
    </div>
    <!-- Detail Informasi -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profil -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Data Personal & Kepegawaian -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Profil</h3>
                    <div class="flex items-center gap-2">
                        <?php if (in_array(session()->get('role'), ['super_admin', 'admin']) && !empty($email['nip'])): ?>
                            <button onclick="syncPegawai('<?= esc($email['nip'], 'js') ?>', this)" class="btn btn-outline btn-xs uppercase tracking-widest transition-colors flex items-center" title="Sinkronkan Data Pegawai dari API">
                                <i class="fas fa-sync-alt mr-1.5 text-slate-500"></i> Sync Pegawai
                            </button>
                        <?php endif; ?>
                        <?php if (in_array(session()->get('role'), ['super_admin', 'admin'])): ?>
                            <a href="<?= site_url('email/edit_profile/' . $email['user']) ?>" class="btn btn-outline btn-xs no-underline">
                                <i class="fas fa-edit mr-1.5"></i> Edit Profil
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
                    <!-- Kolom 1: Informasi Personal -->
                    <div class="space-y-6">
                        <div>
                            <span class="block text-[10px] font-bold text-slate-700 uppercase tracking-widest mb-1.5">Data Pribadi</span>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[9px] font-bold text-slate-700 uppercase tracking-tight">NIK</label>
                                    <p class="text-sm font-semibold text-slate-800 font-mono"><?= esc($email['nik']) ?: '-' ?></p>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-slate-700 uppercase tracking-tight">Tempat, Tanggal Lahir</label>
                                    <p class="text-sm font-semibold text-slate-800 uppercase">
                                        <?= esc($email['tempat_lahir']) ?: '-' ?>,
                                        <?= formatTanggal($email['tanggal_lahir'] ?? null) ?>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-slate-700 uppercase tracking-tight">Pendidikan Terakhir</label>
                                    <p class="text-sm font-semibold text-slate-800 uppercase"><?= esc($email['pendidikan']) ?: '-' ?></p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[9px] font-bold text-slate-700 uppercase tracking-tight">Gelar Depan</label>
                                        <p class="text-sm font-semibold text-slate-800"><?= esc($email['gelar_depan']) ?: '-' ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-[9px] font-bold text-slate-700 uppercase tracking-tight">Gelar Belakang</label>
                                        <p class="text-sm font-semibold text-slate-800"><?= esc($email['gelar_belakang']) ?: '-' ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom 2: Kepegawaian -->
                    <div class="space-y-6">
                        <div>
                            <span class="block text-[10px] font-bold text-slate-700 uppercase tracking-widest mb-1.5 border-b border-slate-100 pb-1">Kepegawaian</span>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[9px] font-bold text-slate-700 uppercase tracking-tight">NIP</label>
                                    <p class="text-sm font-semibold text-slate-800 font-mono"><?= esc($email['nip']) ?: '-' ?></p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[9px] font-bold text-slate-700 uppercase tracking-tight">Status ASN</label>
                                        <p class="text-sm font-semibold text-slate-800 uppercase mt-1">
                                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-bold uppercase border border-slate-200"><?= $email['status_asn'] ?? '-' ?></span>
                                        </p>
                                    </div>
                                    <?php if (!empty($email['eselon_name'])): ?>
                                        <div>
                                            <label class="block text-[9px] font-bold text-slate-700 uppercase tracking-tight">Eselon</label>
                                            <p class="text-sm font-semibold text-slate-800 uppercase mt-1">
                                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-bold uppercase border border-slate-200">Eselon <?= $email['eselon_name'] ?></span>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[9px] font-bold text-slate-700 uppercase tracking-tight">Pangkat</label>
                                        <p id="pangkat-text" class="text-sm font-semibold text-slate-800 uppercase leading-snug"><?= esc($email['pangkat_nama'] ?? '') ?: '-' ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-[9px] font-bold text-slate-700 uppercase tracking-tight">Golongan Ruang</label>
                                        <p id="golru-text" class="text-sm font-semibold text-slate-800 leading-snug"><?= esc($email['pangkat_golruang'] ?? '') ?: '-' ?></p>
                                    </div>
                                </div>
                                <?php if (!empty($email['golongan'] ?? '')): ?>
                                    <div>
                                        <label class="block text-[9px] font-bold text-slate-700 uppercase tracking-tight">Golongan (PPPK)</label>
                                        <p class="text-sm font-semibold text-slate-800 uppercase mt-1">
                                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-bold uppercase border border-slate-200"><?= esc($email['golongan']) ?></span>
                                        </p>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[9px] font-bold text-slate-700 uppercase tracking-tight">Jabatan</label>
                                    </div>
                                    <p id="jabatan-text" class="text-sm font-semibold text-slate-800 uppercase leading-snug"><?= esc($email['jabatan']) ?: '-' ?></p>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-slate-700 uppercase tracking-tight">Unit Kerja</label>
                                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 hover:border-slate-800 transition-all space-y-1 mt-1">
                                        <?php if (!empty($unit_kerja)): ?>
                                            <?php if (!empty($parent_unit_kerja)): ?>
                                                <a href="<?= site_url('email/unit_kerja/' . $parent_unit_kerja['id']) ?>" class="block no-underline group/parent">
                                                    <p class="text-[10px] font-bold text-slate-700 uppercase group-hover/parent:text-slate-800 transition-colors leading-none"><?= esc($parent_unit_kerja['nama_unit_kerja']) ?></p>
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= site_url('email/unit_kerja/' . $unit_kerja['id']) ?>" class="block no-underline group/child">
                                                <p class="text-xs font-bold text-slate-800 uppercase leading-tight group-hover/child:text-black transition-colors"><?= esc($unit_kerja['nama_unit_kerja']) ?></p>
                                            </a>
                                        <?php else: ?>
                                            <p class="text-xs font-bold text-slate-700 uppercase italic">TIDAK TERDAFTAR</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian: Kontrak Kerja (Hanya jika ada data PK atau status PPPK) -->
            <?php if (!empty($pk_data) || in_array($email['status_asn_id'] ?? 0, [2, 3])): ?>
                <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden flex flex-col">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Perjanjian Kerja (PK)</h3>
                        <?php if (in_array(session()->get('role'), ['super_admin', 'admin'])): ?>
                            <a href="<?= site_url('email/edit_pk/' . $email['user']) ?>" class="btn btn-outline btn-xs no-underline">
                                <i class="fas fa-<?= !empty($pk_data) ? 'edit' : 'plus' ?> mr-1.5"></i> <?= !empty($pk_data) ? 'Edit PK' : 'Tambah PK' ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($pk_data)): ?>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[9px] font-bold text-slate-700 uppercase tracking-tight">Nomor PK</label>
                                    <p class="text-sm font-semibold text-slate-800 font-mono"><?= esc($pk_data['nomor']) ?></p>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-slate-700 uppercase tracking-tight">Masa Kontrak</label>
                                    <div class="flex items-center gap-3 mt-1">
                                        <div class="flex-1 p-2 bg-slate-50 border border-slate-200 rounded text-center">
                                            <span class="block text-[8px] font-bold text-slate-700 uppercase">Mulai</span>
                                            <span class="text-[10px] font-bold text-slate-800"><?= formatSingkat($pk_data['tanggal_kontrak_awal']) ?></span>
                                        </div>
                                        <i class="fas fa-arrow-right text-slate-700 text-[10px]"></i>
                                        <div class="flex-1 p-2 bg-slate-50 border border-slate-200 rounded text-center">
                                            <span class="block text-[8px] font-bold text-slate-700 uppercase">Selesai</span>
                                            <span class="text-[10px] font-bold text-slate-800"><?= formatSingkat($pk_data['tanggal_kontrak_akhir']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[9px] font-bold text-slate-700 uppercase tracking-tight">Gaji</label>
                                    <p class="text-sm font-bold text-slate-800">Rp <?= number_format($pk_data['gaji_nominal'], 0, ',', '.') ?></p>
                                    <p class="text-[10px] font-medium text-slate-700 italic mt-0.5 leading-tight">"<?= esc($pk_data['gaji_terbilang']) ?> Rupiah"</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="p-12 text-center">
                            <p class="text-slate-700 italic text-sm">Data Perjanjian Kerja belum tersedia.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Infrastruktur & Kredensial -->
        <div class="flex flex-col gap-6">
            <!-- QR Code (Visible only if TTE status is ISSUE) -->
            <div id="qrcode-card" class="hidden bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">QR Code</h3>
                </div>
                <div class="p-6 flex flex-col items-center gap-4">
                    <a id="qrcode-link" href="" target="_blank" class="relative p-2 bg-white border border-slate-100 rounded-xl shadow-sm hover:border-slate-800 hover:shadow-md transition-all group/qr">
                        <img id="qrcode-image" src="" alt="QR Code" class="w-32 h-32">
                        <!-- Logo Overlay -->
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="w-8 h-8 bg-white p-1 rounded-lg shadow-sm border border-slate-100">
                                <img src="<?= base_url('logo.png') ?>" alt="Logo" class="w-full h-full object-contain">
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Kata Sandi -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Kredensial</h3>
                    <?php if (in_array(session()->get('role'), ['super_admin', 'admin'])): ?>
                        <a href="<?= site_url('email/edit_password/' . $email['user']) ?>" class="btn btn-outline btn-xs no-underline">
                            <i class="fas fa-key mr-1.5"></i> Edit Password
                        </a>
                    <?php endif; ?>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <span class="block text-[10px] font-bold text-slate-700 uppercase tracking-widest mb-1">Password</span>
                            <div class="flex items-center justify-between bg-white border border-slate-200 rounded-lg p-3 shadow-sm group hover:border-slate-800 transition-all">
                                <p class="text-sm font-semibold text-slate-800 font-mono tracking-widest" id="password-text">••••••••</p>
                                <div class="flex items-center gap-2">
                                    <button class="text-slate-700 hover:text-slate-800 transition-all focus:outline-none" onclick="togglePasswordDisplay(this, '<?= esc($email['password'], 'js') ?>')">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                    <button class="text-slate-700 hover:text-slate-800 transition-colors p-1 focus:outline-none" onclick="copyToClipboard('<?= esc($email['password'], 'js') ?>', this)" title="Salin Password">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Penyimpanan -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Penyimpanan</h3>
                </div>
                <div class="p-6 space-y-6 flex-grow">
                    <?php
                    $is_unlimited = ($email['diskquota'] ?? '') == 'unlimited' || empty($email['_diskquota']);
                    $usage_percent = round($email['diskusedpercent_float'] ?? 0, 1);
                    $progress_color = ($usage_percent > 85) ? 'bg-red-600' : (($usage_percent > 70) ? 'bg-amber-500' : 'bg-slate-800');
                    ?>

                    <div class="space-y-3">
                        <div class="flex justify-between items-end">
                            <div>
                                <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest mb-1">Penggunaan Disk</p>
                                <h3 class="text-2xl font-bold text-slate-800">
                                    <?= $is_unlimited ? '∞' : $usage_percent . '<span class="text-sm ml-0.5">%</span>' ?>
                                </h3>
                            </div>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="<?= $progress_color ?> h-full rounded-full transition-all duration-1000" style="width: <?= $is_unlimited ? 100 : $usage_percent ?>%"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                            <p class="text-[9px] font-bold text-slate-700 uppercase tracking-widest mb-1">Digunakan</p>
                            <p class="text-xs font-bold text-slate-800"><?= $email['humandiskused'] ?? '0 KB' ?></p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                            <p class="text-[9px] font-bold text-slate-700 uppercase tracking-widest mb-1">Kuota</p>
                            <p class="text-xs font-bold text-slate-800"><?= $is_unlimited ? '∞' : ($email['humandiskquota'] ?? '-') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text).then(function() {
            const originalIcon = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check text-slate-600"></i>';
            setTimeout(() => {
                btn.innerHTML = originalIcon;
            }, 2000);
        });
    }

    function togglePasswordDisplay(btn, password) {
        const text = document.getElementById('password-text');
        const icon = btn.querySelector('i');
        if (text.textContent.includes('•')) {
            text.textContent = password;
            text.classList.remove('tracking-widest');
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            text.textContent = '••••••••';
            text.classList.add('tracking-widest');
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function renderBsreStatus(status) {
        const container = document.getElementById('bsre-status-container');
        if (!container) return;

        const colorClass = getJsStatusColor(status);
        const label = (status && status.toLowerCase() !== 'not_synced') ? status : 'NOT_SYNCED';

        container.innerHTML = `<span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border ${colorClass}">${label}</span>`;

        // Handle QR Code Visibility
        const qrcodeCard = document.getElementById('qrcode-card');
        const qrcodeImage = document.getElementById('qrcode-image');
        const qrcodeLink = document.getElementById('qrcode-link');
        const hash = '<?= $verification_hash ?>';

        if (status === 'ISSUE' && hash) {
            const profileUrl = `<?= site_url('verifikasi/') ?>${hash}`;
            qrcodeImage.src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(profileUrl)}`;
            qrcodeLink.href = profileUrl;
            qrcodeCard.classList.remove('hidden');
        } else {
            qrcodeCard.classList.add('hidden');
        }
    }

    function syncBsreStatus(email) {
        const container = document.getElementById('bsre-status-container');
        container.innerHTML = '<span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border bg-slate-50 text-slate-400 border-slate-200 animate-pulse"><i class="fas fa-spinner fa-spin mr-1"></i> SYNCING</span>';

        fetch('<?= site_url('bsre/sync-status') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'email=' + encodeURIComponent(email)
            })
            .then(r => r.json()).then(data => {
                if (data.status === 'success') {
                    renderBsreStatus(data.bsre_status);
                } else {
                    const errorMsg = data.message || 'Gagal';
                    container.innerHTML = `<button onclick="showGlobalError('Gagal Sinkronisasi', '${errorMsg.replace(/'/g, "\\'")}')" class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border bg-red-50 text-red-600 border-red-200 hover:bg-red-100 transition-colors">ERROR</button>`;
                }
            })
            .catch((err) => {
                const errorMsg = 'Masalah Koneksi Jaringan';
                container.innerHTML = `<button onclick="showGlobalError('Kesalahan Jaringan', '${errorMsg}')" class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border bg-red-50 text-red-600 border-red-200 hover:bg-red-100 transition-colors">ERROR</button>`;
            });
    }

    function syncPegawai(nip, btn) {
        const originalContent = btn.innerHTML;
        const jabatanElement = document.getElementById('jabatan-text');
        const pangkatElement = document.getElementById('pangkat-text');
        const golruElement = document.getElementById('golru-text');

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> SYNCING';
        jabatanElement.classList.add('animate-pulse', 'text-slate-400');
        pangkatElement.classList.add('animate-pulse', 'text-slate-400');
        if (golruElement) golruElement.classList.add('animate-pulse', 'text-slate-400');

        fetch('<?= site_url('email/sync_pegawai') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'nip=' + encodeURIComponent(nip)
            })
            .then(r => r.json()).then(data => {
                btn.disabled = false;
                btn.innerHTML = originalContent;
                jabatanElement.classList.remove('animate-pulse', 'text-slate-400');
                pangkatElement.classList.remove('animate-pulse', 'text-slate-400');
                if (golruElement) golruElement.classList.remove('animate-pulse', 'text-slate-400');

                if (data.success) {
                    if (data.data.jabatan) {
                        jabatanElement.textContent = data.data.jabatan;
                    }

                    if (data.data.pangkat_nama) {
                        pangkatElement.textContent = data.data.pangkat_nama;
                    }

                    if (data.data.pangkat_golruang && golruElement) {
                        golruElement.textContent = data.data.pangkat_golruang;
                    }
                } else {
                    showGlobalError('Gagal Sinkronisasi Pegawai', data.message || 'Gagal mengambil data dari API');
                }
            })
            .catch((err) => {
                btn.disabled = false;
                btn.innerHTML = originalContent;
                jabatanElement.classList.remove('animate-pulse', 'text-slate-400');
                pangkatElement.classList.remove('animate-pulse', 'text-slate-400');
                if (golruElement) golruElement.classList.remove('animate-pulse', 'text-slate-400');
                showGlobalError('Kesalahan Jaringan', 'Gagal menghubungi server API.');
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const initialStatus = '<?= esc($email['bsre_status'] ?? '', 'js') ?>';
        renderBsreStatus(initialStatus);
    });
</script>
<?= $this->endSection() ?>