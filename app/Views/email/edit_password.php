<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="inline-flex items-center justify-center w-10 h-10 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all shadow-sm no-underline">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-semibold text-gray-900">Ganti Kata Sandi</h1>
    </div>

    <!-- Card Utama -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <form action="<?= site_url('email/update_password/' . $email['user']) ?>" method="post">
            <?= csrf_field() ?>
            <div class="p-8 space-y-6">
                <div class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="w-12 h-12 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-400 shrink-0">
                        <i class="fas fa-user-shield text-xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Akun Email</p>
                        <p class="text-sm font-bold text-gray-900"><?= esc($email['email']) ?></p>
                    </div>
                </div>

                <div class="p-4 rounded-lg bg-amber-50 border border-amber-100 text-[11px] font-medium text-amber-700 leading-relaxed">
                    <div class="flex gap-3">
                        <i class="fas fa-info-circle text-sm mt-0.5"></i>
                        <p>Pembaruan kata sandi akan dikirim langsung ke server cPanel. Mohon gunakan kata sandi yang kuat (minimal 8 karakter dengan kombinasi angka dan simbol).</p>
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi Baru</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <i class="fas fa-lock text-xs"></i>
                        </span>
                        <input type="text" name="password" id="password" required minlength="8"
                            class="block w-full pl-9 pr-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm font-semibold text-gray-900 font-mono tracking-wider"
                            placeholder="Minimal 8 karakter...">
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="bg-gray-50 px-8 py-4 flex flex-col sm:flex-row justify-end gap-3 border-t border-gray-200">
                <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="order-2 sm:order-1 inline-flex items-center justify-center px-6 py-2 bg-white border border-gray-300 rounded-lg font-bold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 shadow-sm transition-all">
                    <i class="fas fa-times mr-2"></i> Batal
                </a>
                <button type="submit" class="order-1 sm:order-2 inline-flex items-center justify-center px-8 py-2 bg-gray-900 text-white rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-gray-800 shadow-sm transition-all">
                    <i class="fas fa-key mr-2"></i> Perbarui Sandi
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
