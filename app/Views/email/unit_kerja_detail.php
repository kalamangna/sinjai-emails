<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Navigasi dan Aksi -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <button onclick="history.back()" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 text-slate-700 font-bold rounded-lg hover:bg-slate-50 transition-all text-xs uppercase tracking-widest no-underline shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </button>

        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= site_url('email/export_unit_kerja_csv/' . $unit_kerja['id']) . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-lg font-bold text-[10px] uppercase tracking-widest no-underline shadow-sm">
                <i class="fas fa-file-csv mr-2 text-emerald-600"></i> Unduh CSV
            </a>
            <a href="<?= site_url('email/export_account_detail_pdf/' . $unit_kerja['id']) . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-lg font-bold text-[10px] uppercase tracking-widest no-underline shadow-sm">
                <i class="fas fa-user-shield mr-2 text-blue-600"></i> Akun PDF
            </a>
            <a href="<?= site_url('email/export_unit_kerja_pdf/' . $unit_kerja['id']) . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-lg font-bold text-[10px] uppercase tracking-widest no-underline shadow-sm">
                <i class="fas fa-file-pdf mr-2 text-red-600"></i> Status PDF
            </a>

            <?php if (session()->get('role') === 'super_admin'): ?>
                <div class="relative group">
                    <button class="inline-flex items-center justify-center px-4 py-2 bg-slate-800 text-white rounded-lg font-bold text-[10px] uppercase tracking-widest hover:bg-slate-700 transition-all shadow-sm">
                        <i class="fas fa-file-contract mr-2 text-white/80"></i> Batch PK <i class="fas fa-chevron-down ml-2 text-[8px] opacity-50"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 overflow-hidden">
                        <button onclick="openExportModal(<?= $unit_kerja['id'] ?>, 'pppk')" class="w-full px-4 py-3 text-left text-[10px] font-bold text-slate-700 uppercase tracking-widest hover:bg-slate-50 border-b border-slate-100 transition-colors">
                            <i class="fas fa-fw fa-user-tie mr-2 text-blue-600"></i> PPPK
                        </button>
                        <button onclick="openExportModal(<?= $unit_kerja['id'] ?>, 'pppk_pw')" class="w-full px-4 py-3 text-left text-[10px] font-bold text-slate-700 uppercase tracking-widest hover:bg-slate-50 transition-colors">
                            <i class="fas fa-fw fa-user-clock mr-2 text-amber-600"></i> Paruh Waktu
                        </button>
                    </div>
                </div>
                <button id="syncAllTteBtn" onclick="syncAllBsreStatus()" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg font-bold text-[10px] uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm">
                    <i class="fas fa-sync-alt mr-2 text-slate-700"></i> Sync TTE
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Informasi Unit Kerja -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 lg:p-8 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div class="flex items-center gap-6">
                <div class="w-16 h-16 bg-slate-100 border border-slate-200 rounded-xl flex items-center justify-center text-slate-700">
                    <i class="fas fa-building text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight"><?= esc($unit_kerja['nama_unit_kerja']) ?></h1>
            </div>

            <div class="flex gap-4 min-w-[360px]">
                <div class="flex-1 bg-slate-50 border border-slate-200 rounded-xl p-4 text-center">
                    <p class="text-[9px] font-bold text-slate-700 uppercase tracking-widest">Total Email</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($total_emails ?? 0, 0, ',', '.') ?></p>
                </div>
                <div class="flex-1 bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center">
                    <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest">Email Aktif</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($active_count ?? 0, 0, ',', '.') ?></p>
                </div>
                <div class="flex-1 bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
                    <p class="text-[9px] font-bold text-blue-600 uppercase tracking-widest">TTE Aktif</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1"><?= number_format($active_bsre_count ?? 0, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sub-Unit -->
    <?php if (!empty($child_units)): ?>
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <button onclick="toggleChildUnits()" class="w-full px-6 py-4 flex justify-between items-center hover:bg-slate-50 transition-all focus:outline-none">
                <div class="flex items-center gap-3">
                    <h6 class="text-[11px] font-bold text-slate-800 uppercase tracking-widest">Daftar Sub-Unit</h6>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] font-bold text-slate-700 uppercase tracking-widest bg-slate-100 px-2 py-0.5 rounded"><?= count($child_units) ?> Unit</span>
                    <i class="fas fa-chevron-down text-slate-700 text-[10px] transition-transform duration-300" id="childUnitsChevron"></i>
                </div>
            </button>
            <div id="childUnitsList" class="hidden px-6 pb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <?php foreach ($child_units as $child): ?>
                        <a href="<?= site_url('email/unit_kerja/' . $child['id']) ?>" class="p-3 bg-slate-50 border border-slate-200 rounded-lg hover:border-slate-800 transition-all no-underline">
                            <span class="text-[10px] font-bold text-slate-700 uppercase tracking-tight line-clamp-1"><?= esc($child['nama_unit_kerja']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Statistik Sertifikat -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <?php if (!empty($bsre_status_counts)): ?>
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h5 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Status TTE</h5>
                </div>
                <div class="p-6 flex flex-col sm:flex-row items-center gap-8 flex-grow">
                    <div class="w-full sm:w-1/2 flex justify-center">
                        <div id="bsreStatusChart" class="w-full max-w-[200px]"></div>
                    </div>
                    <div class="w-full sm:w-1/2 space-y-2 max-h-[200px] overflow-y-auto custom-scrollbar pr-2">
                        <?php foreach ($bsre_status_counts as $key => $data): ?>
                            <div class="p-2 bg-slate-50 border border-slate-200 rounded-lg flex justify-between items-center">
                                <div class="flex items-center truncate">
                                    <span class="w-2 h-2 rounded-full mr-2 chart-legend-dot shrink-0" data-status="<?= $key ?>"></span>
                                    <span class="text-[10px] font-bold text-slate-700 uppercase truncate"><?= esc($data['label']) ?></span>
                                </div>
                                <span class="text-xs font-bold text-slate-800"><?= number_format($data['count'], 0, ',', '.') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($status_asn_stats)): ?>
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h5 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Status ASN</h5>
                </div>
                <div class="p-6 flex flex-col sm:flex-row items-center gap-8 flex-grow">
                    <div class="w-full sm:w-1/2 flex justify-center">
                        <div id="asnStatusChart" class="w-full max-w-[200px]"></div>
                    </div>
                    <div class="w-full sm:w-1/2 space-y-2 max-h-[200px] overflow-y-auto custom-scrollbar pr-2">
                        <?php
                        foreach ($status_asn_stats as $index => $stat):
                            $label = strtoupper($stat['label']);
                            $bgClass = 'bg-slate-700';
                            if ($label === 'PNS') $bgClass = 'bg-blue-600';
                            elseif ($label === 'PPPK') $bgClass = 'bg-emerald-600';
                            elseif (strpos($label, 'PPPK PARUH WAKTU') !== false) $bgClass = 'bg-amber-500';
                        ?>
                            <div class="p-2 bg-slate-50 border border-slate-200 rounded-lg flex justify-between items-center">
                                <div class="flex items-center truncate">
                                    <span class="w-2 h-2 rounded-full mr-2 shrink-0 <?= $bgClass ?>"></span>
                                    <span class="text-[10px] font-bold text-slate-700 uppercase truncate"><?= esc($stat['label']) ?></span>
                                </div>
                                <span class="text-xs font-bold text-slate-800"><?= number_format($stat['count'], 0, ',', '.') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tabel Akun Email -->
    <div id="email-table-container" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-5">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= esc($search ?? '') ?>" class="block w-full pl-9 pr-3 py-2 bg-white border <?= !empty($search) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm transition-all" placeholder="Cari nama, NIP, atau NIK...">
                    </div>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Status ASN</label>
                    <select name="status_asn" class="block w-full px-3 py-2 bg-white border <?= !empty($status_asn) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua Status</option>
                        <?php foreach ($status_asn_options as $option): ?>
                            <option value="<?= esc($option['id']) ?>" <?= (($status_asn ?? '') == $option['id']) ? 'selected' : '' ?>><?= esc($option['nama_status_asn']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Status TTE</label>
                    <select name="bsre_status" class="block w-full px-3 py-2 bg-white border <?= !empty($bsre_status) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua Status</option>
                        <?php foreach ($bsre_status_options as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= ($bsre_status === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-bold text-xs uppercase tracking-widest transition-all">
                        <i class="fas fa-filter mr-2 text-white/80"></i> Filter
                    </button>
                    <a href="<?= site_url('email/unit_kerja/' . $unit_kerja['id']) ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-lg transition-all shadow-sm" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200">Email</th>
                        <th class="px-6 py-3 border-b border-slate-200">Jabatan / Status</th>
                        <th class="px-6 py-3 border-b border-slate-200">Status TTE</th>
                        <th class="px-6 py-3 border-b border-slate-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($emails)): ?>
                        <?php foreach ($emails as $email): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-800 lowercase leading-tight"><?= esc($email['email']) ?></span>
                                        <span class="text-[10px] font-bold text-slate-700 uppercase tracking-tight mt-0.5"><?= esc($email['name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-xs font-medium text-slate-700 uppercase tracking-tight"><?= esc($email['jabatan']) ?: '-' ?></span>
                                        <span class="text-[9px] font-bold text-slate-700 uppercase tracking-widest"><?= esc($email['status_asn']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div id="bsre-status-<?= esc($email['user']) ?>" data-email="<?= esc($email['email']) ?>">
                                        <?php
                                        $st = $email['bsre_status'] ?? '';
                                        $colorClass = 'bg-slate-50 text-slate-700 border-slate-200';
                                        $statusLabel = $st ?: 'NOT_SYNCED';

                                        if ($st === 'ISSUE') {
                                            $colorClass = 'bg-emerald-50 text-emerald-600 border-emerald-200';
                                        } elseif (in_array($st, ['EXPIRED', 'REVOKE', 'SUSPEND'])) {
                                            $colorClass = 'bg-red-50 text-red-600 border-red-200';
                                        } elseif (in_array($st, ['WAITING_FOR_VERIFICATION', 'RENEW'])) {
                                            $colorClass = 'bg-amber-50 text-amber-500 border-amber-200';
                                        } elseif ($st === 'NEW') {
                                            $colorClass = 'bg-blue-50 text-blue-600 border-blue-200';
                                        }
                                        ?>
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border <?= $colorClass ?>"><?= $statusLabel ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-700 hover:text-slate-800 shadow-sm transition-all" title="Detail">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <?php if (session()->get('role') === 'super_admin'): ?>
                                            <form action="<?= site_url('email/delete/' . $email['id']) ?>" method="post" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-700 hover:text-red-600 transition-all shadow-sm" title="Hapus">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-20 text-center">
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

        <?php if (!empty($pagination)): ?>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">
                    Menampilkan <span class="text-slate-800 font-bold"><?= count($emails) ?></span> dari <span class="text-slate-800 font-bold"><?= number_format($total_emails ?? 0, 0, ',', '.') ?></span> data
                </div>
                <div class="pagination-modern">
                    <?= $pagination->links() ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Progress Batch (Hidden by default) -->
<div id="exportProgressModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-slate-800/50 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6 border border-slate-200">
            <h4 class="text-sm font-bold text-slate-800 uppercase tracking-tight mb-4">Pemrosesan Dokumen PK Massal</h4>
            <div class="space-y-4">
                <div class="w-full bg-slate-100 rounded-full h-2">
                    <div id="exportProgressBar" class="bg-blue-600 h-full rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <p id="exportStatusText" class="text-center text-[10px] font-bold text-slate-700 uppercase tracking-widest">Memulai...</p>
            </div>
        </div>
    </div>
</div>

<style>
    /* Pagination Style */
    .pagination-modern ul {
        @apply flex items-center gap-1;
    }

    .pagination-modern li a,
    .pagination-modern li span {
        @apply inline-flex items-center justify-center min-w-[28px] h-[28px] rounded bg-white border border-slate-200 text-[10px] font-bold text-slate-700 transition-all hover:border-slate-800 hover:text-slate-800 shadow-sm no-underline px-1.5;
    }

    .pagination-modern li.active span {
        @apply bg-slate-800 border-slate-800 text-white shadow-sm;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    function toggleChildUnits() {
        const list = document.getElementById('childUnitsList');
        const chevron = document.getElementById('childUnitsChevron');
        list.classList.toggle('hidden');
        chevron.style.transform = list.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
    }

    document.addEventListener("DOMContentLoaded", function() {
        <?php if (!empty($bsre_status_counts)): ?>
            const tteColorMap = {
                'ISSUE': '#059669', // emerald-600
                'EXPIRED': '#dc2626', // red-600
                'REVOKE': '#dc2626', // red-600
                'SUSPEND': '#dc2626', // red-600
                'RENEW': '#f59e0b', // amber-500
                'WAITING_FOR_VERIFICATION': '#f59e0b', // amber-500
                'NEW': '#2563eb', // blue-600
                'NOT_REGISTERED': '#334155', // slate-700
                'NO_CERTIFICATE': '#475569', // slate-600
                'not_synced': '#94a3b8' // slate-400
            };
            const chartData = <?= json_encode($bsre_status_counts) ?>;
            const labels = [],
                series = [],
                colors = [];

            Object.keys(chartData).forEach(key => {
                labels.push(chartData[key].label);
                series.push(chartData[key].count);
                colors.push(tteColorMap[key] || '#e2e8f0');
                const dot = document.querySelector(`.chart-legend-dot[data-status="${key}"]`);
                if (dot) dot.style.backgroundColor = tteColorMap[key] || '#e2e8f0';
            });

            new ApexCharts(document.querySelector("#bsreStatusChart"), {
                series: series,
                labels: labels,
                colors: colors,
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
                                    color: '#334155', // slate-700
                                    offsetY: -5
                                },
                                value: {
                                    show: true,
                                    fontSize: '16px',
                                    fontWeight: 700,
                                    color: '#1e293b', // slate-800
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
                                    color: '#334155', // slate-700
                                    formatter: function(w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString('id-ID')
                                    }
                                }
                            }
                        }
                    }
                }
            }).render();
        <?php endif; ?>

        <?php if (!empty($status_asn_stats)): ?>
            const asnStats = <?= json_encode($status_asn_stats) ?>;
            const asnColors = asnStats.map(s => {
                const label = s.label.toUpperCase();
                if (label === 'PNS') return '#2563eb'; // blue-600
                if (label === 'PPPK') return '#059669'; // emerald-600
                if (label.includes('PPPK PARUH WAKTU')) return '#f59e0b'; // amber-500
                return '#334155'; // slate-700
            });

            new ApexCharts(document.querySelector("#asnStatusChart"), {
                series: asnStats.map(s => s.count),
                labels: asnStats.map(s => s.label),
                colors: asnColors,
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
                                    color: '#334155',
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
                                    color: '#334155',
                                    formatter: function(w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString('id-ID')
                                    }
                                }
                            }
                        }
                    }
                }
            }).render();
        <?php endif; ?>
    });

    function openExportModal(unitId, statusType = 'pppk') {
        const modal = document.getElementById('exportProgressModal');
        const bar = document.getElementById('exportProgressBar');
        const status = document.getElementById('exportStatusText');
        modal.classList.remove('hidden');

        let queryParams = `pk_type=${statusType}`;
        const currentQuery = '<?= $_SERVER['QUERY_STRING'] ?>';
        if (currentQuery) {
            queryParams += `&${currentQuery}`;
        }

        fetch(`<?= site_url('email/api_unit_emails/') ?>${unitId}?${queryParams}`)
            .then(r => r.json()).then(data => {
                if (!data.success) {
                    modal.classList.add('hidden');
                    return alert(data.message || 'Gagal mengambil data email.');
                }

                if (!data.emails || !data.emails.length) {
                    modal.classList.add('hidden');
                    return alert('Tidak ditemukan data PPPK atau PPPK Paruh Waktu di unit ini.');
                }

                const emails = data.emails;
                let processed = 0;
                const process = () => {
                    if (processed >= emails.length) {
                        status.innerText = 'MEMBUAT FILE ZIP...';
                        return fetch(`<?= site_url('email/api_download_zip/') ?>${unitId}`).then(r => r.json()).then(d => {
                            d.files.forEach((f, i) => setTimeout(() => window.location = `<?= site_url('email/download_zip_file/') ?>${f}`, i * 2000));
                            setTimeout(() => modal.classList.add('hidden'), d.files.length * 2000 + 1000);
                        });
                    }
                    const email = emails[processed];
                    fetch(`<?= site_url('email/api_generate_pdf') ?>`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `unit_id=${unitId}&email_id=${email.id}`
                    }).then(() => {
                        processed++;
                        const p = Math.round((processed / emails.length) * 100);
                        bar.style.width = p + '%';
                        status.innerText = `PROSES: ${processed}/${emails.length}`;
                        setTimeout(process, 100);
                    });
                };
                process();
            });
    }

    async function syncAllBsreStatus() {
        const containers = document.querySelectorAll('[id^="bsre-status-"]');
        if (!containers.length) return;
        
        if (!confirm(`Sinkronkan status sertifikat untuk ${containers.length} akun dalam unit ini?`)) {
            return;
        }

        const syncBtn = document.getElementById('syncAllTteBtn');
        const originalBtnContent = syncBtn.innerHTML;

        // 1. Scroll ke tabel secara smooth
        const tableContainer = document.getElementById('email-table-container');
        if (tableContainer) {
            tableContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // 2. Disable tombol dan beri feedback visual
        syncBtn.disabled = true;
        syncBtn.classList.add('opacity-75', 'cursor-not-allowed');
        syncBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Syncing...';

        // 3. Proses secara sekuensial untuk menghindari load server berlebih
        let processed = 0;
        for (const container of containers) {
            const email = container.getAttribute('data-email');
            const originalContent = container.innerHTML;
            
            // Scroll ke container yang sedang diproses
            container.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Set loading state untuk baris ini
            container.innerHTML = '<i class="fas fa-spinner fa-spin text-blue-600 text-[10px]"></i>';
            
            try {
                const response = await fetch('<?= site_url('bsre/sync-status') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'email=' + encodeURIComponent(email)
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    const colorClass = getJsStatusColor(data.bsre_status);
                    container.innerHTML = `<span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border ${colorClass}">${data.bsre_status}</span>`;
                } else {
                    container.innerHTML = originalContent;
                }
            } catch (error) {
                console.error('Sync failed for ' + email, error);
                container.innerHTML = originalContent;
            }

            processed++;
            // Optional: update small progress indicator if needed
        }

        // 4. Restore tombol
        syncBtn.disabled = false;
        syncBtn.classList.remove('opacity-75', 'cursor-not-allowed');
        syncBtn.innerHTML = originalBtnContent;
        
        alert(`Selesai! ${processed} akun telah disinkronkan.`);
    }
</script>
<?= $this->endSection() ?>