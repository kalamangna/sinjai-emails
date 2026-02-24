<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto space-y-8">
    <!-- Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-6 py-4 rounded-2xl flex items-center shadow-sm flash-message transition-all duration-500" role="alert">
            <i class="fas fa-check-circle mr-3 text-emerald-500 text-lg"></i>
            <span class="font-bold text-xs uppercase tracking-wider"><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
        <a href="<?= site_url('email') ?>" class="inline-flex items-center justify-center px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-600 uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm no-underline group">
            <i class="fas fa-arrow-left mr-2.5 group-hover:-translate-x-1 transition-transform"></i> Kembali
        </a>
        <div class="flex flex-wrap items-center gap-3">
            <?php if (($email['status_asn_id'] ?? 0) == 3): ?>
                <a href="<?= site_url('email/export_single_perjanjian_kerja_pdf/' . $email['user']) ?>" class="inline-flex items-center justify-center px-4 py-2.5 bg-slate-900 text-white rounded-xl font-bold text-[10px] uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-md no-underline">
                    <i class="fas fa-file-contract mr-2"></i> PK PDF
                </a>
            <?php endif; ?>
            <a href="https://<?= config('Cpanel')->cpanel_host ?>:2096" target="_blank" class="inline-flex items-center justify-center px-5 py-2.5 bg-emerald-600 text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-md no-underline">
                <i class="fas fa-external-link-alt mr-2"></i> Webmail
            </a>
        </div>
    </div>

    <!-- Profile -->
    <div class="bg-white border border-slate-200 rounded-[2.5rem] p-8 lg:p-12 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-emerald-50 rounded-full blur-3xl opacity-50"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-10">
            <div class="flex flex-col md:flex-row items-center gap-8">
                <div class="w-24 h-24 lg:w-32 lg:h-32 bg-gradient-to-br from-emerald-600 to-emerald-800 rounded-[2rem] flex items-center justify-center shadow-xl text-white shrink-0 relative group">
                    <span class="text-4xl lg:text-5xl font-black uppercase"><?= substr($email['name'], 0, 1) ?></span>
                </div>

                <div class="text-center md:text-left space-y-3">
                    <div class="flex flex-col md:flex-row md:items-center gap-3">
                        <h2 class="text-2xl lg:text-3xl font-black text-slate-900 tracking-tight lowercase break-all"><?= esc($email['email']) ?></h2>
                        <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-300 hover:text-emerald-600 transition-colors" onclick="copyToClipboard('<?= esc($email['email'], 'js') ?>', this)" title="Salin">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <?php $full_name = trim(($email['gelar_depan'] ?? '') . ' ' . $email['name'] . ' ' . ($email['gelar_belakang'] ?? '')); ?>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest"><?= esc($full_name) ?></p>
                    
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 mt-4">
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 border border-slate-100 rounded-xl">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Sertifikat:</span>
                            <div id="bsre-status-container" class="flex items-center min-w-[60px]">
                                <span class="text-[10px] font-black text-slate-300 animate-pulse uppercase tracking-widest italic">Checking...</span>
                            </div>
                            <?php if (session()->get('role') === 'super_admin'): ?>
                            <button onclick="syncBsreStatus('<?= esc($email['email'], 'js') ?>')" class="inline-flex items-center justify-center w-6 h-6 bg-white text-emerald-600 border border-emerald-100 rounded-lg hover:bg-emerald-600 hover:text-white transition-all shadow-sm ml-1" title="Sync">
                                <i class="fas fa-sync-alt text-[10px]"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex-shrink-0 flex flex-col items-center lg:items-end gap-3 border-t lg:border-t-0 lg:border-l border-slate-100 pt-8 lg:pt-0 lg:pl-10">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sistem</p>
                <?php if (($email['suspended_login'] ?? 0) == 0): ?>
                    <?= view('components/badge', ['label' => 'AKTIF', 'type' => 'success', 'rounded' => true]) ?>
                <?php else: ?>
                    <?= view('components/badge', ['label' => 'SUSPENDED', 'type' => 'danger', 'rounded' => true]) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-[2rem] shadow-sm overflow-hidden flex flex-col">
            <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center text-white text-xs">
                        <i class="fas fa-user"></i>
                    </div>
                    <h5 class="text-xs font-black text-slate-900 uppercase tracking-widest">Profil Personel</h5>
                </div>
                <?php if (session()->get('role') === 'super_admin'): ?>
                <button onclick="openEditModal()" class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 rounded-xl text-[10px] font-black text-emerald-600 hover:bg-emerald-600 hover:text-white uppercase tracking-widest transition-all shadow-sm group">
                    <i class="fas fa-edit mr-2 group-hover:rotate-12 transition-transform"></i> Edit
                </button>
                <?php endif; ?>
            </div>
            
            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-10">
                <div class="space-y-8">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">NIP</span>
                            <p class="text-sm font-black text-slate-800 tracking-wider font-mono"><?= esc($email['nip']) ?: '-' ?></p>
                        </div>
                        <div class="space-y-1.5">
                            <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">NIK</span>
                            <p class="text-sm font-black text-slate-800 tracking-wider font-mono"><?= esc($email['nik']) ?: '-' ?></p>
                        </div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Status ASN</span>
                        <div class="flex items-center gap-3 pt-1">
                            <?= view('components/badge', ['label' => $email['status_asn'] ?? 'NON ASN', 'type' => 'info', 'rounded' => false]) ?>
                            <?php if (!empty($email['eselon_name'])): ?>
                                <?= view('components/badge', ['label' => 'Eselon ' . $email['eselon_name'], 'type' => 'neutral', 'rounded' => false]) ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Jabatan</span>
                        <p class="text-sm font-bold text-slate-700 uppercase"><?= esc($email['jabatan']) ?: '-' ?></p>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="space-y-1.5">
                        <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Unit Kerja</span>
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
                            <?php if (!empty($parent_unit_kerja)): ?>
                                <a href="<?= site_url('email/unit_kerja/' . $parent_unit_kerja['id']) ?>" class="block text-[10px] font-bold text-slate-400 hover:text-emerald-600 uppercase mb-1 no-underline transition-colors"><?= esc($parent_unit_kerja['nama_unit_kerja']) ?></a>
                                <a href="<?= site_url('email/unit_kerja/' . $unit_kerja['id']) ?>" class="block text-[13px] font-black text-slate-900 hover:text-emerald-700 uppercase no-underline transition-colors"><?= esc($unit_kerja['nama_unit_kerja'] ?? 'TIDAK TERDAFTAR') ?></a>
                            <?php elseif (!empty($unit_kerja)): ?>
                                <a href="<?= site_url('email/unit_kerja/' . $unit_kerja['id']) ?>" class="block text-[13px] font-black text-slate-900 hover:text-emerald-700 uppercase no-underline transition-colors"><?= esc($unit_kerja['nama_unit_kerja']) ?></a>
                            <?php else: ?>
                                <p class="text-sm font-black text-slate-300 uppercase italic">TIDAK TERDAFTAR</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Password</span>
                        <div class="flex items-center justify-between bg-slate-900 rounded-2xl p-4 shadow-inner">
                            <div class="flex flex-col">
                                <p class="text-sm font-black text-white font-mono tracking-[0.4em]" id="password-text">••••••••</p>
                            </div>
                            <button class="w-10 h-10 rounded-xl bg-white/10 text-slate-400 hover:text-white transition-all focus:outline-none" onclick="togglePasswordDisplay(this, '<?= esc($email['password'], 'js') ?>')" title="Lihat">
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-[2rem] shadow-sm overflow-hidden flex flex-col">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white text-xs">
                        <i class="fas fa-server"></i>
                    </div>
                    <h5 class="text-xs font-black text-slate-900 uppercase tracking-widest">Penyimpanan</h5>
                </div>
            </div>
            <div class="p-8 space-y-8 flex-grow">
                <?php
                $is_unlimited = ($email['diskquota'] ?? '') == 'unlimited' || empty($email['_diskquota']);
                $usage_percent = round($email['diskusedpercent_float'] ?? 0, 1);
                $progress_color = ($usage_percent > 85) ? 'bg-rose-500' : (($usage_percent > 70) ? 'bg-amber-500' : 'bg-emerald-600');
                ?>

                <div class="space-y-4">
                    <div class="flex justify-between items-end">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Disk Usage</p>
                            <h3 class="text-3xl font-black text-slate-900 tracking-tighter">
                                <?= $is_unlimited ? '∞' : $usage_percent . '<span class="text-lg ml-0.5">%</span>' ?>
                            </h3>
                        </div>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3 p-0.5 shadow-inner">
                        <div class="<?= $progress_color ?> h-full rounded-full transition-all duration-1000 shadow-sm relative" style="width: <?= $is_unlimited ? 100 : $usage_percent ?>%">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-4">
                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Digunakan</p>
                        <p class="text-sm font-black text-slate-800"><?= $email['humandiskused'] ?? '0 KB' ?></p>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Kuota</p>
                        <p class="text-sm font-black text-slate-800"><?= $is_unlimited ? '∞' : ($email['humandiskquota'] ?? '-') ?></p>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 space-y-4">
                    <div class="flex justify-between text-[11px] font-bold">
                        <span class="text-slate-400">Created:</span>
                        <span class="text-slate-900"><?= date('d/m/Y', ($email['mtime'] ?? 0)) ?></span>
                    </div>
                    <div class="flex justify-between text-[11px] font-bold">
                        <span class="text-slate-400">Updated:</span>
                        <span class="text-slate-900"><?= date('d/m/Y', strtotime($email['updated_at'] ?? 'now')) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('email/components/modal_edit') ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text).then(function() {
            const originalIcon = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check text-emerald-500"></i>';
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
            text.classList.remove('tracking-[0.4em]');
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            text.textContent = '••••••••';
            text.classList.add('tracking-[0.4em]');
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function renderBsreStatus(status) {
        const container = document.getElementById('bsre-status-container');
        if (!container) return;

        let type = 'neutral';
        if (status === 'ISSUE') type = 'success';
        else if (['EXPIRED', 'REVOKE', 'SUSPEND'].includes(status)) type = 'danger';
        else if (['WAITING_FOR_VERIFICATION', 'RENEW'].includes(status)) type = 'warning';
        else if (status === 'NEW') type = 'info';

        const label = status || 'Belum Sync';
        const colorClasses = {
            'success': 'bg-emerald-50 text-emerald-700 border-emerald-100',
            'info': 'bg-blue-50 text-blue-700 border-blue-100',
            'warning': 'bg-amber-50 text-amber-700 border-amber-100',
            'danger': 'bg-rose-50 text-rose-700 border-rose-100',
            'neutral': 'bg-slate-50 text-slate-700 border-slate-100'
        };
        const cls = colorClasses[type];
        container.innerHTML = `<span class="inline-flex items-center px-2 py-0.5 text-[10px] font-black uppercase tracking-wider border rounded-lg ${cls}">${label}</span>`;
    }

    function syncBsreStatus(email) {
        const container = document.getElementById('bsre-status-container');
        container.innerHTML = '<i class="fas fa-spinner fa-spin text-emerald-500 text-[10px]"></i>';
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
                else container.innerHTML = '<span class="text-[9px] text-rose-500 font-bold uppercase">ERROR</span>';
            })
            .catch(() => {
                container.innerHTML = '<span class="text-[9px] text-rose-500 font-bold uppercase">FAIL</span>';
            });
    }

    function openEditModal() {
        document.getElementById('editModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('DOMContentLoaded', () => {
        const initialStatus = '<?= esc($email['bsre_status'] ?? '', 'js') ?>';
        if (initialStatus) renderBsreStatus(initialStatus);
        else syncBsreStatus('<?= esc($email['email'], 'js') ?>');
    });
</script>
<?= $this->endSection() ?>
