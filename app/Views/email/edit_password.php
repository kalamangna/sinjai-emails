<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="inline-flex items-center justify-center w-10 h-10 bg-white border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition-all shadow-sm no-underline">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Edit Password</h1>
    </div>

    <!-- Card Utama -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <form action="<?= site_url('email/update_password/' . $email['user']) ?>" method="post">
            <?= csrf_field() ?>
            <div class="p-8 space-y-6">
                <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="w-12 h-12 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-700 shrink-0">
                        <i class="fas fa-user-shield text-xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">Email</p>
                        <p class="text-sm font-bold text-slate-800"><?= esc($email['email']) ?></p>
                    </div>
                </div>

                <div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-[11px] font-medium text-amber-500 leading-relaxed">
                    <div class="flex gap-3">
                        <i class="fas fa-info-circle text-sm mt-0.5"></i>
                        <p>Password baru akan dikirim langsung ke server cPanel. Mohon gunakan password yang kuat (minimal 8 karakter dengan kombinasi angka dan simbol).</p>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Password Baru</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                            <i class="fas fa-lock text-xs"></i>
                        </span>
                        <input type="text" name="password" id="password" required minlength="8"
                            class="block w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm font-semibold text-slate-800 font-mono tracking-wider transition-all"
                            placeholder="Minimal 8 karakter...">
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="bg-slate-50 px-8 py-4 flex flex-col sm:flex-row justify-end gap-3 border-t border-slate-200">
                <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="order-2 sm:order-1 inline-flex items-center justify-center px-6 py-2 bg-white border border-slate-200 rounded-lg font-bold text-xs text-slate-700 uppercase tracking-widest hover:bg-slate-50 shadow-sm transition-all">
                    <i class="fas fa-times mr-2"></i> Batal
                </a>
                <button type="submit" class="order-1 sm:order-2 inline-flex items-center justify-center px-8 py-2 bg-slate-800 text-white rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-slate-700 shadow-sm transition-all">
                    <i class="fas fa-key mr-2 text-white/80"></i> Simpan Password
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>