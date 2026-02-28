<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-md mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= site_url('/') ?>" class="inline-flex items-center justify-center w-10 h-10 bg-white border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition-all shadow-sm no-underline">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Ganti Password</h1>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-8">
            <form action="<?= site_url('user/update_password') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label for="old_password" class="block text-xs font-medium text-slate-700 mb-1 uppercase tracking-wider">Password Lama</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                            <i class="fas fa-lock text-xs"></i>
                        </span>
                        <input type="password" name="old_password" id="old_password" required
                            class="block w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 transition-all"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4">
                    <label for="new_password" class="block text-xs font-medium text-slate-700 mb-1 uppercase tracking-wider">Password Baru</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                            <i class="fas fa-key text-xs"></i>
                        </span>
                        <input type="password" name="new_password" id="new_password" required minlength="8"
                            class="block w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 transition-all"
                            placeholder="Min. 8 karakter">
                    </div>
                </div>

                <div>
                    <label for="confirm_password" class="block text-xs font-medium text-slate-700 mb-1 uppercase tracking-wider">Konfirmasi Password Baru</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                            <i class="fas fa-check-double text-xs"></i>
                        </span>
                        <input type="password" name="confirm_password" id="confirm_password" required minlength="8"
                            class="block w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-medium text-slate-800 transition-all"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-bold text-xs uppercase tracking-widest transition-all shadow-sm">
                        <i class="fas fa-save mr-2 text-white/80"></i> Simpan Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
