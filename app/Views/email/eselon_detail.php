<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-10">
    <!-- Back Button -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <a href="javascript:void(0);" onclick="history.back();" class="inline-flex items-center px-6 py-3 bg-slate-900 border border-slate-800 rounded-2xl text-xs font-black text-slate-400 uppercase tracking-widest hover:bg-slate-800 hover:text-slate-200 transition-all shadow-xl no-underline group">
            <i class="fas fa-arrow-left mr-3 group-hover:-translate-x-1 transition-transform"></i> Kembali
        </a>
        <div class="flex gap-3">
            <button onclick="syncAllBsreStatus()" class="inline-flex items-center px-5 py-2.5 bg-amber-500 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-amber-600 transition-all shadow-lg shadow-amber-900/20">
                <i class="fas fa-sync-alt mr-2 text-xs"></i> Sinkron TTE
            </button>
        </div>
    </div>

    <!-- Eselon Header -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] p-10 shadow-2xl overflow-hidden relative group">
        <div class="absolute -right-10 -top-10 w-64 h-64 bg-indigo-500/5 rounded-full blur-3xl group-hover:bg-indigo-500/10 transition-colors"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-10">
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 bg-indigo-600 rounded-3xl flex items-center justify-center shadow-2xl shadow-indigo-900/40 border-4 border-slate-900 group-hover:scale-105 transition-transform duration-500">
                    <i class="fas fa-layer-group text-white text-3xl"></i>
                </div>
                <div class="space-y-2">
                    <h2 class="text-3xl md:text-4xl font-black text-slate-100 uppercase tracking-tighter leading-none">ESELON: <?= esc(strtoupper($eselon['nama_eselon'])) ?></h2>
                    <p class="text-sm font-bold text-slate-500 uppercase tracking-widest">Daftar Akun Email Terkait</p>
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

    <!-- Search Form -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden p-8">
        <form action="<?= current_url() ?>" method="get" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
            <div class="md:col-span-5">
                <label class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Cari</label>
                <input type="text" class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all uppercase tracking-tight placeholder-slate-800" name="search" placeholder="NAMA / EMAIL / JABATAN..." value="<?= esc($search ?? '') ?>">
            </div>
            <div class="md:col-span-4">
                <label class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Status TTE</label>
                <select name="bsre_status" id="bsre_status" class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase tracking-tight cursor-pointer transition-all">
                    <option value="" <?= empty($bsre_status) ? 'selected' : '' ?>>SEMUA STATUS</option>
                    <?php foreach ($bsre_status_options as $key => $label): ?>
                        <option value="<?= esc($key) ?>" <?= ($bsre_status === $key) ? 'selected' : '' ?>>
                            <?= esc($key === 'not_synced' ? $label : $label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-3 flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-black py-3.5 rounded-2xl shadow-xl shadow-blue-900/20 transition-all text-[10px] uppercase tracking-widest">
                    Cari
                </button>
                <a href="<?= current_url() ?>" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 font-black py-3.5 rounded-2xl shadow-sm text-[10px] text-center no-underline uppercase tracking-widest">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Email List for Eselon -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
        <div class="p-0">
            <?php if (!empty($emails)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-800">
                        <thead class="bg-slate-950/50">
                            <tr>
                                <th class="px-10 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Pengguna / Email</th>
                                <th class="px-10 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Jabatan</th>
                                <th class="px-10 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Unit Kerja</th>
                                <th class="px-10 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Sertifikat</th>
                                <th class="px-10 py-6 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 bg-slate-900/30">
                            <?php foreach ($emails as $email): ?>
                                <tr class="hover:bg-slate-800/30 transition-colors group">
                                    <td class="px-10 py-6 whitespace-nowrap align-middle">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-center mr-5 group-hover:border-blue-500/30 transition-all">
                                                <i class="fas fa-envelope text-slate-600 group-hover:text-blue-500 text-lg"></i>
                                            </div>
                                            <div>
                                                <div class="text-base font-black text-slate-200 tracking-tighter leading-none mb-1.5 lowercase"><?= esc($email['email']) ?></div>
                                                <div class="text-[11px] text-slate-500 uppercase font-black tracking-widest"><?= esc($email['name']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-10 py-6 align-middle">
                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter leading-none"><?= esc($email['jabatan']) ?: '-' ?></div>
                                    </td>
                                    <td class="px-10 py-6 align-middle">
                                        <?php if (!empty($email['parent_unit_kerja_name'])): ?>
                                            <div class="text-xs font-black text-slate-300 uppercase tracking-tight"><?= esc($email['parent_unit_kerja_name']) ?></div>
                                            <div class="text-[9px] text-slate-600 uppercase font-bold tracking-tight mt-1"><?= esc($email['unit_kerja_name']) ?></div>
                                        <?php else: ?>
                                            <div class="text-xs font-black text-slate-300 uppercase tracking-tight"><?= esc($email['unit_kerja_name']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-10 py-6 whitespace-nowrap align-middle">
                                        <div id="bsre-status-<?= esc($email['user']) ?>"
                                            data-user="<?= esc($email['user']) ?>"
                                            data-email="<?= esc($email['email']) ?>"
                                            class="bsre-status-container">
                                            $status = $email['bsre_status'] ?? '';
                                            $badgeBase = 'inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border shadow-sm';
                                            
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

                                            $badgeClass = $colors[$status] ?? 'bg-slate-950 text-slate-600 border-slate-800';
                                            $badgeText = $status ?: 'BELUM SINKRON';
                                            ?>
                                            <span class="<?= $badgeBase ?> <?= $badgeClass ?>"><?= esc($badgeText) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-10 py-6 whitespace-nowrap text-center text-sm font-medium align-middle">
                                        <div class="flex justify-center space-x-3">
                                            <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="w-10 h-10 flex items-center justify-center bg-slate-950 text-slate-400 border border-slate-800 rounded-xl hover:bg-blue-600 hover:text-white hover:border-transparent transition-all no-underline shadow-sm">
                                                <i class="fas fa-eye text-sm"></i>
                                            </a>
                                            <form action="<?= site_url('email/delete/' . $email['id']) ?>" method="post" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus email ini?');">
                                                <button type="submit" class="w-10 h-10 flex items-center justify-center bg-slate-950 text-slate-400 border border-slate-800 rounded-xl hover:bg-red-600 hover:text-white hover:border-transparent transition-all shadow-sm">
                                                    <i class="fas fa-trash-alt text-sm"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pagination): ?>
                    <div class="bg-slate-950/50 px-10 py-10 border-t border-slate-800 flex flex-col md:flex-row justify-between items-center gap-8">
                        <div class="text-xs font-black text-slate-500 uppercase tracking-widest">
                            Menampilkan <span class="text-blue-500 px-1"><?= ($pagination->getCurrentPage() - 1) * $pagination->getPerPage() + 1 ?></span> s/d <span class="text-blue-500 px-1"><?= min($pagination->getCurrentPage() * $pagination->getPerPage(), $total_emails) ?></span> dari <span class="text-blue-500 px-1"><?= number_format($total_emails) ?></span> entri
                        </div>
                        <div class="pagination-container font-black uppercase">
                            <?= $pagination->links() ?>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-20 bg-slate-950/30">
                    <div class="w-20 h-20 bg-slate-900 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-slate-800">
                        <i class="fas fa-inbox text-4xl text-slate-700"></i>
                    </div>
                    <h5 class="text-sm font-black text-slate-500 uppercase tracking-widest mb-2">Tidak ada data ditemukan</h5>
                    <p class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">Belum ada akun email yang terdaftar pada eselon ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function updateBsreStatusElement(emailUser, status, keterangan) {
    const bsreStatusDiv = document.getElementById(`bsre-status-${emailUser}`);
    if (!bsreStatusDiv) return;

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
    const badgeText = status || 'UNKNOWN';

    bsreStatusDiv.innerHTML = `<span class="${badgeBase} ${badgeClass}">${badgeText}</span>`;
  }

  function syncBsreStatus(emailUser, emailAddress) {
    const bsreStatusDiv = document.getElementById(`bsre-status-${emailUser}`);
    if (!bsreStatusDiv) return;

    // Show syncing state
    bsreStatusDiv.innerHTML = '<div class="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full mx-auto"></div>';

    fetch('<?= site_url('bsre/sync-status') ?>', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'email=' + encodeURIComponent(emailAddress)
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          // Update display with new status
          updateBsreStatusElement(emailUser, data.bsre_status, '');
        } else {
          bsreStatusDiv.innerHTML = `<span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black bg-red-500/10 text-red-400 border border-red-500/20 uppercase tracking-widest">Error</span>`;
          console.error(`Error syncing Status TTE for ${emailAddress}:`, data.message);
        }
      })
      .catch(error => {
        bsreStatusDiv.innerHTML = `<span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black bg-red-500/10 text-red-400 border border-red-500/20 uppercase tracking-widest">Net Error</span>`;
        console.error(`Network error syncing Status TTE for ${emailAddress}:`, error);
      });
  }

  function syncAllBsreStatus() {
    const statusContainers = document.querySelectorAll('[id^="bsre-status-"]');

    if (statusContainers.length === 0) {
      alert('Tidak ada data untuk disinkronkan.');
      return;
    }

    if (!confirm(`Sinkronkan status TTE untuk ${statusContainers.length} akun yang tampil?`)) {
      return;
    }

    statusContainers.forEach((container, index) => {
      const emailUser = container.id.replace('bsre-status-', '');
      const emailAddress = container.getAttribute('data-email'); // Get email from data attribute

      if (emailUser && emailAddress) {
        setTimeout(() => {
          syncBsreStatus(emailUser, emailAddress);
        }, index * 200); // 200ms delay per request
      }
    });
  }
</script>
<?= $this->endSection() ?>
