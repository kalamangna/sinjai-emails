<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Edit User</h1>
        <a href="<?= site_url('auth/users') ?>" class="btn btn-outline">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="bg-white border border-slate-200 rounded-lg shadow-sm max-w-2xl mx-auto">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Edit Informasi User</h3>
        </div>
        <form action="<?= site_url('auth/users/update/' . $user['id']) ?>" method="POST" class="p-6 space-y-4">
            <?= csrf_field() ?>
            
            <div>
                <label for="username" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Username</label>
                <input type="text" id="username" name="username" value="<?= old('username', $user['username']) ?>" required class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 uppercase transition-all" placeholder="Masukkan username...">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Password <span class="text-slate-500 font-normal italic">(Biarkan kosong jika tidak diubah)</span></label>
                <input type="password" id="password" name="password" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 transition-all" placeholder="Masukkan password baru (opsional)...">
                <p class="mt-1 text-[10px] text-slate-500 uppercase font-bold tracking-tight">Minimal 6 karakter jika diisi.</p>
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Role</label>
                <select id="role" name="role" required class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 transition-all cursor-pointer">
                    <option value="admin" <?= old('role', $user['role']) === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="super_admin" <?= old('role', $user['role']) === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                </select>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button type="submit" class="btn btn-solid">
                    <i class="fas fa-save mr-2 text-white/80"></i> Perbarui User
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
