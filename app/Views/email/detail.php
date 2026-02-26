<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Navigasi dan Aksi -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-4">
            <a href="<?= site_url('email') ?>" class="inline-flex items-center justify-center w-10 h-10 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all shadow-sm no-underline">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-semibold text-gray-900">Detail Akun</h1>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <?php if (($email['status_asn_id'] ?? 0) == 3): ?>
                <a href="<?= site_url('email/export_single_perjanjian_kerja_pdf/' . $email['user']) ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-gray-50 transition-all shadow-sm no-underline">
                    <i class="fas fa-file-contract mr-2"></i> Unduh PK
                </a>
            <?php endif; ?>
            <a href="https://<?= config('Cpanel')->cpanel_host ?>:2096" target="_blank" class="inline-flex items-center justify-center px-4 py-2 bg-gray-900 text-white rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-gray-800 transition-all shadow-sm no-underline">
                <i class="fas fa-external-link-alt mr-2"></i> Webmail
            </a>
        </div>
    </div>

    <!-- Informasi Utama -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 lg:p-8 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div class="flex flex-col md:flex-row items-center gap-6">
                <div class="w-24 h-24 bg-gray-100 rounded-2xl flex items-center justify-center text-gray-400 border border-gray-200 shadow-sm shrink-0">
                    <i class="fas fa-user-circle text-5xl"></i>
                </div>

                <div class="text-center md:text-left space-y-2">
                    <div class="flex flex-col md:flex-row md:items-center gap-2">
                        <p class="text-2xl font-bold text-gray-900 break-all"><?= esc($email['email']) ?></p>
                        <button class="text-gray-400 hover:text-gray-900 transition-colors p-1" onclick="copyToClipboard('<?= esc($email['email'], 'js') ?>', this)" title="Salin Email">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-tight"><?= esc($email['name']) ?></p>

                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 mt-4">
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-50 border border-gray-100 rounded-lg">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status TTE:</span>
                            <div id="bsre-status-container" class="flex items-center">
                                <span class="text-[10px] font-bold text-gray-300 animate-pulse uppercase">Memeriksa...</span>
                            </div>
                            <?php if (session()->get('role') === 'super_admin'): ?>
                                <button onclick="syncBsreStatus('<?= esc($email['email'], 'js') ?>')" class="text-gray-400 hover:text-gray-900 ml-1" title="Sinkronisasi">
                                    <i class="fas fa-sync-alt text-[10px]"></i>
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if (($email['pimpinan'] ?? 0) == 1): ?>
                            <span class="px-2.5 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100">
                                <i class="fas fa-user-tie mr-1"></i> Pimpinan OPD
                            </span>
                        <?php endif; ?>

                        <?php if (($email['pimpinan_desa'] ?? 0) == 1): ?>
                            <span class="px-2.5 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-100">
                                <i class="fas fa-landmark mr-1"></i> Kepala Desa
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="flex-shrink-0 flex flex-col items-center lg:items-end gap-2 pt-6 lg:pt-0 lg:border-l lg:border-gray-100 lg:pl-8">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status Akun</p>
                <?php if (($email['suspended_login'] ?? 0) == 0): ?>
                    <span class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest bg-green-50 text-green-700 border border-green-100">Aktif</span>
                <?php else: ?>
                    <span class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest bg-red-50 text-red-700 border border-red-100">Ditangguhkan</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Detail Informasi -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profil -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Data Personal & Kepegawaian -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="text-xs font-bold text-gray-900 uppercase tracking-tight">Profil</h3>
                    <?php if (session()->get('role') === 'super_admin'): ?>
                        <a href="<?= site_url('email/edit_profile/' . $email['user']) ?>" class="text-xs font-bold text-gray-600 hover:text-gray-900 uppercase tracking-widest transition-all flex items-center no-underline">
                            <i class="fas fa-edit mr-1.5"></i> Perbarui
                        </a>
                    <?php endif; ?>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
                    <!-- Kolom 1: Informasi Personal -->
                    <div class="space-y-6">
                        <div>
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Data Pribadi</span>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-tight">NIK</label>
                                    <p class="text-sm font-semibold text-gray-900 font-mono"><?= esc($email['nik']) ?: '-' ?></p>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-tight">Tempat, Tanggal Lahir</label>
                                    <p class="text-sm font-semibold text-gray-900 uppercase">
                                        <?= esc($email['tempat_lahir']) ?: '-' ?>,
                                        <?php
                                        $tanggal = $email['tanggal_lahir'] ?? null;
                                        if (!empty($tanggal)) {
                                            $date = new DateTime($tanggal);
                                            $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                            $month = $months[$date->format('n') - 1];
                                            echo $date->format('d') . ' ' . $month . ' ' . $date->format('Y');
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-tight">Pendidikan Terakhir</label>
                                    <p class="text-sm font-semibold text-gray-900 uppercase"><?= esc($email['pendidikan']) ?: '-' ?></p>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-tight">Gelar Akademik</label>
                                    <p class="text-sm font-semibold text-gray-900 uppercase">
                                        <?= esc($email['gelar_depan']) ?: '' ?>
                                        <?= esc($email['gelar_belakang']) ?: '' ?>
                                        <?php if (empty($email['gelar_depan']) && empty($email['gelar_belakang'])): ?>
                                            -
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom 2: Kepegawaian -->
                    <div class="space-y-6">
                        <div>
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Kepegawaian</span>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-tight">NIP</label>
                                    <p class="text-sm font-semibold text-gray-900 font-mono"><?= esc($email['nip']) ?: '-' ?></p>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-tight">Status ASN</label>
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 text-[10px] font-bold uppercase border border-gray-200"><?= $email['status_asn'] ?? 'NON ASN' ?></span>
                                        <?php if (!empty($email['eselon_name'])): ?>
                                            <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 text-[10px] font-bold uppercase border border-gray-200">Eselon <?= $email['eselon_name'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-tight">Jabatan</label>
                                    <p class="text-sm font-semibold text-gray-900 uppercase leading-snug"><?= esc($email['jabatan']) ?: '-' ?></p>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-tight">Unit Kerja</label>
                                    <div class="bg-gray-50 border border-gray-100 rounded-lg p-3 hover:border-gray-300 transition-all space-y-1 mt-1">
                                        <?php if (!empty($unit_kerja)): ?>
                                            <?php if (!empty($parent_unit_kerja)): ?>
                                                <a href="<?= site_url('email/unit_kerja/' . $parent_unit_kerja['id']) ?>" class="block no-underline group/parent">
                                                    <p class="text-[10px] font-bold text-gray-400 uppercase group-hover/parent:text-gray-600 transition-colors leading-none"><?= esc($parent_unit_kerja['nama_unit_kerja']) ?></p>
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= site_url('email/unit_kerja/' . $unit_kerja['id']) ?>" class="block no-underline group/child">
                                                <p class="text-xs font-bold text-gray-900 uppercase leading-tight group-hover/child:text-black transition-colors"><?= esc($unit_kerja['nama_unit_kerja']) ?></p>
                                            </a>
                                        <?php else: ?>
                                            <p class="text-xs font-bold text-gray-300 uppercase italic">TIDAK TERDAFTAR</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian: Kontrak Kerja (Hanya jika ada data PK) -->
            <?php if (!empty($pk_data)): ?>
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-xs font-bold text-gray-900 uppercase tracking-tight">Perjanjian Kerja (PK)</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-tight">Nomor PK</label>
                                <p class="text-sm font-semibold text-gray-900 font-mono"><?= esc($pk_data['nomor']) ?></p>
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-tight">Masa Kontrak</label>
                                <div class="flex items-center gap-3 mt-1">
                                    <div class="flex-1 p-2 bg-gray-50 border border-gray-100 rounded text-center">
                                        <span class="block text-[8px] font-bold text-gray-400 uppercase">Mulai</span>
                                        <span class="text-[10px] font-bold text-gray-900"><?= date('d/m/Y', strtotime($pk_data['tanggal_kontrak_awal'])) ?></span>
                                    </div>
                                    <i class="fas fa-arrow-right text-gray-300 text-[10px]"></i>
                                    <div class="flex-1 p-2 bg-gray-50 border border-gray-100 rounded text-center">
                                        <span class="block text-[8px] font-bold text-gray-400 uppercase">Selesai</span>
                                        <span class="text-[10px] font-bold text-gray-900"><?= date('d/m/Y', strtotime($pk_data['tanggal_kontrak_akhir'])) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-tight">Gaji</label>
                                <p class="text-sm font-bold text-gray-900">Rp <?= number_format($pk_data['gaji_nominal'], 0, ',', '.') ?></p>
                                <p class="text-[10px] font-medium text-gray-500 uppercase italic mt-0.5 leading-tight">"<?= esc($pk_data['gaji_terbilang']) ?> Rupiah"</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Infrastruktur & Kredensial -->
        <div class="flex flex-col gap-6">
            <!-- Kata Sandi -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="text-xs font-bold text-gray-900 uppercase tracking-tight">Kredensial</h3>
                    <?php if (session()->get('role') === 'super_admin'): ?>
                        <a href="<?= site_url('email/edit_password/' . $email['user']) ?>" class="text-[10px] font-bold text-gray-600 hover:text-gray-900 uppercase tracking-widest transition-all flex items-center no-underline">
                            <i class="fas fa-key mr-1.5"></i> Ganti
                        </a>
                    <?php endif; ?>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Password</span>
                            <div class="flex items-center justify-between bg-white border border-gray-200 rounded-lg p-3 shadow-sm group hover:border-gray-300 transition-all">
                                <p class="text-sm font-semibold text-gray-900 font-mono tracking-widest" id="password-text">••••••••</p>
                                <div class="flex items-center gap-2">
                                    <button class="text-gray-400 hover:text-gray-900 transition-all focus:outline-none" onclick="togglePasswordDisplay(this, '<?= esc($email['password'], 'js') ?>')">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                    <button class="text-gray-400 hover:text-gray-900 transition-colors p-1 focus:outline-none" onclick="copyToClipboard('<?= esc($email['password'], 'js') ?>', this)" title="Salin Password">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Penyimpanan -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-xs font-bold text-gray-900 uppercase tracking-tight">Penyimpanan</h3>
                </div>
                <div class="p-6 space-y-6 flex-grow">
                    <?php
                    $is_unlimited = ($email['diskquota'] ?? '') == 'unlimited' || empty($email['_diskquota']);
                    $usage_percent = round($email['diskusedpercent_float'] ?? 0, 1);
                    $progress_color = ($usage_percent > 85) ? 'bg-red-600' : (($usage_percent > 70) ? 'bg-amber-500' : 'bg-gray-900');
                    ?>

                    <div class="space-y-3">
                        <div class="flex justify-between items-end">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Penggunaan Disk</p>
                                <h3 class="text-2xl font-bold text-gray-900">
                                    <?= $is_unlimited ? '∞' : $usage_percent . '<span class="text-sm ml-0.5">%</span>' ?>
                                </h3>
                            </div>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="<?= $progress_color ?> h-full rounded-full transition-all duration-1000" style="width: <?= $is_unlimited ? 100 : $usage_percent ?>%"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Digunakan</p>
                            <p class="text-xs font-bold text-gray-900"><?= $email['humandiskused'] ?? '0 KB' ?></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Kuota</p>
                            <p class="text-xs font-bold text-gray-900"><?= $is_unlimited ? '∞' : ($email['humandiskquota'] ?? '-') ?></p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 space-y-3">
                        <div class="flex justify-between text-[10px] font-bold uppercase tracking-tight">
                            <span class="text-gray-400">Dibuat:</span>
                            <span class="text-gray-700"><?= date('d/m/Y', ($email['mtime'] ?? 0)) ?></span>
                        </div>
                        <div class="flex justify-between text-[10px] font-bold uppercase tracking-tight">
                            <span class="text-gray-400">Diperbarui:</span>
                            <span class="text-gray-700"><?= date('d/m/Y', strtotime($email['updated_at'] ?? 'now')) ?></span>
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
            btn.innerHTML = '<i class="fas fa-check text-green-600"></i>';
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

        let colorClass = 'bg-gray-50 text-gray-400 border-gray-100';
        let label = status || 'NOT SYNCED';

        if (status === 'ISSUE') {
            colorClass = 'bg-green-50 text-green-700 border-green-100';
        } else if (['EXPIRED', 'REVOKE', 'SUSPEND'].includes(status)) {
            colorClass = 'bg-red-50 text-red-700 border-red-100';
        } else if (['WAITING_FOR_VERIFICATION', 'RENEW'].includes(status)) {
            colorClass = 'bg-amber-50 text-amber-700 border-amber-100';
        } else if (status === 'NEW') {
            colorClass = 'bg-blue-50 text-blue-700 border-blue-100';
        }

        container.innerHTML = `<span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border ${colorClass}">${label}</span>`;
    }

    function syncBsreStatus(email) {
        const container = document.getElementById('bsre-status-container');
        container.innerHTML = '<i class="fas fa-spinner fa-spin text-gray-400 text-[10px]"></i>';
        fetch('<?= site_url('bsre/sync-status') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'email=' + encodeURIComponent(email)
            })
            .then(r => r.json()).then(data => {
                if (data.status === 'success') renderBsreStatus(data.bsre_status);
                else container.innerHTML = '<span class="text-[9px] text-red-600 font-bold uppercase">Gagal</span>';
            })
            .catch(() => {
                container.innerHTML = '<span class="text-[9px] text-red-600 font-bold uppercase">Error</span>';
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const initialStatus = '<?= esc($email['bsre_status'] ?? '', 'js') ?>';
        if (initialStatus) renderBsreStatus(initialStatus);
        else syncBsreStatus('<?= esc($email['email'], 'js') ?>');
    });
</script>
<?= $this->endSection() ?>