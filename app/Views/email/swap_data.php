<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="<?= site_url('email') ?>" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition-colors">
                    <i class="fas fa-arrow-left text-sm"></i>
                </a>
                <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Tukar Data Akun</h1>
            </div>
            <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest mt-1 ml-11">Menukar profil NIK, NIP, Nama, dll antar dua email</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="p-4 bg-red-50 border border-red-200 rounded-lg flex items-start">
            <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3"></i>
            <div>
                <h4 class="text-xs font-bold text-red-800 uppercase tracking-wider mb-1">Gagal</h4>
                <p class="text-xs text-red-600"><?= session()->getFlashdata('error') ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-tight">Konfigurasi Swap</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Fitur ini digunakan jika ada dua orang yang salah mengaktivasi TTE (tertukar email). Biodata mereka (NIP, NIK, Nama, dll) akan disilangkan tanpa mengubah alamat email asli dan *password*-nya.
                    </p>
                </div>
            </div>
        </div>

        <form action="<?= site_url('email/swap_process') ?>" method="POST" class="p-6 space-y-8" onsubmit="return confirm('Apakah Anda yakin ingin menukar data profil kedua email ini? Proses ini akan langsung tercatat di Audit Trail.');">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                <!-- Akun 1 -->
                <div class="space-y-4">
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Akun Pertama</span>
                        <div class="w-12 h-12 bg-white rounded-full border border-slate-200 flex items-center justify-center mx-auto shadow-sm mb-3">
                            <i class="fas fa-user text-slate-400"></i>
                        </div>
                        <div class="relative text-left">
                            <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-2">Alamat Email 1</label>
                            <input type="email" name="email_1" value="<?= old('email_1') ?>" required
                                class="w-full bg-white text-xs border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-lg px-3 py-2.5 outline-none transition-all"
                                placeholder="contoh1@sinjaikab.go.id">
                        </div>
                    </div>
                </div>

                <!-- Icon Swap (Desktop) -->
                <div class="hidden md:flex justify-center -mx-4 z-10">
                    <div class="w-10 h-10 rounded-full bg-slate-800 text-white flex items-center justify-center shadow-lg transform hover:rotate-180 transition-transform duration-500">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                </div>

                <!-- Akun 2 -->
                <div class="space-y-4">
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Akun Kedua</span>
                        <div class="w-12 h-12 bg-white rounded-full border border-slate-200 flex items-center justify-center mx-auto shadow-sm mb-3">
                            <i class="fas fa-user-friends text-slate-400"></i>
                        </div>
                        <div class="relative text-left">
                            <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-2">Alamat Email 2</label>
                            <input type="email" name="email_2" value="<?= old('email_2') ?>" required
                                class="w-full bg-white text-xs border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-lg px-3 py-2.5 outline-none transition-all"
                                placeholder="contoh2@sinjaikab.go.id">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50/50 border border-blue-100 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                    <ul class="text-[11px] text-blue-800 space-y-1 list-disc list-inside">
                        <li>Data yang akan disilang: <span class="font-bold">NIK, NIP, Nama Lengkap, Jabatan, Golongan, Unit Kerja.</span></li>
                        <li>Data yang <strong>TIDAK</strong> ikut disilang: Email, Password, Status BSrE.</li>
                    </ul>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-6 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider shadow-sm flex items-center gap-2 transition-all">
                    <i class="fas fa-sync-alt"></i>
                    Eksekusi Tukar Data
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
