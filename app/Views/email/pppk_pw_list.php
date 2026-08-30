<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight"><?= $title ?></h1>
            <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest mt-1">
                Total: <span class="text-slate-800"><?= number_format($total_count, 0, ',', '.') ?></span> Pegawai
            </p>
        </div>

        <div class="flex items-center gap-2 w-full lg:w-auto">
            <button onclick="syncAllOnPage()" id="batchSyncBtn" class="flex-1 lg:flex-none btn btn-solid group">
                <i class="fas fa-fingerprint mr-2 group-hover:scale-110 transition-transform"></i>
                <span>Sync TTE</span>
            </button>
        </div>
    </div>

    <!-- Tabel Pegawai -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
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
                        <?php
                        foreach ($emails as $email):
                        ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        <?= esc($email['nomor_pk'] ?: '-') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col" id="pegawai-container-<?= $email['id'] ?>" data-nip="<?= esc($email['nip']) ?>">
                                        <span class="font-bold text-slate-800 uppercase tracking-tight leading-tight"><?= esc($email['name']) ?></span>
                                        <span class="text-[10px] font-bold text-slate-500 mt-0.5">NIP: <?= esc($email['nip'] ?: '-') ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tight mb-1 jabatan-sync-target"><?= esc($email['jabatan'] ?: '-') ?></span>
                                        <div class="unit-kerja-sync-target flex flex-col">
                                            <?php if (!empty($email['parent_unit_kerja_name'])): ?>
                                                <span class="text-[10px] font-bold text-slate-700 uppercase leading-none"><?= esc($email['parent_unit_kerja_name']) ?></span>
                                                <span class="text-xs font-bold text-slate-800 uppercase tracking-tight mt-1"><?= esc($email['unit_kerja_name']) ?></span>
                                            <?php else: ?>
                                                <span class="text-xs font-bold text-slate-800 uppercase tracking-tight"><?= esc($email['unit_kerja_name'] ?: '-') ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div id="bsre-status-<?= $email['id'] ?>" class="bsre-status-container" data-email="<?= esc($email['email']) ?>">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest animate-pulse">Checking...</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="btn btn-table" data-tooltip-target="tooltip-detail-<?= $email['id'] ?>">
                                            <i class="fas fa-eye text-xs text-slate-700"></i>
                                        </a>
                                        <div id="tooltip-detail-<?= $email['id'] ?>" role="tooltip" class="absolute z-10 invisible inline-block px-2.5 py-1 text-[10px] font-bold text-white bg-slate-900 rounded-lg shadow-sm opacity-0 tooltip" x-cloak>
                                            Detail Akun
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                        <a href="<?= site_url('email/edit_pk/' . $email['user']) ?>" class="btn btn-table" data-tooltip-target="tooltip-editpk-<?= $email['id'] ?>">
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

        <?= view('components/pagination', ['items' => $emails, 'pager' => $pager, 'label' => 'Pegawai']) ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function syncAllOnPage() {
        window.syncAllBsreStatus('batchSyncBtn', 'Sinkronkan status TTE?');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const emails = <?= json_encode($emails) ?>;
        emails.forEach(email => {
            if (typeof window.renderBsreStatus === 'function') {
                window.renderBsreStatus(email.bsre_status, `bsre-status-${email.id}`);
            }
        });
    });
</script>
<?= $this->endSection() ?>