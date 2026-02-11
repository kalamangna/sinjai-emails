<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-10">
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <h2 class="text-3xl font-black text-slate-100 uppercase tracking-tighter">Monitoring Website Desa</h2>
        <div class="flex flex-wrap gap-3">
            <form id="pdfExportForm" action="<?= site_url('web_desa_kelurahan/export_pdf') . '?' . ($_SERVER['QUERY_STRING'] ?? '') ?>" method="POST" target="_blank" onsubmit="return preparePdfExport();" class="inline">
                <input type="hidden" name="statusChartData" id="statusChartData">
                <input type="hidden" name="platformChartData" id="platformChartData">
                <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-red-600 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-red-700 transition-all shadow-lg shadow-red-900/20">
                    <i class="fas fa-file-pdf mr-2"></i> Ekspor PDF
                </button>
            </form>
            <button type="button" class="inline-flex items-center px-5 py-2.5 bg-amber-500 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-amber-600 transition-all shadow-lg shadow-amber-900/20" id="batchSyncBtn" onclick="startBatchSync()">
                <i class="fas fa-sync mr-2"></i> Sinkron Masa Berlaku
            </button>
            <a href="<?= site_url('web_desa_kelurahan/create') ?>" class="inline-flex items-center px-5 py-2.5 bg-blue-600 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-900/20 no-underline">
                <i class="fas fa-plus mr-2"></i> Tambah Data
            </a>
        </div>
    </div>

    <!-- Dashboard Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Status Chart Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
            <div class="bg-slate-800/30 px-8 py-5 border-b border-slate-800">
                <h6 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                    <i class="fas fa-chart-pie mr-3 text-blue-500 opacity-50"></i>Status Website
                </h6>
            </div>
            <div class="p-10 flex flex-col sm:flex-row items-center gap-10">
                <div class="w-full sm:w-1/2 min-h-[220px] relative">
                    <div id="statusChart"></div>
                </div>
                <div class="w-full sm:w-1/2">
                    <div class="space-y-3">
                        <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex justify-between items-center group hover:border-slate-700 transition-colors">
                            <div class="flex items-center">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-3 shadow-sm"></span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight group-hover:text-slate-200 transition-colors">Aktif</span>
                            </div>
                            <span class="text-[10px] font-black text-slate-100"><?= number_format($stats['aktif']) ?> (<?= (int)$stats['aktif_percentage'] ?>%)</span>
                        </div>
                        <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex justify-between items-center group hover:border-slate-700 transition-colors">
                            <div class="flex items-center">
                                <span class="w-2 h-2 rounded-full bg-rose-500 mr-3 shadow-sm"></span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight group-hover:text-slate-200 transition-colors">Nonaktif</span>
                            </div>
                            <span class="text-[10px] font-black text-slate-100"><?= number_format($stats['nonaktif']) ?> (<?= (int)$stats['nonaktif_percentage'] ?>%)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Platform Chart Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
            <div class="bg-slate-800/30 px-8 py-5 border-b border-slate-800">
                <h6 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                    <i class="fas fa-microchip mr-3 text-indigo-500 opacity-50"></i>Distribusi Platform
                </h6>
            </div>
            <div class="p-10 flex flex-col sm:flex-row items-center gap-10">
                <div class="w-full sm:w-1/2 min-h-[220px] relative">
                    <div id="platformChart"></div>
                </div>
                <div class="w-full sm:w-1/2 overflow-y-auto max-h-[220px] space-y-2 custom-scrollbar pr-2">
                    <?php foreach ($platform_stats as $index => $ps): ?>
                        <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex justify-between items-center group hover:border-slate-700 transition-colors">
                            <div class="flex items-center">
                                <span class="w-2 h-2 rounded-full mr-3 shadow-sm platform-legend-dot" data-index="<?= $index ?>"></span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight group-hover:text-slate-200 transition-colors"><?= esc($ps['nama_platform']) ?: '-' ?></span>
                            </div>
                            <span class="text-[10px] font-black text-slate-100"><?= number_format($ps['count']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="bg-green-500/10 border border-green-500/20 text-green-400 px-6 py-4 rounded-2xl flex items-center shadow-2xl" role="alert">
            <i class="fas fa-check-circle mr-4 text-xl"></i>
            <span class="font-bold text-sm uppercase tracking-widest"><?= session()->getFlashdata('message') ?></span>
        </div>
    <?php endif; ?>

    <!-- Filter Section -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden p-8">
        <form method="GET" action="<?= site_url('web_desa_kelurahan') ?>" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
            <div class="md:col-span-4">
                <label class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Kata Kunci</label>
                <input type="text" name="search" value="<?= esc($search) ?>" class="block w-full px-5 py-3 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all uppercase placeholder-slate-800" placeholder="DESA, KECAMATAN, DOMAIN...">
            </div>
            <div class="md:col-span-2">
                <label class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Kecamatan</label>
                <select name="kecamatan" class="block w-full px-5 py-3 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all">
                    <option value="">SEMUA</option>
                    <?php foreach ($kecamatan_list as $k): ?>
                        <option value="<?= esc($k['kecamatan']) ?>" <?= ($filterKecamatan === $k['kecamatan']) ? 'selected' : '' ?>><?= esc($k['kecamatan']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Tipe</label>
                <select name="type" class="block w-full px-5 py-3 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all">
                    <option value="">SEMUA</option>
                    <option value="DESA" <?= ($filterType === 'DESA') ? 'selected' : '' ?>>DESA</option>
                    <option value="KELURAHAN" <?= ($filterType === 'KELURAHAN') ? 'selected' : '' ?>>KELURAHAN</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Platform</label>
                <select name="filter_platform" class="block w-full px-5 py-3 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all">
                    <option value="">SEMUA</option>
                    <option value="NULL" <?= ($filterPlatform === 'NULL') ? 'selected' : '' ?>>TIDAK TERDAFTAR</option>
                    <?php foreach ($platforms as $p): ?>
                        <option value="<?= esc($p['nama_platform']) ?>" <?= ($filterPlatform === $p['nama_platform']) ? 'selected' : '' ?>><?= esc($p['nama_platform']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Status</label>
                <select name="status" class="block w-full px-5 py-3 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all">
                    <option value="">SEMUA</option>
                    <option value="AKTIF" <?= ($filterStatus === 'AKTIF') ? 'selected' : '' ?>>AKTIF</option>
                    <option value="NONAKTIF" <?= ($filterStatus === 'NONAKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                </select>
            </div>
            <div class="md:col-span-12 flex justify-end gap-3 mt-4">
                <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-2xl shadow-xl shadow-blue-900/20 transition-all text-[10px] uppercase tracking-widest flex items-center">
                    <i class="fas fa-filter mr-2 text-xs"></i> Terapkan Filter
                </button>
                <a href="<?= site_url('web_desa_kelurahan') ?>" class="px-8 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-black border border-transparent rounded-2xl shadow-sm transition-all text-[10px] uppercase tracking-widest no-underline flex items-center">
                    <i class="fas fa-sync-alt mr-2 text-xs"></i> Atur Ulang
                </a>
            </div>
        </form>
    </div>

    <!-- Progress Bar -->
    <div id="syncProgressContainer" class="hidden bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl overflow-hidden relative group animate-pulse">
        <div class="flex justify-between items-center mb-4">
            <span class="text-xs font-black text-blue-400 uppercase tracking-[0.2em]">Sinkronisasi Masa Berlaku...</span>
            <span id="syncStatusCount" class="text-xs font-black text-slate-500">0/0</span>
        </div>
        <div class="w-full bg-slate-950 rounded-full h-4 p-1 border border-slate-800 shadow-inner">
            <div id="syncProgressBar" class="bg-blue-600 h-full rounded-full transition-all duration-300 shadow-lg shadow-blue-900/40" style="width: 0%"></div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800/50">
                <thead class="bg-slate-950/30">
                    <tr>
                        <th class="px-10 py-8 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Lokasi / Desa</th>
                        <th class="px-10 py-8 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Domain & Platform</th>
                        <th class="px-10 py-8 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Berakhir</th>
                        <th class="px-10 py-8 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Kominfo?</th>
                        <th class="px-10 py-8 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Status</th>
                        <th class="px-10 py-8 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50 bg-slate-900/20">
                    <?php foreach ($websites as $web): ?>
                        <tr class="hover:bg-slate-800/30 transition-colors group website-row" data-id="<?= $web['id'] ?>">
                            <td class="px-10 py-8 whitespace-nowrap align-middle">
                                <div class="text-sm font-black text-slate-200 tracking-tight uppercase"><?= esc($web['desa_kelurahan']) ?></div>
                                <div class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1 opacity-70"><?= esc($web['kecamatan']) ?></div>
                            </td>
                            <td class="px-10 py-8 align-middle">
                                <?php if (!empty($web['domain'])): ?>
                                    <a href="http://<?= esc($web['domain']) ?>" target="_blank" class="text-sm font-bold text-blue-400/90 hover:text-blue-300 no-underline tracking-tight lowercase flex items-center">
                                        <?= esc($web['domain']) ?> <i class="fas fa-external-link-alt ml-2.5 text-[10px] opacity-40"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-700">-</span>
                                <?php endif; ?>
                                <div class="mt-1.5">
                                    <?php
                                    $platform = strtoupper($web['platform_name'] ?? '');
                                    if (empty($platform)): ?>
                                        <span class="text-[9px] text-slate-700 font-black italic tracking-widest">TIDAK TERDAFTAR</span>
                                    <?php else:
                                        $pClass = 'text-slate-600';
                                        if ($platform === 'SIDEKA-NG') $pClass = 'text-blue-500/70';
                                        elseif ($platform === 'OPENSID') $pClass = 'text-cyan-500/70';
                                        elseif ($platform === 'PIHAK KETIGA') $pClass = 'text-amber-500/70';
                                    ?>
                                        <span class="text-[9px] font-black uppercase tracking-[0.2em] <?= $pClass ?>"><?= esc($web['platform_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-10 py-8 whitespace-nowrap align-middle text-sm font-bold text-slate-400/80" id="date-cell-<?= $web['id'] ?>">
                                <?= $web['tanggal_berakhir'] ? date('d-m-Y', strtotime($web['tanggal_berakhir'])) : '-' ?>
                            </td>
                            <td class="px-10 py-8 whitespace-nowrap text-center align-middle">
                                <?php if (strtoupper($web['dikelola_kominfo'] ?? '') === 'YA'): ?>
                                    <i class="fas fa-check-circle text-emerald-500/60 text-lg"></i>
                                <?php else: ?>
                                    <i class="fas fa-times-circle text-slate-800 text-lg"></i>
                                <?php endif; ?>
                            </td>
                            <td class="px-10 py-8 whitespace-nowrap align-middle" id="status-cell-<?= $web['id'] ?>">
                                <?php
                                $status = strtoupper($web['status'] ?? 'NONAKTIF');
                                $badgeBase = 'inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border transition-all';
                                $statusClass = ($status === 'AKTIF') ? 'bg-emerald-500/5 text-emerald-500/80 border-emerald-500/20' : 'bg-rose-500/5 text-rose-500/80 border-rose-500/20';
                                ?>
                                <span class="<?= $badgeBase ?> <?= $statusClass ?>"><?= $status ?></span>
                            </td>
                            <td class="px-10 py-8 whitespace-nowrap text-center align-middle">
                                <a href="<?= site_url('web_desa_kelurahan/edit/' . $web['id']) ?>" class="w-10 h-10 flex items-center justify-center bg-slate-950/50 text-slate-500 border border-slate-800/50 rounded-xl hover:bg-blue-600 hover:text-white hover:border-transparent transition-all no-underline shadow-sm">
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

        const commonDonutOptions = {
            chart: { type: 'donut', height: 220, foreColor: '#94a3b8' },
            stroke: { show: false },
            dataLabels: { enabled: false },
            legend: { show: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '75%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '12px', fontWeight: 900, offsetY: -10 },
                            value: { show: true, fontSize: '20px', fontWeight: 900, offsetY: 10, color: '#f1f5f9' },
                            total: { show: true, label: 'TOTAL', fontSize: '10px', fontWeight: 900, color: '#64748b' }
                        }
                    }
                }
            },
            tooltip: { theme: 'dark' }
        };

        // Status Chart
        new ApexCharts(document.querySelector("#statusChart"), {
            ...commonDonutOptions,
            series: [stats.aktif, stats.nonaktif],
            labels: ['AKTIF', 'NONAKTIF'],
            colors: ['#10b981', '#ef4444']
        }).render();

        // Platform Chart
        const platformColors = ['#6366f1', '#8b5cf6', '#3b82f6', '#0ea5e9', '#06b6d4', '#14b8a6', '#10b981', '#84cc16'];
        const platformSeries = platformStats.map(p => parseInt(p.count));
        const platformLabels = platformStats.map(p => p.nama_platform || 'TIDAK TERDAFTAR');
        const platformBg = platformStats.map((_, i) => platformColors[i % platformColors.length]);

        platformBg.forEach((color, i) => {
            const dot = document.querySelector(`.platform-legend-dot[data-index="${i}"]`);
            if (dot) dot.style.backgroundColor = color;
        });

        new ApexCharts(document.querySelector("#platformChart"), {
            ...commonDonutOptions,
            series: platformSeries,
            labels: platformLabels,
            colors: platformBg
        }).render();
    });

    async function syncExpiration(id) {
        const dateCell = document.getElementById('date-cell-' + id);
        const statusCell = document.getElementById('status-cell-' + id);
        if (dateCell) dateCell.innerHTML = '<i class="fas fa-spinner fa-spin text-blue-500"></i>';

        try {
            const r = await fetch('<?= site_url('web_desa_kelurahan/sync_expiration/') ?>' + id);
            const d = await r.json();
            if (d.status === 'success') {
                dateCell.innerText = d.date;
                if (statusCell && d.web_status) {
                    const badgeClass = (d.web_status === 'AKTIF') ? 'bg-green-500/10 text-green-400 border-green-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20';
                    statusCell.innerHTML = `<span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border shadow-sm ${badgeClass}">${d.web_status}</span>`;
                }
                return true;
            }
        } catch (e) { console.error(e); }
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

        setTimeout(() => location.reload(), 500);
    }
</script>
<?= $this->endSection() ?>
