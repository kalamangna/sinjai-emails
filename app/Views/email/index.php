<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <!-- Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-5 py-3 rounded-xl flex items-center shadow-sm flash-message transition-all duration-500" role="alert">
            <i class="fas fa-check-circle mr-3 text-emerald-500"></i>
            <span class="font-bold text-xs uppercase tracking-wider"><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-rose-50 border border-rose-100 text-rose-700 px-5 py-3 rounded-xl flex items-center shadow-sm flash-message transition-all duration-500" role="alert">
            <i class="fas fa-exclamation-circle mr-3 text-rose-500"></i>
            <span class="font-bold text-xs uppercase tracking-wider"><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-5">
            <div class="w-14 h-14 bg-emerald-600 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-200">
                <i class="fas fa-envelope-open-text text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight uppercase">Data Email</h1>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1 flex items-center">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2 animate-pulse"></span>
                    Update: <span class="text-slate-600 ml-1"><?php echo isset($last_sync_time) ? date('d M Y, H:i', strtotime($last_sync_time)) : '-'; ?></span>
                </p>
            </div>
        </div>
        
        <div class="flex items-center gap-3 w-full lg:w-auto">
            <?php if (session()->get('role') === 'super_admin'): ?>
            <a href="<?= site_url('email/sync') ?>" class="flex-1 lg:flex-none inline-flex items-center justify-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs uppercase tracking-widest transition-all shadow-md no-underline group" id="syncButton">
                <i class="fas fa-sync-alt mr-2 group-hover:rotate-180 transition-transform duration-500"></i>
                <span class="button-text">Sync</span>
            </a>
            <a href="<?= site_url('email/batch_hub') ?>" class="flex-1 lg:flex-none inline-flex items-center justify-center px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold text-xs uppercase tracking-widest transition-all shadow-md no-underline">
                <i class="fas fa-layer-group mr-2"></i> Batch
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?= view('components/card_metric', [
            'label' => 'Total Akun',
            'value' => number_format($total_emails),
            'icon'  => 'fas fa-users',
            'color' => 'indigo',
            'trend' => 'Database'
        ]) ?>
        <?= view('components/card_metric', [
            'label' => 'Aktif',
            'value' => number_format($active_count),
            'icon'  => 'fas fa-check-circle',
            'color' => 'emerald',
            'trend' => round(($active_count / max($total_emails, 1)) * 100, 1) . '%'
        ]) ?>
        <?= view('components/card_metric', [
            'label' => 'Suspended',
            'value' => number_format($suspended_count),
            'icon'  => 'fas fa-user-slash',
            'color' => 'rose',
            'trend' => round(($suspended_count / max($total_emails, 1)) * 100, 1) . '%'
        ]) ?>
    </div>

    <!-- Insights -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-tight">Status ASN</h3>
                </div>
                <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-pie text-xs"></i>
                </div>
            </div>
            <div class="p-8 flex flex-col md:flex-row items-center gap-10">
                <div class="w-full md:w-1/2">
                    <div id="asnStatusChart" class="min-h-[220px]"></div>
                </div>
                <div class="w-full md:w-1/2 space-y-3 max-h-[220px] overflow-y-auto custom-scrollbar pr-4">
                    <?php foreach ($status_asn_counts as $index => $status): ?>
                        <div class="flex justify-between items-center p-3 rounded-xl hover:bg-slate-50 transition-all group">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded-full mr-3 border-2 border-white shadow-sm asn-legend-dot" data-index="<?= $index ?>"></span>
                                <span class="text-[11px] font-bold text-slate-600 uppercase tracking-tight group-hover:text-slate-900 transition-colors"><?= esc($status['name']) ?></span>
                            </div>
                            <span class="text-[11px] font-black text-slate-900 bg-slate-100 px-2 py-0.5 rounded-lg group-hover:bg-emerald-600 group-hover:text-white transition-all"><?= number_format($status['count']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-tight">Sertifikat</h3>
                </div>
                <div class="w-8 h-8 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shield-alt text-xs"></i>
                </div>
            </div>
            <div class="p-8 flex flex-col md:flex-row items-center gap-10">
                <div class="w-full md:w-1/2">
                    <div id="tteStatusChart" class="min-h-[220px]"></div>
                </div>
                <div class="w-full md:w-1/2 space-y-3 max-h-[220px] overflow-y-auto custom-scrollbar pr-4">
                    <?php foreach ($bsre_status_counts as $status): ?>
                        <div class="flex justify-between items-center p-3 rounded-xl hover:bg-slate-50 transition-all group">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded-full mr-3 border-2 border-white shadow-sm tte-legend-dot" data-status="<?= $status['status'] ?>"></span>
                                <span class="text-[11px] font-bold text-slate-600 uppercase tracking-tight group-hover:text-slate-900 transition-colors"><?= esc($status['label']) ?></span>
                            </div>
                            <span class="text-[11px] font-black text-slate-900 bg-slate-100 px-2 py-0.5 rounded-lg group-hover:bg-emerald-600 group-hover:text-white transition-all"><?= number_format($status['count']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="p-8 border-b border-slate-100 bg-slate-50/50">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                <div class="md:col-span-6 lg:col-span-7">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2.5 ml-1">Cari</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                            <i class="fas fa-search text-sm"></i>
                        </span>
                        <input type="text" name="search" value="<?= isset($search) ? esc($search) : '' ?>" class="block w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-semibold text-slate-700 transition-all placeholder:text-slate-400 shadow-inner" placeholder="Nama, Email, NIK, atau NIP...">
                    </div>
                </div>

                <div class="md:col-span-4 lg:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2.5 ml-1">Sertifikat</label>
                    <select name="bsre_status" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-semibold text-slate-700 transition-all appearance-none cursor-pointer">
                        <option value="">Semua</option>
                        <?php foreach ($bsre_status_options as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= ($bsre_status === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2 lg:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center h-[46px] bg-slate-900 hover:bg-emerald-600 text-white rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="<?= site_url('email') ?>" class="inline-flex items-center justify-center w-[46px] h-[46px] bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 rounded-xl transition-all" title="Reset">
                        <i class="fas fa-redo text-xs"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-50/80">
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Akun</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Unit Kerja</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Sertifikat</th>
                        <th class="px-8 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 bg-white">
                    <?php if (!empty($emails)): ?>
                        <?php foreach ($emails as $email): ?>
                            <tr class="hover:bg-emerald-50/30 transition-all group">
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        <div class="w-11 h-11 rounded-xl bg-slate-100 group-hover:bg-white border border-slate-200 flex items-center justify-center transition-colors">
                                            <i class="fas fa-envelope text-slate-400 group-hover:text-emerald-600 text-base"></i>
                                        </div>
                                        <div>
                                            <div class="text-[13px] font-bold text-slate-900 lowercase"><?= esc($email['email']) ?></div>
                                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5 group-hover:text-slate-600"><?= esc($email['name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-[11px] font-bold text-slate-700 uppercase group-hover:text-emerald-700 transition-colors"><?= esc($email['unit_kerja_name']) ?></div>
                                    <?php if (!empty($email['parent_unit_kerja_name'])): ?>
                                        <div class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter mt-1"><?= esc($email['parent_unit_kerja_name']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <?php
                                    $status = $email['bsre_status'] ?? '';
                                    $type = 'neutral';
                                    if ($status === 'ISSUE') $type = 'success';
                                    elseif (in_array($status, ['EXPIRED', 'REVOKE', 'SUSPEND'])) $type = 'danger';
                                    elseif (in_array($status, ['WAITING_FOR_VERIFICATION', 'RENEW'])) $type = 'warning';
                                    elseif ($status === 'NEW') $type = 'info';
                                    
                                    echo view('components/badge', [
                                        'label' => $status ?: 'Belum Sync',
                                        'type' => $type,
                                        'rounded' => true
                                    ]);
                                    ?>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-center">
                                    <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 transition-all no-underline shadow-sm" title="Rincian">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 border border-dashed border-slate-200">
                                        <i class="fas fa-folder-open text-slate-300 text-xl"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Data tidak ditemukan</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pagination): ?>
            <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    Showing <span class="text-slate-900 bg-white px-2 py-1 rounded-md shadow-sm"><?= count($emails) ?></span> of <span class="text-slate-900 bg-white px-2 py-1 rounded-md shadow-sm"><?= number_format($filtered_count) ?></span> entries
                </div>
                <div class="flex items-center">
                    <?= $pagination->links() ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const syncButton = document.getElementById('syncButton');
        if (syncButton) {
            syncButton.addEventListener('click', function(e) {
                const icon = this.querySelector('.fa-sync-alt');
                const text = this.querySelector('.button-text');

                icon.classList.add('animate-spin');
                text.textContent = 'Syncing...';
                this.classList.add('opacity-75', 'pointer-events-none');
            });
        }

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

        // ASN Status Chart
        const asnStats = <?= json_encode($status_asn_counts) ?>;
        const asnColors = ['#10b981', '#3b82f6', '#6366f1', '#8b5cf6', '#d946ef', '#f43f5e', '#f59e0b', '#06b6d4'];
        new ApexCharts(document.querySelector("#asnStatusChart"), {
            ...commonOptions,
            series: asnStats.map(s => parseInt(s.count)),
            labels: asnStats.map(s => s.name),
            colors: asnColors
        }).render();

        asnStats.forEach((_, i) => {
            const dot = document.querySelector(`.asn-legend-dot[data-index="${i}"]`);
            if (dot) dot.style.backgroundColor = asnColors[i % asnColors.length];
        });

        // TTE Status Chart
        const tteStats = <?= json_encode($bsre_status_counts) ?>;
        const tteColorMap = {
            'ISSUE': '#10b981',
            'EXPIRED': '#f43f5e',
            'RENEW': '#3b82f6',
            'WAITING_FOR_VERIFICATION': '#f59e0b',
            'NEW': '#6366f1',
            'NO_CERTIFICATE': '#94a3b8',
            'not_synced': '#cbd5e1'
        };
        new ApexCharts(document.querySelector("#tteStatusChart"), {
            ...commonOptions,
            series: tteStats.map(s => parseInt(s.count)),
            labels: tteStats.map(s => s.label),
            colors: tteStats.map(s => tteColorMap[s.status] || '#cbd5e1')
        }).render();

        tteStats.forEach(s => {
            const dot = document.querySelector(`.tte-legend-dot[data-status="${s.status}"]`);
            if (dot) dot.style.backgroundColor = tteColorMap[s.status] || '#cbd5e1';
        });
    });
</script>
<?= $this->endSection() ?>