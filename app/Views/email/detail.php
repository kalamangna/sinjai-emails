<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto space-y-8 font-medium">
    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-5 py-3 rounded-lg flex items-center shadow-sm flash-message transition-all duration-500" role="alert">
            <i class="fas fa-check-circle mr-3 text-emerald-500"></i>
            <span class="font-bold text-xs uppercase tracking-wider"><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>

    <!-- Nav & Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <a href="<?= site_url('email') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 uppercase tracking-wider hover:bg-slate-50 hover:text-slate-900 active:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500/20 transition-all shadow-sm no-underline group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> Kembali
        </a>
        <div class="flex flex-wrap gap-2">
            <?php if (($email['status_asn_id'] ?? 0) == 3): ?>
                <a href="<?= site_url('email/export_single_perjanjian_kerja_pdf/' . $email['user']) ?>" class="inline-flex items-center justify-center px-3 py-2 bg-slate-800 border border-transparent rounded-lg font-bold text-[10px] text-white uppercase tracking-wider hover:bg-slate-900 active:bg-slate-950 focus:outline-none focus:ring-2 focus:ring-slate-500/20 transition-all shadow-sm no-underline">
                    <i class="fas fa-file-contract mr-1.5"></i> Dokumen PK
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Profile Header -->
    <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm relative overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8 relative z-10">
            <div class="space-y-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight lowercase break-all"><?= esc($email['email']) ?></h2>
                        <button class="inline-flex items-center justify-center text-slate-400 hover:text-blue-600 transition-colors focus:outline-none" onclick="copyToClipboard('<?= esc($email['email'], 'js') ?>', this)" title="Salin Email">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em]"><?= esc($email['name']) ?></p>
                </div>

                <div class="flex items-center gap-3 mt-4 pt-4 border-t border-slate-100">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em]">Sertifikat TTE:</span>
                    <div id="bsre-status-container" class="flex items-center">
                        <span class="text-[10px] font-extrabold text-slate-600 uppercase tracking-widest">Checking...</span>
                    </div>
                    <button onclick="syncBsreStatus('<?= esc($email['email'], 'js') ?>')" class="inline-flex items-center justify-center w-6 h-6 bg-amber-50 text-amber-600 border border-amber-100 rounded-md hover:bg-amber-100 transition-all focus:outline-none ml-1" title="Sinkronkan TTE">
                        <i class="fas fa-sync-alt text-[10px]"></i>
                    </button>
                </div>
            </div>

            <a href="https://<?= config('Cpanel')->cpanel_host ?>:2096" target="_blank" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-wider hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all shadow-md no-underline">
                <i class="fas fa-external-link-alt mr-2 text-sm"></i> Buka Webmail
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Storage Card -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center">
                    <i class="fas fa-hdd mr-2 text-blue-500"></i>Penyimpanan
                </h5>
            </div>
            <div class="p-6 space-y-6 flex-grow">
                <?php
                $is_unlimited = ($email['diskquota'] ?? '') == 'unlimited' || empty($email['_diskquota']);
                $usage_percent = round($email['diskusedpercent_float'] ?? 0, 1);
                $progress_color = ($usage_percent > 85) ? 'bg-rose-500' : (($usage_percent > 70) ? 'bg-amber-500' : 'bg-blue-600');
                ?>

                <div class="space-y-3">
                    <div class="flex justify-between items-end">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Usage</span>
                        <span class="text-lg font-extrabold text-slate-900">
                            <?= $is_unlimited ? '∞' : $usage_percent . '%' ?>
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="<?= $progress_color ?> h-full rounded-full transition-all duration-700 shadow-sm" style="width: <?= $is_unlimited ? 100 : $usage_percent ?>%"></div>
                    </div>
                </div>

                <div class="space-y-3 pt-2">
                    <div class="flex justify-between text-[11px]">
                        <span class="text-slate-500 font-medium">Digunakan</span>
                        <span class="text-slate-900 font-bold"><?= $email['humandiskused'] ?? '0 KB' ?></span>
                    </div>
                    <div class="flex justify-between text-[11px]">
                        <span class="text-slate-500 font-medium">Total Kuota</span>
                        <span class="text-slate-900 font-bold"><?= $is_unlimited ? 'Unlimited' : ($email['humandiskquota'] ?? '-') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Data Card -->
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center">
                    <i class="fas fa-user-circle mr-2 text-blue-500"></i>Data Personel
                </h5>
                <button onclick="openEditModal()" class="inline-flex items-center text-[10px] font-bold text-blue-600 hover:text-blue-700 uppercase tracking-wider focus:outline-none">
                    <i class="fas fa-edit mr-1.5"></i> Edit Profile
                </button>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1">
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">NIP / NIK</span>
                    <p class="text-sm font-bold text-slate-800 tracking-wide font-mono"><?= esc($email['nip']) ?: '-' ?> / <?= esc($email['nik']) ?: '-' ?></p>
                </div>
                <div class="space-y-1">
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">Status / Eselon</span>
                    <div class="flex items-center gap-2 pt-0.5">
                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-100 rounded text-[10px] font-bold uppercase"><?= esc($email['status_asn'] ?? 'NON ASN') ?></span>
                        <?php if (!empty($email['eselon_name'])): ?>
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-700 border border-slate-200 rounded text-[10px] font-bold uppercase"><?= esc($email['eselon_name']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="space-y-1">
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">Jabatan</span>
                    <p class="text-sm font-semibold text-slate-700 leading-relaxed"><?= esc($email['jabatan']) ?: '-' ?></p>
                </div>
                <div class="space-y-1">
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">Unit Kerja</span>
                    <div class="space-y-1">
                        <p class="text-sm font-bold text-slate-800 uppercase tracking-tight leading-tight"><?= esc($unit_kerja['nama_unit_kerja'] ?? 'TIDAK TERDAFTAR') ?></p>
                        <?php if (!empty($parent_unit_kerja)): ?>
                            <p class="text-[10px] font-medium text-slate-400 uppercase tracking-tighter leading-none italic"><?= esc($parent_unit_kerja['nama_unit_kerja']) ?></p>
                        <?php endif; ?>
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

    function renderBsreStatus(status) {
        const container = document.getElementById('bsre-status-container');
        if (!container) return;

        const colors = {
            'ISSUE': 'text-emerald-600',
            'EXPIRED': 'text-rose-600',
            'RENEW': 'text-blue-600',
            'WAITING_FOR_VERIFICATION': 'text-amber-600',
            'NEW': 'text-indigo-600',
            'NO_CERTIFICATE': 'text-slate-400',
        };

        const textClass = colors[status] || 'text-slate-400';
        container.innerHTML = `<span class="text-[10px] font-extrabold uppercase tracking-widest ${textClass}">${status}</span>`;
    }

    function syncBsreStatus(email) {
        const container = document.getElementById('bsre-status-container');
        container.innerHTML = '<i class="fas fa-spinner fa-spin text-slate-400 text-[10px]"></i>';
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
                else container.innerHTML = '<span class="text-[10px] text-rose-500 font-bold">ERROR</span>';
            })
            .catch(() => {
                container.innerHTML = '<span class="text-[10px] text-rose-500 font-bold">ERROR</span>';
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

        const flashMessages = document.querySelectorAll('.flash-message');
        flashMessages.forEach(msg => {
            setTimeout(() => {
                msg.style.opacity = '0';
                msg.style.transform = 'translateY(-10px)';
                setTimeout(() => msg.remove(), 500);
            }, 3000);
        });
    });
</script>
<?= $this->endSection() ?>