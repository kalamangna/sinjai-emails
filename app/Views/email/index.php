<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Email</h1>
            <?php if (!empty($last_sync_time)): ?>
                <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest mt-1">
                    <i class="fas fa-history mr-1"></i> Terakhir Sync: 
                    <span class="text-slate-800"><?= formatTanggalWaktu($last_sync_time) ?></span>
                </p>
            <?php endif; ?>
        </div>
        
        <div class="flex items-center gap-2 w-full lg:w-auto">
            <?php if (session()->get('role') === 'super_admin'): ?>
            <button onclick="confirmSyncCpanel(this)" data-href="<?= site_url('email/sync') ?>" class="flex-1 lg:flex-none inline-flex items-center justify-center px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-lg font-bold text-xs uppercase tracking-widest transition-all shadow-sm group" id="syncCpanelBtn">
                <i class="fas fa-sync-alt mr-2 group-hover:rotate-180 transition-transform duration-500"></i>
                <span>Sync cPanel</span>
            </button>
            <a href="<?= site_url('batch') ?>" class="flex-1 lg:flex-none inline-flex items-center justify-center px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-bold text-xs uppercase tracking-widest transition-all shadow-sm no-underline">
                <i class="fas fa-layer-group mr-2"></i> Batch
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Metrik -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">Total Akun</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($total_emails ?? 0) ?></h3>
        </div>
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm p-6">
            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Email Aktif</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($active_count ?? 0) ?></h3>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl shadow-sm p-6">
            <p class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">TTE Aktif</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($active_bsre_count ?? 0) ?></h3>
        </div>
    </div>

    <!-- Tabel dan Pencarian -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50">
            <form method="GET" action="<?= site_url('email') ?>" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-6 lg:col-span-8">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= esc($search ?? '') ?>" class="block w-full pl-9 pr-3 py-2 bg-white border <?= !empty($search) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm transition-all" placeholder="Cari nama, NIP, atau NIK...">
                    </div>
                </div>

                <div class="md:col-span-4 lg:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Status TTE</label>
                    <select name="bsre_status" class="block w-full px-3 py-2 bg-white border <?= !empty($bsre_status) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua Status</option>
                        <?php foreach ($bsre_status_options ?? [] as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= (($bsre_status ?? '') === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2 lg:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-bold text-xs uppercase tracking-widest transition-all">
                        <i class="fas fa-filter mr-2 text-white/80"></i> Filter
                    </button>
                    <a href="<?= site_url('email') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-lg transition-all shadow-sm" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200">Email</th>
                        <th class="px-6 py-3 border-b border-slate-200">Unit Kerja</th>
                        <th class="px-6 py-3 border-b border-slate-200">Status TTE</th>
                        <th class="px-6 py-3 border-b border-slate-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($emails)): ?>
                        <?php foreach ($emails as $email): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-800 lowercase leading-tight"><?= esc($email['email']) ?></span>
                                        <span class="text-[10px] font-bold text-slate-700 uppercase tracking-tight mt-0.5"><?= esc($email['name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <?php if (!empty($email['parent_unit_kerja_name'])): ?>
                                            <span class="text-[10px] font-bold text-slate-700 uppercase leading-none"><?= esc($email['parent_unit_kerja_name']) ?></span>
                                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight mt-1"><?= esc($email['unit_kerja_name']) ?></span>
                                        <?php else: ?>
                                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight"><?= esc($email['unit_kerja_name']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $status = $email['bsre_status'] ?? '';
                                    $colorClass = 'bg-slate-50 text-slate-700 border-slate-200';
                                    $statusLabel = $status ?: 'NOT_SYNCED';
                                    
                                    if ($status === 'ISSUE') { 
                                        $colorClass = 'bg-emerald-50 text-emerald-600 border-emerald-200'; 
                                    } elseif (in_array($status, ['EXPIRED', 'REVOKE', 'SUSPEND'])) { 
                                        $colorClass = 'bg-red-50 text-red-600 border-red-200'; 
                                    } elseif (in_array($status, ['WAITING_FOR_VERIFICATION', 'RENEW'])) { 
                                        $colorClass = 'bg-amber-50 text-amber-500 border-amber-200'; 
                                    } elseif ($status === 'NEW') {
                                        $colorClass = 'bg-blue-50 text-blue-600 border-blue-200';
                                    }
                                    ?>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border <?= $colorClass ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-700 hover:text-slate-800 transition-all shadow-sm" title="Detail">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <?php if (session()->get('role') === 'super_admin'): ?>
                                            <form action="<?= site_url('email/delete/' . $email['id']) ?>" method="post" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-700 hover:text-red-600 transition-all shadow-sm" title="Hapus">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-700 italic">Data tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (isset($pager)): ?>
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">
                Menampilkan <span class="text-slate-800"><?= count($emails) ?></span> dari <span class="text-slate-800"><?= number_format($total_emails ?? 0) ?></span> akun
            </div>
            <div class="pagination-container">
                <?= $pager->links() ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function confirmSyncCpanel(btn) {
        if (!confirm('Sinkronisasi akan mengambil metadata terbaru dari server cPanel. Proses ini mungkin memakan waktu beberapa saat. Lanjutkan?')) {
            return;
        }

        const url = btn.getAttribute('data-href');
        const icon = btn.querySelector('i');
        const text = btn.querySelector('span');

        // State: Loading
        showGlobalLoading(true);
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        icon.classList.remove('group-hover:rotate-180');
        icon.classList.add('fa-spin');
        text.innerText = 'MEMPROSES...';

        // Redirect
        window.location.href = url;
    }
</script>
<?= $this->endSection() ?>
