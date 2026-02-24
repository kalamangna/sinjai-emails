<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-5 py-3 rounded-lg flex items-center shadow-sm flash-message transition-all duration-500" role="alert">
            <i class="fas fa-check-circle mr-3 text-emerald-500"></i>
            <span class="font-bold text-xs uppercase tracking-wider"><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-rose-50 border border-rose-100 text-rose-700 px-5 py-3 rounded-lg flex items-center shadow-sm flash-message transition-all duration-500" role="alert">
            <i class="fas fa-exclamation-circle mr-3 text-rose-500"></i>
            <span class="font-bold text-xs uppercase tracking-wider"><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <!-- Header & Sync -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div class="space-y-1">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Email</h1>
            <div class="flex items-center text-[11px] text-slate-500 font-medium uppercase tracking-wider">
                <i class="fas fa-history mr-2 text-slate-400"></i>
                Update: <span class="text-slate-700 ml-1"><?php echo isset($last_sync_time) ? get_local_datetime(strtotime($last_sync_time)) : '-'; ?></span>
            </div>
        </div>
        <a href="<?= site_url('email/sync') ?>" class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-wider hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all shadow-sm no-underline group" id="syncButton">
            <div class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full mr-2 hidden spinner-border"></div>
            <i class="fas fa-sync-alt mr-2 group-hover:rotate-180 transition-transform duration-500"></i>
            <span class="button-text">Sync cPanel</span>
        </a>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm flex items-center space-x-5">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-envelope text-xl"></i>
            </div>
            <div>
                <div class="text-2xl font-bold text-slate-900"><?= number_format($total_emails) ?></div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm flex items-center space-x-5">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div>
                <div class="text-2xl font-bold text-slate-900"><?= number_format($active_count) ?></div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Aktif</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm flex items-center space-x-5">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-user-slash text-xl"></i>
            </div>
            <div>
                <div class="text-2xl font-bold text-slate-900"><?= number_format($suspended_count) ?></div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Suspended</div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Kepegawaian -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                <h6 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center">
                    <i class="fas fa-user-tag mr-2 text-blue-500"></i>Status ASN
                </h6>
            </div>
            <div class="p-6 flex flex-col sm:flex-row items-center gap-8">
                <div class="w-full sm:w-1/2 min-h-[200px]">
                    <div id="asnStatusChart"></div>
                </div>
                <div class="w-full sm:w-1/2 space-y-2 max-h-[200px] overflow-y-auto custom-scrollbar pr-2">
                    <?php foreach ($status_asn_counts as $index => $status): ?>
                        <div class="flex justify-between items-center p-2 rounded-lg hover:bg-slate-50 transition-colors">
                            <div class="flex items-center">
                                <span class="w-2 h-2 rounded-full mr-2 asn-legend-dot" data-index="<?= $index ?>"></span>
                                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-tight"><?= esc($status['name']) ?></span>
                            </div>
                            <span class="text-[10px] font-bold text-slate-900"><?= number_format($status['count']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Sertifikat -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                <h6 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center">
                    <i class="fas fa-fingerprint mr-2 text-amber-500"></i>Sertifikat
                </h6>
            </div>
            <div class="p-6 flex flex-col sm:flex-row items-center gap-8">
                <div class="w-full sm:w-1/2 min-h-[200px]">
                    <div id="tteStatusChart"></div>
                </div>
                <div class="w-full sm:w-1/2 space-y-2 max-h-[200px] overflow-y-auto custom-scrollbar pr-2">
                    <?php foreach ($bsre_status_counts as $status): ?>
                        <div class="flex justify-between items-center p-2 rounded-lg hover:bg-slate-50 transition-colors">
                            <div class="flex items-center">
                                <span class="w-2 h-2 rounded-full mr-2 tte-legend-dot" data-status="<?= $status['status'] ?>"></span>
                                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-tight"><?= esc($status['label']) ?></span>
                            </div>
                            <span class="text-[10px] font-bold text-slate-900"><?= number_format($status['count']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-6 border-b border-slate-200">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-5 lg:col-span-6">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= isset($search) ? esc($search) : '' ?>" class="block w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all" placeholder="Nama, Email, NIK, atau NIP...">
                    </div>
                </div>

                <div class="md:col-span-4 lg:col-span-4">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Sertifikat</label>
                    <select name="bsre_status" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                        <option value="">Semua Status</option>
                        <?php foreach ($bsre_status_options as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= ($bsre_status === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-3 lg:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-slate-800 border border-transparent rounded-lg font-bold text-[11px] text-white uppercase tracking-wider hover:bg-slate-900 active:bg-slate-950 focus:outline-none focus:ring-2 focus:ring-slate-500/20 transition-all shadow-sm">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="<?= site_url('email') ?>" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-lg font-bold text-[11px] text-slate-600 uppercase tracking-wider hover:bg-slate-50 hover:text-slate-900 active:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500/20 transition-all shadow-sm no-underline">
                        <i class="fas fa-redo mr-2"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Akun</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Unit Kerja</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sertifikat</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php if (!empty($emails)): ?>
                        <?php foreach ($emails as $email): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center mr-3 border border-slate-200">
                                            <i class="fas fa-envelope text-slate-400 text-sm"></i>
                                        </div>
                                        <div>
                                            <div class="text-[13px] font-bold text-slate-900 lowercase"><?= esc($email['email']) ?></div>
                                            <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide"><?= esc($email['name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-[11px] font-bold text-slate-700 uppercase"><?= esc($email['unit_kerja_name']) ?></div>
                                    <?php if (!empty($email['parent_unit_kerja_name'])): ?>
                                        <div class="text-[9px] font-semibold text-slate-400 uppercase tracking-tighter"><?= esc($email['parent_unit_kerja_name']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $status = $email['bsre_status'] ?? '';
                                    $colors = [
                                        'ISSUE' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'EXPIRED' => 'bg-rose-50 text-rose-700 border-rose-100',
                                        'RENEW' => 'bg-blue-50 text-blue-700 border-blue-100',
                                        'WAITING_FOR_VERIFICATION' => 'bg-amber-50 text-amber-700 border-amber-100',
                                        'NEW' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                        'NO_CERTIFICATE' => 'bg-slate-100 text-slate-600 border-slate-200',
                                    ];
                                    $cls = $colors[$status] ?? 'bg-slate-50 text-slate-400 border-slate-100';
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[9px] font-bold uppercase tracking-wider border <?= $cls ?>">
                                        <?= $status ?: 'NOT SYNCED' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 transition-all no-underline shadow-sm" title="Rincian">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400 text-xs font-medium italic">Tidak ada data email yang ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pagination): ?>
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                    Showing <span class="text-slate-900"><?= count($emails) ?></span> of <span class="text-slate-900"><?= number_format($filtered_count) ?></span> entries
                </div>
                <div class="pagination-container">
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
                const spinner = this.querySelector('.spinner-border');
                const icon = this.querySelector('.fa-sync-alt');
                const text = this.querySelector('.button-text');

                spinner.classList.remove('hidden');
                icon.classList.add('hidden');
                text.textContent = 'Syncing...';
                this.classList.add('opacity-75', 'pointer-events-none');
            });
        }

        const flashMessages = document.querySelectorAll('.flash-message');
        flashMessages.forEach(msg => {
            setTimeout(() => {
                msg.style.opacity = '0';
                msg.style.transform = 'translateY(-10px)';
                setTimeout(() => msg.remove(), 500);
            }, 3000);
        });

        const commonOptions = {
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
            }
        };

        // ASN Status Chart
        const asnStats = <?= json_encode($status_asn_counts) ?>;
        const asnColors = ['#2563eb', '#7c3aed', '#db2777', '#ea580c', '#059669', '#0891b2', '#475569'];
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