<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Riwayat Laporan</h1>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <button onclick="window.location.reload()" class="flex-1 sm:flex-none btn btn-outline no-underline">
                <i class="fas fa-sync-alt mr-2 text-slate-700"></i> Segarkan
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[10px] font-bold tracking-widest">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200 min-w-[280px]">Tipe Laporan</th>
                        <th class="px-6 py-3 border-b border-slate-200 w-28 whitespace-nowrap">Status</th>
                        <th class="px-6 py-3 border-b border-slate-200">Nama File</th>
                        <th class="px-6 py-3 border-b border-slate-200 w-36 whitespace-nowrap">Waktu Mulai</th>
                        <th class="px-6 py-3 border-b border-slate-200 w-36 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($histories)) : ?>
                        <tr>
                            <td colspan="5" class="py-12 px-6 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <i class="fas fa-inbox text-4xl mb-3 text-slate-300"></i>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Belum ada riwayat export</p>
                                </div>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($histories as $h) : ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-file-pdf text-slate-600"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-800 leading-tight"><?= esc($h['type']) ?></p>
                                            <p class="text-[10px] text-slate-500 font-medium uppercase tracking-widest mt-1" title="<?= esc($h['filters']) ?>"><?= esc($h['readable_filters'] ?? 'Semua Data') ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($h['status'] === 'PENDING') : ?>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-black bg-slate-100 text-slate-600 border border-slate-200 uppercase tracking-tighter">
                                            <i class="fas fa-clock mr-1"></i> Menunggu
                                        </span>
                                    <?php elseif ($h['status'] === 'PROCESSING') : ?>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-black bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-tighter">
                                            <i class="fas fa-spinner fa-spin mr-1"></i> Memproses
                                        </span>
                                    <?php elseif ($h['status'] === 'COMPLETED') : ?>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-tighter">
                                            <i class="fas fa-check-circle mr-1"></i> Selesai
                                        </span>
                                    <?php elseif ($h['status'] === 'FAILED') : ?>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-black bg-rose-50 text-rose-700 border border-rose-200 uppercase tracking-tighter" title="<?= esc($h['error_message']) ?>">
                                            <i class="fas fa-exclamation-circle mr-1"></i> Gagal
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <?php if ($h['file_name']) : ?>
                                        <span class="font-mono text-xs break-all"><?= esc($h['file_name']) ?></span>
                                    <?php else : ?>
                                        <span class="text-slate-400 italic">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="font-medium text-slate-800 leading-tight"><?= date('d M Y', strtotime($h['created_at'])) ?></p>
                                    <p class="text-[10px] text-slate-500 font-medium mt-1 uppercase tracking-widest"><?= date('H:i', strtotime($h['created_at'])) ?> WIB</p>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5 flex-nowrap">
                                        <?php if ($h['status'] === 'COMPLETED' && $h['file_path']) : ?>
                                            <a href="<?= site_url('reports/download/' . $h['id']) ?>" target="_blank" id="download-btn-<?= $h['id'] ?>" class="btn btn-outline whitespace-nowrap shrink-0 !py-1.5 !px-3 !text-[10px] !font-bold uppercase tracking-wider text-slate-700 hover:text-slate-900 no-underline shadow-xs">
                                                <i class="fas fa-file-pdf mr-1.5 text-red-600"></i> Lihat PDF
                                            </a>
                                        <?php elseif ($h['status'] === 'FAILED') : ?>
                                            <button onclick="showGlobalError('Detail Kesalahan Export', '<?= addslashes($h['error_message'] ?? 'Terjadi kesalahan saat memproses laporan.') ?>')" class="btn btn-outline whitespace-nowrap shrink-0 !py-1.5 !px-3 !text-[10px] !font-bold uppercase tracking-wider text-rose-600 hover:bg-rose-50 border-rose-200">
                                                <i class="fas fa-info-circle mr-1.5"></i> Info
                                            </button>
                                        <?php else : ?>
                                            <button disabled class="btn btn-outline whitespace-nowrap shrink-0 !py-1.5 !px-3 !text-[10px] !font-bold uppercase tracking-wider text-slate-400 opacity-60 cursor-not-allowed">
                                                <i class="fas fa-spinner fa-spin mr-1.5"></i> Memproses
                                            </button>
                                        <?php endif; ?>
                                        
                                        <form action="<?= site_url('reports/delete/' . $h['id']) ?>" method="post" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat ini? File PDF juga akan terhapus.')">
                                            <button type="submit" class="btn btn-outline shrink-0 !w-8 !h-8 !p-0 text-slate-400 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-200 justify-center" title="Hapus Riwayat">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (!empty($histories)) : ?>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 text-xs text-slate-500">
                <i class="fas fa-info-circle mr-1 text-slate-400"></i> Menampilkan 100 riwayat terbaru. File PDF akan dihapus otomatis dari server setelah 3 hari untuk menghemat ruang penyimpanan.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Auto refresh every 15 seconds if there are pending/processing items -->
<?php
$needsRefresh = false;
foreach ($histories ?? [] as $h) {
    if (in_array($h['status'], ['PENDING', 'PROCESSING'])) {
        $needsRefresh = true;
        break;
    }
}
if ($needsRefresh):
?>
<script>
    setTimeout(function() {
        window.location.reload();
    }, 15000);
</script>
<?php endif; ?>

<?php 
// Check if we need to trigger the worker (either from flashdata or if there are pending jobs)
$hasPendingJobs = false;
foreach ($histories ?? [] as $h) {
    if ($h['status'] === 'PENDING' || $h['status'] === 'PROCESSING') {
        $hasPendingJobs = true;
        break;
    }
}
$shouldTriggerWorker = session()->getFlashdata('trigger_worker') || $hasPendingJobs;
?>

<?php if ($shouldTriggerWorker): ?>
<!-- Trigger the background queue worker via client-side AJAX to bypass server loopback restrictions -->
<script>
    fetch('<?= site_url('api_trigger_queue') ?>').catch(e => console.error(e));
</script>
<?php endif; ?>

<?= $this->endSection() ?>
