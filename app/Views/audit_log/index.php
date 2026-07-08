<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Audit Trail</h1>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
            <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-4">Ringkasan Aksi</h3>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($action_summary as $sum): ?>
                    <?php
                    $actionColors = [
                        'DELETE' => 'bg-red-50 border-red-200 text-red-700',
                        'CREATE' => 'bg-emerald-50 border-emerald-200 text-emerald-700',
                        'UPDATE' => 'bg-blue-50 border-blue-200 text-blue-700',
                        'LOGIN'  => 'bg-violet-50 border-violet-200 text-violet-700',
                        'LOGOUT' => 'bg-slate-50 border-slate-200 text-slate-600',
                    ];
                    $badgeClass = $actionColors[strtoupper($sum['action'])] ?? 'bg-slate-50 border-slate-200 text-slate-700';
                    $badgeBg = [
                        'DELETE' => 'bg-red-600',
                        'CREATE' => 'bg-emerald-600',
                        'UPDATE' => 'bg-blue-600',
                        'LOGIN'  => 'bg-violet-600',
                        'LOGOUT' => 'bg-slate-500',
                    ];
                    $countBg = $badgeBg[strtoupper($sum['action'])] ?? 'bg-slate-700';
                    ?>
                    <div class="border rounded px-3 py-1.5 flex items-center <?= $badgeClass ?>">
                        <span class="text-[10px] font-bold uppercase tracking-tight mr-2"><?= esc($sum['action']) ?></span>
                        <span class="<?= $countBg ?> text-white text-[9px] font-bold px-1.5 rounded-full"><?= $sum['count'] ?></span>
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

    <!-- Filter & Table -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">

        <!-- Filter Bar -->
        <div class="p-6 border-b border-slate-100 bg-slate-50">
            <form method="GET" action="<?= site_url('audit_log') ?>" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">

                <!-- Search -->
                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Cari Pengguna</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= esc($search) ?>" class="block w-full pl-9 pr-3 py-2 bg-white border <?= !empty($search) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm transition-all" placeholder="Nama atau username...">
                    </div>
                </div>

                <!-- Filter Aksi -->
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Aksi</label>
                    <select name="action" id="filter_action" class="block w-full px-3 py-2 bg-white border <?= !empty($filterAction) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua Aksi</option>
                        <?php foreach ($actions as $a): ?>
                            <option value="<?= esc($a['action']) ?>" <?= ($filterAction === $a['action']) ? 'selected' : '' ?>><?= esc($a['action']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Entitas -->
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Entitas</label>
                    <select name="entity" id="filter_entity" class="block w-full px-3 py-2 bg-white border <?= !empty($filterEntity) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua Entitas</option>
                        <?php foreach ($entities as $e): ?>
                            <option value="<?= esc($e['entity']) ?>" <?= ($filterEntity === $e['entity']) ? 'selected' : '' ?>><?= esc($e['entity']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tombol -->
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" id="filterBtn" class="flex-1 btn btn-solid">
                        <i class="fas fa-filter mr-2 text-white/80"></i> Filter
                    </button>
                    <a href="<?= site_url('audit_log') ?>" class="btn btn-outline" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
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
                            <?php
                            $actionBadge = [
                                'DELETE' => 'bg-red-50 text-red-700 border-red-200',
                                'CREATE' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'UPDATE' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'LOGIN'  => 'bg-violet-50 text-violet-700 border-violet-200',
                                'LOGOUT' => 'bg-slate-100 text-slate-600 border-slate-200',
                            ];
                            $badgeClass = $actionBadge[strtoupper($log['action'])] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                            ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-xs font-bold text-slate-800 uppercase tracking-tight"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-800 uppercase tracking-tight"><?= esc($log['user_name'] ?? 'Sistem') ?></span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight mt-0.5"><?= esc($log['username'] ?? '-') ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border <?= $badgeClass ?>">
                                        <?= esc($log['action']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold text-slate-800 uppercase tracking-tight">
                                        <?= esc($log['entity']) ?> <?php if ($log['entity_id']) echo '<span class="text-slate-400">#' . esc($log['entity_id']) . '</span>'; ?>
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

        <?= view('components/pagination', ['items' => $logs, 'pager' => $pager, 'label' => 'log']) ?>
    </div>
</div>
<?= $this->endSection() ?>
