<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Email</h1>
            <?php if (!empty($last_sync_time)): ?>
                <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest mt-1">
                    <i class="fas fa-history mr-1"></i> Terakhir Sync:
                    <span class="text-slate-800"><?= formatTanggalWaktu($last_sync_time) ?></span>
                </p>
            <?php endif; ?>
        </div>

        <div class="flex items-center gap-2 w-full lg:w-auto">
            <?php if (in_array(session()->get('role'), ['super_admin', 'admin'])): ?>
                <a href="<?= site_url('email/swap_data') ?>" class="flex-1 lg:flex-none btn btn-outline no-underline" title="Tukar Data Profil Antar Dua Akun">
                    <i class="fas fa-exchange-alt mr-2 text-slate-700"></i> Tukar Data
                </a>
                <a href="<?= site_url('email/create') ?>" class="flex-1 lg:flex-none btn btn-outline no-underline">
                    <i class="fas fa-plus mr-2 text-slate-700"></i> Tambah
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Metrik -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-lg shadow-sm p-6">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Email</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($total_emails ?? 0, 0, ',', '.') ?></h3>
        </div>
        <div class="bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-lg shadow-sm p-6">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Email Aktif</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($active_count ?? 0, 0, ',', '.') ?></h3>
        </div>
        <div class="bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-lg shadow-sm p-6">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">TTE Aktif</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($active_bsre_count ?? 0, 0, ',', '.') ?></h3>
        </div>
    </div>

    <!-- Tabel dan Pencarian -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50">
            <form method="GET" action="<?= site_url('email') ?>" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-6 lg:col-span-8">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= esc($search ?? '') ?>" class="block w-full pl-9 pr-3 py-2 bg-white border <?= !empty($search) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm transition-all" placeholder="Cari nama, NIP, atau NIK...">
                    </div>
                </div>

                <div class="md:col-span-4 lg:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Status TTE</label>
                    <select name="bsre_status" class="block w-full px-3 py-2 bg-white border <?= !empty($bsre_status) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua Status</option>
                        <?php foreach ($bsre_status_options ?? [] as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= (($bsre_status ?? '') === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2 lg:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 btn btn-solid">
                        <i class="fas fa-filter mr-2 text-white/80"></i> Filter
                    </button>
                    <a href="<?= site_url('email') ?>" class="btn btn-outline" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200">Email</th>
                        <th class="px-6 py-3 border-b border-slate-200">Unit Kerja</th>
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
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-slate-800 lowercase leading-tight"><?= esc($email['email']) ?></span>
                                            <?php if (!empty($email['pensiun_at'])): ?>
                                                <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase bg-red-600 text-white leading-none tracking-tighter">PENSIUN</span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-[10px] font-bold text-slate-700 uppercase tracking-tight mt-0.5"><?= esc($email['name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <?php if (!empty($email['parent_unit_kerja_name'])): ?>
                                            <span class="text-[10px] font-bold text-slate-700 uppercase leading-none"><?= esc($email['parent_unit_kerja_name']) ?></span>
                                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight mt-1"><?= esc($email['unit_kerja_name']) ?></span>
                                        <?php else: ?>
                                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight"><?= esc($email['unit_kerja_name']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $isNeedTte = !empty($email['nip']) || ($email['pimpinan'] ?? 0) == 1 || ($email['pimpinan_desa'] ?? 0) == 1 || !empty($email['unit_kerja_id']);
                                    
                                    if ($isNeedTte) {
                                        $status = $email['bsre_status'] ?? '';
                                        $colorClass = 'bg-slate-100 text-slate-700 border-transparent';
                                        $statusLabel = $status ?: 'NOT_SYNCED';

                                        if ($status === 'ISSUE') {
                                            $colorClass = 'bg-emerald-100 text-emerald-800 border-transparent';
                                        } elseif (in_array($status, ['EXPIRED', 'REVOKE', 'SUSPEND'])) {
                                            $colorClass = 'bg-red-100 text-red-700 border-transparent';
                                        } elseif (in_array($status, ['WAITING_FOR_VERIFICATION', 'RENEW', 'NO_CERTIFICATE'])) {
                                            $colorClass = 'bg-amber-50 text-amber-500 border-amber-200';
                                        } elseif ($status === 'NEW') {
                                            $colorClass = 'bg-blue-100 text-slate-700 border-transparent';
                                        }
                                    } else {
                                        $statusLabel = 'NON_TTE';
                                        $colorClass = 'bg-slate-50 text-slate-400 border-slate-200';
                                    }
                                    ?>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border <?= $colorClass ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="btn btn-table" title="Detail">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <?php if (session()->get('role') === 'super_admin'): ?>
                                            <form action="<?= site_url('email/delete/' . $email['id']) ?>" method="post" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-table" title="Hapus">
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
                            <td colspan="4" class="px-6 py-20 text-center">
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
</script>
<?= $this->endSection() ?>