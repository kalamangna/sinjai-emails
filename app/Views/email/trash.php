<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Kotak Sampah</h1>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[10px] font-bold tracking-widest">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200">Email</th>
                        <th class="px-6 py-3 border-b border-slate-200">Nama</th>
                        <th class="px-6 py-3 border-b border-slate-200">Tgl Dihapus</th>
                        <th class="px-6 py-3 border-b border-slate-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($emails)): ?>
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
                    <?php else: ?>
                        <?php foreach ($emails as $email): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-medium text-slate-800 lowercase leading-tight"><?= esc($email['email']) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold text-slate-800 uppercase tracking-tight"><?= esc($email['name'] ?? '-') ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold text-slate-800 uppercase tracking-tight"><?= esc($email['deleted_at']) ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= site_url('email/trash/restore/' . $email['id']) ?>" class="btn btn-table text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700" title="Pulihkan" onclick="return confirm('Pulihkan akun ini?');">
                                            <i class="fas fa-undo text-xs"></i>
                                        </a>
                                        <form action="<?= site_url('email/trash/force_delete/' . $email['id']) ?>" method="post" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus permanen akun ini? Tidak dapat dibatalkan.');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-table-danger text-red-600 hover:bg-red-50 hover:text-red-700" title="Hapus Permanen">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
