<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight"><?= esc($title) ?></h1>
        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= site_url('admin/helpdesk') ?>" class="btn <?= empty($statusFilter) ? 'btn-solid' : 'btn-outline' ?> btn-sm text-[10px]">Semua</a>
            <a href="<?= site_url('admin/helpdesk?status=Menunggu') ?>" class="btn <?= $statusFilter == 'Menunggu' ? 'btn-solid !bg-amber-500 !border-amber-600' : 'btn-outline text-amber-600 border-amber-200' ?> btn-sm text-[10px]">
                Menunggu (<?= $statusCounts['Menunggu'] ?>)
            </a>
            <a href="<?= site_url('admin/helpdesk?status=Diproses') ?>" class="btn <?= $statusFilter == 'Diproses' ? 'btn-solid !bg-blue-500 !border-blue-600' : 'btn-outline text-blue-600 border-blue-200' ?> btn-sm text-[10px]">
                Diproses (<?= $statusCounts['Diproses'] ?>)
            </a>
            <a href="<?= site_url('admin/helpdesk?status=Selesai') ?>" class="btn <?= $statusFilter == 'Selesai' ? 'btn-solid !bg-emerald-600 !border-emerald-700' : 'btn-outline text-emerald-600 border-emerald-200' ?> btn-sm text-[10px]">
                Selesai (<?= $statusCounts['Selesai'] ?>)
            </a>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap">Tanggal</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap">Tiket ID</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Pemohon & Instansi</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Kategori</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap text-center">Status</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(empty($tickets)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm font-medium text-slate-500 italic">Belum ada tiket yang masuk.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($tickets as $t): 
                            $statusColor = 'bg-slate-100 text-slate-700';
                            if ($t['status'] == 'Menunggu') $statusColor = 'bg-amber-100 text-amber-700';
                            if ($t['status'] == 'Diproses') $statusColor = 'bg-blue-100 text-blue-700';
                            if ($t['status'] == 'Selesai') $statusColor = 'bg-emerald-100 text-emerald-700';
                            if ($t['status'] == 'Ditolak') $statusColor = 'bg-red-100 text-red-700';
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4 text-xs font-semibold text-slate-800 whitespace-nowrap"><?= formatTanggalWaktu($t['created_at']) ?></td>
                            <td class="px-6 py-4 text-xs font-bold text-slate-600 font-mono whitespace-nowrap"><?= esc($t['tiket_id']) ?></td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-slate-800 uppercase mb-1"><?= esc($t['nama_pemohon']) ?></p>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-tight"><?= esc($t['agency_name']) ?></p>
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase"><?= esc($t['kategori_layanan']) ?></td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-widest <?= $statusColor ?>"><?= esc($t['status']) ?></span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?= site_url('admin/helpdesk/detail/' . $t['id']) ?>" class="btn btn-outline btn-xs no-underline">
                                        Lihat Detail
                                    </a>
                                    <form action="<?= site_url('admin/helpdesk/delete/' . $t['id']) ?>" method="POST" onsubmit="return confirm('Hapus permohonan ini?');" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline btn-xs text-red-600 border-red-200 hover:bg-red-50 !p-1.5" title="Hapus Permohonan">
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
        <?= view('components/pagination', ['pager' => $pager, 'items' => $tickets, 'label' => 'Tiket']) ?>
    </div>
</div>
<?= $this->endSection() ?>
