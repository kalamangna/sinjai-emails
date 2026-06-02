<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight uppercase">Audit Trail</h1>
            <p class="text-sm font-medium text-slate-500 uppercase tracking-widest mt-1">Riwayat Aktivitas Sistem</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200">
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Pengguna</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Entitas</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm font-medium text-slate-500 uppercase">Belum ada riwayat aktivitas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 text-xs font-bold text-slate-700 whitespace-nowrap"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-bold text-slate-800 uppercase"><?= esc($log['user_name'] ?? 'Sistem') ?></div>
                                    <div class="text-[10px] text-slate-500"><?= esc($log['username'] ?? '-') ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold uppercase tracking-widest bg-slate-100 text-slate-700">
                                        <?= esc($log['action']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs font-medium text-slate-600">
                                    <?= esc($log['entity']) ?> <?php if ($log['entity_id']) echo '#' . esc($log['entity_id']); ?>
                                </td>
                                <td class="px-6 py-4 text-xs font-medium text-slate-600 max-w-xs truncate" title="<?= esc($log['details']) ?>">
                                    <?= esc($log['details']) ?>
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
