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
        <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Website OPD</h1>

        <div class="flex items-center gap-2 w-full lg:w-auto">
            <a href="<?= site_url('web_opd/export_pdf') ?>" target="_blank" class="flex-1 lg:flex-none btn btn-outline no-underline">
                <i class="fas fa-file-pdf mr-2"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Statistik -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
        <div class="px-4 sm:px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Status Website</h3>
        </div>
        <div class="p-4 sm:p-6 flex flex-col md:flex-row items-center gap-4 sm:gap-8">
            <div class="w-full md:w-1/2 flex justify-center py-2 sm:py-0">
                <div id="statusChart" class="w-full max-w-[160px] sm:max-w-[180px]"></div>
            </div>
            <div class="w-full md:w-1/2 space-y-2">
                <div class="flex justify-between items-center p-2 rounded-lg border border-slate-200 bg-slate-50">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                        <span class="text-[10px] font-bold text-slate-700 uppercase">Aktif</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[9px] font-bold text-slate-400"><?= $stats['total'] > 0 ? round(($stats['aktif'] / $stats['total']) * 100) : 0 ?>%</span>
                        <span class="text-xs font-bold text-slate-800"><?= $stats['aktif'] ?></span>
                    </div>
                </div>
                <div class="flex justify-between items-center p-2 rounded-lg border border-slate-200 bg-slate-50">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                        <span class="text-[10px] font-bold text-slate-700 uppercase">Nonaktif</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[9px] font-bold text-slate-400"><?= $stats['total'] > 0 ? round(($stats['nonaktif'] / $stats['total']) * 100) : 0 ?>%</span>
                        <span class="text-xs font-bold text-slate-800"><?= $stats['nonaktif'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="p-4 sm:p-6 border-b border-slate-100 bg-slate-50">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-8 lg:col-span-9">
                    <label class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-widest">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" id="search-input" class="block w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm text-slate-800 placeholder-slate-400 font-medium transition-all h-[40px]" placeholder="Cari...">
                    </div>
                </div>

                <div class="md:col-span-4 lg:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-widest">Status</label>
                    <div class="flex items-center gap-2">
                        <select id="filter-status" class="flex-1 block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm text-slate-800 font-medium cursor-pointer transition-all h-[40px]">
                            <option value="">Semua Status</option>
                            <option value="AKTIF">AKTIF</option>
                            <option value="NONAKTIF">NONAKTIF</option>
                        </select>
                        <button type="button" id="reset-filters" class="hidden btn btn-outline !w-[40px] !h-[40px] !p-0 shrink-0 flex items-center justify-center rounded-lg transition-all" title="Reset Filter">
                            <i class="fas fa-undo text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[10px] font-bold tracking-widest">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200">OPD</th>
                        <th class="px-6 py-3 border-b border-slate-200">Domain</th>
                        <th class="px-6 py-3 border-b border-slate-200">Status</th>
                        <th class="px-6 py-3 border-b border-slate-200">Keterangan</th>
                        <th class="px-6 py-3 border-b border-slate-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($websites)): ?>
                        <?php foreach ($websites as $web): ?>
                            <tr class="hover:bg-slate-50 transition-colors website-row"
                                data-name="<?= esc(strtoupper($web['nama_unit_kerja'] ?? '')) ?>"
                                data-domain="<?= esc(strtoupper($web['domain'] ?? '')) ?>"
                                data-status="<?= esc(strtoupper($web['status'] ?? 'NONAKTIF')) ?>">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-700 shrink-0">
                                            <i class="fas fa-building text-xs"></i>
                                        </div>
                                        <span class="font-medium text-slate-800 uppercase tracking-tight text-xs"><?= esc($web['nama_unit_kerja'] ?? '-') ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if (!empty($web['domain'])): ?>
                                        <a href="http://<?= esc($web['domain']) ?>" target="_blank" class="text-slate-700 hover:underline text-xs font-medium">
                                            <?= esc($web['domain']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-[10px] text-slate-700 italic">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $status = strtoupper($web['status'] ?? 'NONAKTIF');
                                    $colorClass = ($status === 'AKTIF') ? 'bg-emerald-100 text-emerald-800 border-transparent' : 'bg-red-100 text-red-700 border-transparent';
                                    ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border <?= $colorClass ?>">
                                        <?= $status ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-[10px] text-slate-700 font-medium tracking-tight"><?= esc($web['keterangan'] ?: '') ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php if (in_array(session()->get('role'), ['super_admin', 'admin'])): ?>
                                        <a href="<?= site_url('web_opd/edit/' . $web['id']) ?>" class="btn btn-table" data-tooltip-target="tooltip-edit-<?= $web['id'] ?>">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <div id="tooltip-edit-<?= $web['id'] ?>" role="tooltip" class="absolute z-10 invisible inline-block px-2.5 py-1 text-[10px] font-bold text-white bg-slate-900 rounded-lg shadow-sm opacity-0 tooltip" x-cloak>
                                            Edit Data
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-[10px] font-bold text-slate-700 uppercase italic">Hanya Lihat</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="no-results-row" class="hidden">
                            <td colspan="5" class="px-6 py-20 text-center">
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
                            <td colspan="5" class="px-6 py-20 text-center">
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
        const statusSelect = document.getElementById('filter-status');
        const searchInput = document.getElementById('search-input');
        const resetBtn = document.getElementById('reset-filters');

        let statusChoices;

        function updateResetButton() {
            const searchVal = searchInput ? searchInput.value.trim() : '';
            const statusVal = statusSelect ? statusSelect.value : '';
            if (resetBtn) {
                if (searchVal !== '' || statusVal !== '') {
                    resetBtn.classList.remove('hidden');
                } else {
                    resetBtn.classList.add('hidden');
                }
            }
        }

        if (statusSelect) {
            if (statusSelect.options.length >= 10) {
                statusChoices = new Choices(statusSelect, {
                    searchEnabled: false,
                    itemSelectText: '',
                    shouldSort: false
                });
            }
            statusSelect.addEventListener('change', filterWebsites);
        }

        if (searchInput) {
            searchInput.addEventListener('input', filterWebsites);
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                if (searchInput) searchInput.value = '';
                if (statusChoices) {
                    statusChoices.setChoiceByValue('');
                } else if (statusSelect) {
                    statusSelect.value = '';
                }
                filterWebsites();
            });
        }

        function filterWebsites() {
            updateResetButton();
            const searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const statusVal = statusSelect ? statusSelect.value : '';

            let visibleCount = 0;
            const rows = document.querySelectorAll('.website-row');
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name').toLowerCase();
                const domain = row.getAttribute('data-domain').toLowerCase();
                const status = row.getAttribute('data-status');

                const matchSearch = !searchVal || 
                                    name.includes(searchVal) || 
                                    domain.includes(searchVal);
                const matchStatus = !statusVal || status === statusVal;

                if (matchSearch && matchStatus) {
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
                if (statusVal) params.set('status', statusVal);
                
                const queryString = params.toString();
                exportPdfBtn.href = '<?= site_url("web_opd/export_pdf") ?>' + (queryString ? '?' + queryString : '');
            }

            const noResultsRow = document.getElementById('no-results-row');
            if (visibleCount === 0) {
                if (noResultsRow) noResultsRow.classList.remove('hidden');
            } else {
                if (noResultsRow) noResultsRow.classList.add('hidden');
            }
        }

        const stats = <?= json_encode($stats) ?>;
        new ApexCharts(document.querySelector("#statusChart"), {
            series: [stats.aktif, stats.nonaktif],
            labels: ['AKTIF', 'NONAKTIF'],
            colors: ['#059669', '#dc2626'],
            chart: {
                type: 'donut',
                height: 180,
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
        }).render();
    });
</script><?= $this->endSection() ?>