<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
        </div>
        <div class="flex items-center gap-2 w-full lg:w-auto">
            <a href="<?= site_url('email') ?>" class="flex-1 lg:flex-none inline-flex items-center justify-center px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-lg font-bold text-xs uppercase tracking-widest transition-all shadow-sm no-underline">
                <i class="fas fa-envelope mr-2"></i> Email
            </a>
        </div>
    </div>

    <!-- Metrik Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Email</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($total_emails) ?></h3>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">TTE Aktif</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($active_bsre) ?></h3>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Website OPD</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($web_stats['opd']) ?></h3>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Website Desa/Kelurahan</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($web_stats['desa'] + $web_stats['kelurahan']) ?></h3>
        </div>
    </div>

    <!-- Statistik dan Grafik -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Grafik Status Email -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-xs font-bold text-gray-900 uppercase tracking-tight">Status TTE</h3>
            </div>
            <div class="p-6 flex flex-col md:flex-row items-center gap-8">
                <div class="w-full md:w-1/2 flex justify-center">
                    <div id="emailStatusChart" class="w-full max-w-[200px]"></div>
                </div>
                <div class="w-full md:w-1/2 space-y-2 max-h-[200px] overflow-y-auto custom-scrollbar pr-2">
                    <?php
                    $emailColors = ['#111827', '#374151', '#4b5563', '#6b7280', '#9ca3af', '#d1d5db', '#e5e7eb'];
                    foreach ($email_stats as $index => $stat):
                    ?>
                        <div class="flex justify-between items-center p-1.5 rounded-lg border border-gray-50 bg-gray-50">
                            <div class="flex items-center truncate">
                                <span class="w-2 h-2 rounded-full mr-2 email-legend-dot shrink-0" style="background-color: <?= $emailColors[$index % count($emailColors)] ?>"></span>
                                <span class="text-[10px] font-bold text-gray-700 uppercase truncate"><?= esc($stat['label']) ?></span>
                            </div>
                            <span class="text-xs font-bold text-gray-900"><?= $stat['count'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Grafik Status ASN -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-xs font-bold text-gray-900 uppercase tracking-tight">Status ASN</h3>
            </div>
            <div class="p-6 flex flex-col md:flex-row items-center gap-8">
                <div class="w-full md:w-1/2 flex justify-center">
                    <div id="asnStatusChart" class="w-full max-w-[200px]"></div>
                </div>
                <div class="w-full md:w-1/2 space-y-1.5 max-h-[200px] overflow-y-auto custom-scrollbar pr-2">
                    <?php
                    $asnColors = ['#111827', '#374151', '#4b5563', '#6b7280', '#9ca3af', '#d1d5db', '#e5e7eb'];
                    foreach ($status_asn_stats as $index => $stat):
                    ?>
                        <div class="flex justify-between items-center p-1.5 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="flex items-center truncate">
                                <span class="w-2 h-2 rounded-full mr-2 asn-legend-dot shrink-0" style="background-color: <?= $asnColors[$index % count($asnColors)] ?>"></span>
                                <span class="text-[10px] font-bold text-gray-600 uppercase tracking-tight truncate"><?= esc($stat['label']) ?></span>
                            </div>
                            <span class="text-[10px] font-bold text-gray-900 bg-gray-100 px-1.5 py-0.5 rounded"><?= $stat['count'] ?></span>
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
                height: 200,
                fontFamily: 'Inter, sans-serif'
            },
            stroke: {
                width: 2,
                colors: ['#fff']
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
                                color: '#6B7280',
                                offsetY: -5
                            },
                            value: {
                                show: true,
                                fontSize: '16px',
                                fontWeight: 700,
                                color: '#111827',
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
                                color: '#6B7280',
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                }
                            }
                        }
                    }
                }
            }
        };

        const emailColors = ['#111827', '#374151', '#4b5563', '#6b7280', '#9ca3af', '#d1d5db', '#e5e7eb'];
        const emailStats = <?= json_encode($email_stats) ?>;

        // Chart Status Email
        new ApexCharts(document.querySelector("#emailStatusChart"), {
            ...commonOptions,
            series: emailStats.map(s => s.count),
            labels: emailStats.map(s => s.label),
            colors: emailColors
        }).render();

        // Chart Status ASN
        const asnStats = <?= json_encode($status_asn_stats) ?>;
        const asnColors = ['#111827', '#374151', '#4b5563', '#6b7280', '#9ca3af', '#d1d5db', '#e5e7eb'];

        new ApexCharts(document.querySelector("#asnStatusChart"), {
            ...commonOptions,
            series: asnStats.map(s => s.count),
            labels: asnStats.map(s => s.label),
            colors: asnColors
        }).render();
    });
</script>
<?= $this->endSection() ?>