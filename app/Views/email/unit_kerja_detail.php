<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-10">
    <!-- Actions -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <a href="<?= site_url('email/unit_kerja') ?>" class="inline-flex items-center justify-center px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-600 uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm no-underline group">
            <i class="fas fa-arrow-left mr-2.5 group-hover:-translate-x-1 transition-transform"></i> Kembali
        </a>
        
        <div class="flex flex-wrap items-center gap-3">
            <?php
            $queryString = $_SERVER['QUERY_STRING'] ?? '';
            $exportCsvUrl = site_url('email/export_unit_kerja_csv/' . $unit_kerja['id']) . ($queryString ? '?' . $queryString : '');
            ?>
            <div class="flex items-center bg-white p-1.5 border border-slate-200 rounded-2xl shadow-sm">
                <a href="<?= $exportCsvUrl ?>" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white rounded-xl font-black text-[10px] uppercase tracking-widest transition-all no-underline group">
                    <i class="fas fa-file-csv mr-1.5 group-hover:scale-110 transition-transform"></i> CSV
                </a>
                <a href="<?= site_url('email/export_account_detail_pdf/' . $unit_kerja['id']) ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white text-slate-600 hover:bg-slate-900 hover:text-white rounded-xl font-black text-[10px] uppercase tracking-widest transition-all no-underline group">
                    <i class="fas fa-user-shield mr-1.5 group-hover:rotate-12 transition-transform"></i> Akun PDF
                </a>
                <a href="<?= site_url('email/export_unit_kerja_pdf/' . $unit_kerja['id']) ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white text-slate-600 hover:bg-rose-600 hover:text-white rounded-xl font-black text-[10px] uppercase tracking-widest transition-all no-underline group">
                    <i class="fas fa-file-pdf mr-1.5 group-hover:scale-110 transition-transform"></i> Status PDF
                </a>
            </div>

            <?php if (session()->get('role') === 'super_admin'): ?>
            <button onclick="openExportModal(<?= $unit_kerja['id'] ?>)" class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-blue-700 transition-all shadow-md group">
                <i class="fas fa-file-contract mr-2 group-hover:rotate-12 transition-transform"></i> Batch PK
            </button>
            <button onclick="syncAllBsreStatus()" class="inline-flex items-center justify-center px-5 py-2.5 bg-amber-500 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-amber-600 transition-all shadow-md group">
                <i class="fas fa-sync-alt mr-2 group-hover:rotate-180 transition-transform duration-500"></i> Sync
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Unit Info -->
    <div class="bg-white border border-slate-200 rounded-[2.5rem] p-8 lg:p-12 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 bg-emerald-50 rounded-full blur-3xl opacity-50"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-10">
            <div class="flex items-center gap-8">
                <div class="w-20 h-20 lg:w-24 lg:h-24 bg-emerald-600 rounded-3xl flex items-center justify-center shadow-xl text-white shrink-0">
                    <i class="fas fa-building text-3xl lg:text-4xl"></i>
                </div>
                <div>
                    <h2 class="text-xl lg:text-3xl font-black text-slate-900 tracking-tight uppercase max-w-2xl"><?= esc($unit_kerja['nama_unit_kerja']) ?></h2>
                    <?php if ($unit_kerja['parent_name'] ?? ''): ?>
                        <div class="mt-3 flex items-center">
                            <span class="inline-flex items-center px-3 py-1 bg-slate-100 text-slate-500 rounded-lg text-[9px] font-black uppercase tracking-widest">
                                <?= esc($unit_kerja['parent_name']) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 bg-slate-900 rounded-[2rem] p-6 lg:p-8 min-w-[280px]">
                <div class="text-center space-y-1 border-r border-white/10 pr-6">
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Total Email</p>
                    <p class="text-3xl font-black text-white tracking-tighter"><?= number_format($total_emails ?? 0) ?></p>
                </div>
                <div class="text-center space-y-1 pl-6">
                    <p class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">Sertifikat</p>
                    <p class="text-3xl font-black text-emerald-500 tracking-tighter"><?= number_format($active_count ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sub-Units -->
    <?php if (!empty($child_units)): ?>
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
            <button onclick="toggleChildUnits()" class="w-full px-8 py-5 flex justify-between items-center group focus:outline-none hover:bg-slate-50 transition-all">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center text-xs">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <h6 class="text-[11px] font-black text-slate-900 uppercase tracking-widest">Sub-Unit</h6>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-100 px-2 py-0.5 rounded-lg group-hover:bg-indigo-600 group-hover:text-white transition-all"><?= count($child_units) ?> Unit</span>
                    <i class="fas fa-chevron-down text-slate-300 text-[10px] transition-transform duration-500 group-hover:text-slate-900" id="childUnitsChevron"></i>
                </div>
            </button>
            <div id="childUnitsList" class="hidden px-8 pb-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <?php foreach ($child_units as $child): ?>
                        <a href="<?= site_url('email/unit_kerja/' . $child['id']) ?>" class="p-4 bg-slate-50 border border-slate-100 rounded-2xl group hover:bg-indigo-600 transition-all no-underline shadow-sm">
                            <span class="text-[11px] font-black text-slate-600 uppercase tracking-tight group-hover:text-white transition-colors line-clamp-1"><?= esc($child['nama_unit_kerja']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- TTE Analysis -->
    <?php if (!empty($bsre_status_counts)): ?>
        <div class="bg-white border border-slate-200 rounded-[2.5rem] shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white text-xs">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <h5 class="text-xs font-black text-slate-900 uppercase tracking-widest">Sertifikat</h5>
                </div>
            </div>
            <div class="p-8 flex flex-col xl:flex-row items-center gap-12">
                <div class="w-full xl:w-1/3 flex justify-center">
                    <div id="bsreStatusChart" class="w-full max-w-[280px]"></div>
                </div>
                <div class="w-full xl:w-2/3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($bsre_status_counts as $key => $data): ?>
                        <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl flex justify-between items-center group hover:bg-emerald-600 transition-all duration-300">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded-full mr-3 border-2 border-white shadow-sm chart-legend-dot" data-status="<?= $key ?>"></span>
                                <span class="text-[10px] font-black text-slate-500 uppercase group-hover:text-white transition-colors"><?= esc($data['label']) ?></span>
                            </div>
                            <span class="text-[12px] font-black text-slate-900 bg-white px-2 py-0.5 rounded-lg group-hover:text-emerald-600 transition-all shadow-sm"><?= number_format($data['count']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="lg:col-span-12 bg-white border border-slate-200 rounded-[2.5rem] shadow-sm overflow-hidden">
        <div class="p-8 bg-slate-50/50 border-b border-slate-100">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                <div class="md:col-span-4 lg:col-span-5">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 ml-1">Cari</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                            <i class="fas fa-search text-sm"></i>
                        </span>
                        <input type="text" name="search" value="<?= esc($search ?? '') ?>" class="block w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-semibold text-slate-700 transition-all placeholder:text-slate-400 shadow-inner" placeholder="Cari...">
                    </div>
                </div>
                <div class="md:col-span-3 lg:col-span-3">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 ml-1">Status ASN</label>
                    <select name="status_asn" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-semibold text-slate-700 transition-all appearance-none cursor-pointer">
                        <option value="">Semua</option>
                        <?php foreach ($status_asn_options as $option): ?>
                            <option value="<?= esc($option['id']) ?>" <?= (($status_asn ?? '') == $option['id']) ? 'selected' : '' ?>><?= esc($option['nama_status_asn']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-3 lg:col-span-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 ml-1">Sertifikat</label>
                    <select name="bsre_status" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-semibold text-slate-700 transition-all appearance-none cursor-pointer">
                        <option value="">Semua</option>
                        <?php foreach ($bsre_status_options as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= ($bsre_status === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2 lg:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center h-[46px] bg-slate-900 hover:bg-emerald-600 text-white rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all shadow-md">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="<?= site_url('email/unit_kerja/' . $unit_kerja['id']) ?>" class="inline-flex items-center justify-center w-[46px] h-[46px] bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 rounded-xl transition-all shadow-sm" title="Reset">
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
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest w-40">Sertifikat</th>
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
                                            <div class="text-[13px] font-bold text-slate-900 lowercase leading-tight"><?= esc($email['email']) ?></div>
                                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1 group-hover:text-slate-600"><?= esc($email['name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-[12px] font-black text-slate-700 leading-tight mb-1.5 group-hover:text-emerald-700 transition-colors"><?= esc($email['jabatan']) ?: '-' ?></div>
                                    <span class="text-[9px] font-black text-blue-600 uppercase tracking-widest bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-100"><?= esc($email['status_asn']) ?></span>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div id="bsre-status-<?= esc($email['user']) ?>" data-email="<?= esc($email['email']) ?>">
                                        <?php
                                        $st = $email['bsre_status'] ?? '';
                                        $type = 'neutral';
                                        if ($st === 'ISSUE') $type = 'success';
                                        elseif (in_array($st, ['EXPIRED', 'REVOKE', 'SUSPEND'])) $type = 'danger';
                                        elseif (in_array($st, ['WAITING_FOR_VERIFICATION', 'RENEW'])) $type = 'warning';
                                        elseif ($st === 'NEW') $type = 'info';
                                        
                                        echo view('components/badge', ['label' => $st ?: 'Belum Sync', 'type' => $type, 'rounded' => true]);
                                        ?>
                                    </div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-center">
                                    <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 transition-all shadow-sm">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center text-slate-400 uppercase text-xs font-bold italic tracking-widest">Kosong</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($pagination)): ?>
            <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    Showing <span class="text-slate-900 bg-white px-2 py-1 rounded-md shadow-sm"><?= count($emails) ?></span> of <span class="text-slate-900 bg-white px-2 py-1 rounded-md shadow-sm"><?= number_format($total_emails ?? 0) ?></span> entries
                </div>
                <div class="pagination-modern">
                    <?= $pagination->links() ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Modern Pagination Styling */
    .pagination-modern ul { @apply flex items-center gap-2; }
    .pagination-modern li a, .pagination-modern li span { 
        @apply inline-flex items-center justify-center min-w-[36px] h-[36px] rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-600 transition-all hover:border-emerald-500 hover:text-emerald-600 shadow-sm no-underline px-2;
    }
    .pagination-modern li.active span { 
        @apply bg-emerald-600 border-emerald-600 text-white shadow-lg shadow-emerald-200;
    }
</style>

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
                    height: 280,
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
                },
                tooltip: { theme: 'light' }
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
                    return alert('Gagal.');
                }
                const emails = data.emails;
                let processed = 0;
                const process = () => {
                    if (processed >= emails.length) {
                        status.innerText = 'ZIP...';
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
                        status.innerText = `Proses: ${processed}/${emails.length}`;
                        setTimeout(process, 100);
                    });
                };
                process();
            });
    }

    function syncAllBsreStatus() {
        const containers = document.querySelectorAll('[id^="bsre-status-"]');
        if (!containers.length || !confirm('Sync?')) return;

        containers.forEach((c, i) => {
            setTimeout(() => {
                const user = c.id.replace('bsre-status-', '');
                const email = c.getAttribute('data-email');
                c.innerHTML = '<div class="flex items-center gap-2"><i class="fas fa-spinner fa-spin text-emerald-500 text-[10px]"></i></div>';
                fetch('<?= site_url('bsre/sync-status') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'email=' + encodeURIComponent(email)
                }).then(r => r.json()).then(d => {
                    if (d.status === 'success') {
                        let type = 'neutral';
                        if (d.bsre_status === 'ISSUE') type = 'success';
                        else if (['EXPIRED', 'REVOKE', 'SUSPEND'].includes(d.bsre_status)) type = 'danger';
                        else if (['WAITING_FOR_VERIFICATION', 'RENEW'].includes(d.bsre_status)) type = 'warning';
                        else if (d.bsre_status === 'NEW') type = 'info';

                        const colorClasses = {
                            'success': 'bg-emerald-50 text-emerald-700 border-emerald-100',
                            'info': 'bg-blue-50 text-blue-700 border-blue-100',
                            'warning': 'bg-amber-50 text-amber-700 border-amber-100',
                            'danger': 'bg-rose-50 text-rose-700 border-rose-100',
                            'neutral': 'bg-slate-50 text-slate-700 border-slate-100'
                        };
                        const cls = colorClasses[type];
                        c.innerHTML = `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider border ${cls}">${d.bsre_status}</span>`;
                    }
                });
            }, i * 250);
        });
    }
</script>
<?= $this->endSection() ?>