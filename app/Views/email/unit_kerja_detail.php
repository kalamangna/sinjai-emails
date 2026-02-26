<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Navigasi dan Aksi -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <a href="<?= site_url('email/unit_kerja') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-50 transition-all text-xs uppercase tracking-widest no-underline shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>

        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= site_url('email/export_unit_kerja_csv/' . $unit_kerja['id']) . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-bold text-[10px] uppercase tracking-widest no-underline shadow-sm">
                <i class="fas fa-file-csv mr-2 text-green-600"></i> Unduh CSV
            </a>
            <a href="<?= site_url('email/export_account_detail_pdf/' . $unit_kerja['id']) ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-bold text-[10px] uppercase tracking-widest no-underline shadow-sm">
                <i class="fas fa-user-shield mr-2 text-blue-600"></i> Akun PDF
            </a>
            <a href="<?= site_url('email/export_unit_kerja_pdf/' . $unit_kerja['id']) ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-bold text-[10px] uppercase tracking-widest no-underline shadow-sm">
                <i class="fas fa-file-pdf mr-2 text-red-600"></i> Status PDF
            </a>

            <?php if (session()->get('role') === 'super_admin'): ?>
                <button onclick="openExportModal(<?= $unit_kerja['id'] ?>)" class="inline-flex items-center justify-center px-4 py-2 bg-gray-900 text-white rounded-lg font-bold text-[10px] uppercase tracking-widest hover:bg-gray-800 transition-all shadow-sm">
                    <i class="fas fa-file-contract mr-2"></i> Batch PK
                </button>
                <button onclick="syncAllBsreStatus()" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg font-bold text-[10px] uppercase tracking-widest hover:bg-gray-50 transition-all shadow-sm">
                    <i class="fas fa-sync-alt mr-2"></i> Sync TTE
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Informasi Unit Kerja -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 lg:p-8 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div class="flex items-center gap-6">
                <div class="w-16 h-16 bg-gray-100 border border-gray-200 rounded-xl flex items-center justify-center text-gray-400">
                    <i class="fas fa-building text-2xl"></i>
                </div>
                <h1 class="text-2xl font-semibold text-gray-900 uppercase tracking-tight"><?= esc($unit_kerja['nama_unit_kerja']) ?></h1>
            </div>

            <div class="flex gap-4 min-w-[240px]">
                <div class="flex-1 bg-gray-50 border border-gray-100 rounded-xl p-4 text-center">
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Total Email</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($total_emails ?? 0) ?></p>
                </div>
                <div class="flex-1 bg-gray-900 border border-gray-900 rounded-xl p-4 text-center">
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">TTE Aktif</p>
                    <p class="text-2xl font-bold text-white mt-1"><?= number_format($active_count ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sub-Unit -->
    <?php if (!empty($child_units)): ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <button onclick="toggleChildUnits()" class="w-full px-6 py-4 flex justify-between items-center hover:bg-gray-50 transition-all focus:outline-none">
                <div class="flex items-center gap-3">
                    <h6 class="text-[11px] font-bold text-gray-900 uppercase tracking-widest">Daftar Sub-Unit</h6>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest bg-gray-100 px-2 py-0.5 rounded"><?= count($child_units) ?> Unit</span>
                    <i class="fas fa-chevron-down text-gray-300 text-[10px] transition-transform duration-300" id="childUnitsChevron"></i>
                </div>
            </button>
            <div id="childUnitsList" class="hidden px-6 pb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <?php foreach ($child_units as $child): ?>
                        <a href="<?= site_url('email/unit_kerja/' . $child['id']) ?>" class="p-3 bg-gray-50 border border-gray-100 rounded-lg hover:border-gray-300 transition-all no-underline">
                            <span class="text-[10px] font-bold text-gray-600 uppercase tracking-tight line-clamp-1"><?= esc($child['nama_unit_kerja']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Statistik Sertifikat -->
    <?php if (!empty($bsre_status_counts)): ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h5 class="text-xs font-bold text-gray-900 uppercase tracking-tight">Status TTE</h5>
            </div>
            <div class="p-6 flex flex-col lg:flex-row items-center gap-8">
                <div class="w-full lg:w-1/3 flex justify-center">
                    <div id="bsreStatusChart" class="w-full max-w-[200px]"></div>
                </div>
                <div class="w-full lg:w-2/3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <?php foreach ($bsre_status_counts as $key => $data): ?>
                        <div class="p-3 bg-gray-50 border border-gray-100 rounded-lg flex justify-between items-center">
                            <div class="flex items-center">
                                <span class="w-2.5 h-2.5 rounded-full mr-2 chart-legend-dot" data-status="<?= $key ?>"></span>
                                <span class="text-[10px] font-bold text-gray-500 uppercase"><?= esc($data['label']) ?></span>
                            </div>
                            <span class="text-xs font-bold text-gray-900"><?= number_format($data['count']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Tabel Akun Email -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= esc($search ?? '') ?>" class="block w-full pl-9 pr-3 py-2 bg-white border <?= !empty($search) ? 'border-gray-900 ring-1 ring-gray-900' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm" placeholder="Cari akun atau nama...">
                    </div>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status ASN</label>
                    <select name="status_asn" class="block w-full px-3 py-2 bg-white border <?= !empty($status_asn) ? 'border-gray-900 ring-1 ring-gray-900' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm appearance-none cursor-pointer">
                        <option value="">Semua Status</option>
                        <?php foreach ($status_asn_options as $option): ?>
                            <option value="<?= esc($option['id']) ?>" <?= (($status_asn ?? '') == $option['id']) ? 'selected' : '' ?>><?= esc($option['nama_status_asn']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status TTE</label>
                    <select name="bsre_status" class="block w-full px-3 py-2 bg-white border <?= !empty($bsre_status) ? 'border-gray-900 ring-1 ring-gray-900' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm appearance-none cursor-pointer">
                        <option value="">Semua Status</option>
                        <?php foreach ($bsre_status_options as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= ($bsre_status === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-lg font-bold text-xs uppercase tracking-widest transition-all">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="<?= site_url('email/unit_kerja/' . $unit_kerja['id']) ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg transition-all shadow-sm" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-gray-200">Email</th>
                        <th class="px-6 py-3 border-b border-gray-200">Jabatan / Status</th>
                        <th class="px-6 py-3 border-b border-gray-200">Status TTE</th>
                        <th class="px-6 py-3 border-b border-gray-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (!empty($emails)): ?>
                        <?php foreach ($emails as $email): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900 lowercase leading-tight"><?= esc($email['email']) ?></span>
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tight mt-0.5"><?= esc($email['name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-xs font-medium text-gray-700 uppercase tracking-tight"><?= esc($email['jabatan']) ?: '-' ?></span>
                                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest"><?= esc($email['status_asn']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div id="bsre-status-<?= esc($email['user']) ?>" data-email="<?= esc($email['email']) ?>">
                                        <?php
                                        $st = $email['bsre_status'] ?? '';
                                        $colorClass = 'bg-gray-100 text-gray-600 border-gray-200';
                                        $statusLabel = $st ?: 'NOT SYNCED';

                                        if ($st === 'ISSUE') {
                                            $colorClass = 'bg-green-50 text-green-700 border-green-100';
                                        } elseif (in_array($st, ['EXPIRED', 'REVOKE', 'SUSPEND'])) {
                                            $colorClass = 'bg-red-50 text-red-700 border-red-100';
                                        } elseif (in_array($st, ['WAITING_FOR_VERIFICATION', 'RENEW'])) {
                                            $colorClass = 'bg-amber-50 text-amber-700 border-amber-100';
                                        } elseif ($st === 'NEW') {
                                            $colorClass = 'bg-blue-50 text-blue-700 border-blue-100';
                                        }
                                        ?>
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border <?= $colorClass ?>"><?= $statusLabel ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-300 text-gray-400 hover:text-gray-900 shadow-sm" title="Detail">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <?php if (session()->get('role') === 'super_admin'): ?>
                                            <form action="<?= site_url('email/delete/' . $email['id']) ?>" method="post" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-300 text-gray-400 hover:text-red-600 transition-all shadow-sm" title="Hapus">
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
                            <td colspan="4" class="px-6 py-10 text-center text-gray-300 uppercase text-xs font-bold italic tracking-widest">Data Kosong</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($pagination)): ?>
            <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                    Menampilkan <span class="text-gray-900 font-bold"><?= count($emails) ?></span> dari <span class="text-gray-900 font-bold"><?= number_format($total_emails ?? 0) ?></span> data
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
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6 border border-gray-200">
            <h4 class="text-sm font-bold text-gray-900 uppercase tracking-tight mb-4">Pemrosesan Dokumen PK Massal</h4>
            <div class="space-y-4">
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div id="exportProgressBar" class="bg-gray-900 h-full rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <p id="exportStatusText" class="text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Memulai...</p>
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
        @apply inline-flex items-center justify-center min-w-[28px] h-[28px] rounded bg-white border border-gray-200 text-[10px] font-bold text-gray-600 transition-all hover:border-gray-400 hover:text-gray-900 shadow-sm no-underline px-1.5;
    }

    .pagination-modern li.active span {
        @apply bg-gray-900 border-gray-900 text-white shadow-sm;
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
                'ISSUE': '#111827',
                'EXPIRED': '#9ca3af',
                'RENEW': '#4b5563',
                'WAITING_FOR_VERIFICATION': '#d1d5db',
                'NEW': '#374151',
                'NO_CERTIFICATE': '#e5e7eb',
                'not_synced': '#f3f4f6'
            };
            const chartData = <?= json_encode($bsre_status_counts) ?>;
            const labels = [],
                series = [],
                colors = [];

            Object.keys(chartData).forEach(key => {
                labels.push(chartData[key].label);
                series.push(chartData[key].count);
                colors.push(tteColorMap[key] || '#f3f4f6');
                const dot = document.querySelector(`.chart-legend-dot[data-status="${key}"]`);
                if (dot) dot.style.backgroundColor = tteColorMap[key] || '#f3f4f6';
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
            }).render();
        <?php endif; ?>
    });

    function openExportModal(unitId) {
        const modal = document.getElementById('exportProgressModal');
        const bar = document.getElementById('exportProgressBar');
        const status = document.getElementById('exportStatusText');
        modal.classList.remove('hidden');

        fetch(`<?= site_url('email/api_unit_emails/') ?>${unitId}`)
            .then(r => r.json()).then(data => {
                if (!data.success || !data.emails.length) {
                    modal.classList.add('hidden');
                    return alert('Gagal mengambil data email.');
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

    function syncAllBsreStatus() {
        const containers = document.querySelectorAll('[id^="bsre-status-"]');
        if (!containers.length || !confirm('Sinkronkan semua status sertifikat dalam unit ini?')) return;

        containers.forEach((c, i) => {
            setTimeout(() => {
                const email = c.getAttribute('data-email');
                c.innerHTML = '<i class="fas fa-spinner fa-spin text-gray-300 text-[10px]"></i>';
                fetch('<?= site_url('bsre/sync-status') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'email=' + encodeURIComponent(email)
                }).then(r => r.json()).then(d => {
                    if (d.status === 'success') {
                        let colorClass = 'bg-gray-100 text-gray-600 border-gray-200';
                        if (d.bsre_status === 'ISSUE') colorClass = 'bg-green-50 text-green-700 border-green-100';
                        else if (['EXPIRED', 'REVOKE', 'SUSPEND'].includes(d.bsre_status)) colorClass = 'bg-red-50 text-red-700 border-red-100';
                        else if (['WAITING_FOR_VERIFICATION', 'RENEW'].includes(d.bsre_status)) colorClass = 'bg-amber-50 text-amber-700 border-amber-100';
                        else if (d.bsre_status === 'NEW') colorClass = 'bg-blue-50 text-blue-700 border-blue-100';

                        c.innerHTML = `<span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border ${colorClass}">${d.bsre_status}</span>`;
                    }
                });
            }, i * 200);
        });
    }
</script>
<?= $this->endSection() ?>