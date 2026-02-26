<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Email</h1>
            <?php if (!empty($last_sync_time)): ?>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">
                    <i class="fas fa-history mr-1"></i> Terakhir Sync: 
                    <span class="text-gray-600"><?= date('d/m/Y H:i', strtotime($last_sync_time)) ?></span>
                </p>
            <?php endif; ?>
        </div>
        
        <div class="flex items-center gap-2 w-full lg:w-auto">
            <?php if (session()->get('role') === 'super_admin'): ?>
            <button onclick="confirmSyncCpanel(this)" data-href="<?= site_url('email/sync') ?>" class="flex-1 lg:flex-none inline-flex items-center justify-center px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg font-bold text-xs uppercase tracking-widest transition-all shadow-sm group" id="syncCpanelBtn">
                <i class="fas fa-sync-alt mr-2 group-hover:rotate-180 transition-transform duration-500"></i>
                <span>Sync cPanel</span>
            </button>
            <a href="<?= site_url('batch') ?>" class="flex-1 lg:flex-none inline-flex items-center justify-center px-4 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-lg font-bold text-xs uppercase tracking-widest transition-all shadow-sm no-underline">
                <i class="fas fa-layer-group mr-2"></i> Batch
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Metrik -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <p class="text-sm font-medium text-gray-500">Total Email</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($total_emails) ?></h3>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <p class="text-sm font-medium text-gray-500">Aktif</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($active_count) ?></h3>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <p class="text-sm font-medium text-gray-500">Tidak Aktif</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($suspended_count) ?></h3>
        </div>
    </div>

    <!-- Tabel dan Pencarian -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-6 lg:col-span-8">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= isset($search) ? esc($search) : '' ?>" class="block w-full pl-9 pr-3 py-2 bg-white border <?= !empty($search) ? 'border-gray-900 ring-1 ring-gray-900' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm" placeholder="Cari nama atau email...">
                    </div>
                </div>

                <div class="md:col-span-4 lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="bsre_status" class="block w-full px-3 py-2 bg-white border <?= !empty($bsre_status) ? 'border-gray-900 ring-1 ring-gray-900' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm appearance-none cursor-pointer">
                        <option value="">Semua Status</option>
                        <?php foreach ($bsre_status_options as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= ($bsre_status === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2 lg:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-lg font-bold text-xs uppercase tracking-widest transition-all">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="<?= site_url('email') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg transition-all shadow-sm" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-gray-200">Email</th>
                        <th class="px-6 py-3 border-b border-gray-200">Unit Kerja</th>
                        <th class="px-6 py-3 border-b border-gray-200">Status TTE</th>
                        <th class="px-6 py-3 border-b border-gray-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (!empty($emails)): ?>
                        <?php foreach ($emails as $email): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900 lowercase"><?= esc($email['email']) ?></span>
                                        <span class="text-xs text-gray-500 uppercase font-medium tracking-tight"><?= esc($email['name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <?php if (!empty($email['parent_unit_kerja_name'])): ?>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase leading-none"><?= esc($email['parent_unit_kerja_name']) ?></span>
                                            <span class="text-xs font-bold text-gray-900 uppercase tracking-tight mt-1"><?= esc($email['unit_kerja_name']) ?></span>
                                        <?php else: ?>
                                            <span class="text-xs font-bold text-gray-900 uppercase tracking-tight"><?= esc($email['unit_kerja_name']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $status = $email['bsre_status'] ?? '';
                                    $colorClass = 'bg-gray-100 text-gray-600 border-gray-200';
                                    $statusLabel = $status ?: 'NOT SYNCED';
                                    
                                    if ($status === 'ISSUE') { 
                                        $colorClass = 'bg-green-50 text-green-700 border-green-100'; 
                                    } elseif (in_array($status, ['EXPIRED', 'REVOKE', 'SUSPEND'])) { 
                                        $colorClass = 'bg-red-50 text-red-700 border-red-100'; 
                                    } elseif (in_array($status, ['WAITING_FOR_VERIFICATION', 'RENEW'])) { 
                                        $colorClass = 'bg-amber-50 text-amber-700 border-amber-100'; 
                                    } elseif ($status === 'NEW') {
                                        $colorClass = 'bg-blue-50 text-blue-700 border-blue-100';
                                    }
                                    ?>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border <?= $colorClass ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-300 text-gray-400 hover:text-gray-900 transition-all shadow-sm" title="Detail">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <?php if (session()->get('role') === 'super_admin'): ?>
                                            <form action="<?= site_url('email/delete/' . $email['id']) ?>" method="post" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-300 text-gray-400 hover:text-red-600 transition-all shadow-sm" title="Hapus">
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
                            <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">Data tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (isset($pager)): ?>
        <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                Menampilkan <span class="text-gray-900"><?= count($emails) ?></span> dari <span class="text-gray-900"><?= number_format($total_emails) ?></span> akun
            </div>
            <div class="pagination-container">
                <?= $pager->links() ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .pagination-container ul { @apply flex items-center gap-1; }
    .pagination-container li a, .pagination-container li span { 
        @apply inline-flex items-center justify-center min-w-[28px] h-[28px] rounded bg-white border border-gray-200 text-[10px] font-bold text-gray-600 transition-all hover:border-gray-400 hover:text-gray-900 shadow-sm no-underline px-1.5;
    }
    .pagination-container li.active span { 
        @apply bg-gray-900 border-gray-900 text-white shadow-sm;
    }
</style>

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
<?= $this->endSection() ?>
