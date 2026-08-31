<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Navigasi dan Aksi -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <button onclick="history.back()" class="btn btn-outline text-xs">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </button>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Dropdown Export -->
            <div class="relative group">
                <button class="btn btn-outline text-xs px-3 py-2">
                    <i class="fas fa-download mr-1.5 text-slate-600"></i> Export <i class="fas fa-chevron-down ml-1 text-[8px] opacity-50 transition-transform duration-300 group-hover:rotate-180"></i>
                </button>
                <div class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 overflow-hidden">
                    <a href="<?= site_url('email/export_unit_kerja_csv/' . $unit_kerja['id']) . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '') ?>" class="block px-4 py-3 text-left text-[10px] font-bold text-slate-700 uppercase tracking-widest hover:bg-slate-50 border-b border-slate-100 transition-colors no-underline">
                        <i class="fas fa-fw fa-file-csv mr-2 text-slate-400"></i> CSV
                    </a>
                    <a href="<?= site_url('email/export_unit_kerja_excel/' . $unit_kerja['id']) . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '') ?>" class="block px-4 py-3 text-left text-[10px] font-bold text-slate-700 uppercase tracking-widest hover:bg-slate-50 border-b border-slate-100 transition-colors no-underline">
                        <i class="fas fa-fw fa-file-excel mr-2 text-emerald-600"></i> Excel
                    </a>
                    <a href="<?= site_url('email/export_account_detail_pdf/' . $unit_kerja['id']) . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '') ?>" class="block px-4 py-3 text-left text-[10px] font-bold text-slate-700 uppercase tracking-widest hover:bg-slate-50 border-b border-slate-100 transition-colors no-underline">
                        <i class="fas fa-fw fa-user-shield mr-2 text-blue-600"></i> Akun PDF
                    </a>
                    <a href="<?= site_url('email/export_unit_kerja_pdf/' . $unit_kerja['id']) . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '') ?>" class="block px-4 py-3 text-left text-[10px] font-bold text-slate-700 uppercase tracking-widest hover:bg-slate-50 transition-colors no-underline">
                        <i class="fas fa-fw fa-file-pdf mr-2 text-red-600"></i> Status PDF
                    </a>
                </div>
            </div>

            <?php if (in_array(session()->get('role'), ['super_admin', 'admin'])): ?>
                <!-- Dropdown Batch PK -->
                <div class="relative group">
                    <button class="btn btn-outline text-xs px-3 py-2">
                        <i class="fas fa-file-contract mr-1.5 text-slate-600"></i> Batch PK <i class="fas fa-chevron-down ml-1 text-[8px] opacity-50 transition-transform duration-300 group-hover:rotate-180"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 overflow-hidden">
                        <button onclick="openExportModal(<?= $unit_kerja['id'] ?>, 'pppk')" class="w-full px-4 py-3 text-left text-[10px] font-bold text-slate-700 uppercase tracking-widest hover:bg-slate-50 border-b border-slate-100 transition-colors focus:outline-none">
                            <i class="fas fa-fw fa-user-tie mr-2 text-slate-500"></i> PPPK
                        </button>
                        <button onclick="openExportModal(<?= $unit_kerja['id'] ?>, 'pppk_pw')" class="w-full px-4 py-3 text-left text-[10px] font-bold text-slate-700 uppercase tracking-widest hover:bg-slate-50 transition-colors focus:outline-none">
                            <i class="fas fa-fw fa-user-clock mr-2 text-slate-500"></i> PPPK PW
                        </button>
                    </div>
                </div>

                <!-- Tombol Batch Password -->
                <button id="batchPasswordBtn" onclick="openBatchPasswordModal()" class="btn btn-outline text-xs px-3 py-2">
                    <i class="fas fa-key mr-1.5 text-slate-600"></i> Batch Password
                </button>

                <!-- Dropdown Sinkronisasi -->
                <div class="relative group">
                    <button id="mainSyncBtn" class="btn btn-solid text-xs px-3 py-2">
                        <i class="fas fa-sync-alt mr-1.5 text-white/80"></i> Sync <i class="fas fa-chevron-down ml-1 text-[8px] opacity-50 transition-transform duration-300 group-hover:rotate-180"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 overflow-hidden">
                        <button id="syncAllTteBtn" onclick="syncAllBsreStatus()" class="w-full px-4 py-3 text-left text-[10px] font-bold text-slate-700 uppercase tracking-widest hover:bg-slate-50 border-b border-slate-100 transition-colors focus:outline-none">
                            <i class="fas fa-fw fa-fingerprint mr-2 text-slate-500"></i> Sync TTE
                        </button>
                        <button id="syncAllPegawaiBtn" onclick="syncAllPegawai('syncAllPegawaiBtn')" class="w-full px-4 py-3 text-left text-[10px] font-bold text-slate-700 uppercase tracking-widest hover:bg-slate-50 transition-colors focus:outline-none">
                            <i class="fas fa-fw fa-user-check mr-2 text-slate-500"></i> Sync Pegawai
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Informasi Unit Kerja -->
    <div class="bg-white border border-slate-200 rounded-lg p-4 sm:p-6 lg:p-8 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 lg:gap-8">
            <div class="flex items-center gap-4 sm:gap-6">
                <div class="w-12 h-12 sm:w-16 sm:h-16 bg-slate-100 border border-slate-200 rounded-lg flex items-center justify-center text-slate-700 shrink-0">
                    <i class="fas fa-building text-xl sm:text-2xl"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-800 uppercase tracking-tight"><?= esc($unit_kerja['nama_unit_kerja']) ?></h1>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">ID Unit: <?= $unit_kerja['id'] ?></p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 w-full lg:w-auto shrink-0">
                <div class="bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-2xl p-3 text-center">
                    <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Total Email</p>
                    <p class="text-xl sm:text-2xl font-bold text-slate-800 mt-0.5"><?= number_format($total_emails ?? 0, 0, ',', '.') ?></p>
                </div>
                <div class="bg-white border border-slate-200 border-l-4 border-l-emerald-600 rounded-lg p-3 text-center">
                    <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest">TTE Aktif</p>
                    <p class="text-xl sm:text-2xl font-bold text-slate-800 mt-0.5"><?= number_format($active_bsre_count ?? 0, 0, ',', '.') ?></p>
                </div>
                <?php $expired_count = $bsre_status_counts['EXPIRED']['count'] ?? 0; ?>
                <div class="bg-white border border-slate-200 border-l-4 border-l-red-600 rounded-lg p-3 text-center">
                    <p class="text-[9px] font-bold text-red-600 uppercase tracking-widest">TTE Expired</p>
                    <p class="text-xl sm:text-2xl font-bold text-slate-800 mt-0.5"><?= number_format($expired_count, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sub-Unit -->
    <?php if (!empty($child_units)): ?>
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
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
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h5 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Status TTE</h5>
                </div>
                <div class="p-4 sm:p-6 flex flex-col sm:flex-row items-center gap-4 sm:gap-8 flex-grow">
                    <div class="w-full sm:w-1/2 flex justify-center py-2 sm:py-0">
                        <div id="bsreStatusChart" class="w-full max-w-[180px] sm:max-w-[200px]"></div>
                    </div>
                    <div class="w-full sm:w-1/2 space-y-2 max-h-[180px] sm:max-h-[200px] overflow-y-auto custom-scrollbar pr-1 sm:pr-2">
                        <?php foreach ($bsre_status_counts as $key => $data):
                            $bgClass = 'bg-slate-400';
                            if ($key === 'ISSUE') $bgClass = 'bg-emerald-600';
                            elseif (in_array($key, ['EXPIRED', 'REVOKE', 'SUSPEND'])) $bgClass = 'bg-red-600';
                            elseif (in_array($key, ['WAITING_FOR_VERIFICATION', 'RENEW', 'NO_CERTIFICATE'])) $bgClass = 'bg-amber-500';
                            elseif ($key === 'NEW') $bgClass = 'bg-emerald-500';
                        ?>
                            <div class="p-2 bg-slate-50 border border-slate-200 rounded-lg flex justify-between items-center">
                                <div class="flex items-center truncate mr-2">
                                    <span class="w-2 h-2 rounded-full mr-2 shrink-0 <?= $bgClass ?>"></span>
                                    <span class="text-[10px] font-bold text-slate-700 uppercase truncate"><?= esc($data['label']) ?></span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-[9px] font-bold text-slate-400"><?= ($total_emails ?? 0) > 0 ? round(($data['count'] / $total_emails) * 100) : 0 ?>%</span>
                                    <span class="text-xs font-bold text-slate-800"><?= number_format($data['count'], 0, ',', '.') ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($status_asn_stats)): ?>
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h5 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Status ASN</h5>
                </div>
                <div class="p-4 sm:p-6 flex flex-col sm:flex-row items-center gap-4 sm:gap-8 flex-grow">
                    <div class="w-full sm:w-1/2 flex justify-center py-2 sm:py-0">
                        <div id="asnStatusChart" class="w-full max-w-[180px] sm:max-w-[200px]"></div>
                    </div>
                    <div class="w-full sm:w-1/2 space-y-2 max-h-[180px] sm:max-h-[200px] overflow-y-auto custom-scrollbar pr-1 sm:pr-2">
                        <?php
                        foreach ($status_asn_stats as $index => $stat):
                            $label = strtoupper($stat['label']);
                            $bgClass = 'bg-slate-300';
                            if ($label === 'PNS') $bgClass = 'bg-slate-800';
                            elseif ($label === 'PPPK') $bgClass = 'bg-slate-600';
                            elseif (strpos($label, 'PPPK PARUH WAKTU') !== false) $bgClass = 'bg-slate-400';
                        ?>
                            <div class="p-2 bg-slate-50 border border-slate-200 rounded-lg flex justify-between items-center">
                                <div class="flex items-center truncate mr-2">
                                    <span class="w-2 h-2 rounded-full mr-2 shrink-0 <?= $bgClass ?>"></span>
                                    <span class="text-[10px] font-bold text-slate-700 uppercase truncate"><?= esc($stat['label']) ?></span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-[9px] font-bold text-slate-400"><?= ($total_emails ?? 0) > 0 ? round(($stat['count'] / $total_emails) * 100) : 0 ?>%</span>
                                    <span class="text-xs font-bold text-slate-800"><?= number_format($stat['count'], 0, ',', '.') ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tabel Akun Email -->
    <div id="email-table-container" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-slate-100 bg-slate-50">
            <form id="unitDetailFilterForm" method="GET" action="" class="grid grid-cols-1 md:grid-cols-12 gap-y-4 gap-x-4 items-end">
                <div class="md:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-widest">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= esc($search ?? '') ?>" class="block w-full pl-9 pr-3 py-2 bg-white border <?= !empty($search) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm transition-all placeholder-slate-400" placeholder="Cari...">
                    </div>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-widest">Status ASN</label>
                    <select name="status_asn" class="block w-full px-3 py-2 bg-white border <?= !empty($status_asn) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua Status</option>
                        <?php foreach ($status_asn_options as $option): ?>
                            <option value="<?= esc($option['id']) ?>" <?= (($status_asn ?? '') == $option['id']) ? 'selected' : '' ?>><?= esc($option['nama_status_asn']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-widest">Status TTE</label>
                    <select name="bsre_status" class="block w-full px-3 py-2 bg-white border <?= !empty($bsre_status) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua Status</option>
                        <?php foreach ($bsre_status_options as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= ($bsre_status === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-widest">Password</label>
                    <select name="password_status" class="block w-full px-3 py-2 bg-white border <?= !empty($password_status) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua Status</option>
                        <option value="empty" <?= (($password_status ?? '') === 'empty') ? 'selected' : '' ?>>Tanpa Password</option>
                        <option value="filled" <?= (($password_status ?? '') === 'filled') ? 'selected' : '' ?>>Ada Password</option>
                    </select>
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 btn btn-solid text-xs">
                        <i class="fas fa-filter mr-1.5 text-white/80"></i> Filter
                    </button>
                    <a href="<?= site_url('email/unit_kerja/' . $unit_kerja['id']) ?>" class="btn btn-outline text-xs px-3" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm min-w-[700px]">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[10px] font-bold tracking-widest">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200">Email</th>
                        <th class="px-6 py-3 border-b border-slate-200">Jabatan / Status</th>
                        <?php if ($showUnitKerjaColumn ?? false): ?>
                            <th class="px-6 py-3 border-b border-slate-200">Unit Kerja</th>
                        <?php endif; ?>
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
                                        <?php if (!empty($email['nip'])): ?>
                                            <span class="text-[10px] font-medium text-slate-500 font-mono tracking-tight mt-0.5"><?= esc($email['nip']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1" id="pegawai-container-<?= esc($email['user']) ?>" data-nip="<?= esc($email['nip'] ?? '') ?>" data-email="<?= esc($email['email'] ?? '') ?>" data-status-asn-id="<?= esc($email['status_asn_id'] ?? '') ?>">
                                        <?php
                                        $displayJabatan = (!empty($email['is_plt_in_this_unit']) && !empty($email['jabatan_plt'])) ? $email['jabatan_plt'] : ($email['jabatan'] ?: '-');
                                        $isPltInSameUnit = !empty($email['unit_kerja_plt_id']) && (
                                            $email['unit_kerja_plt_id'] == $unit_kerja['id'] || 
                                            $email['unit_kerja_plt_id'] == ($email['unit_kerja_id'] ?? null) ||
                                            (!empty($target_unit_ids) && in_array($email['unit_kerja_plt_id'], $target_unit_ids))
                                        );
                                        ?>
                                        <span class="text-xs font-medium text-slate-700 uppercase tracking-tight jabatan-text"><?= esc($displayJabatan) ?></span>
                                        <?php if (!empty($email['jabatan_plt']) && empty($email['is_plt_in_this_unit']) && $isPltInSameUnit): ?>
                                            <span class="text-xs font-medium text-amber-700 uppercase tracking-tight leading-snug"><?= esc($email['jabatan_plt']) ?></span>
                                        <?php endif; ?>
                                        <span class="text-[9px] font-bold text-slate-700 uppercase tracking-widest"><?= !empty($email['status_asn']) ? esc($email['status_asn']) : 'NON-ASN' ?></span>
                                    </div>
                                </td>
                                <?php if ($showUnitKerjaColumn ?? false): ?>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col unit-kerja-sync-target">
                                            <?php if (!empty($email['parent_unit_kerja_name'])): ?>
                                                <span class="text-[10px] font-bold text-slate-700 uppercase leading-none"><?= esc($email['parent_unit_kerja_name']) ?></span>
                                                <span class="text-xs font-bold text-slate-800 uppercase tracking-tight mt-1"><?= esc($email['unit_kerja_name']) ?></span>
                                            <?php else: ?>
                                                <span class="text-xs font-bold text-slate-800 uppercase tracking-tight"><?= esc($email['unit_kerja_name']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($email['unit_kerja_plt_name']) && $email['unit_kerja_plt_name'] !== $email['unit_kerja_name'] && $isPltInSameUnit): ?>
                                                <span class="text-xs font-bold text-amber-700 uppercase tracking-tight mt-1"><?= esc($email['unit_kerja_plt_name']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div id="bsre-status-<?= esc($email['user']) ?>" data-email="<?= esc($email['email']) ?>" data-name="<?= esc($email['name']) ?>" data-nip="<?= esc($email['nip'] ?? '') ?>" data-nik="<?= esc($email['nik'] ?? '') ?>" data-status="<?= esc($email['bsre_status'] ?? '') ?>">
                                        <?php
                                        $isNeedTte = !empty($email['nip']) || ($email['pimpinan'] ?? 0) == 1 || ($email['pimpinan_desa'] ?? 0) == 1 || !empty($email['unit_kerja_id']);

                                        if ($isNeedTte) {
                                            $st = $email['bsre_status'] ?? '';
                                            $colorClass = 'bg-slate-100 text-slate-700 border-transparent';
                                            $statusLabel = $st ?: 'NOT_SYNCED';

                                            if ($st === 'ISSUE') {
                                                $colorClass = 'bg-emerald-100 text-emerald-800 border-transparent';
                                            } elseif (in_array($st, ['EXPIRED', 'REVOKE', 'SUSPEND'])) {
                                                $colorClass = 'bg-red-100 text-red-700 border-transparent';
                                            } elseif (in_array($st, ['WAITING_FOR_VERIFICATION', 'RENEW', 'NO_CERTIFICATE'])) {
                                                $colorClass = 'bg-amber-50 text-amber-500 border-amber-200';
                                            } elseif ($st === 'NEW') {
                                                $colorClass = 'bg-blue-100 text-slate-700 border-transparent';
                                            }
                                        } else {
                                            $statusLabel = 'NON_TTE';
                                            $colorClass = 'bg-slate-50 text-slate-400 border-slate-200';
                                        }
                                        ?>
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border <?= $colorClass ?>"><?= $statusLabel ?></span>
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
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= ($showUnitKerjaColumn ?? false) ? 5 : 4 ?>" class="px-6 py-20 text-center">
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

        <?= view('components/pagination', ['items' => $emails, 'pager' => $pager, 'label' => 'data']) ?>
    </div>
</div>

<!-- Modal Batch Update Password -->
<?php
$batchPasswordContent = '
    <div class="space-y-4">
        <!-- Mode Selector -->
        <div id="batchPwInputSection">
            <div class="grid grid-cols-2 gap-3 mb-4">
                <button type="button" id="batchPwModeAuto"
                    onclick="setBatchPwMode(\'auto\')"
                    class="flex flex-col items-center gap-1.5 p-4 rounded-xl border-2 border-slate-800 bg-slate-50 text-slate-800 transition-all text-left">
                    <i class="fas fa-magic text-base"></i>
                    <span class="text-[10px] font-bold uppercase tracking-widest mt-0.5">Auto per Akun</span>
                    <span class="text-[9px] text-slate-500 text-center leading-tight">Generate dari nama & NIP<br>(sama seperti edit password)</span>
                </button>
                <button type="button" id="batchPwModeManual"
                    onclick="setBatchPwMode(\'manual\')"
                    class="flex flex-col items-center gap-1.5 p-4 rounded-xl border-2 border-slate-200 bg-white text-slate-500 hover:border-slate-400 transition-all text-left">
                    <i class="fas fa-keyboard text-base"></i>
                    <span class="text-[10px] font-bold uppercase tracking-widest mt-0.5">Manual Seragam</span>
                    <span class="text-[9px] text-slate-500 text-center leading-tight">Satu password untuk<br>semua akun</span>
                </button>
            </div>

            <!-- Info auto mode -->
            <div id="batchPwAutoInfo" class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest mb-1.5"><i class="fas fa-info-circle mr-1.5 text-slate-500"></i>Format Password Otomatis</p>
                <p class="text-xs text-slate-600 font-mono tracking-wide"><span class="bg-white border border-slate-200 rounded px-2 py-1 font-semibold">NamaDepan@XX#</span></p>
                <p class="text-[10px] text-slate-500 mt-2 leading-relaxed">XX = digit ke-3 & ke-4 dari NIP. Untuk akun tanpa NIP, XX = tanggal hari ini.</p>
            </div>

            <!-- Input manual password (tersembunyi di mode auto) -->
            <div id="batchPwManualInput" class="hidden space-y-1.5">
                <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-widest">Password Baru (untuk semua akun)</label>
                <div class="relative">
                    <input type="password" id="batchPasswordInput"
                        class="block w-full px-3 py-2 pr-10 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-mono tracking-wider"
                        placeholder="Minimal 8 karakter..." autocomplete="new-password">
                    <button type="button" onclick="toggleBatchPwVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 transition-colors">
                        <i class="fas fa-eye text-xs" id="batchPwToggleIcon"></i>
                    </button>
                </div>
            </div>

            <p id="batchPwEmailCount" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-3"></p>
        </div>

        <!-- Progress -->
        <div id="batchPwProgressSection" class="hidden space-y-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas fa-spinner fa-spin text-slate-700 text-xs"></i>
                    <span id="batchPwStatusText" class="text-xs font-bold text-slate-800 uppercase tracking-tight">Memulai...</span>
                </div>
                <span id="batchPwProgressPct" class="text-xs font-mono font-bold text-slate-700">0%</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden p-0.5 border border-slate-200">
                <div id="batchPwProgressBar" class="bg-slate-800 h-full rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <p id="batchPwCurrentAccount" class="text-[10px] font-mono text-slate-500 truncate text-center"></p>
        </div>

        <!-- Hasil -->
        <div id="batchPwResultSection" class="hidden py-1">
            <div id="batchPwResultContent"></div>
        </div>
    </div>
';

$batchPasswordFooter = '
    <button type="button" id="batchPwCancelBtn" onclick="closeModal(\'batchPasswordModal\')" class="btn btn-outline text-xs">
        Batal
    </button>
    <button type="button" id="batchPwStartBtn" onclick="startBatchUpdatePassword()" class="btn btn-solid text-xs">
        <i class="fas fa-key mr-1.5 text-white/80"></i> Mulai Update Password
    </button>
';

echo view('components/modal', [
    'id'        => 'batchPasswordModal',
    'title'     => 'Batch Update Password',
    'size'      => 'md',
    'showClose' => true,
    'content'   => $batchPasswordContent,
    'footer'    => $batchPasswordFooter,
], ['saveData' => false]);
?>

<!-- Modal Progress Batch -->
<?php
$modalContent = '
    <div class="space-y-4">
        <div class="w-full bg-slate-100 rounded-full h-2">
            <div id="exportProgressBar" class="bg-slate-700 h-full rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
        <p id="exportStatusText" class="text-center text-[10px] font-bold text-slate-700 uppercase tracking-widest">Memulai...</p>
    </div>
';

echo view('components/modal', [
    'id' => 'exportProgressModal',
    'title' => 'Pemrosesan Dokumen PK Massal',
    'size' => 'sm',
    'showClose' => false,
    'content' => $modalContent
], ['saveData' => false]);
?>

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
                'NEW': '#10b981', // emerald-500
                'NO_CERTIFICATE': '#f59e0b', // amber-500
                'NOT_REGISTERED': '#94a3b8', // slate-400
                'not_synced': '#94a3b8' // slate-400
            };
            const chartData = <?= json_encode($bsre_status_counts) ?>;
            const labels = [],
                series = [],
                colors = [];

            Object.keys(chartData).forEach(key => {
                labels.push(chartData[key].label);
                series.push(chartData[key].count);
                colors.push(tteColorMap[key] || '#94a3b8');
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
                                    color: '#94a3b8',
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
                                    color: '#94a3b8',
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

        // ASN Status Chart
        <?php if (!empty($status_asn_stats)): ?>
            const statusAsnStats = <?= json_encode($status_asn_stats) ?>;
            const asnColors = statusAsnStats.map(s => {
                const label = s.label.toUpperCase();
                if (label === 'PNS') return '#1e293b'; // slate-800
                if (label === 'PPPK') return '#475569'; // slate-600
                if (label.includes('PPPK PARUH WAKTU')) return '#94a3b8'; // slate-400
                return '#cbd5e1'; // slate-300
            });

            new ApexCharts(document.querySelector("#asnStatusChart"), {
                series: statusAsnStats.map(s => s.count),
                labels: statusAsnStats.map(s => s.label),
                colors: asnColors,
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
                                    color: '#94a3b8',
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
                                    color: '#94a3b8',
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
                    return showGlobalError('Gagal Mengambil Data', data.message || 'Gagal mengambil data email.');
                }

                if (!data.emails || !data.emails.length) {
                    modal.classList.add('hidden');
                    return showGlobalAlert('Informasi', 'Tidak ada data PPPK di unit ini.', 'info');
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

        if (!confirm('Sinkronkan status TTE?')) {
            return;
        }

        const mainBtn = document.getElementById('mainSyncBtn');
        const syncBtn = document.getElementById('syncAllTteBtn');
        const originalMainContent = mainBtn.innerHTML;
        const originalBtnContent = syncBtn.innerHTML;

        // 1. Scroll ke tabel secara smooth
        const tableContainer = document.getElementById('email-table-container');
        if (tableContainer) {
            tableContainer.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        // 2. Disable tombol dan beri feedback visual
        mainBtn.disabled = true;
        mainBtn.classList.add('opacity-75', 'cursor-not-allowed', 'bg-slate-700');
        syncBtn.disabled = true;
        syncBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sinkronisasi TTE...';

        // 3. Proses secara sekuensial untuk menghindari load server berlebih
        let processed = 0;
        let success = 0;
        let failed = 0;

        for (const container of containers) {
            const email = container.getAttribute('data-email');
            
            // Scroll ke container yang sedang diproses
            container.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            // Set loading state untuk baris ini (skeleton pulse)
            container.innerHTML = '<span class="inline-block h-4 w-16 bg-slate-200 rounded animate-pulse align-middle"></span>';

            try {
                const response = await fetch('<?= site_url('bsre/sync-status') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                    },
                    body: 'email=' + encodeURIComponent(email)
                });

                const data = await response.json();

                if (data.status === 'success') {
                    const colorClass = getJsStatusColor(data.bsre_status);
                    container.innerHTML = `<span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border ${colorClass}">${data.bsre_status}</span>`;
                    success++;
                } else {
                    const errorMsg = data.message || 'Gagal';
                    container.innerHTML = `<button onclick="showGlobalError('Gagal Sinkronisasi', '${errorMsg.replace(/'/g, "\\'")}')" class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border bg-red-50 text-red-600 border-red-200 hover:bg-red-100 transition-colors">ERROR</button>`;
                    failed++;
                }
            } catch (error) {
                console.error('Sync failed for ' + email, error);
                const errorMsg = 'Masalah Koneksi Jaringan';
                container.innerHTML = `<button onclick="showGlobalError('Kesalahan Jaringan', '${errorMsg}')" class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border bg-red-50 text-red-600 border-red-200 hover:bg-red-100 transition-colors">ERROR</button>`;
                failed++;
            }

            processed++;
            mainBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> TTE: ${processed}/${containers.length}`;
            syncBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Sinkronisasi ${processed}/${containers.length}...`;
        }

        // 4. Restore tombol
        mainBtn.disabled = false;
        mainBtn.classList.remove('opacity-75', 'cursor-not-allowed', 'bg-slate-700');
        mainBtn.innerHTML = originalMainContent;
        syncBtn.disabled = false;
        syncBtn.innerHTML = originalBtnContent;

        showSyncResult(processed, success, failed);
    }

    async function syncAllPegawai() {
        const containers = document.querySelectorAll('[id^="pegawai-container-"]');
        const validContainers = Array.from(containers).filter(c => (c.getAttribute('data-nip') && c.getAttribute('data-nip').trim() !== '') || (c.getAttribute('data-email') && c.getAttribute('data-email').trim() !== ''));
        
        if (!validContainers.length) {
            if (typeof window.showGlobalAlert === 'function') {
                window.showGlobalAlert('Info Sinkronisasi', 'Tidak ada data akun yang dapat disinkronkan.', 'info');
            } else if (typeof window.showGlobalError === 'function') {
                window.showGlobalError('Info Sinkronisasi', 'Tidak ada data akun yang dapat disinkronkan.');
            }
            return;
        }

        if (!confirm('Sinkronkan data pegawai?')) {
            return;
        }

        const mainBtn = document.getElementById('mainSyncBtn');
        const syncBtn = document.getElementById('syncAllPegawaiBtn');
        const originalMainContent = mainBtn.innerHTML;
        const originalBtnContent = syncBtn.innerHTML;

        mainBtn.disabled = true;
        mainBtn.classList.add('opacity-75', 'cursor-not-allowed', 'bg-slate-700');
        syncBtn.disabled = true;
        syncBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sinkronisasi Pegawai...';

        let processed = 0;
        let success = 0;
        let failed = 0;

        for (const container of validContainers) {
            const nip = container.getAttribute('data-nip') || '';
            const email = container.getAttribute('data-email') || '';
            const row = container.closest('tr');
            const textElement = container.querySelector('.jabatan-text');
            const unitTarget = row ? row.querySelector('.unit-kerja-sync-target') : null;
            const originalJabatan = textElement ? textElement.textContent : '';
            const originalUnit = unitTarget ? unitTarget.innerHTML : '';
            
            container.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (textElement) {
                textElement.innerHTML = '<div class="h-3.5 bg-slate-200 rounded animate-pulse w-36 my-0.5"></div>';
            }
            if (unitTarget) {
                unitTarget.innerHTML = '<div class="space-y-1.5 py-0.5"><div class="h-2.5 bg-slate-200 rounded animate-pulse w-20"></div><div class="h-3.5 bg-slate-200 rounded animate-pulse w-32"></div></div>';
            }

            let fetchSuccess = false;
            let lastData = null;
            let isRateLimit = false;

            for (let attempt = 0; attempt <= 2; attempt++) {
                try {
                    const response = await fetch('<?= site_url('email/sync_pegawai') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                        },
                        body: 'nip=' + encodeURIComponent(nip) + '&email=' + encodeURIComponent(email)
                    });

                    const is429 = response.status === 429;
                    lastData = await response.json().catch(() => null);

                    isRateLimit = is429 || (lastData && (lastData.code === 429 || lastData.is_rate_limit || (lastData.message && /rate\s*limit|terlalu\s*banyak/i.test(lastData.message))));

                    if (isRateLimit && attempt < 2) {
                        const waitSec = (attempt + 1) * 2;
                        syncBtn.innerHTML = `<i class="fas fa-hourglass-half animate-spin mr-2"></i> Pedinginan Rate Limit (${waitSec}s)...`;
                        if (textElement) {
                            textElement.innerHTML = `<span class="text-amber-600 font-bold text-[10px] animate-pulse"><i class="fas fa-hourglass-half mr-1"></i> RATE LIMIT (${waitSec}s)</span>`;
                        }
                        await new Promise(resolve => setTimeout(resolve, waitSec * 1000));
                        continue;
                    }

                    if (lastData && lastData.success) {
                        fetchSuccess = true;
                    }
                    break;
                } catch (error) {
                    if (attempt < 2) {
                        await new Promise(resolve => setTimeout(resolve, 2000));
                        continue;
                    }
                    break;
                }
            }

            if (fetchSuccess && lastData) {
                if (textElement) {
                    if (lastData.no_data) {
                        textElement.innerHTML = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-bold uppercase border bg-amber-50 text-amber-600 border-amber-200" title="Data tidak ditemukan di API">NO DATA</span>`;
                    } else if (lastData.data && lastData.data.jabatan) {
                        textElement.textContent = lastData.data.jabatan;
                    } else {
                        textElement.textContent = originalJabatan;
                    }
                }

                if (unitTarget && lastData.data) {
                    const parentName = lastData.data.parent_unit_kerja_name || '';
                    const unitName = lastData.data.unit_kerja_name || '';
                    if (unitName) {
                        let unitHtml = `<span class="text-xs font-bold text-slate-800 uppercase tracking-tight">${unitName}</span>`;
                        if (parentName) {
                            unitHtml += `<span class="text-[9px] font-bold text-slate-500 uppercase leading-none mt-0.5">${parentName}</span>`;
                        }
                        unitTarget.innerHTML = unitHtml;
                    } else if (originalUnit) {
                        unitTarget.innerHTML = originalUnit;
                    }
                }
                success++;
            } else {
                if (textElement) {
                    if (isRateLimit) {
                        textElement.innerHTML = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-bold uppercase border bg-amber-50 text-amber-700 border-amber-300" title="API Terkena Rate Limit (Silakan coba beberapa saat lagi)">RATE LIMIT</span>`;
                    } else {
                        textElement.innerHTML = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-bold uppercase border bg-red-50 text-red-600 border-red-200" title="${((lastData && lastData.message) ? lastData.message : 'Sinkronisasi Gagal').replace(/"/g, '&quot;')}">FAILED</span>`;
                    }
                }
                if (unitTarget && originalUnit) {
                    unitTarget.innerHTML = originalUnit;
                }
                failed++;
            }

            processed++;
            mainBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> PEG: ${processed}/${validContainers.length}`;
            syncBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Sinkronisasi ${processed}/${validContainers.length}...`;

            // Micro-pacing delay (200ms) untuk mencegah penumpukan request ke server
            await new Promise(resolve => setTimeout(resolve, 200));
        }

        mainBtn.disabled = false;
        mainBtn.classList.remove('opacity-75', 'cursor-not-allowed', 'bg-slate-700');
        mainBtn.innerHTML = originalMainContent;
        syncBtn.disabled = false;
        syncBtn.innerHTML = originalBtnContent;

        showSyncResult(processed, success, failed);
    }

    // ============================
    // Batch Update Password
    // ============================

    let _batchPwMode = 'auto'; // 'auto' | 'manual'

    function setBatchPwMode(mode) {
        _batchPwMode = mode;
        const autoBtn    = document.getElementById('batchPwModeAuto');
        const manualBtn  = document.getElementById('batchPwModeManual');
        const autoInfo   = document.getElementById('batchPwAutoInfo');
        const manualInp  = document.getElementById('batchPwManualInput');

        if (mode === 'auto') {
            autoBtn.classList.replace('border-slate-200', 'border-slate-800');
            autoBtn.classList.replace('bg-white', 'bg-slate-50');
            autoBtn.classList.replace('text-slate-500', 'text-slate-800');
            manualBtn.classList.replace('border-slate-800', 'border-slate-200');
            manualBtn.classList.replace('bg-slate-50', 'bg-white');
            manualBtn.classList.replace('text-slate-800', 'text-slate-500');
            autoInfo.classList.remove('hidden');
            manualInp.classList.add('hidden');
        } else {
            manualBtn.classList.replace('border-slate-200', 'border-slate-800');
            manualBtn.classList.replace('bg-white', 'bg-slate-50');
            manualBtn.classList.replace('text-slate-500', 'text-slate-800');
            autoBtn.classList.replace('border-slate-800', 'border-slate-200');
            autoBtn.classList.replace('bg-slate-50', 'bg-white');
            autoBtn.classList.replace('text-slate-800', 'text-slate-500');
            autoInfo.classList.add('hidden');
            manualInp.classList.remove('hidden');
        }
    }

    /**
     * Logika identik dengan generatePassword() di edit_password.php
     */
    function generatePasswordForAccount(name, nip, useAltNipPart = false) {
        let suffix = new Date().getDate();
        if (nip && nip.length >= 8) {
            suffix = useAltNipPart ? nip.substring(6, 8) : nip.substring(2, 4);
        } else if (nip && nip.length >= 4) {
            suffix = nip.substring(2, 4);
        }
        const namePart = name.replace(/\s+/g, '').substring(0, 5).toLowerCase();
        if (!namePart) return `@${suffix}#`;
        const capitalizedNamePart = namePart.charAt(0).toUpperCase() + namePart.slice(1);
        return `${capitalizedNamePart}@${suffix}#`;
    }

    function openBatchPasswordModal() {
        const emailRows = document.querySelectorAll('[id^="bsre-status-"]');
        const emailCount = emailRows.length;
        document.getElementById('batchPwEmailCount').textContent =
            `Akan memperbarui password untuk ${emailCount} akun yang tampil.`;

        // Reset state
        const pwInput = document.getElementById('batchPasswordInput');
        if (pwInput) { pwInput.value = ''; pwInput.type = 'password'; }
        const toggleIcon = document.getElementById('batchPwToggleIcon');
        if (toggleIcon) toggleIcon.className = 'fas fa-eye text-xs';

        document.getElementById('batchPwInputSection').classList.remove('hidden');
        document.getElementById('batchPwProgressSection').classList.add('hidden');
        document.getElementById('batchPwResultSection').classList.add('hidden');

        // Reset ke mode auto
        _batchPwMode = 'auto';
        setBatchPwMode('auto');

        const startBtn = document.getElementById('batchPwStartBtn');
        const cancelBtn = document.getElementById('batchPwCancelBtn');
        if (startBtn) startBtn.classList.remove('hidden');
        if (cancelBtn) cancelBtn.textContent = 'Batal';

        openModal('batchPasswordModal');
    }

    function closeBatchPasswordModal() {
        closeModal('batchPasswordModal');
    }

    function toggleBatchPwVisibility() {
        const input = document.getElementById('batchPasswordInput');
        const icon  = document.getElementById('batchPwToggleIcon');
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash text-xs';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye text-xs';
        }
    }

    async function startBatchUpdatePassword() {
        // Kumpulkan data akun dari atribut pada elemen bsre-status-*
        const emailRows = document.querySelectorAll('[id^="bsre-status-"]');
        const accounts = Array.from(emailRows).map(el => ({
            email : el.getAttribute('data-email'),
            name  : el.getAttribute('data-name') || '',
            nip   : el.getAttribute('data-nip')  || '',
        })).filter(a => Boolean(a.email));

        if (!accounts.length) {
            showGlobalAlert('Informasi', 'Tidak ada akun yang dapat diperbarui.', 'info');
            return;
        }

        // Validasi mode manual
        let manualPassword = '';
        if (_batchPwMode === 'manual') {
            manualPassword = (document.getElementById('batchPasswordInput').value || '').trim();
            if (!manualPassword) {
                showGlobalAlert('Perhatian', 'Password tidak boleh kosong.', 'warning');
                return;
            }
            if (manualPassword.length < 8) {
                showGlobalAlert('Perhatian', 'Password minimal 8 karakter.', 'warning');
                return;
            }
        }

        const modeLabel = _batchPwMode === 'auto' ? 'otomatis per akun' : `seragam "${manualPassword}"`;
        if (!confirm(`Yakin ingin memperbarui password ${accounts.length} akun dengan mode ${modeLabel}?\n\nTindakan ini tidak dapat dibatalkan!`)) {
            return;
        }

        // Tampilkan progress
        document.getElementById('batchPwInputSection').classList.add('hidden');
        document.getElementById('batchPwProgressSection').classList.remove('hidden');
        document.getElementById('batchPwActionFooter').classList.add('hidden');

        const progressBar = document.getElementById('batchPwProgressBar');
        const statusText  = document.getElementById('batchPwStatusText');
        const pctText     = document.getElementById('batchPwProgressPct');
        const curAccount  = document.getElementById('batchPwCurrentAccount');

        let processed = 0, success = 0, failed = 0;
        const failedAccounts = [];

        for (const account of accounts) {
            const password = _batchPwMode === 'auto'
                ? generatePasswordForAccount(account.name, account.nip)
                : manualPassword;

            if (statusText) statusText.textContent = `Memproses (${processed + 1}/${accounts.length})`;
            if (curAccount) curAccount.textContent = account.email;

            try {
                const response = await fetch('<?= site_url('email/api_batch_update_password') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                    },
                    body: JSON.stringify({ email: account.email, password: password })
                });

                const data = await response.json();
                if (data.success) {
                    success++;
                } else {
                    failed++;
                    failedAccounts.push({ email: account.email, reason: data.message || 'Gagal' });
                }
            } catch (err) {
                failed++;
                failedAccounts.push({ email: account.email, reason: 'Kesalahan jaringan' });
            }

            processed++;
            const pct = Math.round((processed / accounts.length) * 100);
            if (progressBar) progressBar.style.width = pct + '%';
            if (pctText) pctText.textContent = pct + '%';
        }

        // Tampilkan hasil
        document.getElementById('batchPwProgressSection').classList.add('hidden');
        document.getElementById('batchPwResultSection').classList.remove('hidden');

        let resultHtml = `
            <div class="space-y-4">
                <div class="flex items-center gap-3 p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="w-10 h-10 rounded-lg ${failed === 0 ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-amber-100 text-amber-700 border border-amber-200'} flex items-center justify-center shrink-0 text-base">
                        <i class="fas ${failed === 0 ? 'fa-check-circle' : 'fa-exclamation-triangle'}"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Proses Pembaruan Selesai</h4>
                        <p class="text-[10px] font-medium text-slate-500 mt-0.5">${success} dari ${accounts.length} akun berhasil diperbarui.</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center">
                        <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Berhasil</p>
                        <p class="text-2xl font-bold text-emerald-700 mt-1">${success}</p>
                    </div>
                    <div class="${failed > 0 ? 'bg-red-50 border border-red-200' : 'bg-slate-50 border border-slate-200'} rounded-xl p-4 text-center">
                        <p class="text-[10px] font-bold ${failed > 0 ? 'text-red-600' : 'text-slate-400'} uppercase tracking-widest">Gagal</p>
                        <p class="text-2xl font-bold ${failed > 0 ? 'text-red-700' : 'text-slate-700'} mt-1">${failed}</p>
                    </div>
                </div>`;

        if (failedAccounts.length > 0) {
            resultHtml += `
                <div class="space-y-2">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Daftar Akun Gagal</p>
                    <div class="max-h-40 overflow-y-auto custom-scrollbar space-y-1.5 pr-1">`;
            failedAccounts.forEach(f => {
                resultHtml += `
                    <div class="flex justify-between items-center p-2.5 bg-red-50/70 border border-red-100 rounded-lg text-xs">
                        <span class="text-slate-700 font-mono font-medium truncate mr-2">${f.email}</span>
                        <span class="text-[10px] font-bold text-red-600 uppercase shrink-0">${f.reason}</span>
                    </div>`;
            });
            resultHtml += `
                    </div>
                </div>`;
        }

        resultHtml += `</div>`;
        document.getElementById('batchPwResultContent').innerHTML = resultHtml;

        document.getElementById('batchPwActionFooter').classList.remove('hidden');
        const startBtn = document.getElementById('batchPwStartBtn');
        const cancelBtn = document.getElementById('batchPwCancelBtn');
        if (startBtn) startBtn.classList.add('hidden');
        if (cancelBtn) {
            cancelBtn.textContent = 'Selesai';
            cancelBtn.className = 'btn btn-solid text-xs';
        }
    }

</script>
<?= $this->endSection() ?>