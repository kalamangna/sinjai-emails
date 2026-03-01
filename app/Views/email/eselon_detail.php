<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Navigasi dan Aksi -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <button onclick="history.back()" class="btn btn-outline">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </button>
        <div class="flex gap-2">
            <button id="syncAllTteBtn" onclick="syncAllBsreStatus()" class="btn btn-solid">
                <i class="fas fa-sync-alt mr-2 text-white/80"></i> Sync TTE
            </button>
        </div>
    </div>

    <!-- Header Halaman -->
    <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-slate-100 border border-slate-200 rounded-lg flex items-center justify-center text-slate-700">
                    <i class="fas fa-layer-group text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight leading-none">Eselon <?= esc($eselon['nama_eselon']) ?></h1>
            </div>
            <div class="flex gap-4 min-w-[240px]">
                <div class="flex-1 bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-lg p-4 text-center">
                    <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Total Email</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($total_emails ?? 0, 0, ',', '.') ?></p>
                </div>
                <div class="flex-1 bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-lg p-4 text-center">
                    <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">TTE Aktif</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($active_bsre_count ?? 0, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
        <form action="<?= current_url() ?>" method="get" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-7">
                <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Pencarian</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" name="search" value="<?= esc($search ?? '') ?>" class="block w-full pl-9 pr-4 py-2 bg-white border <?= !empty($search) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm transition-all" placeholder="Cari nama, NIP, atau NIK...">
                </div>
            </div>
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Status TTE</label>
                <select name="bsre_status" class="block w-full px-3 py-2 bg-white border <?= !empty($bsre_status) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm appearance-none cursor-pointer transition-all">
                    <option value="">Semua Status</option>
                    <?php foreach ($bsre_status_options as $key => $label): ?>
                        <option value="<?= esc($key) ?>" <?= ($bsre_status === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 btn btn-solid">
                    <i class="fas fa-filter mr-2 text-white/80"></i> Filter
                </button>
                <a href="<?= current_url() ?>" class="btn btn-outline" title="Reset">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel -->
    <div id="email-table-container" class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-4 border-b border-slate-200">Email</th>
                        <th class="px-6 py-4 border-b border-slate-200">Jabatan</th>
                        <th class="px-6 py-4 border-b border-slate-200">Unit Kerja</th>
                        <th class="px-6 py-4 border-b border-slate-200">Status TTE</th>
                        <th class="px-6 py-4 border-b border-slate-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php if (!empty($emails)): ?>
                        <?php foreach ($emails as $email): ?>
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-800 lowercase leading-tight"><?= esc($email['email']) ?></span>
                                        <span class="text-[10px] font-bold text-slate-700 uppercase tracking-tight mt-0.5"><?= esc($email['name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-medium text-slate-700 uppercase tracking-tight leading-snug"><?= esc($email['jabatan']) ?: '-' ?></span>
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
                                    <div id="bsre-status-<?= esc($email['user']) ?>" data-email="<?= esc($email['email']) ?>">
                                        <?php
                                        $st = $email['bsre_status'] ?? '';
                                        $colorClass = 'bg-slate-100 text-slate-700 border-transparent';
                                        $label = $st ?: 'NOT_SYNCED';

                                        if ($st === 'ISSUE') $colorClass = 'bg-emerald-100 text-emerald-800 border-transparent';
                                        elseif (in_array($st, ['EXPIRED', 'REVOKE', 'SUSPEND'])) $colorClass = 'bg-red-100 text-red-700 border-transparent';
                                        elseif (in_array($st, ['WAITING_FOR_VERIFICATION', 'RENEW', 'NO_CERTIFICATE'])) $colorClass = 'bg-amber-50 text-amber-500 border-amber-200';
                                        elseif ($st === 'NEW') $colorClass = 'bg-blue-100 text-slate-700 border-transparent';
                                        ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase border <?= $colorClass ?>">
                                            <?= $label ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="btn btn-table" title="Detail">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <?php if (session()->get('role') === 'super_admin'): ?>
                                            <form action="<?= site_url('email/delete/' . $email['id']) ?>" method="post" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-table" title="Hapus">
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
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                        <i class="fas fa-search text-slate-300 text-lg"></i>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Data tidak ditemukan</span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($emails)): ?>
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">
                    <?php
                    $start = ($pagination->getCurrentPage() - 1) * $pagination->getPerPage() + 1;
                    $end = $start + count($emails) - 1;
                    ?>
                    Menampilkan <span class="text-slate-800"><?= $start ?> - <?= $end ?></span> dari <span class="text-slate-800"><?= number_format($total_emails, 0, ',', '.') ?></span> akun
                </div>
                <?php if (isset($pagination) && $pagination->getPageCount() > 1): ?>
                    <div class="pagination-container">
                        <?= $pagination->links() ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .pagination-container ul {
        @apply flex items-center gap-1;
    }

    .pagination-container li a,
    .pagination-container li span {
        @apply inline-flex items-center justify-center min-w-[28px] h-[28px] rounded bg-white border border-slate-200 text-[10px] font-bold text-slate-700 transition-all hover:border-slate-800 hover:text-slate-800 shadow-sm no-underline px-1.5;
    }

    .pagination-container li.active span {
        @apply bg-slate-800 border-slate-800 text-white shadow-sm;
    }
</style>

<script>
    async function syncAllBsreStatus() {
        const containers = document.querySelectorAll('[id^="bsre-status-"]');
        if (!containers.length) return;
        
        if (!confirm(`Sinkronkan status sertifikat untuk ${containers.length} akun dalam eselon ini?`)) {
            return;
        }

        const syncBtn = document.getElementById('syncAllTteBtn');
        const originalBtnContent = syncBtn.innerHTML;

        // 1. Scroll ke tabel secara smooth
        const tableContainer = document.getElementById('email-table-container');
        if (tableContainer) {
            tableContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // 2. Disable tombol dan beri feedback visual
        syncBtn.disabled = true;
        syncBtn.classList.add('opacity-75', 'cursor-not-allowed');
        syncBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Syncing...';

        // 3. Proses secara sekuensial
        let processed = 0;
        for (const container of containers) {
            const email = container.getAttribute('data-email');
            const originalContent = container.innerHTML;
            
            // Scroll ke container yang sedang diproses
            container.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Set loading state untuk baris ini
            container.innerHTML = '<i class="fas fa-spinner fa-spin text-slate-700 text-[10px]"></i>';
            
            try {
                const response = await fetch('<?= site_url('bsre/sync-status') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'email=' + encodeURIComponent(email)
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    const colorClass = getJsStatusColor(data.bsre_status);
                    container.innerHTML = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase border ${colorClass}">${data.bsre_status}</span>`;
                } else {
                    container.innerHTML = originalContent;
                }
            } catch (error) {
                console.error('Sync failed for ' + email, error);
                container.innerHTML = originalContent;
            }

            processed++;
        }

        // 4. Restore tombol
        syncBtn.disabled = false;
        syncBtn.classList.remove('opacity-75', 'cursor-not-allowed');
        syncBtn.innerHTML = originalBtnContent;
        
        alert(`Selesai! ${processed} akun telah disinkronkan.`);
    }
</script>
<?= $this->endSection() ?>