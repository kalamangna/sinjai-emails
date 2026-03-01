<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-tight">Website Desa & Kelurahan</h1>

        <div class="flex items-center gap-2 w-full lg:w-auto">
            <a href="<?= site_url('web_desa_kelurahan/export_pdf') ?>" class="flex-1 lg:flex-none inline-flex items-center justify-center px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 border-transparent rounded-lg font-bold text-xs uppercase tracking-widest transition-all shadow-sm no-underline">
                <i class="fas fa-file-pdf mr-2 text-red-600"></i> Unduh PDF
            </a>
            <?php if (session()->get('role') === 'super_admin'): ?>
                <button type="button" class="flex-1 lg:flex-none inline-flex items-center justify-center px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg font-bold text-xs uppercase tracking-widest transition-all shadow-sm focus:outline-none" id="batchSyncBtn" onclick="startBatchSync()">
                    <i class="fas fa-sync mr-2 text-white/80"></i> Sync Expiration
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Progress Sinkronisasi -->
    <div id="syncProgressContainer" class="hidden bg-gray-800 rounded-lg p-6 shadow-sm overflow-hidden relative">
        <div class="relative z-10 space-y-4">
            <div class="flex justify-between items-end">
                <div>
                    <p class="text-[10px] font-bold text-gray-700 uppercase tracking-widest mb-1">Sinkronisasi RDAP</p>
                    <h4 class="text-sm font-bold text-white uppercase tracking-tight">Memproses Data...</h4>
                </div>
                <div class="text-right">
                    <span id="syncStatusCount" class="text-2xl font-bold text-white leading-none">0/0</span>
                </div>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-2">
                <div id="syncProgressBar" class="bg-white h-full rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <!-- Statistik -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Status -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-xs font-bold text-gray-800 uppercase tracking-tight">Status Website</h3>
            </div>
            <div class="p-6 flex flex-col md:flex-row items-center gap-8">
                <div class="w-full md:w-1/2 flex justify-center">
                    <div id="statusChart" class="w-full max-w-[180px]"></div>
                </div>
                <div class="w-full md:w-1/2 space-y-2">
                    <div class="flex justify-between items-center p-2 rounded-lg border border-gray-200 bg-gray-50">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                            <span class="text-[10px] font-bold text-gray-700 uppercase">Aktif</span>
                        </div>
                        <span class="text-xs font-bold text-gray-800"><?= $stats['aktif'] ?></span>
                    </div>
                    <div class="flex justify-between items-center p-2 rounded-lg border border-gray-200 bg-gray-50">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-gray-100"></span>
                            <span class="text-[10px] font-bold text-gray-700 uppercase">Nonaktif</span>
                        </div>
                        <span class="text-xs font-bold text-gray-800"><?= $stats['nonaktif'] ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Teknologi -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="text-xs font-bold text-gray-800 uppercase tracking-tight">Distribusi Platform</h3>
            </div>
            <div class="p-6 flex flex-col md:flex-row items-center gap-8">
                <div class="w-full md:w-1/2 flex justify-center">
                    <div id="platformChart" class="w-full max-w-[180px]"></div>
                </div>
                <div class="w-full md:w-1/2 space-y-1.5 max-h-[150px] overflow-y-auto custom-scrollbar pr-2">
                    <?php foreach ($platform_stats as $index => $ps): ?>
                        <div class="flex justify-between items-center p-1.5 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="flex items-center truncate">
                                <span class="w-2 h-2 rounded-full mr-2 platform-legend-dot shrink-0" data-index="<?= $index ?>"></span>
                                <span class="text-[10px] font-bold text-gray-700 uppercase tracking-tight truncate"><?= esc($ps['nama_platform'] ?: 'Lainnya') ?></span>
                            </div>
                            <span class="text-[10px] font-bold text-gray-800 bg-gray-100 px-1.5 py-0.5 rounded"><?= $ps['count'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50">
            <form method="GET" action="<?= site_url('web_desa_kelurahan') ?>" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-700">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= esc($search) ?>" class="block w-full pl-9 pr-3 py-2 bg-white border <?= !empty($search) ? 'border-gray-800 ring-1 ring-gray-800' : 'border-gray-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm transition-all" placeholder="Cari desa atau kelurahan...">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Tipe</label>
                    <select name="type" class="block w-full px-3 py-2 bg-white border <?= !empty($filterType) ? 'border-gray-800 ring-1 ring-gray-800' : 'border-gray-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua Tipe</option>
                        <option value="DESA" <?= ($filterType === 'DESA') ? 'selected' : '' ?>>DESA</option>
                        <option value="KELURAHAN" <?= ($filterType === 'KELURAHAN') ? 'selected' : '' ?>>KELURAHAN</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Platform</label>
                    <select name="filter_platform" class="block w-full px-3 py-2 bg-white border <?= !empty($filterPlatform) ? 'border-gray-800 ring-1 ring-gray-800' : 'border-gray-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua Platform</option>
                        <option value="NULL" <?= ($filterPlatform === 'NULL') ? 'selected' : '' ?>>Tanpa Platform</option>
                        <?php foreach ($platforms as $p): ?>
                            <option value="<?= esc($p['nama_platform']) ?>" <?= ($filterPlatform === $p['nama_platform']) ? 'selected' : '' ?>><?= esc($p['nama_platform']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Status</label>
                    <select name="status" class="block w-full px-3 py-2 bg-white border <?= !empty($filterStatus) ? 'border-gray-800 ring-1 ring-gray-800' : 'border-gray-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua Status</option>
                        <option value="AKTIF" <?= ($filterStatus === 'AKTIF') ? 'selected' : '' ?>>AKTIF</option>
                        <option value="NONAKTIF" <?= ($filterStatus === 'NONAKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg font-bold text-xs uppercase tracking-widest transition-all">
                        <i class="fas fa-filter mr-2 text-white/80"></i> Filter
                    </button>
                    <a href="<?= site_url('web_desa_kelurahan') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 border-transparent rounded-lg transition-all shadow-sm" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-100 text-gray-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-gray-200">Desa / Kelurahan</th>
                        <th class="px-6 py-3 border-b border-gray-200">Domain / Platform</th>
                        <th class="px-6 py-3 border-b border-gray-200">Tanggal Berakhir</th>
                        <th class="px-6 py-3 border-b border-gray-200">Status</th>
                        <th class="px-6 py-3 border-b border-gray-200">Dikelola Kominfo</th>
                        <th class="px-6 py-3 border-b border-gray-200">Keterangan</th>
                        <th class="px-6 py-3 border-b border-gray-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (!empty($websites)): ?>
                        <?php foreach ($websites as $web): ?>
                            <tr class="hover:bg-gray-50 transition-colors website-row" data-id="<?= $web['id'] ?>">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-800 uppercase tracking-tight text-xs"><?= esc($web['desa_kelurahan']) ?></span>
                                    <span class="text-[10px] text-gray-700 uppercase font-bold tracking-widest mt-0.5"><?= esc($web['kecamatan']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (!empty($web['domain'])): ?>
                                    <a href="http://<?= esc($web['domain']) ?>" target="_blank" class="text-emerald-700 hover:underline text-xs font-medium block">
                                        <?= esc($web['domain']) ?>
                                    </a>
                                <?php endif; ?>
                                <span class="text-[9px] font-bold text-gray-700 uppercase tracking-tight"><?= esc($web['platform_name'] ?: 'Lainnya') ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" id="date-cell-<?= $web['id'] ?>">
                                <span class="text-xs font-medium text-gray-700">
                                    <?php if (stripos($web['desa_kelurahan'], 'KELURAHAN') !== false): ?>
                                        <?= formatTanggal('2027-02-01') ?>
                                    <?php else: ?>
                                        <?= formatTanggal($web['tanggal_berakhir']) ?>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" id="status-cell-<?= $web['id'] ?>">
                                <?php
                                $status = strtoupper($web['status'] ?? 'NONAKTIF');
                                $colorClass = ($status === 'AKTIF') ? 'bg-emerald-100 text-emerald-800 border-transparent' : 'bg-red-100 text-red-700 border-transparent';
                                ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border <?= $colorClass ?>">
                                    <?= $status ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($web['dikelola_kominfo'] === 'YA'): ?>
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-blue-700 border-transparent" title="Dikelola Kominfo">
                                        <i class="fas fa-check text-[10px]"></i>
                                    </span>
                                <?php else: ?>
                                    <span class="text-[10px] text-gray-700 font-bold tracking-widest">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-[10px] text-gray-700 font-medium tracking-tight"><?= esc($web['keterangan'] ?: '') ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php if (session()->get('role') === 'super_admin'): ?>
                                    <a href="<?= site_url('web_desa_kelurahan/edit/' . $web['id']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300 border-transparent shadow-sm transition-all" title="Edit">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-[10px] font-bold text-gray-700 uppercase italic">Hanya Lihat</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="px-6 py-20 text-center">
                                                    <div class="flex flex-col items-center justify-center">
                                                        <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center mb-3">
                                                            <i class="fas fa-search text-gray-300 text-lg"></i>
                                                        </div>
                                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest italic">Data tidak ditemukan</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>            </table>
        </div>

        <?php if (isset($pager)): ?>
        <div class="px-6 py-4 border-t border-gray-100">
            <?= $pager->links() ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const stats = <?= json_encode($stats) ?>;
        const platformStats = <?= json_encode($platform_stats) ?>;

        const commonOptions = {
            chart: {
                type: 'donut',
                height: 180,
                fontFamily: 'Inter, sans-serif'
            },
            stroke: {
                width: 2,
                colors: ['#ffffff']
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
                                color: '#9ca3af',
                                offsetY: -5
                            },
                            value: {
                                show: true,
                                fontSize: '16px',
                                fontWeight: 700,
                                color: '#1e293b',
                                offsetY: 5,
                                formatter: function(val) {
                                    return val
                                }
                            },
                            total: {
                                show: true,
                                label: 'TOTAL',
                                fontSize: '10px',
                                fontWeight: 700,
                                color: '#9ca3af',
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                }
                            }
                        }
                    }
                }
            }
        };

        new ApexCharts(document.querySelector("#statusChart"), {
            ...commonOptions,
            series: [stats.aktif, stats.nonaktif],
            labels: ['AKTIF', 'NONAKTIF'],
            colors: ['#047857', '#9ca3af']
        }).render();

        const pColors = ['#10b981', '#047857', '#6ee7b7', '#dc2626', '#1e293b', '#9ca3af', '#f1f5f9', '#f8fafc'];
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
        if (dateCell) dateCell.innerHTML = '<i class="fas fa-spinner fa-spin text-gray-700 text-[10px]"></i>';
        try {
            const r = await fetch('<?= site_url('web_desa_kelurahan/sync_expiration/') ?>' + id);
            const d = await r.json();
            if (d.status === 'success') {
                dateCell.innerHTML = `<span class="text-xs font-medium text-gray-700">${d.date}</span>`;
                if (statusCell && d.web_status) {
                    const status = d.web_status.toUpperCase();
                    const colorClass = (status === 'AKTIF') ? 'bg-emerald-100 text-emerald-800 border-transparent' : 'bg-red-100 text-red-700 border-transparent';
                    statusCell.innerHTML = `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold border ${colorClass}">${status}</span>`;
                }
                return true;
            }
        } catch (e) {
            console.error(e);
        }
        if (dateCell) dateCell.innerHTML = '<span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Gagal</span>';
        return false;
    }

    async function startBatchSync() {
        if (!confirm('Sinkronkan data sekarang?')) return;
        const btn = document.getElementById('batchSyncBtn');
        const container = document.getElementById('syncProgressContainer');
        const bar = document.getElementById('syncProgressBar');
        const count = document.getElementById('syncStatusCount');

        btn.disabled = true;
        container.classList.remove('hidden');
        container.scrollIntoView({ behavior: 'smooth', block: 'center' });

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