<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-5">
            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200">
                <i class="fas fa-landmark text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Web Desa</h1>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1 flex items-center">
                    <span class="w-2 h-2 bg-indigo-500 rounded-full mr-2 animate-pulse"></span> Status Website Desa
                </p>
            </div>
        </div>
        
        <div class="flex items-center gap-3 w-full lg:w-auto">
            <a href="<?= site_url('web_desa_kelurahan/export_pdf') ?>" class="flex-1 lg:flex-none inline-flex items-center justify-center px-6 py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-xs uppercase tracking-widest transition-all shadow-md no-underline group">
                <i class="fas fa-file-pdf mr-2"></i> PDF
            </a>
            <?php if (session()->get('role') === 'super_admin'): ?>
            <button type="button" class="flex-1 lg:flex-none inline-flex items-center justify-center px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs uppercase tracking-widest transition-all shadow-md shadow-amber-100 focus:outline-none group" id="batchSyncBtn" onclick="startBatchSync()">
                <i class="fas fa-sync mr-2"></i> Sync
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sync Progress -->
    <div id="syncProgressContainer" class="hidden bg-indigo-900 rounded-2xl p-6 shadow-xl overflow-hidden relative">
        <div class="absolute top-0 right-0 -mr-10 -mt-10 w-40 h-40 bg-white/5 rounded-full blur-2xl"></div>
        <div class="relative z-10 space-y-4">
            <div class="flex justify-between items-end">
                <div>
                    <p class="text-[10px] font-black text-indigo-300 uppercase tracking-widest mb-1">Sinkronisasi</p>
                    <h4 class="text-sm font-bold text-white uppercase tracking-tight">Sync RDAP...</h4>
                </div>
                <div class="text-right">
                    <span id="syncStatusCount" class="text-2xl font-black text-white leading-none">0/0</span>
                </div>
            </div>
            <div class="w-full bg-indigo-950 rounded-full h-3 p-0.5 border border-white/5 shadow-inner">
                <div id="syncProgressBar" class="bg-gradient-to-r from-emerald-500 to-indigo-400 h-full rounded-full transition-all duration-300 relative" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-tight">Status Operasional</h3>
                </div>
                <div class="w-8 h-8 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-pie text-xs"></i>
                </div>
            </div>
            <div class="p-8 flex flex-col md:flex-row items-center gap-10">
                <div class="w-full md:w-1/2">
                    <div id="statusChart" class="min-h-[200px]"></div>
                </div>
                <div class="w-full md:w-1/2 space-y-4">
                    <div class="flex justify-between items-center p-3 rounded-xl bg-emerald-50 border border-emerald-100">
                        <div class="flex items-center gap-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm"></span>
                            <span class="text-xs font-bold text-emerald-900 uppercase">Aktif</span>
                        </div>
                        <span class="text-xs font-black text-emerald-600"><?= $stats['aktif'] ?></span>
                    </div>
                    <div class="flex justify-between items-center p-3 rounded-xl bg-rose-50 border border-rose-100">
                        <div class="flex items-center gap-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-sm"></span>
                            <span class="text-xs font-bold text-rose-900 uppercase">Nonaktif</span>
                        </div>
                        <span class="text-xs font-black text-rose-600"><?= $stats['nonaktif'] ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-tight">Teknologi</h3>
                </div>
                <div class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-microchip text-xs"></i>
                </div>
            </div>
            <div class="p-8 flex flex-col md:flex-row items-center gap-10">
                <div class="w-full md:w-1/2">
                    <div id="platformChart" class="min-h-[220px]"></div>
                </div>
                <div class="w-full md:w-1/2 space-y-2 max-h-[180px] overflow-y-auto custom-scrollbar pr-4">
                    <?php foreach ($platform_stats as $index => $ps): ?>
                        <div class="flex justify-between items-center p-2 rounded-lg hover:bg-slate-50 group">
                            <div class="flex items-center truncate">
                                <span class="w-2.5 h-2.5 rounded-full mr-3 platform-legend-dot shrink-0" data-index="<?= $index ?>"></span>
                                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-tight truncate"><?= esc($ps['nama_platform'] ?: 'Lainnya') ?></span>
                            </div>
                            <span class="text-[10px] font-black text-slate-900 bg-slate-100 px-2 py-0.5 rounded"><?= $ps['count'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="p-8 border-b border-slate-100 bg-slate-50/50">
            <form method="GET" action="<?= site_url('web_desa_kelurahan') ?>" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                <div class="md:col-span-4 lg:col-span-4">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2.5 ml-1">Nama</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                            <i class="fas fa-search text-sm"></i>
                        </span>
                        <input type="text" name="search" value="<?= esc($search) ?>" class="block w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 text-sm font-semibold text-slate-700 transition-all placeholder:text-slate-400 shadow-inner" placeholder="Cari...">
                    </div>
                </div>

                <div class="md:col-span-2 lg:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2.5 ml-1">Tipe</label>
                    <select name="type" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-semibold text-slate-700 transition-all appearance-none cursor-pointer">
                        <option value="">Semua</option>
                        <option value="DESA" <?= ($filterType === 'DESA') ? 'selected' : '' ?>>DESA</option>
                        <option value="KELURAHAN" <?= ($filterType === 'KELURAHAN') ? 'selected' : '' ?>>KELURAHAN</option>
                    </select>
                </div>

                <div class="md:col-span-2 lg:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2.5 ml-1">Platform</label>
                    <select name="filter_platform" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-semibold text-slate-700 transition-all appearance-none cursor-pointer">
                        <option value="">Semua</option>
                        <option value="NULL" <?= ($filterPlatform === 'NULL') ? 'selected' : '' ?>>Tanpa Platform</option>
                        <?php foreach ($platforms as $p): ?>
                            <option value="<?= esc($p['nama_platform']) ?>" <?= ($filterPlatform === $p['nama_platform']) ? 'selected' : '' ?>><?= esc($p['nama_platform']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2 lg:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2.5 ml-1">Status</label>
                    <select name="status" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-semibold text-slate-700 transition-all appearance-none cursor-pointer">
                        <option value="">Semua</option>
                        <option value="AKTIF" <?= ($filterStatus === 'AKTIF') ? 'selected' : '' ?>>AKTIF</option>
                        <option value="NONAKTIF" <?= ($filterStatus === 'NONAKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                    </select>
                </div>

                <div class="md:col-span-2 lg:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center h-[46px] bg-slate-900 hover:bg-indigo-600 text-white rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="<?= site_url('web_desa_kelurahan') ?>" class="inline-flex items-center justify-center w-[46px] h-[46px] bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 rounded-xl transition-all" title="Reset">
                        <i class="fas fa-redo text-xs"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-50/80">
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Desa / Kelurahan</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Domain</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest w-40">Berakhir</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest w-32">Status</th>
                        <th class="px-8 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 bg-white">
                    <?php foreach ($websites as $web): ?>
                        <tr class="hover:bg-indigo-50/30 transition-all group website-row" data-id="<?= $web['id'] ?>">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 group-hover:bg-white border border-slate-200 flex items-center justify-center transition-colors">
                                        <i class="fas fa-landmark text-slate-400 group-hover:text-indigo-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="text-[12px] font-bold text-slate-900 leading-tight uppercase group-hover:text-indigo-700 transition-colors"><?= esc($web['desa_kelurahan']) ?></div>
                                        <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mt-1"><?= esc($web['kecamatan']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <?php if (!empty($web['domain'])): ?>
                                    <a href="http://<?= esc($web['domain']) ?>" target="_blank" class="inline-flex items-center text-sm font-bold text-indigo-600 hover:text-indigo-800 transition-colors lowercase group/link mb-1">
                                        <?= esc($web['domain']) ?>
                                    </a>
                                <?php endif; ?>
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest"><?= esc($web['platform_name'] ?: 'Lainnya') ?></span>
                                </div>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap" id="date-cell-<?= $web['id'] ?>">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-700">
                                        <?php if (stripos($web['desa_kelurahan'], 'KELURAHAN') !== false): ?>
                                            01 Feb 2027
                                        <?php else: ?>
                                            <?= $web['tanggal_berakhir'] ? date('d M Y', strtotime($web['tanggal_berakhir'])) : '-' ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap" id="status-cell-<?= $web['id'] ?>">
                                <?php
                                $status = strtoupper($web['status'] ?? 'NONAKTIF');
                                $type = ($status === 'AKTIF') ? 'success' : 'danger';
                                echo view('components/badge', ['label' => $status, 'type' => $type, 'rounded' => true]);
                                ?>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap text-center">
                                <?php if (session()->get('role') === 'super_admin'): ?>
                                <a href="<?= site_url('web_desa_kelurahan/edit/' . $web['id']) ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 transition-all no-underline shadow-sm" title="Edit">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                                <?php else: ?>
                                    <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest italic">View Only</span>
                                <?php endif; ?>
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
                height: 220,
                fontFamily: 'Plus Jakarta Sans, sans-serif'
            },
            stroke: { width: 3, colors: ['#fff'] },
            dataLabels: { enabled: false },
            legend: { show: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '80%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '11px', fontWeight: 700, offsetY: -10, color: '#94a3b8' },
                            value: { show: true, fontSize: '20px', fontWeight: 800, offsetY: 10, color: '#1e293b' },
                            total: { show: true, label: 'TOTAL', fontSize: '9px', fontWeight: 800, color: '#64748b' }
                        }
                    }
                }
            }
        };

        new ApexCharts(document.querySelector("#statusChart"), {
            ...commonOptions,
            series: [stats.aktif, stats.nonaktif],
            labels: ['AKTIF', 'NONAKTIF'],
            colors: ['#10b981', '#f43f5e']
        }).render();

        const pColors = ['#6366f1', '#3b82f6', '#06b6d4', '#10b981', '#f59e0b', '#f43f5e', '#8b5cf6', '#d946ef'];
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
        if (dateCell) dateCell.innerHTML = '<div class="flex items-center gap-2"><i class="fas fa-spinner fa-spin text-indigo-500 text-[10px]"></i></div>';
        try {
            const r = await fetch('<?= site_url('web_desa_kelurahan/sync_expiration/') ?>' + id);
            const d = await r.json();
            if (d.status === 'success') {
                dateCell.innerHTML = `<span class="text-xs font-bold text-emerald-600">${d.date}</span>`;
                if (statusCell && d.web_status) {
                    const type = (d.web_status === 'AKTIF') ? 'success' : 'danger';
                    statusCell.innerHTML = `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider border bg-${type === 'success' ? 'emerald' : 'rose'}-100 text-${type === 'success' ? 'emerald' : 'rose'}-700 border-${type === 'success' ? 'emerald' : 'rose'}-200">${d.web_status}</span>`;
                }
                return true;
            }
        } catch (e) {
            console.error(e);
        }
        if (dateCell) dateCell.innerHTML = '<span class="text-[10px] font-bold text-rose-500 uppercase tracking-widest">Error</span>';
        return false;
    }

    async function startBatchSync() {
        if (!confirm('Sinkronkan data?')) return;
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
        setTimeout(() => location.reload(), 1500);
    }
</script>
<?= $this->endSection() ?>
