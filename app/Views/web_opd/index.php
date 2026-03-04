<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Website OPD</h1>

        <div class="flex items-center gap-2 w-full lg:w-auto">
            <a href="<?= site_url('web_opd/export_pdf') ?>" class="flex-1 lg:flex-none btn btn-outline no-underline">
                <i class="fas fa-file-pdf mr-2"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Statistik -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Status Website</h3>
        </div>
        <div class="p-6 flex flex-col md:flex-row items-center gap-8">
            <div class="w-full md:w-1/2 flex justify-center">
                <div id="statusChart" class="w-full max-w-[180px]"></div>
            </div>
            <div class="w-full md:w-1/2 space-y-2">
                <div class="flex justify-between items-center p-2 rounded-lg border border-slate-200 bg-slate-50">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                        <span class="text-[10px] font-bold text-slate-700 uppercase">Aktif</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[9px] font-bold text-slate-400"><?= $stats['total'] > 0 ? round(($stats['aktif'] / $stats['total']) * 100) : 0 ?>%</span>
                        <span class="text-xs font-bold text-slate-800"><?= $stats['aktif'] ?></span>
                    </div>
                </div>
                <div class="flex justify-between items-center p-2 rounded-lg border border-slate-200 bg-slate-50">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                        <span class="text-[10px] font-bold text-slate-700 uppercase">Nonaktif</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[9px] font-bold text-slate-400"><?= $stats['total'] > 0 ? round(($stats['nonaktif'] / $stats['total']) * 100) : 0 ?>%</span>
                        <span class="text-xs font-bold text-slate-800"><?= $stats['nonaktif'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50">
            <form method="GET" action="<?= site_url('web_opd') ?>" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-7">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= esc($search) ?>" class="block w-full pl-9 pr-3 py-2 bg-white border <?= !empty($search) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm transition-all" placeholder="Cari OPD atau domain...">
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Status</label>
                    <select name="status" class="block w-full px-3 py-2 bg-white border <?= !empty($filterStatus) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua Status</option>
                        <option value="AKTIF" <?= ($filterStatus === 'AKTIF') ? 'selected' : '' ?>>AKTIF</option>
                        <option value="NONAKTIF" <?= ($filterStatus === 'NONAKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 btn btn-solid">
                        <i class="fas fa-filter mr-2 text-white/80"></i> Filter
                    </button>
                    <a href="<?= site_url('web_opd') ?>" class="btn btn-outline" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200">OPD</th>
                        <th class="px-6 py-3 border-b border-slate-200">Domain</th>
                        <th class="px-6 py-3 border-b border-slate-200">Status</th>
                        <th class="px-6 py-3 border-b border-slate-200">Keterangan</th>
                        <th class="px-6 py-3 border-b border-slate-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($websites)): ?>
                        <?php foreach ($websites as $web): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-700 shrink-0">
                                            <i class="fas fa-building text-xs"></i>
                                        </div>
                                        <span class="font-medium text-slate-800 uppercase tracking-tight text-xs"><?= esc($web['nama_unit_kerja'] ?? '-') ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if (!empty($web['domain'])): ?>
                                        <a href="http://<?= esc($web['domain']) ?>" target="_blank" class="text-slate-700 hover:underline text-xs font-medium">
                                            <?= esc($web['domain']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-[10px] text-slate-700 italic">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $status = strtoupper($web['status'] ?? 'NONAKTIF');
                                    $colorClass = ($status === 'AKTIF') ? 'bg-emerald-100 text-emerald-800 border-transparent' : 'bg-red-100 text-red-700 border-transparent';
                                    ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border <?= $colorClass ?>">
                                        <?= $status ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-[10px] text-slate-700 font-medium tracking-tight"><?= esc($web['keterangan'] ?: '') ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php if (in_array(session()->get('role'), ['super_admin', 'admin'])): ?>
                                        <a href="<?= site_url('web_opd/edit/' . $web['id']) ?>" class="btn btn-table" title="Edit">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-[10px] font-bold text-slate-700 uppercase italic">Hanya Lihat</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
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
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($websites)): ?>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">
                    <?php
                    $start = ($pager->getCurrentPage() - 1) * $pager->getPerPage() + 1;
                    $end = $start + count($websites) - 1;
                    ?>
                    Menampilkan <span class="text-slate-800"><?= $start ?> - <?= $end ?></span> dari <span class="text-slate-800"><?= number_format($stats['total'] ?? 0, 0, ',', '.') ?></span> website
                </div>
                <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
                    <div class="pagination-container">
                        <?= $pager->links() ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .pagination-container ul {
        @apply flex items-center gap-1;
    }

    .pagination-container li a,
    .pagination-container li span {
        @apply inline-flex items-center justify-center min-w-[28px] h-[28px] rounded bg-white border border-slate-200 text-[10px] font-bold text-slate-700 transition-all hover:border-slate-800 hover:text-slate-800 shadow-sm no-underline px-1.5;
    }

    .pagination-container li.active span {
        @apply bg-slate-800 border-slate-800 text-white shadow-sm;
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const stats = <?= json_encode($stats) ?>;
        new ApexCharts(document.querySelector("#statusChart"), {
            series: [stats.aktif, stats.nonaktif],
            labels: ['AKTIF', 'NONAKTIF'],
            colors: ['#059669', '#dc2626'],
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
        }).render();
    });
</script><?= $this->endSection() ?>