<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-5">
            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200">
                <i class="fas fa-globe text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Web OPD</h1>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1 flex items-center">
                    <span class="w-2 h-2 bg-indigo-500 rounded-full mr-2 animate-pulse"></span> Status Website OPD
                </p>
            </div>
        </div>
        
        <div class="flex items-center gap-3 w-full lg:w-auto">
            <a href="<?= site_url('web_opd/export_pdf') ?>" class="flex-1 lg:flex-none inline-flex items-center justify-center px-6 py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-xs uppercase tracking-widest transition-all shadow-md no-underline group">
                <i class="fas fa-file-pdf mr-2"></i> PDF
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1 flex flex-col gap-6">
            <?= view('components/card_metric', [
                'label' => 'Aktif',
                'value' => $stats['aktif'],
                'icon'  => 'fas fa-check-circle',
                'color' => 'emerald',
                'trend' => round(($stats['aktif'] / max($stats['total'], 1)) * 100, 1) . '%'
            ]) ?>
            <?= view('components/card_metric', [
                'label' => 'Nonaktif',
                'value' => $stats['nonaktif'],
                'icon'  => 'fas fa-times-circle',
                'color' => 'rose',
                'trend' => round(($stats['nonaktif'] / max($stats['total'], 1)) * 100, 1) . '%'
            ]) ?>
        </div>

        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-tight">Status Web</h3>
                </div>
                <div class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-donut text-xs"></i>
                </div>
            </div>
            <div class="p-8 flex flex-col md:flex-row items-center gap-10">
                <div class="w-full md:w-1/2">
                    <div id="statusChart" class="min-h-[220px]"></div>
                </div>
                <div class="w-full md:w-1/2 space-y-4">
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-between group hover:bg-emerald-600 transition-all duration-300">
                        <div class="flex items-center gap-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 group-hover:bg-white shadow-sm"></span>
                            <span class="text-xs font-bold text-emerald-900 group-hover:text-white uppercase">Aktif</span>
                        </div>
                        <span class="text-xs font-black text-emerald-600 group-hover:text-white"><?= $stats['aktif'] ?></span>
                    </div>
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-between group hover:bg-rose-600 transition-all duration-300">
                        <div class="flex items-center gap-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 group-hover:bg-white shadow-sm"></span>
                            <span class="text-xs font-bold text-rose-900 group-hover:text-white uppercase">Nonaktif</span>
                        </div>
                        <span class="text-xs font-black text-rose-600 group-hover:text-white"><?= $stats['nonaktif'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="p-8 border-b border-slate-100 bg-slate-50/50">
            <form method="GET" action="<?= site_url('web_opd') ?>" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                <div class="md:col-span-7 lg:col-span-8">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2.5 ml-1">Cari</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                            <i class="fas fa-search text-sm"></i>
                        </span>
                        <input type="text" name="search" value="<?= esc($search) ?>" class="block w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 text-sm font-semibold text-slate-700 transition-all placeholder:text-slate-400 shadow-inner" placeholder="Nama OPD atau Domain...">
                    </div>
                </div>

                <div class="md:col-span-3 lg:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2.5 ml-1">Status</label>
                    <select name="status" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 text-sm font-semibold text-slate-700 transition-all appearance-none cursor-pointer">
                        <option value="">Semua</option>
                        <option value="AKTIF" <?= ($filterStatus === 'AKTIF') ? 'selected' : '' ?>>AKTIF</option>
                        <option value="NONAKTIF" <?= ($filterStatus === 'NONAKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                    </select>
                </div>

                <div class="md:col-span-2 lg:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center h-[46px] bg-slate-900 hover:bg-indigo-600 text-white rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="<?= site_url('web_opd') ?>" class="inline-flex items-center justify-center w-[46px] h-[46px] bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 rounded-xl transition-all" title="Reset">
                        <i class="fas fa-redo text-xs"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-50/80">
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">OPD</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Domain</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 bg-white">
                    <?php foreach ($websites as $web): ?>
                        <tr class="hover:bg-indigo-50/30 transition-all group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 group-hover:bg-white border border-slate-200 flex items-center justify-center transition-colors">
                                        <i class="fas fa-building text-slate-400 group-hover:text-indigo-600 text-sm"></i>
                                    </div>
                                    <div class="text-[12px] font-bold text-slate-900 leading-tight uppercase group-hover:text-indigo-700 transition-colors">
                                        <?= esc($web['nama_unit_kerja'] ?? '-') ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <?php if (!empty($web['domain'])): ?>
                                    <a href="http://<?= esc($web['domain']) ?>" target="_blank" class="inline-flex items-center text-sm font-bold text-blue-600 hover:text-indigo-600 transition-colors lowercase group/link">
                                        <?= esc($web['domain']) ?>
                                        <i class="fas fa-external-link-alt ml-2 text-[9px] opacity-0 group-hover/link:opacity-100 transition-all"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-[11px] font-bold text-slate-300 italic uppercase">Belum Ada</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap">
                                <?php
                                $status = strtoupper($web['status'] ?? 'NONAKTIF');
                                $type = ($status === 'AKTIF') ? 'success' : 'danger';
                                echo view('components/badge', ['label' => $status, 'type' => $type, 'rounded' => true]);
                                ?>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap text-center">
                                <?php if (session()->get('role') === 'super_admin'): ?>
                                <a href="<?= site_url('web_opd/edit/' . $web['id']) ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 transition-all no-underline shadow-sm" title="Edit">
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
        new ApexCharts(document.querySelector("#statusChart"), {
            series: [stats.aktif, stats.nonaktif],
            labels: ['AKTIF', 'NONAKTIF'],
            colors: ['#10b981', '#f43f5e'],
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
        }).render();
    });
</script>
<?= $this->endSection() ?>
