<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Navigasi dan Aksi -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <button onclick="history.back()" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 text-slate-700 font-bold rounded-lg hover:bg-slate-50 transition-all text-xs uppercase tracking-widest no-underline shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </button>
        <div class="flex flex-wrap gap-2">
            <a href="<?= site_url('email/export_pimpinan_pdf') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm no-underline">
                <i class="fas fa-file-pdf mr-2 text-red-600"></i> Unduh PDF
            </a>
            <button id="syncAllTteBtn" onclick="syncAllBsreStatus()" class="inline-flex items-center justify-center px-4 py-2 bg-slate-800 text-white rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-slate-700 transition-all shadow-sm">
                <i class="fas fa-sync-alt mr-2 text-white/80"></i> Sync TTE
            </button>
        </div>
    </div>

    <!-- Header Halaman -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-slate-100 border border-slate-200 rounded-xl flex items-center justify-center text-slate-700">
                    <i class="fas fa-user-tie text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Pimpinan</h1>
            </div>
            <div class="bg-slate-50 px-6 py-2 rounded-lg border border-slate-200 text-center">
                <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">Total Akun</p>
                <p class="text-xl font-bold text-slate-800"><?= number_format($total_emails) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <form action="<?= current_url() ?>" method="get" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-7">
                <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Pencarian</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" name="search" value="<?= esc($search ?? '') ?>" class="block w-full pl-9 pr-4 py-2 bg-white border <?= !empty($search) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm transition-all" placeholder="Cari nama, NIP, atau NIK...">
                </div>
            </div>
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Status TTE</label>
                <select name="bsre_status" class="block w-full px-3 py-2 bg-white border <?= !empty($bsre_status) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm appearance-none cursor-pointer transition-all">
                    <option value="">Semua Status</option>
                    <?php foreach ($bsre_status_options as $key => $label): ?>
                        <option value="<?= esc($key) ?>" <?= ($bsre_status === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-bold text-xs uppercase tracking-widest transition-all">
                    <i class="fas fa-filter mr-2 text-white/80"></i> Filter
                </button>
                <a href="<?= site_url('email/pimpinan') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-lg transition-all shadow-sm" title="Reset">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel -->
    <div id="email-table-container" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-700 uppercase text-[10px] font-bold">
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
                                        $colorClass = 'bg-slate-50 text-slate-700 border-slate-200';
                                        $label = $st ?: 'NOT_SYNCED';

                                        if ($st === 'ISSUE') $colorClass = 'bg-emerald-50 text-emerald-600 border-emerald-200';
                                        elseif (in_array($st, ['EXPIRED', 'REVOKE', 'SUSPEND'])) $colorClass = 'bg-red-50 text-red-600 border-red-200';
                                        elseif (in_array($st, ['WAITING_FOR_VERIFICATION', 'RENEW'])) $colorClass = 'bg-amber-50 text-amber-500 border-amber-200';
                                        elseif ($st === 'NEW') $colorClass = 'bg-blue-50 text-blue-600 border-blue-200';
                                        ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase border <?= $colorClass ?>">
                                            <?= $label ?>
                                        </span>
                                    </div>
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
                            <td colspan="5" class="px-6 py-12 text-center text-slate-700 text-xs font-medium italic uppercase tracking-widest">Data tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pagination): ?>
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">
                    Menampilkan <span class="text-slate-800"><?= count($emails) ?></span> dari <span class="text-slate-800"><?= number_format($total_emails) ?></span> akun
                </div>
                <div class="pagination-container">
                    <?= $pagination->links() ?>
                </div>
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
        
        if (!confirm(`Sinkronkan status sertifikat untuk ${containers.length} pimpinan yang tampil?`)) {
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
            container.innerHTML = '<i class="fas fa-spinner fa-spin text-blue-600 text-[10px]"></i>';
            
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
        
        alert(`Selesai! ${processed} akun pimpinan telah disinkronkan.`);
    }
</script>
<?= $this->endSection() ?>