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
    <!-- Navigasi dan Aksi -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <button onclick="history.back()" class="btn btn-outline text-xs">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </button>

        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= site_url('email/export_pimpinan_pdf') ?>" target="_blank" class="btn btn-outline text-xs px-3 py-2 no-underline">
                <i class="fas fa-file-pdf mr-1.5 text-red-600"></i> Export PDF
            </a>

            <?php if (in_array(session()->get('role'), ['super_admin', 'admin'])): ?>
                <!-- Dropdown Sinkronisasi -->
                <div class="relative group">
                    <button id="mainSyncBtn" class="btn btn-solid text-xs px-3 py-2">
                        <i class="fas fa-sync-alt mr-1.5 text-white/80"></i> Sync <i class="fas fa-chevron-down ml-1 text-[8px] opacity-50 transition-transform duration-300 group-hover:rotate-180"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 overflow-hidden">
                        <button id="syncAllTteBtn" onclick="handleSyncAllTte()" class="w-full px-4 py-3 text-left text-[10px] font-bold text-slate-700 uppercase tracking-widest hover:bg-slate-50 border-b border-slate-100 transition-colors focus:outline-none">
                            <i class="fas fa-fw fa-fingerprint mr-2 text-slate-500"></i> Sync TTE
                        </button>
                        <button id="syncAllPegawaiBtn" onclick="handleSyncAllPegawai()" class="w-full px-4 py-3 text-left text-[10px] font-bold text-slate-700 uppercase tracking-widest hover:bg-slate-50 transition-colors focus:outline-none">
                            <i class="fas fa-fw fa-user-check mr-2 text-slate-500"></i> Sync Pegawai
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Informasi Pimpinan -->
    <div class="bg-white border border-slate-200 rounded-lg p-4 sm:p-6 lg:p-8 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 lg:gap-8">
            <div class="flex items-center gap-4 sm:gap-6">
                <div class="w-12 h-12 sm:w-16 sm:h-16 bg-slate-100 border border-slate-200 rounded-lg flex items-center justify-center text-slate-700 shrink-0">
                    <i class="fas fa-user-tie text-xl sm:text-2xl"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-800 uppercase tracking-tight">Pimpinan</h1>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Pemerintah Kabupaten Sinjai</p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 w-full lg:w-auto shrink-0">
                <div class="bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-lg p-3 text-center">
                    <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Total Email</p>
                    <p class="text-xl sm:text-2xl font-bold text-slate-800 mt-0.5"><?= number_format($total_emails ?? 0, 0, ',', '.') ?></p>
                </div>
                <div class="bg-white border border-slate-200 border-l-4 border-l-emerald-600 rounded-lg p-3 text-center">
                    <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest">TTE Aktif</p>
                    <p class="text-xl sm:text-2xl font-bold text-slate-800 mt-0.5"><?= number_format($active_bsre_count ?? 0, 0, ',', '.') ?></p>
                </div>
                <div class="bg-white border border-slate-200 border-l-4 border-l-red-600 rounded-lg p-3 text-center">
                    <p class="text-[9px] font-bold text-red-600 uppercase tracking-widest">TTE Expired</p>
                    <p class="text-xl sm:text-2xl font-bold text-slate-800 mt-0.5"><?= number_format($expired_bsre_count ?? 0, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-6">
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
                <label class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-widest">Status TTE</label>
                <div class="flex items-center gap-2">
                    <select id="filter-status" class="flex-1 block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm text-slate-800 font-medium cursor-pointer transition-all h-[40px]">
                        <option value="">Semua Status</option>
                        <?php foreach ($bsre_status_options as $key => $label): ?>
                            <option value="<?= esc(strtoupper($key)) ?>"><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="reset-filters" class="hidden btn btn-outline !w-[40px] !h-[40px] !p-0 shrink-0 flex items-center justify-center rounded-lg transition-all" title="Reset Filter">
                        <i class="fas fa-undo text-xs"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel -->
    <div id="email-table-container" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[10px] font-bold tracking-widest">
                    <tr>
                        <th class="px-6 py-4 border-b border-slate-200">Email</th>
                        <th class="px-6 py-4 border-b border-slate-200">Jabatan</th>
                        <th class="px-6 py-4 border-b border-slate-200">Unit Kerja</th>
                        <th class="px-6 py-4 border-b border-slate-200 w-48">Status TTE</th>
                        <th class="px-6 py-4 border-b border-slate-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php if (!empty($emails)): ?>
                        <?php foreach ($emails as $email): ?>
                            <?php
                            $st = $email['bsre_status'] ?? '';
                            $statusAttr = strtoupper($st ?: 'NOT_SYNCED');
                            ?>
                            <tr class="hover:bg-slate-50 transition-colors group email-row"
                                data-name="<?= esc(strtoupper($email['name'])) ?>"
                                data-email="<?= esc(strtoupper($email['email'])) ?>"
                                data-nip="<?= esc($email['nip']) ?>"
                                data-nik="<?= esc($email['nik']) ?>"
                                data-unit-kerja="<?= esc(strtoupper($email['unit_kerja_name'] ?? '')) ?>"
                                data-parent-unit-kerja="<?= esc(strtoupper($email['parent_unit_kerja_name'] ?? '')) ?>"
                                data-status="<?= $statusAttr ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col" id="pegawai-container-<?= $email['id'] ?>" data-nip="<?= esc($email['nip'] ?? '') ?>" data-email="<?= esc($email['email'] ?? '') ?>" data-status-asn-id="<?= esc($email['status_asn_id'] ?? '') ?>">
                                        <span class="font-medium text-slate-800 lowercase leading-tight"><?= esc($email['email']) ?></span>
                                        <span class="text-[10px] font-bold text-slate-700 uppercase tracking-tight mt-0.5"><?= esc($email['name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1 jabatan-sync-target">
                                        <span class="text-xs font-medium text-slate-700 uppercase tracking-tight leading-snug"><?= esc($email['jabatan']) ?: '-' ?></span>
                                        <?php if (!empty($email['jabatan_plt'])): ?>
                                            <span class="text-xs font-medium text-amber-700 uppercase tracking-tight leading-snug"><?= esc($email['jabatan_plt']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col unit-kerja-sync-target">
                                        <?php if (!empty($email['parent_unit_kerja_name'])): ?>
                                            <span class="text-[10px] font-bold text-slate-700 uppercase leading-none"><?= esc($email['parent_unit_kerja_name']) ?></span>
                                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight mt-1"><?= esc($email['unit_kerja_name']) ?></span>
                                        <?php else: ?>
                                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight"><?= esc($email['unit_kerja_name']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($email['unit_kerja_plt_name']) && $email['unit_kerja_plt_name'] !== $email['unit_kerja_name']): ?>
                                            <span class="text-xs font-bold text-amber-700 uppercase tracking-tight mt-1"><?= esc($email['unit_kerja_plt_name']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap w-48">
                                    <div id="bsre-status-<?= esc($email['user']) ?>" data-email="<?= esc($email['email']) ?>">
                                        <?php
                                        $st = $email['bsre_status'] ?? '';
                                        $colorClass = 'bg-slate-100 text-slate-700 border-transparent';
                                        $label = $st ?: 'NOT_SYNCED';

                                        if ($st === 'ISSUE') $colorClass = 'bg-emerald-100 text-emerald-800 border-transparent';
                                        elseif (in_array($st, ['EXPIRED', 'REVOKE', 'SUSPEND'])) $colorClass = 'bg-red-100 text-red-700 border-transparent';
                                        elseif (in_array($st, ['WAITING_FOR_VERIFICATION', 'RENEW', 'NO_CERTIFICATE'])) $colorClass = 'bg-amber-50 text-amber-500 border-amber-200';
                                        elseif ($st === 'NEW') $colorClass = 'bg-blue-100 text-slate-700 border-transparent';
                                        ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase border <?= $colorClass ?>">
                                            <?= $label ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="btn btn-table" title="Detail">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <?php if (session()->get('role') === 'super_admin'): ?>
                                            <form action="<?= site_url('email/delete/' . $email['id']) ?>" method="post" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-table-danger" title="Hapus">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
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

        <?= view('components/pagination', ['items' => $emails, 'pager' => $pager, 'label' => 'akun']) ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function handleSyncAllTte() {
        if (typeof window.syncAllBsreStatus === 'function') {
            window.syncAllBsreStatus('syncAllTteBtn', 'Sinkronkan status TTE?');
        }
    }

    function handleSyncAllPegawai() {
        if (typeof window.syncAllPegawai === 'function') {
            window.syncAllPegawai('syncAllPegawaiBtn', 'Sinkronkan data pegawai?');
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const statusSelect = document.getElementById('filter-status');
        const searchInput = document.getElementById('search-input');
        const resetContainer = document.getElementById('reset-container');
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
            statusSelect.addEventListener('change', filterEmails);
        }
        
        if (searchInput) {
            searchInput.addEventListener('input', filterEmails);
        }
        
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                if (searchInput) searchInput.value = '';
                if (statusChoices) {
                    statusChoices.setChoiceByValue('');
                } else if (statusSelect) {
                    statusSelect.value = '';
                }
                filterEmails();
            });
        }
        
        function filterEmails() {
            updateResetButton();
            const searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const statusVal = statusSelect ? statusSelect.value : '';
            
            let visibleCount = 0;
            const rows = document.querySelectorAll('.email-row');
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name').toLowerCase();
                const email = row.getAttribute('data-email').toLowerCase();
                const nip = row.getAttribute('data-nip').toLowerCase();
                const nik = row.getAttribute('data-nik').toLowerCase();
                const unitKerja = row.getAttribute('data-unit-kerja').toLowerCase();
                const parentUnitKerja = row.getAttribute('data-parent-unit-kerja').toLowerCase();
                const status = row.getAttribute('data-status');
                
                const matchSearch = !searchVal || 
                                    name.includes(searchVal) || 
                                    email.includes(searchVal) || 
                                    nip.includes(searchVal) || 
                                    nik.includes(searchVal) ||
                                    unitKerja.includes(searchVal) ||
                                    parentUnitKerja.includes(searchVal);
                const matchStatus = !statusVal || status === statusVal;
                
                if (matchSearch && matchStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update Export PDF href dynamically
            const exportPdfBtn = document.querySelector('a[href*="export_pimpinan_pdf"], a[href*="export_pimpinan_desa_pdf"]');
            if (exportPdfBtn) {
                const params = new URLSearchParams();
                if (searchVal) params.set('search', searchVal);
                if (statusVal) params.set('bsre_status', statusVal.toLowerCase());
                
                const queryString = params.toString();
                const baseExportUrl = exportPdfBtn.getAttribute('href').split('?')[0];
                exportPdfBtn.href = baseExportUrl + (queryString ? '?' + queryString : '');
            }
            
            const noResultsRow = document.getElementById('no-results-row');
            if (visibleCount === 0) {
                if (noResultsRow) noResultsRow.classList.remove('hidden');
            } else {
                if (noResultsRow) noResultsRow.classList.add('hidden');
            }
        }
    });
</script>
<?= $this->endSection() ?>