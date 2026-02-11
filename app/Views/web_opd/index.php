<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-10">
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <h2 class="text-3xl font-black text-slate-100 uppercase tracking-tighter">Monitoring Website OPD</h2>
        <div class="flex flex-wrap gap-3">
            <form id="pdfExportForm" action="<?= site_url('web_opd/export_pdf') . '?' . ($_SERVER['QUERY_STRING'] ?? '') ?>" method="POST" target="_blank" onsubmit="return preparePdfExport();" class="inline">
                <input type="hidden" name="statusChartData" id="statusChartData">
                <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-red-600 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-red-700 transition-all shadow-lg shadow-red-900/20">
                    <i class="fas fa-file-pdf mr-2"></i> Ekspor PDF
                </button>
            </form>
            <a href="<?= site_url('web_opd/create') ?>" class="inline-flex items-center px-5 py-2.5 bg-blue-600 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-900/20 no-underline">
                <i class="fas fa-plus mr-2"></i> Tambah Data
            </a>
        </div>
    </div>

    <!-- Dashboard Charts -->
    <div class="flex justify-center">
        <div class="w-full max-w-2xl bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
            <div class="bg-slate-800/30 px-8 py-5 border-b border-slate-800">
                <h6 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                    <i class="fas fa-chart-pie mr-3 text-blue-500 opacity-50"></i>Status Keaktifan Website
                </h6>
            </div>
            <div class="p-10 flex flex-col sm:flex-row items-center gap-10">
                <div class="w-full sm:w-1/2 min-h-[220px] relative">
                    <div id="statusChart"></div>
                </div>
                <div class="w-full sm:w-1/2 space-y-3">
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

    <?php if (session()->getFlashdata('message')): ?>
        <div class="bg-green-500/10 border border-green-500/20 text-green-400 px-6 py-4 rounded-2xl flex items-center shadow-2xl" role="alert">
            <i class="fas fa-check-circle mr-4 text-xl"></i>
            <span class="font-bold text-sm uppercase tracking-widest"><?= session()->getFlashdata('message') ?></span>
        </div>
    <?php endif; ?>

    <!-- Filter Section -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden p-8">
        <form method="GET" action="<?= site_url('web_opd') ?>" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
            <div class="md:col-span-7">
                <label class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Kata Kunci</label>
                <input type="text" name="search" value="<?= esc($search) ?>" class="block w-full px-5 py-3 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all uppercase placeholder-slate-800" placeholder="OPD ATAU DOMAIN...">
            </div>
            <div class="md:col-span-2">
                <label class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Status</label>
                <select name="status" class="block w-full px-5 py-3 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all">
                    <option value="">SEMUA</option>
                    <option value="AKTIF" <?= ($filterStatus === 'AKTIF') ? 'selected' : '' ?>>AKTIF</option>
                    <option value="NONAKTIF" <?= ($filterStatus === 'NONAKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                </select>
            </div>
            <div class="md:col-span-3 flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-black py-3 rounded-2xl shadow-xl shadow-blue-900/20 transition-all text-[10px] uppercase tracking-widest flex items-center justify-center">
                    <i class="fas fa-filter mr-2 text-xs"></i> Filter
                </button>
                <a href="<?= site_url('web_opd') ?>" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 font-black border border-transparent rounded-2xl shadow-sm transition-all text-[10px] uppercase tracking-widest no-underline flex items-center justify-center">
                    <i class="fas fa-sync-alt mr-2 text-xs"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800">
                <thead class="bg-slate-950/30">
                    <tr>
                        <th class="px-8 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Perangkat Daerah (OPD)</th>
                        <th class="px-8 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Domain Website</th>
                        <th class="px-8 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Status</th>
                        <th class="px-8 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Keterangan</th>
                        <th class="px-8 py-6 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 bg-slate-900/30">
                    <?php foreach ($websites as $web): ?>
                        <tr class="hover:bg-slate-800/30 transition-colors group">
                            <td class="px-8 py-6 align-middle">
                                <div class="text-sm font-black text-slate-200 tracking-tight uppercase leading-snug"><?= esc($web['nama_unit_kerja'] ?? '') ?: '-' ?></div>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap align-middle">
                                <?php if (!empty($web['domain'])): ?>
                                    <a href="http://<?= esc($web['domain']) ?>" target="_blank" class="text-sm font-bold text-blue-400 hover:text-blue-300 no-underline tracking-tight lowercase flex items-center">
                                        <?= esc($web['domain']) ?> <i class="fas fa-external-link-alt ml-2 text-[10px] opacity-50"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-700">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap align-middle">
                                <?php
                                $status = strtoupper($web['status'] ?? 'NONAKTIF');
                                $statusClass = ($status === 'AKTIF') ? 'bg-emerald-500/10 text-emerald-500/80 border-emerald-500/20' : 'bg-rose-500/10 text-rose-500/80 border-rose-500/20';
                                ?>
                                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border shadow-sm <?= $statusClass ?>"><?= $status ?></span>
                            </td>
                            <td class="px-8 py-6 text-sm text-slate-500 leading-relaxed italic"><?= esc($web['keterangan'] ?? '') ?: '-' ?></td>
                            <td class="px-8 py-6 whitespace-nowrap text-center align-middle">
                                <a href="<?= site_url('web_opd/edit/' . $web['id']) ?>" class="w-10 h-10 flex items-center justify-center bg-slate-950 text-slate-400 border border-slate-800 rounded-xl hover:bg-blue-600 hover:text-white hover:border-transparent transition-all no-underline shadow-sm">
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

        const options = {
            series: [stats.aktif, stats.nonaktif],
            labels: ['AKTIF', 'NONAKTIF'],
            colors: ['#10b981', '#ef4444'],
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

        new ApexCharts(document.querySelector("#statusChart"), options).render();
    });

    function preparePdfExport() {
        // Preparation for PDF export if needed
        return true;
    }
</script>
<?= $this->endSection() ?>
