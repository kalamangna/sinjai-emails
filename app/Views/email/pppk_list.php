<?php echo $this->extend('layouts/main'); ?>
<?php echo $this->section('content'); ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight"><?php echo $title; ?></h1>
            <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest mt-1">
                Total: <span class="text-slate-800"><?php echo number_format($total_count, 0, ',', '.'); ?></span> Pegawai
            </p>
        </div>

        <div class="flex items-center gap-2 w-full lg:w-auto">
            <div class="flex-1 lg:flex-none flex gap-2">
                <button onclick="syncAllOnPage()" id="batchSyncBtn" class="flex-1 lg:flex-none btn btn-solid group">
                    <i class="fas fa-fingerprint mr-2 group-hover:scale-110 transition-transform"></i>
                    <span>Sync TTE</span>
                </button>
                <button onclick="syncAllPegawai()" id="batchSyncPegawaiBtn" class="flex-1 lg:flex-none btn btn-outline group bg-white">
                    <i class="fas fa-sync-alt mr-2 group-hover:rotate-180 transition-transform duration-500 text-slate-700"></i>
                    <span class="text-slate-700">Sync Pegawai</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Tabel Pegawai -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-slate-100 bg-slate-50">
            <form id="pppkFilterForm" method="GET" action="" class="grid grid-cols-1 md:grid-cols-12 gap-y-4 gap-x-4 items-end">
                <div class="md:col-span-4">
                    <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-widest">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= esc($search ?? '') ?>" class="block w-full pl-9 pr-3 py-2 bg-white border <?= !empty($search) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm transition-all placeholder-slate-400" placeholder="Cari nama, email, NIP...">
                    </div>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-widest">Filter BUP</label>
                    <select name="bup_status" class="block w-full px-3 py-2 bg-white border <?= !empty($bup_status) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua BUP</option>
                        <?php foreach ($bup_status_options as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= (($bup_status ?? '') === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-widest">Status TTE</label>
                    <select name="bsre_status" class="block w-full px-3 py-2 bg-white border <?= !empty($bsre_status) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua Status</option>
                        <?php foreach ($bsre_status_options as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= (($bsre_status ?? '') === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 btn btn-solid text-xs">
                        <i class="fas fa-filter mr-1.5 text-white/80"></i> Filter
                    </button>
                    <a href="<?= site_url('email/pppk') ?>" class="btn btn-outline text-xs px-3" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[10px] font-bold tracking-widest">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200">No. PK</th>
                        <th class="px-6 py-3 border-b border-slate-200">Nama / NIP</th>
                        <th class="px-6 py-3 border-b border-slate-200">Jabatan / Unit Kerja</th>
                        <th class="px-6 py-3 border-b border-slate-200">Status TTE</th>
                        <th class="px-6 py-3 border-b border-slate-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($emails)): ?>
                        <?php foreach ($emails as $email): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        <?php echo esc($email['nomor_pk'] ?: '-'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col" id="pegawai-container-<?php echo $email['id']; ?>" data-nip="<?php echo esc($email['nip']); ?>">
                                        <span class="font-bold text-slate-800 uppercase tracking-tight leading-tight"><?php echo esc($email['name']); ?></span>
                                        <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                                            <span class="text-[10px] font-bold text-slate-500 font-mono">NIP: <?php echo esc($email['nip'] ?: '-'); ?></span>
                                            <?php
                                            $bupInfo = hitungBupInfo($email);
                                            if ($bupInfo && ($bupInfo['is_approaching'] || $bupInfo['is_pensiun'])):
                                                $bupBadgeClass = $bupInfo['is_pensiun']
                                                    ? 'bg-rose-50 text-rose-700 border-rose-200'
                                                    : 'bg-amber-50 text-amber-700 border-amber-200';
                                                $bupTooltip = $bupInfo['is_pensiun']
                                                    ? 'Telah mencapai BUP (TMT ' . formatTanggal($bupInfo['tmt_pensiun']) . ')'
                                                    : 'Mendekati BUP: ' . $bupInfo['sisa_waktu_label'] . ' (TMT ' . formatTanggal($bupInfo['tmt_pensiun']) . ')';
                                            ?>
                                                <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase border <?= $bupBadgeClass ?>" title="<?= esc($bupTooltip) ?>">
                                                    BUP <?= $bupInfo['bup_age'] ?> THN
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tight mb-1 jabatan-sync-target"><?= esc($email['jabatan'] ?: '-') ?></span>
                                        <div class="unit-kerja-sync-target flex flex-col">
                                            <?php if (!empty($email['parent_unit_kerja_name'])): ?>
                                                <span class="text-[10px] font-bold text-slate-700 uppercase leading-none"><?php echo esc($email['parent_unit_kerja_name']); ?></span>
                                                <span class="text-xs font-bold text-slate-800 uppercase tracking-tight mt-1"><?php echo esc($email['unit_kerja_name']); ?></span>
                                            <?php else: ?>
                                                <span class="text-xs font-bold text-slate-800 uppercase tracking-tight"><?php echo esc($email['unit_kerja_name'] ?: '-'); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div id="bsre-status-<?php echo $email['id']; ?>" class="bsre-status-container" data-email="<?php echo esc($email['email']); ?>">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest animate-pulse">Checking...</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="<?php echo site_url('email/detail/' . $email['user']); ?>" class="btn btn-table" data-tooltip-target="tooltip-detail-<?= $email['id'] ?>">
                                            <i class="fas fa-eye text-xs text-slate-700"></i>
                                        </a>
                                        <div id="tooltip-detail-<?= $email['id'] ?>" role="tooltip" class="absolute z-10 invisible inline-block px-2.5 py-1 text-[10px] font-bold text-white bg-slate-900 rounded-lg shadow-sm opacity-0 tooltip" x-cloak>
                                            Detail Akun
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                        <a href="<?php echo site_url('email/edit_pk/' . $email['user']); ?>" class="btn btn-table" data-tooltip-target="tooltip-editpk-<?= $email['id'] ?>">
                                            <i class="fas fa-file-contract text-xs text-slate-700"></i>
                                        </a>
                                        <div id="tooltip-editpk-<?= $email['id'] ?>" role="tooltip" class="absolute z-10 invisible inline-block px-2.5 py-1 text-[10px] font-bold text-white bg-slate-900 rounded-lg shadow-sm opacity-0 tooltip" x-cloak>
                                            Edit Perjanjian Kerja
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Data tidak ditemukan</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php echo view('components/pagination', ['items' => $emails, 'pager' => $pager, 'label' => 'Pegawai']); ?>
    </div>
</div>
<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>
<script>
    function syncAllOnPage() {
        window.syncAllBsreStatus('batchSyncBtn', 'Sinkronkan status TTE?');
    }

    function syncAllPegawai() {
        window.syncAllPegawai('batchSyncPegawaiBtn', 'Sinkronkan data pegawai?');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const emails = <?php echo json_encode($emails); ?>;
        emails.forEach(email => {
            if (typeof window.renderBsreStatus === 'function') {
                window.renderBsreStatus(email.bsre_status, `bsre-status-${email.id}`);
            }
        });
    });
</script>
<?php echo $this->endSection(); ?>