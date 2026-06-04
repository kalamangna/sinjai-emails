<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Audit Trail</h1>
            <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest mt-1">Riwayat Aktivitas Sistem</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
            <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-4">Ringkasan Aksi</h3>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($action_summary as $sum): ?>
                    <div class="bg-slate-50 border border-slate-200 rounded px-3 py-1.5 flex items-center">
                        <span class="text-[10px] font-bold text-slate-700 uppercase tracking-tight mr-2"><?= esc($sum['action']) ?></span>
                        <span class="bg-slate-700 text-white text-[9px] font-bold px-1.5 rounded-full"><?= $sum['count'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
            <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-4">Ringkasan Entitas</h3>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($entity_summary as $sum): ?>
                    <div class="bg-slate-50 border border-slate-200 rounded px-3 py-1.5 flex items-center">
                        <span class="text-[10px] font-bold text-slate-700 uppercase tracking-tight mr-2"><?= esc($sum['entity']) ?></span>
                        <span class="bg-slate-600 text-white text-[9px] font-bold px-1.5 rounded-full"><?= $sum['count'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200">Waktu</th>
                        <th class="px-6 py-3 border-b border-slate-200">Pengguna</th>
                        <th class="px-6 py-3 border-b border-slate-200">Aksi</th>
                        <th class="px-6 py-3 border-b border-slate-200">Entitas</th>
                        <th class="px-6 py-3 border-b border-slate-200">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($logs)): ?>
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
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold text-slate-800 uppercase tracking-tight"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-800 uppercase tracking-tight"><?= esc($log['user_name'] ?? 'Sistem') ?></span>
                                        <span class="text-[10px] font-bold text-slate-700 uppercase tracking-tight mt-0.5"><?= esc($log['username'] ?? '-') ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border bg-slate-100 text-slate-700 border-slate-200">
                                        <?= esc($log['action']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold text-slate-800 uppercase tracking-tight">
                                        <?= esc($log['entity']) ?> <?php if ($log['entity_id']) echo '#' . esc($log['entity_id']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-medium text-slate-600 block max-w-md truncate" title="<?= esc($log['details']) ?>">
                                        <?= esc($log['details']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
