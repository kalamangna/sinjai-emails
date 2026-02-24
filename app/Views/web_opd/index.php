<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="space-y-1">
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Website OPD</h2>
            <p class="text-slate-500 text-xs font-medium uppercase tracking-wider">Status Website Perangkat Daerah</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?= site_url('web_opd/export_pdf') ?>" class="inline-flex items-center justify-center px-3 py-2 bg-rose-600 border border-transparent rounded-lg font-bold text-[10px] text-white uppercase tracking-wider hover:bg-rose-700 active:bg-rose-800 transition-all shadow-sm no-underline">
                <i class="fas fa-file-pdf mr-1.5"></i> PDF
            </a>
        </div>
    </div>

    <!-- Chart Overview -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden max-w-2xl mx-auto">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
            <h6 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest flex items-center">
                <i class="fas fa-chart-pie mr-2 text-blue-500"></i>Status
            </h6>
        </div>
        <div class="p-6 flex flex-col sm:flex-row items-center justify-around gap-8">
            <div id="statusChart" class="w-full max-w-[200px]"></div>
            <div class="space-y-3 w-full max-w-[200px]">
                <div class="flex justify-between items-center p-2 rounded-lg bg-slate-50 border border-slate-100">
                    <div class="flex items-center">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 shadow-sm"></span>
                        <span class="text-[10px] font-bold text-slate-600 uppercase">Aktif</span>
                    </div>
                    <span class="text-[11px] font-extrabold text-slate-900"><?= $stats['aktif'] ?></span>
                </div>
                <div class="flex justify-between items-center p-2 rounded-lg bg-slate-50 border border-slate-100">
                    <div class="flex items-center">
                        <span class="w-2 h-2 rounded-full bg-rose-500 mr-2 shadow-sm"></span>
                        <span class="text-[10px] font-bold text-slate-600 uppercase">Nonaktif</span>
                    </div>
                    <span class="text-[11px] font-extrabold text-slate-900"><?= $stats['nonaktif'] ?></span>
                </div>
            </div>
        </div>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-5 py-3 rounded-lg flex items-center shadow-sm">
            <i class="fas fa-check-circle mr-3 text-emerald-500"></i>
            <span class="font-bold text-xs uppercase tracking-wider"><?= session()->getFlashdata('message') ?></span>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <form method="GET" action="<?= site_url('web_opd') ?>" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-7">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Pencarian</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" name="search" value="<?= esc($search) ?>" class="block w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all" placeholder="Nama OPD atau Domain...">
                </div>
            </div>
            <div class="md:col-span-2">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Status</label>
                <select name="status" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                    <option value="">Semua</option>
                    <option value="AKTIF" <?= ($filterStatus === 'AKTIF') ? 'selected' : '' ?>>AKTIF</option>
                    <option value="NONAKTIF" <?= ($filterStatus === 'NONAKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                </select>
            </div>
            <div class="md:col-span-3 flex gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-slate-800 border border-transparent rounded-lg font-bold text-[11px] text-white uppercase tracking-wider hover:bg-slate-900 active:bg-slate-950 transition-all shadow-sm focus:outline-none">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                <a href="<?= site_url('web_opd') ?>" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-lg font-bold text-[11px] text-slate-600 uppercase tracking-wider hover:bg-slate-50 hover:text-slate-900 active:bg-slate-100 transition-all shadow-sm no-underline" title="Reset Filter">
                    <i class="fas fa-redo mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Perangkat Daerah</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Domain</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Keterangan</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php foreach ($websites as $web): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="text-[13px] font-bold text-slate-900 leading-tight uppercase"><?= esc($web['nama_unit_kerja'] ?? '-') ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (!empty($web['domain'])): ?>
                                    <a href="http://<?= esc($web['domain']) ?>" target="_blank" class="text-sm font-bold text-blue-600 hover:text-blue-700 no-underline lowercase flex items-center group/link">
                                        <?= esc($web['domain']) ?> <i class="fas fa-external-link-alt ml-1.5 text-[10px] opacity-0 group-hover/link:opacity-40 transition-opacity"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-300 italic text-xs">Belum ada domain</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $status = strtoupper($web['status'] ?? 'NONAKTIF');
                                $stCls = ($status === 'AKTIF') ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100';
                                ?>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold border <?= $stCls ?>"><?= $status ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-[10px] font-medium text-slate-500 line-clamp-2 max-w-[200px]" title="<?= esc($web['keterangan']) ?>">
                                    <?= esc($web['keterangan']) ?: '-' ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="<?= site_url('web_opd/edit/' . $web['id']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 transition-all shadow-sm no-underline">
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
        new ApexCharts(document.querySelector("#statusChart"), {
            series: [stats.aktif, stats.nonaktif],
            labels: ['AKTIF', 'NONAKTIF'],
            colors: ['#10b981', '#f43f5e'],
            chart: {
                type: 'donut',
                height: 200,
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
                                fontSize: '11px',
                                fontWeight: 700,
                                offsetY: -5
                            },
                            value: {
                                show: true,
                                fontSize: '18px',
                                fontWeight: 800,
                                offsetY: 5,
                                color: '#1e293b'
                            },
                            total: {
                                show: true,
                                label: 'TOTAL',
                                fontSize: '9px',
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
        }).render();
    });

    function preparePdfExport() {
        return true;
    }
</script>
<?= $this->endSection() ?>