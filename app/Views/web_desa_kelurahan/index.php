<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .choices {
        margin-bottom: 0 !important;
    }
    .choices__inner {
        min-height: 38px !important;
        border-color: #e2e8f0 !important;
        border-radius: 0.5rem !important;
        background-color: #ffffff !important;
        padding: 4px 8px !important;
        display: flex;
        align-items: center;
    }
    .choices__list--single {
        padding: 0 !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        color: #334155 !important;
    }
    #reset-filters {
        height: 38px !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Website Desa dan Kelurahan</h1>
            <?php if (!empty($last_sync_website)): ?>
                <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest mt-1">
                    <i class="fas fa-history mr-1"></i> Terakhir Sync:
                    <span class="text-slate-800"><?= formatTanggalWaktu($last_sync_website) ?></span>
                </p>
            <?php endif; ?>
        </div>

        <div class="flex items-center gap-2 w-full lg:w-auto">
            <a href="<?= site_url('web_desa_kelurahan/export_pdf') ?>" class="flex-1 lg:flex-none btn btn-outline no-underline">
                <i class="fas fa-file-pdf mr-2"></i> Export PDF
            </a>
            <?php if (in_array(session()->get('role'), ['super_admin', 'admin'])): ?>
                <button type="button" class="flex-1 lg:flex-none btn btn-solid" id="batchSyncBtn" onclick="startBatchSync()">
                    <i class="fas fa-sync mr-2 text-white/80"></i> Sync Expiration
                </button>
            <?php endif; ?>
        </div>
    </div>




    <!-- Statistik -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Status -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Status Website</h3>
            </div>
            <div class="p-6 flex flex-col md:flex-row items-center gap-8">
                <div class="w-full md:w-1/2 flex justify-center">
                    <div id="statusChart" class="w-full max-w-[180px]"></div>
                </div>
                <div class="w-full md:w-1/2 space-y-2">
                    <div class="flex justify-between items-center p-2 rounded-lg border border-slate-200 bg-slate-50">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                            <span class="text-[10px] font-bold text-slate-700 uppercase">Aktif</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] font-bold text-slate-400"><?= ($stats['total'] ?? 0) > 0 ? round(($stats['aktif'] / $stats['total']) * 100) : 0 ?>%</span>
                            <span class="text-xs font-bold text-slate-800"><?= $stats['aktif'] ?></span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center p-2 rounded-lg border border-slate-200 bg-slate-50">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                            <span class="text-[10px] font-bold text-slate-700 uppercase">Nonaktif</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] font-bold text-slate-400"><?= ($stats['total'] ?? 0) > 0 ? round(($stats['nonaktif'] / $stats['total']) * 100) : 0 ?>%</span>
                            <span class="text-xs font-bold text-slate-800"><?= $stats['nonaktif'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Teknologi -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Distribusi Platform</h3>
            </div>
            <div class="p-6 flex flex-col md:flex-row items-center gap-8">
                <div class="w-full md:w-1/2 flex justify-center">
                    <div id="platformChart" class="w-full max-w-[180px]"></div>
                </div>
                <div class="w-full md:w-1/2 space-y-2 max-h-[160px] overflow-y-auto custom-scrollbar pr-2">
                    <?php 
                    $platform_total = array_sum(array_column($platform_stats, 'count'));
                    foreach ($platform_stats as $index => $ps): 
                    ?>
                        <div class="flex justify-between items-center p-2 rounded-lg border border-slate-200 bg-slate-50">
                            <div class="flex items-center gap-2 truncate mr-2">
                                <span class="w-2.5 h-2.5 rounded-full platform-legend-dot shrink-0" data-index="<?= $index ?>"></span>
                                <span class="text-[10px] font-bold text-slate-700 uppercase tracking-tight truncate"><?= esc($ps['nama_platform'] ?: 'TIDAK TERDAFTAR') ?></span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-[9px] font-bold text-slate-400"><?= $platform_total > 0 ? round(($ps['count'] / $platform_total) * 100) : 0 ?>%</span>
                                <span class="text-xs font-bold text-slate-800"><?= $ps['count'] ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
        <div class="p-6 border-b border-slate-100 bg-slate-50">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" id="search-input" class="block w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm transition-all" placeholder="Cari desa, kelurahan, kecamatan, atau domain...">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Tipe</label>
                    <select id="filter-type" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm cursor-pointer transition-all">
                        <option value="">Semua Tipe</option>
                        <option value="DESA">DESA</option>
                        <option value="KELURAHAN">KELURAHAN</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Platform</label>
                    <select id="filter-platform" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm cursor-pointer transition-all">
                        <option value="">Semua Platform</option>
                        <option value="NULL">TIDAK TERDAFTAR</option>
                        <?php foreach ($platforms as $p): ?>
                            <option value="<?= esc(strtoupper($p['nama_platform'])) ?>"><?= esc($p['nama_platform']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Status</label>
                    <select id="filter-status" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm cursor-pointer transition-all">
                        <option value="">Semua Status</option>
                        <option value="AKTIF">AKTIF</option>
                        <option value="NONAKTIF">NONAKTIF</option>
                    </select>
                </div>

                <div class="md:col-span-1 flex gap-2">
                    <button type="button" id="reset-filters" class="w-full btn btn-outline justify-center" title="Reset">
                        <i class="fas fa-undo"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200">Desa / Kelurahan</th>
                        <th class="px-6 py-3 border-b border-slate-200">Domain / Platform</th>
                        <th class="px-6 py-3 border-b border-slate-200">hosting / server</th>
                        <th class="px-6 py-3 border-b border-slate-200">Tanggal Berakhir</th>
                        <th class="px-6 py-3 border-b border-slate-200">Status</th>
                        <th class="px-6 py-3 border-b border-slate-200">Dikelola Kominfo</th>
                        <th class="px-6 py-3 border-b border-slate-200">Keterangan</th>
                        <th class="px-6 py-3 border-b border-slate-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($websites)): ?>
                        <?php foreach ($websites as $web): ?>
                            <?php
                            $type = (stripos($web['desa_kelurahan'], 'KELURAHAN') !== false) ? 'KELURAHAN' : 'DESA';
                            $platform = strtoupper($web['platform_name'] ?: 'TIDAK TERDAFTAR');
                            $status = strtoupper($web['status'] ?? 'NONAKTIF');
                            ?>
                            <tr class="hover:bg-slate-50 transition-colors website-row" 
                                data-id="<?= $web['id'] ?>"
                                data-name="<?= esc(strtoupper($web['desa_kelurahan'])) ?>"
                                data-kecamatan="<?= esc(strtoupper($web['kecamatan'])) ?>"
                                data-domain="<?= esc(strtoupper($web['domain'] ?? '')) ?>"
                                data-type="<?= $type ?>"
                                data-platform="<?= $platform ?>"
                                data-status="<?= $status ?>">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-medium text-slate-800 uppercase tracking-tight text-xs"><?= esc($web['desa_kelurahan']) ?></span>
                                    <span class="text-[10px] text-slate-700 uppercase font-bold tracking-widest mt-0.5"><?= esc($web['kecamatan']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (!empty($web['domain'])): ?>
                                    <a href="http://<?= esc($web['domain']) ?>" target="_blank" class="text-slate-700 hover:underline text-xs font-medium block">
                                        <?= esc($web['domain']) ?>
                                    </a>
                                <?php endif; ?>
                                <span class="text-[9px] font-bold text-slate-700 uppercase tracking-tight"><?= esc($web['platform_name'] ?: 'TIDAK TERDAFTAR') ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-1.5">
                                        <?php
                                        $hostStatus = strtoupper($web['hosting_status'] ?? 'UNKNOWN');
                                        $dotColor = ($hostStatus === 'AKTIF') ? 'bg-emerald-500' : (($hostStatus === 'NONAKTIF') ? 'bg-rose-500' : 'bg-slate-300');
                                        $dotTitle = 'Port check: ' . ($hostStatus === 'AKTIF' ? 'Terhubung (Online)' : ($hostStatus === 'NONAKTIF' ? 'Putus (Offline)' : 'Belum dicek'));
                                        ?>
                                        <span class="w-2.5 h-2.5 rounded-full <?= $dotColor ?> inline-block" id="host-status-dot-<?= $web['id'] ?>" title="<?= $dotTitle ?>"></span>
                                        <span class="text-xs font-semibold text-slate-700" id="ip-cell-<?= $web['id'] ?>"><?= esc($web['ip_address'] ?: '-') ?></span>
                                    </div>
                                    <span class="text-[9px] font-bold text-slate-500 uppercase tracking-tight mt-0.5" id="provider-cell-<?= $web['id'] ?>"><?= esc($web['hosting_provider'] ?: 'BELUM DI-SYNC') ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" id="date-cell-<?= $web['id'] ?>">
                                <span class="text-xs font-medium text-slate-700">
                                    <?php if (stripos($web['desa_kelurahan'], 'KELURAHAN') !== false): ?>
                                        <?= formatTanggal('2027-02-01') ?>
                                    <?php else: ?>
                                        <?= formatTanggal($web['tanggal_berakhir']) ?>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" id="status-cell-<?= $web['id'] ?>">
                                <?php
                                $status = strtoupper($web['status'] ?? 'NONAKTIF');
                                $colorClass = ($status === 'AKTIF') ? 'bg-emerald-100 text-emerald-800 border-transparent' : 'bg-red-100 text-red-700 border-transparent';
                                ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border <?= $colorClass ?>">
                                    <?= $status ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($web['dikelola_kominfo'] === 'YA'): ?>
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-slate-700 border-transparent" title="Dikelola Kominfo">
                                        <i class="fas fa-check text-[10px]"></i>
                                    </span>
                                <?php else: ?>
                                    <span class="text-[10px] text-slate-700 font-bold tracking-widest">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-[10px] text-slate-700 font-medium tracking-tight"><?= esc($web['keterangan'] ?: '') ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php if (in_array(session()->get('role'), ['super_admin', 'admin'])): ?>
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="<?= site_url('web_desa_kelurahan/edit/' . $web['id']) ?>" class="btn btn-table" title="Edit">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="text-[10px] font-bold text-slate-700 uppercase italic">Hanya Lihat</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="no-results-row" class="hidden">
                        <td colspan="8" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                    <i class="fas fa-search text-slate-300 text-lg"></i>
                                </div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Data tidak ditemukan</span>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" class="px-6 py-20 text-center">
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

        <!-- Pagination disembunyikan karena seluruh data dimuat langsung dalam 1 halaman -->
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize Choices.js for Filter Form Dropdowns
        const typeSelect = document.getElementById('filter-type');
        const platformSelect = document.getElementById('filter-platform');
        const statusSelect = document.getElementById('filter-status');
        const searchInput = document.getElementById('search-input');
        const resetBtn = document.getElementById('reset-filters');

        let typeChoices, platformChoices, statusChoices;

        if (typeSelect) {
            typeChoices = new Choices(typeSelect, {
                searchEnabled: false,
                itemSelectText: '',
                shouldSort: false
            });
            typeSelect.addEventListener('change', filterWebsites);
        }
        if (platformSelect) {
            platformChoices = new Choices(platformSelect, {
                searchEnabled: false,
                itemSelectText: '',
                shouldSort: false
            });
            platformSelect.addEventListener('change', filterWebsites);
        }
        if (statusSelect) {
            statusChoices = new Choices(statusSelect, {
                searchEnabled: false,
                itemSelectText: '',
                shouldSort: false
            });
            statusSelect.addEventListener('change', filterWebsites);
        }

        if (searchInput) {
            searchInput.addEventListener('input', filterWebsites);
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                if (searchInput) searchInput.value = '';
                if (typeChoices) typeChoices.setChoiceByValue('');
                if (platformChoices) platformChoices.setChoiceByValue('');
                if (statusChoices) statusChoices.setChoiceByValue('');
                filterWebsites();
            });
        }

        function filterWebsites() {
            const searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const typeVal = typeSelect ? typeSelect.value : '';
            const platformVal = platformSelect ? platformSelect.value : '';
            const statusVal = statusSelect ? statusSelect.value : '';

            let visibleCount = 0;
            const rows = document.querySelectorAll('.website-row');
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name').toLowerCase();
                const kecamatan = row.getAttribute('data-kecamatan').toLowerCase();
                const domain = row.getAttribute('data-domain').toLowerCase();
                const type = row.getAttribute('data-type');
                const platform = row.getAttribute('data-platform');
                const status = row.getAttribute('data-status');

                const matchSearch = !searchVal || 
                                    name.includes(searchVal) || 
                                    kecamatan.includes(searchVal) || 
                                    domain.includes(searchVal);
                const matchType = !typeVal || type === typeVal;
                const matchPlatform = !platformVal || 
                                       (platformVal === 'NULL' && (!platform || platform === 'TIDAK TERDAFTAR')) || 
                                       platform === platformVal;
                const matchStatus = !statusVal || status === statusVal;

                if (matchSearch && matchType && matchPlatform && matchStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Update Export PDF href dynamically
            const exportPdfBtn = document.querySelector('a[href*="export_pdf"]');
            if (exportPdfBtn) {
                const params = new URLSearchParams();
                if (searchVal) params.set('search', searchVal);
                if (typeVal) params.set('type', typeVal);
                if (platformVal) params.set('filter_platform', platformVal);
                if (statusVal) params.set('status', statusVal);
                
                const queryString = params.toString();
                exportPdfBtn.href = '<?= site_url("web_desa_kelurahan/export_pdf") ?>' + (queryString ? '?' + queryString : '');
            }

            const noResultsRow = document.getElementById('no-results-row');
            if (visibleCount === 0) {
                if (noResultsRow) noResultsRow.classList.remove('hidden');
            } else {
                if (noResultsRow) noResultsRow.classList.add('hidden');
            }
        }

        const stats = <?= json_encode($stats) ?>;
        const platformStats = <?= json_encode($platform_stats) ?>;

        const commonOptions = {
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
                                    return val
                                }
                            },
                            total: {
                                show: true,
                                label: 'TOTAL',
                                fontSize: '10px',
                                fontWeight: 700,
                                color: '#9ca3af',
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                }
                            }
                        }
                    }
                }
            }
        };

        new ApexCharts(document.querySelector("#statusChart"), {
            ...commonOptions,
            series: [stats.aktif, stats.nonaktif],
            labels: ['AKTIF', 'NONAKTIF'],
            colors: ['#059669', '#dc2626']
        }).render();

        const pColors = ['#1e293b', '#334155', '#475569', '#64748b', '#94a3b8', '#cbd5e1'];
        new ApexCharts(document.querySelector("#platformChart"), {
            ...commonOptions,
            series: platformStats.map(p => parseInt(p.count)),
            labels: platformStats.map(p => p.nama_platform || 'TIDAK TERDAFTAR'),
            colors: pColors
        }).render();

        document.querySelectorAll('.platform-legend-dot').forEach((dot, i) => {
            dot.style.backgroundColor = pColors[i % pColors.length];
        });
    });

    async function syncExpiration(id) {
        const dateCell = document.getElementById('date-cell-' + id);
        const statusCell = document.getElementById('status-cell-' + id);
        const ipCell = document.getElementById('ip-cell-' + id);
        const providerCell = document.getElementById('provider-cell-' + id);
        const hostDot = document.getElementById('host-status-dot-' + id);
        
        if (dateCell) dateCell.innerHTML = '<i class="fas fa-spinner fa-spin text-slate-700 text-[10px]"></i>';
        if (ipCell) ipCell.innerHTML = '<i class="fas fa-spinner fa-spin text-slate-700 text-[10px]"></i>';
        
        try {
            const r = await fetch('<?= site_url('web_desa_kelurahan/sync_expiration/') ?>' + id);
            const d = await r.json();
            if (d.status === 'success') {
                if (dateCell) dateCell.innerHTML = `<span class="text-xs font-medium text-slate-700">${d.date}</span>`;
                if (ipCell) ipCell.innerHTML = d.ip_address;
                if (providerCell) providerCell.innerHTML = d.hosting_provider;
                
                if (hostDot) {
                    hostDot.className = 'w-2.5 h-2.5 rounded-full inline-block ' + (d.hosting_status === 'AKTIF' ? 'bg-emerald-500' : (d.hosting_status === 'NONAKTIF' ? 'bg-rose-500' : 'bg-slate-300'));
                    hostDot.title = 'Port check: ' + (d.hosting_status === 'AKTIF' ? 'Terhubung (Online)' : (d.hosting_status === 'NONAKTIF' ? 'Putus (Offline)' : 'Belum dicek'));
                }
                
                if (statusCell && d.web_status) {
                    const status = d.web_status.toUpperCase();
                    const colorClass = (status === 'AKTIF') ? 'bg-emerald-100 text-emerald-800 border-transparent' : 'bg-red-100 text-red-700 border-transparent';
                    statusCell.innerHTML = `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold border ${colorClass}">${status}</span>`;
                }
                return true;
            }
        } catch (e) {
            console.error(e);
        }
        if (dateCell) dateCell.innerHTML = '<span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Gagal</span>';
        if (ipCell) ipCell.innerHTML = '-';
        return false;
    }

    async function startBatchSync() {
        if (!confirm('Sinkronkan semua data website sekarang? Proses ini membutuhkan beberapa menit.')) return;
        const btn = document.getElementById('batchSyncBtn');
        const originalBtnContent = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sinkronisasi...';

        const rows = [...document.querySelectorAll('.website-row')];
        const total = rows.length;
        let success = 0;
        let failed  = 0;

        for (let i = 0; i < total; i++) {
            const row = rows[i];
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            row.classList.add('bg-slate-100');

            try {
                const result = await syncExpiration(row.getAttribute('data-id'));
                if (result) success++; else failed++;
            } catch (e) {
                failed++;
            }

            row.classList.remove('bg-slate-100');
        }

        btn.disabled = false;
        btn.innerHTML = originalBtnContent;

        showSyncResult(total, success, failed);
    }
</script>
<?= $this->endSection() ?>