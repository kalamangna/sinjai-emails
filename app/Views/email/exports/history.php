<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Riwayat Laporan</h1>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">
                Daftar antrean laporan PDF latar belakang
            </p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <button onclick="window.location.reload()" class="flex-1 sm:flex-none btn btn-outline no-underline">
                <i class="fas fa-sync-alt mr-2 text-slate-700"></i> Segarkan
            </button>
        </div>
    </div>

    <!-- Alert Success -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg flex items-start shadow-sm" role="alert">
            <i class="fas fa-check-circle mt-0.5 mr-3 text-emerald-500"></i>
            <div class="text-sm font-medium">
                <?= session()->getFlashdata('success') ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Table Card -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-100 text-slate-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200">Tipe Laporan</th>
                        <th class="px-6 py-3 border-b border-slate-200">Status</th>
                        <th class="px-6 py-3 border-b border-slate-200">Nama File</th>
                        <th class="px-6 py-3 border-b border-slate-200">Waktu Mulai</th>
                        <th class="px-6 py-3 border-b border-slate-200 text-right">Aksi</th>
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
                                        <span class="font-mono text-xs"><?= esc($h['file_name']) ?></span>
                                    <?php else : ?>
                                        <span class="text-slate-400 italic">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-slate-800 leading-tight"><?= date('d M Y', strtotime($h['created_at'])) ?></p>
                                    <p class="text-[10px] text-slate-500 font-medium mt-1 uppercase tracking-widest"><?= date('H:i', strtotime($h['created_at'])) ?> WIB</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if ($h['status'] === 'COMPLETED' && $h['file_path']) : ?>
                                            <a href="<?= site_url('reports/download/' . $h['id']) ?>" id="download-btn-<?= $h['id'] ?>" class="inline-flex items-center justify-center px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded text-[10px] uppercase font-bold hover:bg-indigo-100 transition-colors">
                                                <i class="fas fa-download mr-1.5"></i> Download
                                            </a>
                                        <?php elseif ($h['status'] === 'FAILED') : ?>
                                            <button onclick="alert('Error: <?= addslashes($h['error_message']) ?>')" class="inline-flex items-center justify-center px-3 py-1.5 bg-slate-50 text-slate-600 rounded text-[10px] uppercase font-bold hover:bg-slate-100 transition-colors">
                                                <i class="fas fa-info-circle mr-1.5"></i> Info
                                            </button>
                                        <?php else : ?>
                                            <button disabled class="inline-flex items-center justify-center px-3 py-1.5 bg-slate-50 text-slate-400 rounded text-[10px] uppercase font-bold cursor-not-allowed">
                                                <i class="fas fa-hourglass-half mr-1.5"></i> Proses
                                            </button>
                                        <?php endif; ?>
                                        
                                        <form action="<?= site_url('reports/delete/' . $h['id']) ?>" method="post" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat ini? File PDF juga akan terhapus.')">
                                            <button type="submit" class="inline-flex items-center justify-center px-2 py-1.5 bg-rose-50 text-rose-600 rounded text-[10px] uppercase font-bold hover:bg-rose-100 transition-colors" title="Hapus">
                                                <i class="fas fa-trash-alt"></i>
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

<?php if (session()->getFlashdata('trigger_worker')): ?>
<!-- Trigger the background queue worker via client-side AJAX to bypass server loopback restrictions -->
<script>
    fetch('<?= site_url('api_trigger_queue') ?>').catch(e => console.error(e));
</script>
<?php endif; ?>

<script>
    // Auto-download new completed PDFs
    const completedJobs = document.querySelectorAll('a[id^="download-btn-"]');
    const autoDownloaded = JSON.parse(localStorage.getItem('auto_downloaded') || '[]');
    
    completedJobs.forEach(btn => {
        const id = btn.id.replace('download-btn-', '');
        if (!autoDownloaded.includes(id)) {
            autoDownloaded.push(id);
            localStorage.setItem('auto_downloaded', JSON.stringify(autoDownloaded));
            
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = btn.href;
            document.body.appendChild(iframe);
        }
    });
</script>

<?= $this->endSection() ?>
