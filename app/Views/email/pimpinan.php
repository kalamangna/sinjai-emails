<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-10">
    <!-- Tombol Kembali dan Aksi -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <a href="javascript:void(0);" onclick="history.back();" class="inline-flex items-center px-6 py-3 bg-slate-900 border border-slate-800 rounded-2xl text-xs font-black text-slate-400 uppercase tracking-widest hover:bg-slate-800 hover:text-slate-200 transition-all shadow-xl no-underline group">
            <i class="fas fa-arrow-left mr-3 group-hover:-translate-x-1 transition-transform"></i> Kembali
        </a>
        <div class="flex flex-wrap gap-3">
            <?php
            $queryString = \Config\Services::request()->getUri()->getQuery();
            $pdfUrl = site_url('email/export_pimpinan_pdf') . ($queryString ? '?' . $queryString : '');
            ?>
            <a href="<?= $pdfUrl ?>" class="inline-flex items-center px-5 py-2.5 bg-red-600 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-red-700 transition-all shadow-lg shadow-red-900/20 no-underline">
                <i class="fas fa-file-pdf mr-2 text-base"></i> Ekspor PDF
            </a>
            <button onclick="syncAllBsreStatus()" class="inline-flex items-center px-5 py-2.5 bg-amber-500 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-amber-600 transition-all shadow-lg shadow-amber-900/20">
                <i class="fas fa-sync-alt mr-2 text-xs"></i> Sinkron Sertifikat
            </button>
        </div>
    </div>

    <!-- Header Halaman -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] p-10 shadow-2xl overflow-hidden relative group">
        <div class="absolute -right-10 -top-10 w-64 h-64 bg-blue-500/5 rounded-full blur-3xl group-hover:bg-blue-500/10 transition-colors"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-10">
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 bg-blue-600 rounded-3xl flex items-center justify-center shadow-2xl shadow-blue-900/40 border-4 border-slate-900 group-hover:scale-105 transition-transform duration-500">
                    <i class="fas fa-user-tie text-white text-3xl"></i>
                </div>
                <div class="space-y-2">
                    <h2 class="text-3xl md:text-4xl font-black text-slate-100 uppercase tracking-tighter leading-none">Pimpinan Perangkat Daerah</h2>
                    <p class="text-sm font-bold text-slate-500 uppercase tracking-widest">Daftar Akun Identitas Digital Pejabat Struktural</p>
                </div>
            </div>
            <div class="flex gap-10 bg-slate-950 px-10 py-6 rounded-[2rem] border border-slate-800 shadow-inner">
                <div class="text-center space-y-1">
                    <div class="text-3xl font-black text-slate-100 tracking-tighter"><?= number_format($total_emails) ?></div>
                    <div class="text-[9px] font-black text-slate-600 uppercase tracking-[0.2em]">Total Akun</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Pencarian -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden p-8">
        <form action="<?= current_url() ?>" method="get" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
            <div class="md:col-span-5">
                <label class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Kata Kunci</label>
                <input type="text" class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all uppercase placeholder-slate-800" name="search" placeholder="NAMA, EMAIL, NIP..." value="<?= esc($search ?? '') ?>">
            </div>
            <div class="md:col-span-4">
                <label class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Status Sertifikat</label>
                <select name="bsre_status" class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all">
                    <option value="">SEMUA STATUS</option>
                    <?php foreach ($bsre_status_options as $key => $label): ?>
                        <option value="<?= esc($key) ?>" <?= ($bsre_status === $key) ? 'selected' : '' ?>>
                            <?= esc($key === 'not_synced' ? $label : $key) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-3 flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-black py-3.5 rounded-2xl shadow-xl shadow-blue-900/20 transition-all text-[10px] uppercase tracking-widest">Cari</button>
                <a href="<?= current_url() ?>" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 font-black py-3.5 rounded-2xl shadow-sm text-[10px] text-center no-underline uppercase tracking-widest flex items-center justify-center uppercase tracking-widest">Reset</a>
            </div>
        </form>
    </div>

    <!-- Tabel Section -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800/50">
                <thead class="bg-slate-950/30">
                    <tr>
                        <th class="px-10 py-8 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Alamat Email / Nama</th>
                        <th class="px-10 py-8 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Jabatan</th>
                        <th class="px-10 py-8 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Unit Kerja</th>
                        <th class="px-10 py-8 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Sertifikat</th>
                        <th class="px-10 py-8 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50 bg-slate-900/20">
                    <?php if (!empty($emails)): ?>
                        <?php foreach ($emails as $email): ?>
                            <tr class="hover:bg-slate-800/30 transition-colors group">
                                <td class="px-10 py-8 whitespace-nowrap align-middle">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-950 border border-slate-800/50 flex items-center justify-center mr-6 group-hover:border-blue-500/30 transition-all shadow-inner">
                                            <i class="fas fa-envelope text-slate-600 group-hover:text-blue-500/70 text-lg"></i>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-base font-bold text-slate-100 tracking-tight leading-none lowercase"><?= esc($email['email']) ?></div>
                                            <div class="text-[11px] text-slate-500 uppercase font-bold tracking-widest opacity-80"><?= esc($email['name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-10 py-8 align-middle">
                                    <div class="text-xs font-black text-slate-400 uppercase tracking-tight leading-none opacity-90"><?= esc($email['jabatan']) ?: '-' ?></div>
                                </td>
                                <td class="px-10 py-8 align-middle">
                                    <?php if (!empty($email['parent_unit_kerja_name'])): ?>
                                        <div class="text-sm font-bold text-slate-300 uppercase tracking-tight"><?= esc(strtoupper($email['parent_unit_kerja_name'])) ?></div>
                                        <div class="text-[10px] text-slate-500 uppercase font-bold tracking-tighter mt-1.5 opacity-60"><?= esc(strtoupper($email['unit_kerja_name'])) ?></div>
                                    <?php else: ?>
                                        <div class="text-sm font-bold text-slate-300 uppercase tracking-tight"><?= esc(strtoupper($email['unit_kerja_name'])) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-10 py-8 whitespace-nowrap align-middle">
                                    <div id="bsre-status-<?= esc($email['user']) ?>" data-user="<?= esc($email['user']) ?>" data-email="<?= esc($email['email']) ?>">
                                        <?php
                                        $status = $email['bsre_status'] ?? '';
                                        $badgeBase = 'inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border transition-all';
                                        
                                        $colors = [
                                            'ISSUE' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                            'EXPIRED' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                            'RENEW' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                            'WAITING_FOR_VERIFICATION' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                            'NEW' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                                            'NO_CERTIFICATE' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                                            'NOT_REGISTERED' => 'bg-fuchsia-500/10 text-fuchsia-400 border-fuchsia-500/20',
                                            'SUSPEND' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                            'REVOKE' => 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20',
                                        ];

                                        $badgeClass = $colors[$status] ?? 'bg-slate-950/50 text-slate-500 border-slate-800/50';
                                        ?>
                                        <span class="<?= $badgeBase ?> <?= $badgeClass ?>"><?= $status ?: 'BELUM SINKRON' ?></span>
                                    </div>
                                </td>
                                <td class="px-10 py-8 whitespace-nowrap text-center align-middle">
                                    <div class="flex justify-center space-x-4">
                                        <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="w-10 h-10 flex items-center justify-center bg-slate-950/50 text-slate-500 border border-slate-800/50 rounded-xl hover:bg-blue-600 hover:text-white hover:border-transparent transition-all no-underline shadow-sm" title="Lihat Rincian">
                                            <i class="fas fa-eye text-sm"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-10 py-24 text-center align-middle">
                                <div class="w-20 h-20 bg-slate-900 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-slate-800 shadow-inner">
                                    <i class="fas fa-inbox text-4xl text-slate-700"></i>
                                </div>
                                <h5 class="text-sm font-black text-slate-500 uppercase tracking-widest">Data Tidak Ditemukan</h5>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pagination): ?>
            <div class="bg-slate-950/50 px-10 py-10 border-t border-slate-800 flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="text-xs font-black text-slate-500 uppercase tracking-widest">
                    Total: <span class="text-blue-500 px-1"><?= number_format($total_emails) ?></span> Akun Ditemukan
                </div>
                <div class="pagination-container font-black uppercase">
                    <?= $pagination->links() ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function updateBsreStatusElement(emailUser, status, keterangan) {
    const div = document.getElementById(`bsre-status-${emailUser}`);
    if (!div) return;

    const badgeBase = 'inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border shadow-sm';
    
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
    div.innerHTML = `<span class="${badgeBase} ${badgeClass}">${status || 'UNKNOWN'}</span>`;
  }

  function syncBsreStatus(emailUser, emailAddress) {
    const div = document.getElementById(`bsre-status-${emailUser}`);
    if (!div) return;
    div.innerHTML = '<div class="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full mx-auto"></div>';
    fetch('<?= site_url('bsre/sync-status') ?>', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' }, body: 'email=' + encodeURIComponent(emailAddress) })
      .then(r => r.json()).then(d => { if (d.status === 'success') updateBsreStatusElement(emailUser, d.bsre_status); else div.innerHTML = '<span class="text-red-500 font-black text-[10px]">GAGAL</span>'; })
      .catch(() => div.innerHTML = '<span class="text-red-500 font-black text-[10px]">ERROR</span>');
  }

  function syncAllBsreStatus() {
    const containers = document.querySelectorAll('[id^="bsre-status-"]');
    if (!containers.length || !confirm(`Sinkronkan ${containers.length} akun yang tampil?`)) return;
    containers.forEach((c, i) => setTimeout(() => syncBsreStatus(c.id.replace('bsre-status-', ''), c.getAttribute('data-email')), i * 200));
  }
</script>
<?= $this->endSection() ?>