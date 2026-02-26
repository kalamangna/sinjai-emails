<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Navigasi dan Aksi -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <a href="<?= site_url('email') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-50 transition-all text-xs uppercase tracking-widest no-underline shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
        <div class="flex flex-wrap gap-2">
            <a href="<?= site_url('email/export_pimpinan_desa_pdf') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-gray-50 transition-all shadow-sm no-underline">
                <i class="fas fa-file-pdf mr-2 text-red-600"></i> Unduh PDF
            </a>
            <button onclick="syncAllBsreStatus()" class="inline-flex items-center justify-center px-4 py-2 bg-gray-900 text-white rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-gray-800 transition-all shadow-sm">
                <i class="fas fa-sync-alt mr-2"></i> Sync TTE
            </button>
        </div>
    </div>

    <!-- Header Halaman -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gray-100 border border-gray-200 rounded-xl flex items-center justify-center text-gray-400">
                    <i class="fas fa-users-cog text-2xl"></i>
                </div>
                <h1 class="text-2xl font-semibold text-gray-900">Kepala Desa</h1>
            </div>
            <div class="bg-gray-50 px-6 py-2 rounded-lg border border-gray-100 text-center">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Akun</p>
                <p class="text-xl font-bold text-gray-900"><?= number_format($total_emails) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <form action="<?= current_url() ?>" method="get" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-7">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" name="search" value="<?= esc($search ?? '') ?>" class="block w-full pl-9 pr-4 py-2 bg-white border <?= !empty($search) ? 'border-gray-900 ring-1 ring-gray-900' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm" placeholder="Cari nama, desa, atau NIP...">
                </div>
            </div>
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status TTE</label>
                <select name="bsre_status" class="block w-full px-3 py-2 bg-white border <?= !empty($bsre_status) ? 'border-gray-900 ring-1 ring-gray-900' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm appearance-none cursor-pointer">
                    <option value="">Semua Status</option>
                    <?php foreach ($bsre_status_options as $key => $label): ?>
                        <option value="<?= esc($key) ?>" <?= ($bsre_status === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gray-900 text-white rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-gray-800 transition-all">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                <a href="<?= site_url('email/pimpinan_desa') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg transition-all shadow-sm" title="Reset">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-4 border-b border-gray-200">Email</th>
                        <th class="px-6 py-4 border-b border-gray-200">Jabatan</th>
                        <th class="px-6 py-4 border-b border-gray-200">Wilayah</th>
                        <th class="px-6 py-4 border-b border-gray-200">Status TTE</th>
                        <th class="px-6 py-4 border-b border-gray-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <?php if (!empty($emails)): ?>
                        <?php foreach ($emails as $email): ?>
                            <tr class="hover:bg-gray-50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900 lowercase leading-tight"><?= esc($email['email']) ?></span>
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tight mt-0.5"><?= esc($email['name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-medium text-gray-700 uppercase tracking-tight leading-snug"><?= esc($email['jabatan']) ?: '-' ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-gray-900 uppercase tracking-tight"><?= esc($email['unit_kerja_name']) ?></span>
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5"><?= esc(trim(str_ireplace('KANTOR', '', $email['parent_unit_kerja_name'] ?? 'Kecamatan Tidak Terdata'))) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div id="bsre-status-<?= esc($email['user']) ?>" data-email="<?= esc($email['email']) ?>">
                                        <?php
                                        $st = $email['bsre_status'] ?? '';
                                        $colorClass = 'bg-gray-50 text-gray-400 border-gray-100';
                                        $label = $st ?: 'NOT SYNCED';

                                        if ($st === 'ISSUE') $colorClass = 'bg-green-50 text-green-700 border-green-100';
                                        elseif (in_array($st, ['EXPIRED', 'REVOKE', 'SUSPEND'])) $colorClass = 'bg-red-50 text-red-700 border-red-100';
                                        elseif (in_array($st, ['WAITING_FOR_VERIFICATION', 'RENEW'])) $colorClass = 'bg-amber-50 text-amber-700 border-amber-100';
                                        elseif ($st === 'NEW') $colorClass = 'bg-blue-50 text-blue-700 border-blue-100';
                                        ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase border <?= $colorClass ?>">
                                            <?= $label ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-300 text-gray-400 hover:text-gray-900 rounded-md text-[10px] font-bold uppercase tracking-widest shadow-sm transition-all" title="Detail">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <?php if (session()->get('role') === 'super_admin'): ?>
                                            <form action="<?= site_url('email/delete/' . $email['id']) ?>" method="post" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-300 text-gray-400 hover:text-red-600 rounded-md text-[10px] font-bold uppercase tracking-widest shadow-sm transition-all" title="Hapus">
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
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-xs font-medium italic">Data tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pagination): ?>
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                    Menampilkan <span class="text-gray-900"><?= count($emails) ?></span> dari <span class="text-gray-900"><?= number_format($total_emails) ?></span> akun
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
        @apply inline-flex items-center justify-center min-w-[28px] h-[28px] rounded bg-white border border-gray-200 text-[10px] font-bold text-gray-600 transition-all hover:border-gray-400 hover:text-gray-900 shadow-sm no-underline px-1.5;
    }

    .pagination-container li.active span {
        @apply bg-gray-900 border-gray-900 text-white shadow-sm;
    }
</style>

<script>
    function syncAllBsreStatus() {
        const containers = document.querySelectorAll('[id^="bsre-status-"]');
        if (!containers.length || !confirm('Sinkronkan status sertifikat untuk kepala desa yang tampil?')) return;

        containers.forEach((c, i) => {
            setTimeout(() => {
                const email = c.getAttribute('data-email');
                c.innerHTML = '<i class="fas fa-spinner fa-spin text-gray-300 text-[10px]"></i>';
                fetch('<?= site_url('bsre/sync-status') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'email=' + encodeURIComponent(email)
                }).then(r => r.json()).then(d => {
                    if (d.status === 'success') {
                        let colorClass = 'bg-gray-50 text-gray-400 border-gray-100';
                        if (d.bsre_status === 'ISSUE') colorClass = 'bg-green-50 text-green-700 border-green-100';
                        else if (['EXPIRED', 'REVOKE', 'SUSPEND'].includes(d.bsre_status)) colorClass = 'bg-red-50 text-red-700 border-red-100';
                        else if (['WAITING_FOR_VERIFICATION', 'RENEW'].includes(d.bsre_status)) colorClass = 'bg-amber-50 text-amber-700 border-amber-100';
                        else if (d.bsre_status === 'NEW') colorClass = 'bg-blue-50 text-blue-700 border-blue-100';

                        c.innerHTML = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase border ${colorClass}">${d.bsre_status}</span>`;
                    }
                });
            }, i * 200);
        });
    }
</script>
<?= $this->endSection() ?>