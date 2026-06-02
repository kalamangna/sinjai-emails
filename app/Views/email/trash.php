<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight uppercase">Manajemen Sampah</h1>
            <p class="text-sm font-medium text-slate-500 uppercase tracking-widest mt-1">Daftar Akun yang Dihapus (Soft Deleted)</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200">
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Tgl Dihapus</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($emails)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm font-medium text-slate-500 uppercase">Tidak ada data sampah.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($emails as $email): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 text-sm font-bold text-slate-700"><?= esc($email['email']) ?></td>
                                <td class="px-6 py-4 text-xs font-medium text-slate-600"><?= esc($email['name'] ?? '-') ?></td>
                                <td class="px-6 py-4 text-xs font-medium text-slate-600"><?= esc($email['deleted_at']) ?></td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="<?= site_url('email/trash/restore/' . $email['id']) ?>" class="inline-flex items-center px-3 py-1.5 bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase tracking-wider rounded-lg hover:bg-emerald-100 transition-colors" onclick="return confirm('Pulihkan akun ini?')">
                                        Pulihkan
                                    </a>
                                    <a href="<?= site_url('email/trash/force_delete/' . $email['id']) ?>" class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-700 text-[10px] font-bold uppercase tracking-wider rounded-lg hover:bg-red-100 transition-colors" onclick="return confirm('Hapus permanen akun ini? Tidak dapat dibatalkan.')">
                                        Hapus Permanen
                                    </a>
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
