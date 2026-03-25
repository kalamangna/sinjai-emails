<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Manajemen User Login</h1>
        <a href="<?= site_url('auth/users/add') ?>" class="btn btn-solid">
            <i class="fas fa-plus mr-2 text-white/80"></i> Tambah User
        </a>
    </div>

    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200">Username</th>
                        <th class="px-6 py-3 border-b border-slate-200">Role</th>
                        <th class="px-6 py-3 border-b border-slate-200">Dibuat Pada</th>
                        <th class="px-6 py-3 border-b border-slate-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-slate-800 tracking-tight"><?= esc($user['username']) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border <?= $user['role'] === 'super_admin' ? 'bg-slate-800 text-white border-transparent' : 'bg-slate-100 text-slate-700 border-slate-200' ?>">
                                        <?= strtoupper(str_replace('_', ' ', $user['role'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-[10px] font-medium text-slate-500 uppercase">
                                    <?= date('d M Y, H:i', strtotime($user['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= site_url('auth/users/edit/' . $user['id']) ?>" class="btn btn-table" title="Edit">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <?php if (session()->get('id') != $user['id']): ?>
                                            <form action="<?= site_url('auth/users/delete/' . $user['id']) ?>" method="post" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
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
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400 font-bold uppercase tracking-widest text-[10px]">
                                Tidak ada data user.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>