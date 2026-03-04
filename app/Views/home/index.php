<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Dashboard</h1>
        </div>
        <div class="flex items-center gap-2 w-full lg:w-auto">
            <a href="<?= site_url('email') ?>" class="flex-1 lg:flex-none btn btn-solid no-underline">
                <i class="fas fa-envelope mr-2 text-white/80"></i> Email
            </a>
        </div>
    </div>

    <!-- Metrik Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <a href="<?= site_url('email') ?>" class="bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-lg p-6 shadow-sm hover:border-slate-800 transition-all no-underline group">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest group-hover:text-slate-700">Total Email</p>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-2xl font-bold text-slate-800"><?= number_format($total_emails, 0, ',', '.') ?></h3>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Aktif</span>
            </div>
        </a>
        <a href="<?= site_url('email?bsre_status=ISSUE') ?>" class="bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-lg p-6 shadow-sm hover:border-slate-800 transition-all no-underline group">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest group-hover:text-slate-700">Status TTE</p>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-2xl font-bold text-slate-800"><?= number_format($active_bsre, 0, ',', '.') ?></h3>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Aktif</span>
            </div>
        </a>
        <a href="<?= site_url('web_opd') ?>" class="bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-lg p-6 shadow-sm hover:border-slate-800 transition-all no-underline group">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest group-hover:text-slate-700">Website OPD</p>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-2xl font-bold text-slate-800"><?= number_format($web_stats['opd_aktif'], 0, ',', '.') ?></h3>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                    Aktif <span class="text-slate-400 ml-1">(<?= $web_stats['opd_total'] > 0 ? round(($web_stats['opd_aktif'] / $web_stats['opd_total']) * 100) : 0 ?>%)</span>
                </span>
            </div>
        </a>
        <a href="<?= site_url('web_desa_kelurahan') ?>" class="bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-lg p-6 shadow-sm hover:border-slate-800 transition-all no-underline group">
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
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Status TTE</h3>
            </div>
            <div class="p-6 flex flex-col md:flex-row items-center gap-8">
                <div class="w-full md:w-1/2 flex justify-center">
                    <div id="emailStatusChart" class="w-full max-w-[300px]"></div>
                </div>
                <div class="w-full md:w-1/2 space-y-2 max-h-[300px] overflow-y-auto custom-scrollbar pr-2">
                    <?php foreach ($email_stats as $index => $stat):
                        $status = $stat['label'];
                        $bgClass = 'bg-slate-700'; // Default
                        if ($status === 'ISSUE') $bgClass = 'bg-emerald-600';
                        elseif (in_array($status, ['EXPIRED', 'REVOKE', 'SUSPEND'])) $bgClass = 'bg-red-600';
                        elseif (in_array($status, ['WAITING_FOR_VERIFICATION', 'RENEW', 'NO_CERTIFICATE'])) $bgClass = 'bg-amber-500';
                        elseif ($status === 'NEW') $bgClass = 'bg-emerald-500';
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
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Status ASN</h3>
            </div>
            <div class="p-6 flex flex-col md:flex-row items-center gap-8">
                <div class="w-full md:w-1/2 flex justify-center">
                    <div id="asnStatusChart" class="w-full max-w-[300px]"></div>
                </div>
                <div class="w-full md:w-1/2 space-y-2 max-h-[300px] overflow-y-auto custom-scrollbar pr-2">
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
    });
</script>
<?= $this->endSection() ?>