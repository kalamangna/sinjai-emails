<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .apexcharts-bar-series rect {
        cursor: pointer !important;
    }

    .apexcharts-yaxis-label {
        cursor: pointer !important;
        text-decoration: underline !important;
        text-underline-offset: 2px;
    }

    .apexcharts-yaxis-label:hover {
        fill: #1e293b !important;
        font-weight: 700 !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Dashboard</h1>
        </div>
        <div class="flex items-center gap-2 w-full lg:w-auto">
            <?php if (in_array(session()->get('role'), ['super_admin', 'admin'])): ?>
                <button type="button" onclick="triggerSyncCpanel()" id="syncCpanelBtn" class="flex-1 lg:flex-none btn btn-outline bg-white group" title="Sinkronisasi Akun & Kuota Email dari cPanel">
                    <i class="fas fa-sync-alt mr-2 text-slate-700 group-hover:rotate-180 transition-transform duration-500"></i>
                    <span class="text-slate-700">Sync cPanel</span>
                </button>
            <?php endif; ?>
            <a href="<?= site_url('email') ?>" class="flex-1 lg:flex-none btn btn-solid no-underline">
                <i class="fas fa-envelope mr-2 text-white/80"></i> Email
            </a>
        </div>
    </div>

    <!-- Status Sinkronisasi & Health Check -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Card Terakhir Sinkronisasi -->
        <div class="lg:col-span-8 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="text-[10px] font-bold text-slate-800 uppercase tracking-[0.2em]">Terakhir Sinkronisasi</h3>
                <i class="fas fa-history text-slate-400 text-xs"></i>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="flex items-center gap-4 p-3 bg-slate-50 border border-slate-100 rounded-xl transition-all hover:bg-slate-100/50">
                    <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center shrink-0">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Kuota Email</p>
                        <p class="text-xs font-bold text-slate-700"><?= !empty($last_sync_cpanel) ? formatTanggalWaktu($last_sync_cpanel) : '-' ?></p>
                    </div>
                </div>

                <div class="flex items-center gap-4 p-3 bg-slate-50 border border-slate-100 rounded-xl transition-all hover:bg-slate-100/50">
                    <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center shrink-0">
                        <i class="fas fa-signature"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Sertifikat TTE</p>
                        <p class="text-xs font-bold text-slate-700"><?= !empty($last_sync_tte) ? formatTanggalWaktu($last_sync_tte) : '-' ?></p>
                    </div>
                </div>

                <div class="flex items-center gap-4 p-3 bg-slate-50 border border-slate-100 rounded-xl transition-all hover:bg-slate-100/50">
                    <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center shrink-0">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Data Pegawai</p>
                        <p class="text-xs font-bold text-slate-700"><?= !empty($last_sync_pegawai) ? formatTanggalWaktu($last_sync_pegawai) : '-' ?></p>
                    </div>
                </div>

                <div class="flex items-center gap-4 p-3 bg-slate-50 border border-slate-100 rounded-xl transition-all hover:bg-slate-100/50">
                    <div class="w-10 h-10 bg-slate-200 text-slate-600 rounded-lg flex items-center justify-center shrink-0">
                        <i class="fas fa-globe"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Website Desa</p>
                        <p class="text-xs font-bold text-slate-700"><?= !empty($last_sync_website) ? formatTanggalWaktu($last_sync_website) : '-' ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Layanan Eksternal -->
        <div class="lg:col-span-4 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="text-[10px] font-bold text-slate-800 uppercase tracking-[0.2em]">Layanan Eksternal</h3>
                <i class="fas fa-plug text-slate-400 text-xs"></i>
            </div>
            <div id="healthCheckContent" class="p-6 space-y-4 flex-grow">
                <div class="animate-pulse space-y-4">
                    <?php for ($i = 0; $i < 3; $i++): ?>
                        <div class="flex justify-between items-center">
                            <div class="h-2 bg-slate-100 rounded w-24"></div>
                            <div class="h-4 bg-slate-50 rounded w-16"></div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrik Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <a href="<?= site_url('email') ?>" class="bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-2xl p-6 shadow-sm hover:border-slate-800 transition-all no-underline group">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest group-hover:text-slate-700">Total Email</p>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-2xl font-bold text-slate-800"><?= number_format($total_emails, 0, ',', '.') ?></h3>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Aktif</span>
            </div>
        </a>
        <a href="<?= site_url('email?bsre_status=ISSUE') ?>" class="bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-2xl p-6 shadow-sm hover:border-slate-800 transition-all no-underline group">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest group-hover:text-slate-700">Status TTE</p>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-2xl font-bold text-slate-800"><?= number_format($active_bsre, 0, ',', '.') ?></h3>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Aktif</span>
            </div>
        </a>
        <a href="<?= site_url('web_opd') ?>" class="bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-2xl p-6 shadow-sm hover:border-slate-800 transition-all no-underline group">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest group-hover:text-slate-700">Website OPD</p>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-2xl font-bold text-slate-800"><?= number_format($web_stats['opd_aktif'], 0, ',', '.') ?></h3>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                    Aktif <span class="text-slate-400 ml-1">(<?= $web_stats['opd_total'] > 0 ? round(($web_stats['opd_aktif'] / $web_stats['opd_total']) * 100) : 0 ?>%)</span>
                </span>
            </div>
        </a>
        <a href="<?= site_url('web_desa_kelurahan') ?>" class="bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-2xl p-6 shadow-sm hover:border-slate-800 transition-all no-underline group">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest group-hover:text-slate-700">Website Desa & Kelurahan</p>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-2xl font-bold text-slate-800"><?= number_format($web_stats['desa_aktif'] + $web_stats['kelurahan_aktif'], 0, ',', '.') ?></h3>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                    Aktif <span class="text-slate-400 ml-1">(<?php
                                                                $total_web_dk = ($web_stats['desa_total'] ?? 0) + ($web_stats['kelurahan_total'] ?? 0);
                                                                $aktif_web_dk = ($web_stats['desa_aktif'] ?? 0) + ($web_stats['kelurahan_aktif'] ?? 0);
                                                                echo $total_web_dk > 0 ? round(($aktif_web_dk / $total_web_dk) * 100) : 0;
                                                                ?>%)</span>
                </span>
            </div>
        </a>
    </div>

    <!-- Statistik dan Grafik -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Grafik Status Email -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-4 sm:px-6 py-4 border-b border-slate-100 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Status TTE</h3>
            </div>
            <div class="p-4 sm:p-6 flex flex-col md:flex-row items-center gap-4 sm:gap-8">
                <div class="w-full md:w-1/2 flex justify-center py-2 sm:py-0">
                    <div id="emailStatusChart" class="w-full max-w-[220px] sm:max-w-[300px]"></div>
                </div>
                <div class="w-full md:w-1/2 space-y-2 max-h-[220px] sm:max-h-[300px] overflow-y-auto custom-scrollbar pr-1 sm:pr-2">
                    <?php foreach ($email_stats as $index => $stat):
                        $status = $stat['label'];
                        $bgClass = 'bg-slate-700'; // Default
                        if ($status === 'ISSUE') $bgClass = 'bg-emerald-600';
                        elseif (in_array($status, ['EXPIRED', 'REVOKE', 'SUSPEND'])) $bgClass = 'bg-red-600';
                        elseif (in_array($status, ['WAITING_FOR_VERIFICATION', 'RENEW', 'NO_CERTIFICATE'])) $bgClass = 'bg-amber-500';
                        elseif ($status === 'NEW') $bgClass = 'bg-slate-600';
                        elseif ($status === 'NON_TTE') $bgClass = 'bg-slate-300';
                        elseif ($status === 'NOT_SYNCED') $bgClass = 'bg-slate-400';
                    ?>
                        <div class="flex justify-between items-center p-2 rounded-lg border border-slate-200 bg-slate-50">
                            <div class="flex items-center truncate">
                                <span class="w-2 h-2 rounded-full mr-2 email-legend-dot shrink-0 <?= $bgClass ?>"></span>
                                <span class="text-[10px] font-bold text-slate-700 uppercase truncate"><?= esc($stat['label']) ?></span>
                            </div>
                            <span class="text-xs font-bold text-slate-800"><?= number_format($stat['count'], 0, ',', '.') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Grafik Status ASN -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-4 sm:px-6 py-4 border-b border-slate-100 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Status ASN</h3>
            </div>
            <div class="p-4 sm:p-6 flex flex-col md:flex-row items-center gap-4 sm:gap-8">
                <div class="w-full md:w-1/2 flex justify-center py-2 sm:py-0">
                    <div id="asnStatusChart" class="w-full max-w-[220px] sm:max-w-[300px]"></div>
                </div>
                <div class="w-full md:w-1/2 space-y-2 max-h-[220px] sm:max-h-[300px] overflow-y-auto custom-scrollbar pr-1 sm:pr-2">
                    <?php
                    foreach ($status_asn_stats as $index => $stat):
                        $label = strtoupper($stat['label']);
                        $bgClass = 'bg-slate-300';
                        if ($label === 'PNS') $bgClass = 'bg-slate-800';
                        elseif ($label === 'PPPK') $bgClass = 'bg-slate-600';
                        elseif (strpos($label, 'PPPK PARUH WAKTU') !== false) $bgClass = 'bg-slate-400';
                    ?>
                        <div class="flex justify-between items-center p-2 rounded-lg border border-slate-200 bg-slate-50">
                            <div class="flex items-center truncate">
                                <span class="w-2 h-2 rounded-full mr-2 asn-legend-dot shrink-0 <?= $bgClass ?>"></span>
                                <span class="text-[10px] font-bold text-slate-700 uppercase tracking-tight truncate"><?= esc($stat['label']) ?></span>
                            </div>
                            <span class="text-xs font-bold text-slate-800"><?= number_format($stat['count'], 0, ',', '.') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 10 OPD Teraktif TTE -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">TTE Teraktif</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[10px] font-bold tracking-widest">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200 w-12 text-center">No</th>
                        <th class="px-6 py-3 border-b border-slate-200">Nama OPD</th>
                        <th class="px-6 py-3 border-b border-slate-200 text-center">Total Akun</th>
                        <th class="px-6 py-3 border-b border-slate-200 text-center">TTE Aktif</th>
                        <th class="px-6 py-3 border-b border-slate-200">Persentase</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($top_opd_tte)): ?>
                        <?php foreach ($top_opd_tte as $index => $opd):
                            $percent = round($opd['active_percentage'] ?? 0, 1);
                            $progress_color = ($percent >= 85) ? 'bg-emerald-600' : (($percent >= 50) ? 'bg-amber-500' : 'bg-slate-600');
                        ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-center font-bold text-slate-500 text-xs w-12">
                                    <?= $index + 1 ?>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-800 uppercase text-xs">
                                    <a href="<?= site_url('email/unit_kerja/' . $opd['opd_id']) ?>" class="hover:text-slate-900 hover:underline underline-offset-2 transition-colors">
                                        <?= esc($opd['opd_name']) ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-center font-medium text-slate-700 text-xs">
                                    <?= number_format($opd['total_wajib_tte'] ?? 0, 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-slate-800 text-xs">
                                    <?= number_format($opd['total_active_tte'] ?? 0, 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3 min-w-[150px]">
                                        <div class="flex-grow bg-slate-100 rounded-full h-2 overflow-hidden">
                                            <div class="<?= $progress_color ?> h-full rounded-full transition-all duration-1000" style="width: <?= $percent ?>%"></div>
                                        </div>
                                        <span class="text-[11px] font-black text-slate-800 w-10 text-right shrink-0">
                                            <?= $percent ?>%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center">
                                <span class="text-xs font-medium text-slate-400 italic">Data tidak tersedia</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top 10 Persentase Penggunaan Disk -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Penggunaan Disk Terbesar</h3>
        </div>
        <div class="p-6">
            <div id="diskUsageChart" class="w-full"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const commonOptions = {
            chart: {
                type: 'donut',
                height: 300,
                fontFamily: 'Inter, sans-serif'
            },
            tooltip: {
                enabled: false
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
                                    return parseInt(val).toLocaleString('id-ID')
                                }
                            },
                            total: {
                                show: true,
                                label: 'TOTAL',
                                fontSize: '10px',
                                fontWeight: 700,
                                color: '#9ca3af',
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString('id-ID')
                                }
                            }
                        }
                    }
                }
            }
        };

        const emailStats = <?= json_encode($email_stats) ?>;
        const emailColors = emailStats.map(s => {
            const status = s.label.toUpperCase();
            if (status === 'ISSUE') return '#059669'; // emerald-600
            if (['EXPIRED', 'REVOKE', 'SUSPEND'].includes(status)) return '#dc2626'; // red-600
            if (['WAITING_FOR_VERIFICATION', 'RENEW', 'NO_CERTIFICATE'].includes(status)) return '#f59e0b'; // amber-500
            if (status === 'NEW') return '#475569'; // slate-600
            if (status === 'NON_TTE') return '#cbd5e1'; // slate-300
            return '#94a3b8'; // slate-400
        });

        // Chart Status Email
        new ApexCharts(document.querySelector("#emailStatusChart"), {
            ...commonOptions,
            series: emailStats.map(s => s.count),
            labels: emailStats.map(s => s.label),
            colors: emailColors
        }).render();

        // Chart Status ASN
        const asnStats = <?= json_encode($status_asn_stats) ?>;
        const asnColors = asnStats.map(s => {
            const label = s.label.toUpperCase();
            if (label === 'PNS') return '#1e293b'; // slate-800
            if (label === 'PPPK') return '#475569'; // slate-600
            if (label.includes('PPPK PARUH WAKTU')) return '#94a3b8'; // slate-400
            return '#cbd5e1'; // slate-300
        });

        new ApexCharts(document.querySelector("#asnStatusChart"), {
            ...commonOptions,
            series: asnStats.map(s => s.count),
            labels: asnStats.map(s => s.label),
            colors: asnColors
        }).render();

        // Chart Penggunaan Disk Terbesar (Top 10)
        const diskStats = <?= json_encode($top_disk_emails ?? []) ?>;
        new ApexCharts(document.querySelector("#diskUsageChart"), {
            chart: {
                type: 'bar',
                height: 420,
                fontFamily: 'Inter, sans-serif',
                toolbar: {
                    show: false
                },
                events: {
                    dataPointSelection: function(event, chartContext, config) {
                        if (config.dataPointIndex !== -1 && diskStats[config.dataPointIndex]) {
                            const item = diskStats[config.dataPointIndex];
                            window.location.href = '<?= site_url("email/detail/") ?>' + item.prefix;
                        }
                    }
                }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    barHeight: '55%',
                    borderRadius: 4,
                }
            },
            colors: ['#475569'], // slate-600
            dataLabels: {
                enabled: true,
                formatter: function(val, opt) {
                    return diskStats[opt.dataPointIndex] ? diskStats[opt.dataPointIndex].percentage + '%' : '';
                },
                style: {
                    fontSize: '9px',
                    fontFamily: 'Inter, sans-serif',
                    fontWeight: 700,
                    colors: ['#ffffff']
                },
                offsetX: -6
            },
            series: [{
                name: 'Persentase Digunakan',
                data: diskStats.map(item => item.percentage)
            }],
            xaxis: {
                categories: diskStats.map(item => item.email),
                labels: {
                    style: {
                        fontSize: '9px',
                        fontWeight: 600,
                        colors: '#64748b'
                    },
                    formatter: function(val) {
                        return val + '%';
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '9px',
                        fontWeight: 600,
                        colors: '#475569'
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(val, opt) {
                        if (!diskStats[opt.dataPointIndex]) return '';
                        const item = diskStats[opt.dataPointIndex];
                        return item.human_size + ' (' + item.percentage + '%)';
                    }
                },
                custom: function({
                    series,
                    seriesIndex,
                    dataPointIndex,
                    w
                }) {
                    if (!diskStats[dataPointIndex]) return '';
                    const item = diskStats[dataPointIndex];
                    return '<div class="p-2 text-[10px] font-medium bg-slate-800 text-white rounded shadow-md border border-slate-700">' +
                        '<div class="font-bold text-slate-200">' + item.email + '</div>' +
                        '<div class="text-slate-400 mt-0.5">' + item.name + '</div>' +
                        '<div class="mt-1 font-bold text-emerald-400">Digunakan: ' + item.human_size + ' (' + item.percentage + '%)</div>' +
                        '</div>';
                }
            }
        }).render();

        // Handle clicks on Y-axis labels for full email redirection
        document.querySelector("#diskUsageChart").addEventListener('click', function(e) {
            const yLabel = e.target.closest('.apexcharts-yaxis-label');
            if (yLabel) {
                const labelText = yLabel.textContent.trim();
                const item = diskStats.find(d => d.email === labelText);
                if (item) {
                    window.location.href = '<?= site_url("email/detail/") ?>' + item.prefix;
                }
            }
        });

        // Health Check Sync
        fetch('<?= site_url('api/health-check') ?>')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('healthCheckContent');
                container.innerHTML = '';

                // Loop secara dinamis dari data API
                Object.values(data).forEach(service => {
                    const isUp = service.status === 'UP';
                    const isMocked = service.is_mocked;

                    let bgStatus = 'bg-red-50 text-red-600 border-red-100';
                    let dotStatus = 'bg-red-500';

                    if (isUp) {
                        if (isMocked) {
                            bgStatus = 'bg-indigo-50 text-indigo-600 border-indigo-100';
                            dotStatus = 'bg-indigo-500';
                        } else {
                            bgStatus = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                            dotStatus = 'bg-emerald-500';
                        }
                    }

                    const html = `
                        <div class="flex items-center justify-between p-2 rounded-lg border border-transparent hover:border-slate-50 transition-colors" title="${service.message || ''}">
                            <span class="text-[10px] font-bold text-slate-600 uppercase tracking-tight">${service.label}</span>
                            <div class="flex items-center px-2 py-0.5 rounded-full border ${bgStatus}">
                                <div class="w-1 h-1 rounded-full ${dotStatus} mr-1.5 animate-pulse"></div>
                                <span class="text-[8px] font-bold uppercase tracking-widest">${service.text}</span>
                            </div>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', html);
                });
            })
            .catch(error => {
                document.getElementById('healthCheckContent').innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-[9px] text-red-500 font-bold uppercase">Gagal memuat status layanan</p></div>';
            });
    });

    async function triggerSyncCpanel() {
        if (!confirm('Apakah Anda yakin ingin menyinkronkan data akun dan kuota email dari cPanel sekarang?')) {
            return;
        }

        const btn = document.getElementById('syncCpanelBtn');
        const originalHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><span>Menyinkronkan...</span>';
        }

        try {
            const response = await fetch('<?= site_url('dashboard/sync_cpanel') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= config('Security')->headerName ?>': '<?= csrf_hash() ?>'
                }
            });

            const data = await response.json();

            if (response.ok && (data.status === 'success' || data.success)) {
                const synced = data.data?.synced ?? 0;
                const deleted = data.data?.deleted ?? 0;

                const iconEl  = document.getElementById('sync-result-icon');
                const msgEl   = document.getElementById('sync-result-message');
                const statsEl = document.getElementById('sync-result-stats');

                if (iconEl) {
                    iconEl.className = 'w-14 h-14 rounded-full flex items-center justify-center text-2xl bg-emerald-100';
                    iconEl.innerHTML = '<i class="fas fa-check text-emerald-600"></i>';
                }
                if (msgEl) {
                    msgEl.textContent = data.message || 'Sinkronisasi cPanel berhasil diselesaikan.';
                }
                if (statsEl) {
                    statsEl.innerHTML = `
                        <div class="flex-1 rounded-lg p-3 text-center bg-slate-100 text-slate-700">
                            <div class="text-xl font-bold">${synced}</div>
                            <div class="text-[10px] font-bold uppercase tracking-widest mt-0.5">Disinkron</div>
                        </div>
                        <div class="flex-1 rounded-lg p-3 text-center bg-emerald-50 text-emerald-700">
                            <div class="text-xl font-bold">${deleted}</div>
                            <div class="text-[10px] font-bold uppercase tracking-widest mt-0.5">Dihapus</div>
                        </div>
                    `;
                }

                if (typeof syncResultShouldReload !== 'undefined') {
                    syncResultShouldReload = true;
                }

                if (typeof openModal === 'function') {
                    openModal('global-sync-result-modal');
                } else {
                    alert(data.message || 'Sinkronisasi cPanel berhasil!');
                    window.location.reload();
                }
            } else {
                const errorMsg = data.message || 'Terjadi kesalahan saat sinkronisasi data cPanel.';
                if (typeof window.showGlobalError === 'function') {
                    window.showGlobalError('Gagal Sinkronisasi cPanel', errorMsg);
                } else {
                    alert(errorMsg);
                }
            }
        } catch (err) {
            const errMsg = 'Gagal terhubung ke server: ' + (err.message || 'Kesalahan jaringan');
            if (typeof window.showGlobalError === 'function') {
                window.showGlobalError('Kesalahan Jaringan', errMsg);
            } else {
                alert(errMsg);
            }
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
                btn.innerHTML = originalHtml;
            }
        }
    }
    window.triggerSyncCpanel = triggerSyncCpanel;
</script>
<?= $this->endSection() ?>