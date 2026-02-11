<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto space-y-10 font-medium">
    <!-- Tombol Kembali & PK -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <a href="javascript:void(0);" onclick="history.back();" class="inline-flex items-center px-6 py-3 bg-slate-900 border border-slate-800 rounded-2xl text-xs font-black text-slate-400 uppercase tracking-widest hover:bg-slate-800 hover:text-slate-200 transition-all shadow-xl no-underline group">
            <i class="fas fa-arrow-left mr-3 group-hover:-translate-x-1 transition-transform"></i> Kembali
        </a>
        <?php if (($email['status_asn_id'] ?? 0) == 3): ?>
            <a href="<?= site_url('email/export_single_perjanjian_kerja_pdf/' . $email['user']) ?>" class="inline-flex items-center px-8 py-3 bg-cyan-600 border border-transparent rounded-2xl font-black text-xs text-white uppercase tracking-widest hover:bg-cyan-700 transition-all shadow-xl shadow-cyan-900/20 no-underline">
                <i class="fas fa-file-contract mr-3"></i> Dokumen PK
            </a>
        <?php endif; ?>
    </div>

    <!-- Header Akun -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] p-10 shadow-2xl relative overflow-hidden group">
        <div class="absolute -right-10 -top-10 w-48 h-48 bg-blue-500/5 rounded-full blur-3xl group-hover:bg-blue-500/10 transition-colors"></div>
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-10">
            <div class="space-y-6">
                <div class="space-y-3">
                    <h2 class="text-3xl md:text-5xl font-black text-blue-500 tracking-tighter lowercase leading-none break-all"><?= esc($email['email']) ?></h2>
                    <button class="inline-flex items-center px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-[10px] font-black text-slate-500 hover:text-blue-400 hover:border-blue-500/30 transition-all uppercase tracking-widest shadow-inner" onclick="copyToClipboard('<?= esc($email['email'], 'js') ?>', this)">
                        <i class="fas fa-copy mr-2"></i> SALIN ALAMAT
                    </button>
                </div>
                
                <div class="flex flex-wrap items-center gap-4">
                    <div id="bsre-status-container" class="flex items-center">
                        <span class="px-4 py-1.5 bg-slate-950 border border-slate-800 rounded-full text-[10px] font-black text-slate-600 uppercase tracking-[0.2em]">BELUM SINKRON</span>
                    </div>
                    <button class="w-10 h-10 flex items-center justify-center bg-slate-950 border border-slate-800 rounded-full text-slate-500 hover:text-blue-400 hover:border-blue-500/30 transition-all shadow-sm group/sync" onclick="syncBsreStatus('<?= esc($email['email'], 'js') ?>')">
                        <i class="fas fa-sync-alt text-xs group-hover/sync:rotate-180 transition-transform duration-500"></i>
                    </button>
                    <?php if (($email['suspended_login'] ?? 0) == 0): ?>
                        <span class="px-4 py-1.5 bg-green-500/10 border border-green-500/20 rounded-full text-[10px] font-black text-green-400 uppercase tracking-[0.2em]">AKTIF</span>
                    <?php else: ?>
                        <span class="px-4 py-1.5 bg-red-500/10 border border-red-500/20 rounded-full text-[10px] font-black text-red-400 uppercase tracking-[0.2em]">DITANGGUHKAN</span>
                    <?php endif; ?>
                </div>
            </div>
            <a href="https://<?= config('Cpanel')->cpanel_host ?>:2096" target="_blank" class="inline-flex items-center px-10 py-5 bg-slate-800 border border-slate-700 rounded-2xl font-black text-xs text-slate-100 uppercase tracking-[0.2em] hover:bg-slate-700 transition-all shadow-2xl no-underline">
                <i class="fas fa-sign-in-alt mr-3 text-lg text-blue-500"></i> Masuk Webmail
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Kapasitas Penyimpanan -->
        <div class="lg:col-span-12 bg-slate-900 border border-slate-800 rounded-[2.5rem] overflow-hidden shadow-2xl">
            <div class="bg-slate-800/30 px-10 py-6 border-b border-slate-800">
                <h5 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                    <i class="fas fa-hdd mr-3 text-cyan-500 opacity-50"></i>Kapasitas Penyimpanan
                </h5>
            </div>
            <div class="p-10 space-y-10">
                <?php
                $is_unlimited = ($email['diskquota'] ?? '') == 'unlimited' || empty($email['_diskquota']);
                $usage_percent = round($email['diskusedpercent_float'] ?? 0, 2);
                $progress_class = ($usage_percent > 80) ? 'bg-red-500' : (($usage_percent > 60) ? 'bg-amber-500' : 'bg-blue-500');
                ?>

                <div class="space-y-4">
                    <div class="flex justify-between items-end">
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Penggunaan Ruang Disk</span>
                        <span class="text-2xl font-black <?= $is_unlimited ? 'text-cyan-400' : 'text-slate-100' ?> tracking-tighter">
                            <?= $is_unlimited ? 'UNLIMITED' : $usage_percent . '%' ?>
                        </span>
                    </div>
                    <div class="w-full bg-slate-950 rounded-full h-4 p-1 border border-slate-800 shadow-inner">
                        <div class="<?= $is_unlimited ? 'bg-cyan-500' : $progress_class ?> h-full rounded-full transition-all duration-1000 shadow-lg" style="width: <?= $is_unlimited ? 100 : $usage_percent ?>%"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="p-6 bg-slate-950 rounded-2xl border border-slate-800 text-center space-y-1">
                        <div class="text-xl font-black text-slate-100 tracking-tight"><?= $email['humandiskused'] ?? '0 KB' ?></div>
                        <div class="text-[9px] font-black text-slate-600 uppercase tracking-widest">Digunakan</div>
                    </div>
                    <div class="p-6 bg-slate-950 rounded-2xl border border-slate-800 text-center space-y-1">
                        <div class="text-xl font-black text-blue-400 tracking-tight"><?= $is_unlimited ? '∞' : ($email['humandiskquota'] ?? '-') ?></div>
                        <div class="text-[9px] font-black text-slate-600 uppercase tracking-widest">Total Kuota</div>
                    </div>
                    <div class="p-6 bg-slate-950 rounded-2xl border border-slate-800 text-center space-y-1">
                        <div class="text-xl font-black text-green-400 tracking-tight"><?= $is_unlimited ? '∞' : (number_format(($email['_diskquota'] - $email['_diskused']) / 1048576, 2) . ' MB') ?></div>
                        <div class="text-[9px] font-black text-slate-600 uppercase tracking-widest">Tersisa</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Kepegawaian -->
        <div class="lg:col-span-12 bg-slate-900 border border-slate-800 rounded-[2.5rem] overflow-hidden shadow-2xl">
            <div class="bg-slate-800/30 px-10 py-6 border-b border-slate-800 flex justify-between items-center">
                <h5 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                    <i class="fas fa-user-circle mr-3 text-blue-500 opacity-50"></i>Data Personel & Jabatan
                </h5>
                <button onclick="openEditModal()" class="px-5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-[10px] font-black text-slate-400 hover:text-white hover:border-slate-600 transition-all uppercase tracking-widest shadow-sm">
                    <i class="fas fa-pencil-alt mr-2"></i> Ubah Data
                </button>
            </div>
            <div class="p-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-16 gap-y-10">
                    <div class="space-y-2">
                        <span class="block text-[10px] font-black text-slate-600 uppercase tracking-[0.2em]">Nama Lengkap</span>
                        <span class="block text-lg font-black text-slate-200 uppercase tracking-tight leading-snug"><?= esc($email['name']) ?></span>
                    </div>
                    <div class="space-y-2">
                        <span class="block text-[10px] font-black text-slate-600 uppercase tracking-[0.2em]">Status Kepegawaian</span>
                        <div class="flex gap-3 pt-1">
                            <span class="px-4 py-1.5 bg-blue-500/10 border border-blue-500/20 rounded-xl text-[10px] font-black text-blue-400 uppercase tracking-widest"><?= esc($email['status_asn'] ?? 'BUKAN ASN') ?></span>
                            <?php if ($email['eselon_id']): ?>
                                <span class="px-4 py-1.5 bg-indigo-500/10 border border-indigo-500/20 rounded-xl text-[10px] font-black text-indigo-400 uppercase tracking-widest">ESELON <?= esc($email['eselon_id']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <span class="block text-[10px] font-black text-slate-600 uppercase tracking-[0.2em]">NIK (Identitas)</span>
                        <span class="block text-lg font-black text-slate-300 tracking-widest leading-none"><?= esc($email['nik']) ?: '-' ?></span>
                    </div>
                    <div class="space-y-2">
                        <span class="block text-[10px] font-black text-slate-600 uppercase tracking-[0.2em]">NIP (Kepegawaian)</span>
                        <span class="block text-lg font-black text-slate-300 tracking-widest leading-none"><?= esc($email['nip']) ?: '-' ?></span>
                    </div>
                    <div class="space-y-2">
                        <span class="block text-[10px] font-black text-slate-600 uppercase tracking-[0.2em]">Jabatan Struktural / Pelaksana</span>
                        <span class="block text-base font-bold text-slate-200 uppercase tracking-tight leading-relaxed"><?= esc($email['jabatan']) ?: '-' ?></span>
                    </div>
                    <div class="space-y-2">
                        <span class="block text-[10px] font-black text-slate-600 uppercase tracking-[0.2em]">Unit Kerja Terdaftar</span>
                        <div class="space-y-2">
                            <?php if (!empty($parent_unit_kerja)): ?>
                                <a href="<?= site_url('email/unit_kerja/' . $parent_unit_kerja['id']) ?>" class="block text-sm font-black text-blue-400 hover:text-blue-300 no-underline uppercase tracking-tight transition-colors">
                                    <i class="fas fa-building mr-2 opacity-50"></i><?= esc(strtoupper($parent_unit_kerja['nama_unit_kerja'])) ?>
                                </a>
                                <div class="pl-6 border-l-2 border-slate-800 ml-1 mt-2">
                                    <a href="<?= site_url('email/unit_kerja/' . ($current_unit_kerja['id'] ?? '')) ?>" class="block text-xs font-bold text-slate-500 hover:text-slate-300 no-underline uppercase tracking-widest transition-colors">
                                        <?= esc(strtoupper($current_unit_kerja['nama_unit_kerja'] ?? '')) ?>
                                    </a>
                                </div>
                            <?php elseif (!empty($current_unit_kerja)): ?>
                                <a href="<?= site_url('email/unit_kerja/' . $current_unit_kerja['id']) ?>" class="block text-base font-black text-blue-400 hover:text-blue-300 no-underline uppercase tracking-tight transition-colors">
                                    <i class="fas fa-building mr-2 opacity-50"></i><?= esc(strtoupper($current_unit_kerja['nama_unit_kerja'])) ?>
                                </a>
                            <?php else: ?>
                                <span class="block text-base font-black text-slate-200 uppercase tracking-tight">TIDAK TERDAFTAR</span>
                            <?php endif; ?>
                        </div>
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
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-check mr-2 text-green-400"></i> COPIED';
      btn.classList.add('border-green-500/50', 'text-green-400');
      setTimeout(() => { btn.innerHTML = originalText; btn.classList.remove('border-green-500/50', 'text-green-400'); }, 2000);
    });
  }

  function renderBsreStatus(status, keterangan = '') {
    const container = document.getElementById('bsre-status-container');
    if (!container) return;
    
    const badgeBase = 'px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-[0.2em] border shadow-sm';
    
    const colors = {
        'ISSUE': 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        'EXPIRED': 'bg-red-500/10 text-red-400 border-red-500/20',
        'RENEW': 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'WAITING_FOR_VERIFICATION': 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        'NEW': 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
        'NO_CERTIFICATE': 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
        'NOT_REGISTERED': 'bg-fuchsia-500/10 text-fuchsia-400 border-fuchsia-500/20',
        'SUSPEND': 'bg-purple-500/10 text-purple-400 border-purple-500/20',
        'REVOKE': 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20',
    };

    const badgeClass = colors[status] || 'bg-slate-950 text-slate-600 border-slate-800';
    container.innerHTML = `<span class="${badgeBase} ${badgeClass}">${status}</span>`;
  }

  function syncBsreStatus(email) {
    const container = document.getElementById('bsre-status-container');
    container.innerHTML = '<div class="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full"></div>';
    fetch('<?= site_url('bsre/sync-status') ?>', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' }, body: 'email=' + encodeURIComponent(email) })
      .then(r => r.json()).then(data => { if (data.status === 'success') renderBsreStatus(data.bsre_status); else container.innerHTML = '<span class="text-xs font-black text-red-500 uppercase">FAILED</span>'; })
      .catch(() => { container.innerHTML = '<span class="text-xs font-black text-red-500 uppercase">ERROR</span>'; });
  }

  function openEditModal() { document.getElementById('editModal').classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
  function closeEditModal() { document.getElementById('editModal').classList.add('hidden'); document.body.style.overflow = 'auto'; }

  document.addEventListener('DOMContentLoaded', () => {
    const initialStatus = '<?= esc($email['bsre_status'] ?? '', 'js') ?>';
    if (initialStatus) renderBsreStatus(initialStatus);
  });
</script>
<?= $this->endSection() ?>
