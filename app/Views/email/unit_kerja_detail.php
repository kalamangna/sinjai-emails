<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <!-- Navigasi & Aksi -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <a href="<?= site_url('email') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all shadow-sm no-underline group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> Kembali
        </a>
        <div class="flex flex-wrap gap-2">
            <?php
            $queryString = $_SERVER['QUERY_STRING'] ?? '';
            $exportCsvUrl = site_url('email/export_unit_kerja_csv/' . $unit_kerja['id']) . ($queryString ? '?' . $queryString : '');
            ?>
            <a href="<?= $exportCsvUrl ?>" class="inline-flex items-center justify-center px-3 py-2 bg-emerald-600 text-white rounded-lg font-bold text-[10px] uppercase tracking-wider hover:bg-emerald-700 active:bg-emerald-800 transition-all shadow-sm no-underline">
                <i class="fas fa-file-csv mr-1.5"></i> CSV
            </a>
            <a href="<?= site_url('email/export_account_detail_pdf/' . $unit_kerja['id']) ?>" class="inline-flex items-center justify-center px-3 py-2 bg-slate-700 text-white rounded-lg font-bold text-[10px] uppercase tracking-wider hover:bg-slate-800 active:bg-slate-900 transition-all shadow-sm no-underline">
                <i class="fas fa-user-shield mr-1.5"></i> Akun PDF
            </a>
            <a href="<?= site_url('email/export_unit_kerja_pdf/' . $unit_kerja['id']) ?>" class="inline-flex items-center justify-center px-3 py-2 bg-rose-600 text-white rounded-lg font-bold text-[10px] uppercase tracking-wider hover:bg-rose-700 active:bg-rose-800 transition-all shadow-sm no-underline">
                <i class="fas fa-file-pdf mr-1.5"></i> Status PDF
            </a>
            <button onclick="openExportModal(<?= $unit_kerja['id'] ?>)" class="inline-flex items-center justify-center px-3 py-2 bg-blue-600 text-white rounded-lg font-bold text-[10px] uppercase tracking-wider hover:bg-blue-700 active:bg-blue-800 transition-all shadow-sm focus:outline-none">
                <i class="fas fa-file-contract mr-1.5"></i> Batch PK
            </button>
            <button onclick="syncAllBsreStatus()" class="inline-flex items-center justify-center px-3 py-2 bg-amber-500 text-white rounded-lg font-bold text-[10px] uppercase tracking-wider hover:bg-amber-600 active:bg-amber-700 transition-all shadow-sm focus:outline-none">
                <i class="fas fa-sync-alt mr-1.5"></i> Sync TTE
            </button>
        </div>
    </div>

    <!-- Profil Unit Kerja -->
    <div class="bg-white border border-slate-200 rounded-xl p-8 shadow-sm relative overflow-hidden group">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-center group-hover:scale-105 transition-transform duration-500 shadow-sm">
                    <i class="fas fa-building text-blue-600 text-2xl"></i>
                </div>
                <div class="space-y-1">
                    <h2 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight leading-none uppercase"><?= esc($unit_kerja['nama_unit_kerja']) ?></h2>
                    <?php if ($unit_kerja['parent_name'] ?? ''): ?>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest flex items-center">
                            <i class="fas fa-level-up-alt rotate-90 mr-2 opacity-50"></i><?= esc($unit_kerja['parent_name']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex gap-8 bg-slate-50 px-8 py-4 rounded-xl border border-slate-100">
                <div class="text-center space-y-0.5">
                    <div class="text-xl font-bold text-slate-900"><?= number_format($total_emails ?? 0) ?></div>
                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Total</div>
                </div>
                <div class="w-px bg-slate-200 h-8 self-center"></div>
                <div class="text-center space-y-0.5">
                    <div class="text-xl font-bold text-emerald-600"><?= number_format($active_count ?? 0) ?></div>
                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Aktif</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sub-Unit -->
    <?php if (!empty($child_units)): ?>
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <button onclick="toggleChildUnits()" class="w-full bg-slate-50 px-6 py-3 border-b border-slate-200 flex justify-between items-center group focus:outline-none">
                <h6 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest flex items-center">
                    <i class="fas fa-sitemap mr-2 text-blue-500 opacity-50"></i>Sub-Unit
                </h6>
                <i class="fas fa-chevron-down text-slate-400 text-[10px] transition-transform duration-300 group-hover:text-slate-600" id="childUnitsChevron"></i>
            </button>
            <div id="childUnitsList" class="p-6 hidden">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <?php foreach ($child_units as $child): ?>
                        <a href="<?= site_url('email/unit_kerja/' . $child['id']) ?>" class="p-3 bg-white border border-slate-100 rounded-lg hover:border-blue-300 hover:bg-blue-50/30 transition-all no-underline">
                            <span class="text-[11px] font-bold text-slate-600 uppercase tracking-tight"><?= esc($child['nama_unit_kerja']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Analisis TTE -->
    <?php if (!empty($bsre_status_counts)): ?>
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                <h6 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest flex items-center">
                    <i class="fas fa-fingerprint mr-2 text-blue-500 opacity-50"></i>Sertifikat
                </h6>
            </div>
            <div class="p-6 flex flex-col lg:flex-row items-center gap-10">
                <div class="w-full lg:w-1/3 flex justify-center">
                    <div id="bsreStatusChart" class="w-full max-w-[240px]"></div>
                </div>
                <div class="w-full lg:w-2/3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <?php foreach ($bsre_status_counts as $key => $data): ?>
                        <div class="p-3 bg-slate-50 border border-slate-100 rounded-lg flex justify-between items-center group hover:bg-white hover:border-slate-200 transition-all shadow-sm">
                            <div class="flex items-center">
                                <span class="w-2 h-2 rounded-full mr-2 chart-legend-dot" data-status="<?= $key ?>"></span>
                                <span class="text-[10px] font-bold text-slate-500 uppercase"><?= esc($data['label']) ?></span>
                            </div>
                            <span class="text-[11px] font-extrabold text-slate-900"><?= number_format($data['count']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Filter & Search -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-4 lg:col-span-5">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Pencarian</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" name="search" value="<?= esc($search ?? '') ?>" class="block w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all" placeholder="Nama, Email, NIK, NIP...">
                </div>
            </div>
            <div class="md:col-span-3 lg:col-span-3">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Status ASN</label>
                <select name="status_asn" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                    <option value="">Semua Status</option>
                    <?php foreach ($status_asn_options as $option): ?>
                        <option value="<?= esc($option['id']) ?>" <?= (($status_asn ?? '') == $option['id']) ? 'selected' : '' ?>><?= esc($option['nama_status_asn']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-3 lg:col-span-2">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Sertifikat</label>
                <select name="bsre_status" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                    <option value="">Semua Status</option>
                    <?php foreach ($bsre_status_options as $key => $label): ?>
                        <option value="<?= esc($key) ?>" <?= ($bsre_status === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-2 lg:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-slate-800 border border-transparent rounded-lg font-bold text-[11px] text-white uppercase tracking-wider hover:bg-slate-900 active:bg-slate-950 transition-all shadow-sm focus:outline-none">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                <a href="<?= site_url('email/unit_kerja/' . $unit_kerja['id']) ?>" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-lg font-bold text-[11px] text-slate-600 uppercase tracking-wider hover:bg-slate-50 hover:text-slate-900 active:bg-slate-100 transition-all shadow-sm no-underline" title="Reset Filter">
                    <i class="fas fa-redo mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Akun</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Jabatan</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sertifikat</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php if (!empty($emails)): ?>
                        <?php foreach ($emails as $email): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-9 h-9 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center mr-3 group-hover:bg-blue-50 transition-all">
                                            <i class="fas fa-envelope text-slate-400 group-hover:text-blue-600 text-sm"></i>
                                        </div>
                                        <div>
                                            <div class="text-[13px] font-bold text-slate-900 lowercase leading-none mb-1"><?= esc($email['email']) ?></div>
                                            <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide opacity-80"><?= esc($email['name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-[11px] font-bold text-slate-700 leading-tight mb-0.5"><?= esc($email['jabatan']) ?: '-' ?></div>
                                    <div class="text-[10px] font-semibold text-blue-600 uppercase tracking-tight"><?= esc($email['status_asn']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div id="bsre-status-<?= esc($email['user']) ?>" data-email="<?= esc($email['email']) ?>">
                                        <?php
                                        $st = $email['bsre_status'] ?? '';
                                        $cols = [
                                            'ISSUE' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                            'EXPIRED' => 'bg-rose-50 text-rose-700 border-rose-100',
                                            'RENEW' => 'bg-blue-50 text-blue-700 border-blue-100',
                                            'WAITING_FOR_VERIFICATION' => 'bg-amber-50 text-amber-700 border-amber-100',
                                            'NEW' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                            'NO_CERTIFICATE' => 'bg-slate-100 text-slate-600 border-slate-200',
                                        ];
                                        $c = $cols[$st] ?? 'bg-slate-50 text-slate-400 border-slate-100';
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[9px] font-bold uppercase tracking-wider border <?= $c ?>">
                                            <?= $st ?: 'NOT SYNCED' ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 transition-all no-underline shadow-sm">
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

        <?php if (!empty($pagination)): ?>
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                    Showing <span class="text-slate-900"><?= count($emails) ?></span> of <span class="text-slate-900"><?= number_format($total_emails ?? 0) ?></span> accounts
                </div>
                <div class="pagination-container">
                    <?= $pagination->links() ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Progress PK -->
<div id="exportProgressModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog">
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"></div>
        <div class="inline-block bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full border border-slate-200 relative z-10">
            <div class="p-8 space-y-6">
                <div class="text-center space-y-2">
                    <h3 class="text-lg font-bold text-slate-900 uppercase tracking-tight">Generating PDF Documents</h3>
                    <p id="exportStatusText" class="text-xs text-slate-500 font-medium italic">Menyiapkan antrian data...</p>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-4 overflow-hidden">
                    <div id="exportProgressBar" class="bg-blue-600 h-full rounded-full transition-all duration-300 flex items-center justify-center text-[8px] font-bold text-white" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
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
                'ISSUE': '#10b981',
                'EXPIRED': '#f43f5e',
                'RENEW': '#3b82f6',
                'WAITING_FOR_VERIFICATION': '#f59e0b',
                'NEW': '#6366f1',
                'NO_CERTIFICATE': '#94a3b8',
                'not_synced': '#cbd5e1'
            };
            const chartData = <?= json_encode($bsre_status_counts) ?>;
            const labels = [],
                series = [],
                colors = [];

            Object.keys(chartData).forEach(key => {
                labels.push(chartData[key].label);
                series.push(chartData[key].count);
                colors.push(tteColorMap[key] || '#cbd5e1');
                const dot = document.querySelector(`.chart-legend-dot[data-status="${key}"]`);
                if (dot) dot.style.backgroundColor = tteColorMap[key] || '#cbd5e1';
            });

            new ApexCharts(document.querySelector("#bsreStatusChart"), {
                series: series,
                labels: labels,
                colors: colors,
                chart: {
                    type: 'donut',
                    height: 240,
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
                                    fontSize: '20px',
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
                },
                tooltip: {
                    theme: 'light'
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
                    return alert('Data tidak ditemukan atau bukan PPPK Paruh Waktu.');
                }
                const emails = data.emails;
                let processed = 0;
                const process = () => {
                    if (processed >= emails.length) {
                        status.innerText = 'Compressing to ZIP...';
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
                        bar.innerText = p + '%';
                        status.innerText = `Processing: ${email.name} (${processed}/${emails.length})`;
                        setTimeout(process, 100);
                    });
                };
                process();
            });
    }

    function syncAllBsreStatus() {
        const containers = document.querySelectorAll('[id^="bsre-status-"]');
        if (!containers.length || !confirm('Sinkronkan status TTE untuk semua akun yang tampil?')) return;

        containers.forEach((c, i) => {
            setTimeout(() => {
                const user = c.id.replace('bsre-status-', '');
                const email = c.getAttribute('data-email');
                c.innerHTML = '<i class="fas fa-spinner fa-spin text-slate-400 text-[10px]"></i>';
                fetch('<?= site_url('bsre/sync-status') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'email=' + encodeURIComponent(email)
                }).then(r => r.json()).then(d => {
                    if (d.status === 'success') {
                        const colors = {
                            'ISSUE': 'bg-emerald-50 text-emerald-700 border-emerald-100',
                            'EXPIRED': 'bg-rose-50 text-rose-700 border-rose-100',
                            'RENEW': 'bg-blue-50 text-blue-700 border-blue-100',
                            'WAITING_FOR_VERIFICATION': 'bg-amber-50 text-amber-700 border-amber-100',
                            'NEW': 'bg-indigo-50 text-indigo-700 border-indigo-100',
                            'NO_CERTIFICATE': 'bg-slate-100 text-slate-600 border-slate-200',
                        };
                        const cls = colors[d.bsre_status] || 'bg-slate-50 text-slate-400 border-slate-100';
                        c.innerHTML = `<span class="inline-flex items-center px-2.5 py-1 rounded-md text-[9px] font-bold uppercase tracking-wider border ${cls}">${d.bsre_status}</span>`;
                    }
                });
            }, i * 250);
        });
    }
</script>
<?= $this->endSection() ?>