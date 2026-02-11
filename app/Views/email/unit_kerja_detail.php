<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-10">
    <!-- Navigasi & Aksi -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <a href="<?= site_url('email') ?>" class="inline-flex items-center px-6 py-3 bg-slate-900 border border-slate-800 rounded-2xl text-xs font-black text-slate-400 uppercase tracking-widest hover:bg-slate-800 hover:text-slate-200 transition-all shadow-xl no-underline group">
            <i class="fas fa-arrow-left mr-3 group-hover:-translate-x-1 transition-transform"></i> Kembali ke Dasbor
        </a>
        <div class="flex flex-wrap gap-3">
            <a href="<?= site_url('email/export_unit_kerja_csv/' . $unit_kerja['id']) . '?' . ($_SERVER['QUERY_STRING'] ?? '') ?>" class="inline-flex items-center px-5 py-2.5 bg-green-600 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-green-700 transition-all shadow-lg shadow-green-900/20 no-underline">
                <i class="fas fa-file-csv mr-2"></i> Ekspor CSV
            </a>
            <form id="pdfExportForm" action="<?= site_url('email/export_unit_kerja_pdf/' . $unit_kerja['id']) . '?' . ($_SERVER['QUERY_STRING'] ?? '') ?>" method="POST" class="inline" target="_blank">
                <input type="hidden" name="statusChartData" id="statusChartData">
                <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-red-600 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-red-700 transition-all shadow-lg shadow-red-900/20" onclick="return preparePdfExport();">
                    <i class="fas fa-file-pdf mr-2"></i> Status PDF
                </button>
            </form>
            <button onclick="openExportModal(<?= $unit_kerja['id'] ?>)" class="inline-flex items-center px-5 py-2.5 bg-cyan-600 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-cyan-700 transition-all shadow-lg shadow-cyan-900/20">
                <i class="fas fa-file-contract mr-2"></i> Dokumen PK
            </button>
            <button onclick="syncAllBsreStatus()" class="inline-flex items-center px-5 py-2.5 bg-amber-500 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-amber-600 transition-all shadow-lg shadow-amber-900/20">
                <i class="fas fa-sync-alt mr-2 text-xs"></i> Sinkron TTE
            </button>
        </div>
    </div>

    <!-- Profil Unit Kerja -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] p-10 shadow-2xl overflow-hidden relative group">
        <div class="absolute -right-10 -top-10 w-64 h-64 bg-blue-500/5 rounded-full blur-3xl group-hover:bg-blue-500/10 transition-colors"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-10">
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 bg-blue-600 rounded-3xl flex items-center justify-center shadow-2xl shadow-blue-900/40 border-4 border-slate-900 group-hover:scale-105 transition-transform duration-500">
                    <i class="fas fa-building text-white text-3xl"></i>
                </div>
                <div class="space-y-2">
                    <h2 class="text-3xl md:text-4xl font-black text-slate-100 uppercase tracking-tighter leading-none"><?= esc($unit_kerja['nama_unit_kerja']) ?></h2>
                    <?php if ($unit_kerja['parent_name'] ?? ''): ?>
                        <p class="text-sm font-bold text-slate-500 uppercase tracking-widest">Bagian Dari: <span class="text-blue-400 ml-2"><?= esc($unit_kerja['parent_name']) ?></span></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex gap-10 bg-slate-950 px-10 py-6 rounded-[2rem] border border-slate-800 shadow-inner">
                <div class="text-center space-y-1">
                    <div class="text-3xl font-black text-slate-100 tracking-tighter"><?= number_format($total_emails ?? 0) ?></div>
                    <div class="text-[9px] font-black text-slate-600 uppercase tracking-[0.2em]">Total Akun</div>
                </div>
                <div class="text-center space-y-1">
                    <div class="text-3xl font-black text-green-500 tracking-tighter"><?= number_format($active_count ?? 0) ?></div>
                    <div class="text-[9px] font-black text-slate-600 uppercase tracking-[0.2em]">Sertifikat Aktif</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sub-Unit Struktur -->
    <?php if (!empty($child_units)): ?>
        <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
            <div class="bg-slate-800/30 px-8 py-5 border-b border-slate-800 flex justify-between items-center cursor-pointer group" onclick="toggleChildUnits()">
                <h6 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                    <i class="fas fa-sitemap mr-3 text-cyan-500 opacity-50"></i>Struktur Sub-Unit Organisasi
                </h6>
                <i class="fas fa-chevron-down text-slate-600 text-xs transition-transform duration-300 group-hover:text-slate-400" id="childUnitsChevron"></i>
            </div>
            <div id="childUnitsList" class="p-8 hidden bg-slate-950/30">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($child_units as $child): ?>
                        <a href="<?= site_url('email/unit_kerja/' . $child['id']) ?>" class="p-4 bg-slate-900 border border-slate-800 rounded-2xl hover:border-blue-500/30 transition-all no-underline group/item">
                            <span class="text-xs font-bold text-slate-500 group-hover/item:text-blue-400 uppercase tracking-tight transition-colors"><?= esc($child['nama_unit_kerja']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Analisis Distribusi TTE -->
    <?php if (!empty($bsre_status_counts)): ?>
        <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
            <div class="bg-slate-800/30 px-8 py-5 border-b border-slate-800">
                <h6 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                    <i class="fas fa-chart-pie mr-3 text-blue-500 opacity-50"></i>Analisis Sertifikat Elektronik
                </h6>
            </div>
            <div class="p-10 flex flex-col lg:flex-row items-center gap-12">
                <div class="w-full lg:w-1/3 min-h-[250px] relative">
                    <div id="bsreStatusChart"></div>
                </div>
                <div class="w-full lg:w-2/3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php foreach ($bsre_status_counts as $key => $data): ?>
                            <div class="p-4 bg-slate-950 rounded-2xl border border-slate-800 flex justify-between items-center group hover:border-slate-700 transition-colors">
                                <div class="flex items-center">
                                    <span class="w-2 h-2 rounded-full mr-3 shadow-sm chart-legend-dot" data-status="<?= $key ?>"></span>
                                    <span class="text-xs font-bold text-slate-400 uppercase group-hover:text-slate-200 transition-colors"><?= esc($data['label']) ?></span>
                                </div>
                                <span class="px-3 py-1 rounded-lg text-xs font-black bg-slate-800 text-slate-100 border border-slate-700"><?= number_format($data['count']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Filter & Pencarian -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden p-8">
        <form method="GET" action="" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end">
            <div class="lg:col-span-3">
                <label class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Kata Kunci</label>
                <input type="text" name="search" value="<?= esc($search ?? '') ?>" class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all uppercase tracking-tight placeholder-slate-800" placeholder="NAMA / EMAIL / NIP...">
            </div>
            <div class="lg:col-span-3">
                <label class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Status Kepegawaian</label>
                <select name="status_asn" class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase tracking-tight cursor-pointer transition-all">
                    <option value="">SEMUA STATUS</option>
                    <?php foreach ($status_asn_options as $option): ?>
                        <option value="<?= esc($option['id']) ?>" <?= (($status_asn ?? '') == $option['id']) ? 'selected' : '' ?>><?= esc(strtoupper($option['nama_status_asn'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="lg:col-span-2">
                <label class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Status Sertifikat</label>
                <select name="bsre_status" class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase tracking-tight cursor-pointer transition-all">
                    <option value="">SEMUA STATUS</option>
                    <?php foreach ($bsre_status_options as $key => $label): ?>
                        <option value="<?= esc($key) ?>" <?= (($bsre_status ?? '') === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="lg:col-span-2">
                <label class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Baris</label>
                <select name="per_page" class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase tracking-tight cursor-pointer transition-all">
                    <?php foreach ([25, 50, 100, 250] as $p): ?>
                        <option value="<?= $p ?>" <?= (($per_page ?? 100) == $p) ? 'selected' : '' ?>><?= $p ?> Baris</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="lg:col-span-2 flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-black py-3.5 rounded-2xl shadow-xl shadow-blue-900/20 transition-all text-[10px] uppercase tracking-widest">Cari</button>
                <a href="<?= current_url() ?>" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 font-black py-3.5 rounded-2xl shadow-sm text-[10px] text-center no-underline uppercase tracking-widest">Reset</a>
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Akun -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
        <div class="p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-800/50">
                    <thead class="bg-slate-950/30">
                        <tr>
                            <th class="px-10 py-8 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Pengguna / Email</th>
                            <th class="px-10 py-8 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Jabatan & Status</th>
                            <th class="px-10 py-8 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Sertifikat</th>
                            <th class="px-10 py-8 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50 bg-slate-900/20">
                        <?php foreach (($emails ?? []) as $email): ?>
                            <tr class="hover:bg-slate-800/30 transition-colors group">
                                <td class="px-10 py-8 whitespace-nowrap align-middle">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-950 border border-slate-800/50 flex items-center justify-center mr-6 group-hover:border-blue-500/30 transition-all shadow-inner">
                                            <i class="fas fa-envelope text-slate-600 group-hover:text-blue-500/70 text-lg"></i>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-base font-bold text-slate-100 tracking-tight leading-none lowercase"><?= esc($email['email']) ?></div>
                                            <div class="text-[11px] text-slate-500 uppercase font-bold tracking-widest opacity-80"><?= esc($email['name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-10 py-8 align-middle">
                                    <div class="text-[11px] font-black text-blue-400/80 uppercase tracking-widest mb-1.5"><?= esc($email['status_asn']) ?></div>
                                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter leading-none"><?= esc($email['jabatan']) ?: '-' ?></div>
                                </td>
                                <td class="px-10 py-8 whitespace-nowrap align-middle">
                                    <div id="bsre-status-<?= esc($email['user']) ?>" data-email="<?= esc($email['email']) ?>">
                                        <?php
                                        $status = $email['bsre_status'] ?? '';
                                        $badgeBase = 'inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border transition-all';
                                        
                                        $colors = [
                                            'ISSUE' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                            'EXPIRED' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                            'RENEW' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                            'WAITING_FOR_VERIFICATION' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                            'NEW' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                                            'NO_CERTIFICATE' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                                            'NOT_REGISTERED' => 'bg-fuchsia-500/10 text-fuchsia-400 border-fuchsia-500/20',
                                            'SUSPEND' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                            'REVOKE' => 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20',
                                        ];

                                        $badgeClass = $colors[$status] ?? 'bg-slate-950/50 text-slate-500 border-slate-800/50';
                                        ?>
                                        <span class="<?= $badgeBase ?> <?= $badgeClass ?>"><?= $status ?: 'BELUM SINKRON' ?></span>
                                    </div>
                                </td>
                                <td class="px-10 py-8 whitespace-nowrap text-center text-sm font-medium align-middle">
                                    <div class="flex justify-center space-x-3">
                                        <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="w-10 h-10 flex items-center justify-center bg-slate-950/50 text-slate-500 border border-slate-800/50 rounded-xl hover:bg-blue-600 hover:text-white hover:border-transparent transition-all no-underline shadow-sm">
                                            <i class="fas fa-eye text-sm"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($pagination)): ?>
                <div class="bg-slate-950/50 px-10 py-10 border-t border-slate-800 flex flex-col md:flex-row justify-between items-center gap-8">
                    <div class="text-xs font-black text-slate-500 uppercase tracking-widest">
                        Total: <span class="text-blue-500 px-1"><?= number_format($total_emails ?? 0) ?></span> Akun Ditemukan
                    </div>
                    <div class="pagination-container font-black uppercase">
                        <?= $pagination->links() ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Progress PDF -->
<div id="exportProgressModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-slate-900 rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-800">
            <div class="p-10 space-y-8">
                <h3 class="text-xl font-black text-slate-100 uppercase tracking-tight text-center">Memproses Dokumen PDF</h3>
                <div class="w-full bg-slate-950 rounded-full h-5 p-1 border border-slate-800 shadow-inner">
                    <div id="exportProgressBar" class="bg-blue-600 h-full rounded-full transition-all duration-300 text-[9px] font-black text-white flex items-center justify-center" style="width: 0%">0%</div>
                </div>
                <p id="exportStatusText" class="text-xs text-slate-500 text-center font-bold uppercase tracking-widest italic animate-pulse">Menyiapkan data...</p>
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
                'ISSUE': '#10b981', // Emerald
                'EXPIRED': '#ef4444', // Red
                'RENEW': '#3b82f6', // Blue
                'WAITING_FOR_VERIFICATION': '#f59e0b', // Amber
                'NEW': '#6366f1', // Indigo
                'NO_CERTIFICATE': '#eab308', // Yellow
                'NOT_REGISTERED': '#d946ef', // Fuchsia
                'SUSPEND': '#a855f7', // Purple
                'REVOKE': '#71717a', // Zinc
                'not_synced': '#334155' // Slate-700
            };
            const chartData = <?= json_encode($bsre_status_counts) ?>;
            const labels = [], series = [], colors = [];
            
            Object.keys(chartData).forEach(key => {
                const color = tteColorMap[key] || '#' + Math.floor(Math.random() * 16777215).toString(16);
                labels.push(chartData[key].label);
                series.push(chartData[key].count);
                colors.push(color);
                
                // Update legend dots
                const dot = document.querySelector(`.chart-legend-dot[data-status="${key}"]`);
                if (dot) dot.style.backgroundColor = color;
            });

            const options = {
                series: series,
                labels: labels,
                chart: {
                    type: 'donut',
                    height: 280,
                    foreColor: '#94a3b8'
                },
                stroke: { show: false },
                dataLabels: { enabled: false },
                colors: colors,
                legend: { show: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '75%',
                            labels: {
                                show: true,
                                name: { show: true, fontSize: '12px', fontWeight: 900, offsetY: -10 },
                                value: { show: true, fontSize: '24px', fontWeight: 900, offsetY: 10, color: '#f1f5f9' },
                                total: {
                                    show: true,
                                    label: 'TOTAL',
                                    fontSize: '10px',
                                    fontWeight: 900,
                                    color: '#64748b'
                                }
                            }
                        }
                    }
                },
                tooltip: { theme: 'dark' }
            };

            const chart = new ApexCharts(document.querySelector("#bsreStatusChart"), options);
            chart.render();
        <?php endif; ?>
    });

    function preparePdfExport() {
        // Prepare data URL if needed for PDF export (though ApexCharts is harder to export via dataURL directly in JS without plugin)
        return true;
    }

    function openExportModal(unitId) {
        const modal = document.getElementById('exportProgressModal');
        const bar = document.getElementById('exportProgressBar');
        const status = document.getElementById('exportStatusText');
        modal.classList.remove('hidden');

        fetch(`<?= site_url('email/api_unit_emails/') ?>${unitId}${window.location.search}`)
            .then(r => r.json()).then(data => {
                if (!data.success || !data.emails.length) return alert('Data tidak ditemukan');
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
                            body: new URLSearchParams({
                                unit_id: unitId,
                                email_id: email.id
                            })
                        })
                        .then(() => {
                            processed++;
                            const p = Math.round((processed / emails.length) * 100);
                            bar.style.width = p + '%';
                            bar.innerText = p + '%';
                            status.innerText = `PDF: ${email.name} (${processed}/${emails.length})`;
                            setTimeout(process, 50);
                        });
                };
                process();
            });
    }

    function syncBsreStatus(emailUser, emailAddress) {
        const div = document.getElementById(`bsre-status-${emailUser}`);
        div.innerHTML = '<div class="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full mx-auto"></div>';
        fetch('<?= site_url('bsre/sync-status') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'email=' + encodeURIComponent(emailAddress)
            })
            .then(r => r.json()).then(data => {
                if (data.status === 'success') {
                    const badgeBase = 'inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border shadow-sm';
                    const colors = {
                        'ISSUE': 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                        'EXPIRED': 'bg-red-500/10 text-red-400 border-red-500/20',
                        'RENEW': 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                        'WAITING_FOR_VERIFICATION': 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                        'NEW': 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                        'NO_CERTIFICATE': 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                        'NOT_REGISTERED': 'bg-fuchsia-500/10 text-fuchsia-400 border-fuchsia-500/20',
                        'SUSPEND': 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                        'REVOKE': 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20',
                    };
                    const badgeClass = colors[data.bsre_status] || 'bg-slate-950 text-slate-600 border-slate-800';
                    div.innerHTML = `<span class="${badgeBase} ${badgeClass}">${data.bsre_status}</span>`;
                } else div.innerHTML = '<span class="text-[10px] font-black text-red-500">ERROR</span>';
            });
    }

    function syncAllBsreStatus() {
        const containers = document.querySelectorAll('[id^="bsre-status-"]');
        if (!containers.length || !confirm('Sinkronkan semua akun yang tampil?')) return;
        containers.forEach((c, i) => setTimeout(() => syncBsreStatus(c.id.replace('bsre-status-', ''), c.getAttribute('data-email')), i * 200));
    }
</script>
<?= $this->endSection() ?>