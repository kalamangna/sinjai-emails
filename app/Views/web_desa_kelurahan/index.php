<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="space-y-1">
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Website Desa & Kelurahan</h2>
            <p class="text-slate-500 text-xs font-medium uppercase tracking-wider">Pemantauan keaktifan website desa dan kelurahan</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?= site_url('web_desa_kelurahan/export_pdf') ?>" class="inline-flex items-center justify-center px-3 py-2 bg-rose-600 border border-transparent rounded-lg font-bold text-[10px] text-white uppercase tracking-wider hover:bg-rose-700 active:bg-rose-800 transition-all shadow-sm no-underline">
                <i class="fas fa-file-pdf mr-1.5"></i> PDF
            </a>
            <button type="button" class="inline-flex items-center justify-center px-3 py-2 bg-amber-500 border border-transparent rounded-lg font-bold text-[10px] text-white uppercase tracking-wider hover:bg-amber-600 active:bg-amber-700 transition-all shadow-sm focus:outline-none" id="batchSyncBtn" onclick="startBatchSync()">
                <i class="fas fa-sync mr-1.5"></i> Sync Expiry
            </button>
        </div>
    </div>

    <!-- Charts Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                <h6 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest flex items-center">
                    <i class="fas fa-chart-pie mr-2 text-blue-500"></i>Status Keaktifan
                </h6>
            </div>
            <div class="p-6 flex items-center justify-around gap-6">
                <div id="statusChart" class="w-full max-w-[180px]"></div>
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-[10px] font-bold text-slate-600 uppercase">Aktif: <?= $stats['aktif'] ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        <span class="text-[10px] font-bold text-slate-600 uppercase">Nonaktif: <?= $stats['nonaktif'] ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                <h6 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest flex items-center">
                    <i class="fas fa-microchip mr-2 text-indigo-500"></i>Distribusi Platform
                </h6>
            </div>
            <div class="p-6 flex items-center justify-around gap-6">
                <div id="platformChart" class="w-full max-w-[180px]"></div>
                <div class="max-h-[140px] overflow-y-auto custom-scrollbar pr-4 space-y-1">
                    <?php foreach ($platform_stats as $index => $ps): ?>
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full platform-legend-dot" data-index="<?= $index ?>"></span>
                            <span class="text-[9px] font-bold text-slate-500 uppercase truncate max-w-[100px]"><?= esc($ps['nama_platform'] ?: 'N/A') ?>: <?= $ps['count'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <form method="GET" action="<?= site_url('web_desa_kelurahan') ?>" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-3 lg:col-span-4">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Pencarian</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" name="search" value="<?= esc($search) ?>" class="block w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all" placeholder="Desa, Kecamatan, Domain...">
                </div>
            </div>
            <div class="md:col-span-2 lg:col-span-2">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Tipe</label>
                <select name="type" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                    <option value="">Semua</option>
                    <option value="DESA" <?= ($filterType === 'DESA') ? 'selected' : '' ?>>DESA</option>
                    <option value="KELURAHAN" <?= ($filterType === 'KELURAHAN') ? 'selected' : '' ?>>KELURAHAN</option>
                </select>
            </div>
            <div class="md:col-span-3 lg:col-span-2">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Platform</label>
                <select name="filter_platform" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                    <option value="">Semua</option>
                    <option value="NULL" <?= ($filterPlatform === 'NULL') ? 'selected' : '' ?>>TANPA PLATFORM</option>
                    <?php foreach ($platforms as $p): ?>
                        <option value="<?= esc($p['nama_platform']) ?>" <?= ($filterPlatform === $p['nama_platform']) ? 'selected' : '' ?>><?= esc($p['nama_platform']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-2 lg:col-span-2">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Status</label>
                <select name="status" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                    <option value="">Semua</option>
                    <option value="AKTIF" <?= ($filterStatus === 'AKTIF') ? 'selected' : '' ?>>AKTIF</option>
                    <option value="NONAKTIF" <?= ($filterStatus === 'NONAKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                </select>
            </div>
            <div class="md:col-span-2 lg:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-slate-800 border border-transparent rounded-lg font-bold text-[11px] text-white uppercase tracking-wider hover:bg-slate-900 active:bg-slate-950 transition-all shadow-sm focus:outline-none">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                <a href="<?= site_url('web_desa_kelurahan') ?>" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-lg font-bold text-[11px] text-slate-600 uppercase tracking-wider hover:bg-slate-50 hover:text-slate-900 active:bg-slate-100 transition-all shadow-sm no-underline" title="Reset Filter">
                    <i class="fas fa-redo mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Sync Progress -->
    <div id="syncProgressContainer" class="hidden bg-blue-50 border border-blue-100 rounded-xl p-4 space-y-2">
        <div class="flex justify-between text-[10px] font-bold text-blue-600 uppercase">
            <span>Synchronizing Expiry Dates...</span>
            <span id="syncStatusCount">0/0</span>
        </div>
        <div class="w-full bg-blue-100 rounded-full h-1.5">
            <div id="syncProgressBar" class="bg-blue-600 h-full rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Desa & Kelurahan</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Domain & Platform</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Berakhir</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php foreach ($websites as $web): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors website-row" data-id="<?= $web['id'] ?>">
                            <td class="px-6 py-4">
                                <div class="text-[13px] font-bold text-slate-900 uppercase leading-none mb-1"><?= esc($web['desa_kelurahan']) ?></div>
                                <div class="text-[10px] font-medium text-slate-400 uppercase tracking-tight"><?= esc($web['kecamatan']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (!empty($web['domain'])): ?>
                                    <a href="http://<?= esc($web['domain']) ?>" target="_blank" class="text-sm font-bold text-blue-600 hover:text-blue-700 no-underline lowercase flex items-center group/link">
                                        <?= esc($web['domain']) ?> <i class="fas fa-external-link-alt ml-1.5 text-[10px] opacity-0 group-hover/link:opacity-40 transition-opacity"></i>
                                    </a>
                                <?php endif; ?>
                                <div class="text-[9px] font-bold text-slate-400 uppercase mt-1">
                                    <?= esc($web['platform_name'] ?: 'No Platform') ?>
                                    <?= (strtoupper($web['dikelola_kominfo'] ?? '') === 'YA') ? ' • <span class="text-emerald-600">Kominfo</span>' : '' ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-slate-600" id="date-cell-<?= $web['id'] ?>">
                                <?php if (stripos($web['desa_kelurahan'], 'KELURAHAN') !== false): ?>
                                    01-02-2027
                                <?php else: ?>
                                    <?= $web['tanggal_berakhir'] ? date('d-m-Y', strtotime($web['tanggal_berakhir'])) : '-' ?>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" id="status-cell-<?= $web['id'] ?>">
                                <?php
                                $status = strtoupper($web['status'] ?? 'NONAKTIF');
                                $stCls = ($status === 'AKTIF') ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100';
                                ?>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[9px] font-bold uppercase tracking-wider border <?= $stCls ?>"><?= $status ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="<?= site_url('web_desa_kelurahan/edit/' . $web['id']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-300 transition-all no-underline">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const stats = <?= json_encode($stats) ?>;
        const platformStats = <?= json_encode($platform_stats) ?>;
        const commonOptions = {
            chart: {
                type: 'donut',
                height: 180,
                foreColor: '#64748b'
            },
            stroke: {
                show: false
            },
            dataLabels: {
                enabled: false
            },
            legend: {
                show: false
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '75%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                fontSize: '10px',
                                fontWeight: 700,
                                offsetY: -5
                            },
                            value: {
                                show: true,
                                fontSize: '16px',
                                fontWeight: 800,
                                offsetY: 5,
                                color: '#1e293b'
                            },
                            total: {
                                show: true,
                                label: 'TOTAL',
                                fontSize: '8px',
                                fontWeight: 700,
                                color: '#94a3b8'
                            }
                        }
                    }
                }
            },
            tooltip: {
                theme: 'light'
            }
        };

        new ApexCharts(document.querySelector("#statusChart"), {
            ...commonOptions,
            series: [stats.aktif, stats.nonaktif],
            labels: ['AKTIF', 'NONAKTIF'],
            colors: ['#10b981', '#f43f5e']
        }).render();

        const pColors = ['#3b82f6', '#6366f1', '#8b5cf6', '#d946ef', '#f43f5e', '#f59e0b', '#10b981', '#06b6d4'];
        new ApexCharts(document.querySelector("#platformChart"), {
            ...commonOptions,
            series: platformStats.map(p => parseInt(p.count)),
            labels: platformStats.map(p => p.nama_platform || 'N/A'),
            colors: pColors
        }).render();

        document.querySelectorAll('.platform-legend-dot').forEach((dot, i) => {
            dot.style.backgroundColor = pColors[i % pColors.length];
        });
    });

    async function syncExpiration(id) {
        const dateCell = document.getElementById('date-cell-' + id);
        const statusCell = document.getElementById('status-cell-' + id);
        if (dateCell) dateCell.innerHTML = '<i class="fas fa-spinner fa-spin text-blue-600"></i>';
        try {
            const r = await fetch('<?= site_url('web_desa_kelurahan/sync_expiration/') ?>' + id);
            const d = await r.json();
            if (d.status === 'success') {
                dateCell.innerText = d.date;
                if (statusCell && d.web_status) {
                    const cls = (d.web_status === 'AKTIF') ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100';
                    statusCell.innerHTML = `<span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold border ${cls}">${d.web_status}</span>`;
                }
                return true;
            }
        } catch (e) {
            console.error(e);
        }
        if (dateCell) dateCell.innerText = 'Error';
        return false;
    }

    async function startBatchSync() {
        if (!confirm('Sinkronkan masa berlaku untuk semua data yang tampil?')) return;
        const btn = document.getElementById('batchSyncBtn');
        const container = document.getElementById('syncProgressContainer');
        const bar = document.getElementById('syncProgressBar');
        const count = document.getElementById('syncStatusCount');
        btn.disabled = true;
        container.classList.remove('hidden');
        const rows = document.querySelectorAll('.website-row');
        const total = rows.length;
        for (let i = 0; i < total; i++) {
            await syncExpiration(rows[i].getAttribute('data-id'));
            const p = ((i + 1) / total) * 100;
            bar.style.width = p + '%';
            count.innerText = `${i + 1}/${total}`;
        }
        setTimeout(() => location.reload(), 1000);
    }

    function preparePdfExport() {
        return true;
    }
</script>
<?= $this->endSection() ?>