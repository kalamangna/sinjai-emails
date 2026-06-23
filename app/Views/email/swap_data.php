<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="<?= site_url('email') ?>" class="btn btn-outline no-underline">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Tukar Data Akun</h1>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Menukar profil NIK, NIP, Nama, dll antar dua email</p>
            </div>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="p-4 bg-red-50 border border-red-200 rounded-lg flex items-start">
            <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3"></i>
            <div>
                <h4 class="text-[10px] font-bold text-red-800 uppercase tracking-wider mb-1">Gagal</h4>
                <p class="text-xs text-red-600"><?= session()->getFlashdata('error') ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Konfigurasi Swap</h3>
        </div>
        
        <form action="<?= site_url('email/swap_process') ?>" method="POST" class="p-6 space-y-6" onsubmit="return confirm('Apakah Anda yakin ingin menukar data profil kedua email ini? Proses ini akan langsung tercatat di Audit Trail.');">
            <div class="bg-blue-50/50 border border-blue-100 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                    <div>
                        <p class="text-xs text-slate-600 leading-relaxed mb-2">
                            Fitur ini digunakan jika ada dua orang yang salah mengaktivasi TTE (tertukar email). Biodata mereka akan disilangkan tanpa mengubah alamat email asli dan <em>password</em>-nya.
                        </p>
                        <ul class="text-[11px] text-blue-800 space-y-1 list-disc list-inside">
                            <li>Data yang disilang: <span class="font-bold">NIK, NIP, Nama, Jabatan, Golongan, Unit Kerja.</span></li>
                            <li>Data yang <strong>TETAP</strong>: Email, Password, Status TTE.</li>
                        </ul>
                    </div>
                </div>
            </div>

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
                            <select name="email_1" class="choices-search w-full" required>
                                <option value="">Pilih Email Pertama...</option>
                                <?php foreach ($emails as $em): ?>
                                    <option value="<?= esc($em['email']) ?>" <?= old('email_1') == $em['email'] ? 'selected' : '' ?>>
                                        <?= esc($em['email']) ?> - <?= esc($em['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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
                            <select name="email_2" class="choices-search w-full" required>
                                <option value="">Pilih Email Kedua...</option>
                                <?php foreach ($emails as $em): ?>
                                    <option value="<?= esc($em['email']) ?>" <?= old('email_2') == $em['email'] ? 'selected' : '' ?>>
                                        <?= esc($em['email']) ?> - <?= esc($em['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>



            <div class="flex justify-end pt-4 border-t border-slate-100 gap-2">
                <a href="<?= site_url('email') ?>" class="btn btn-outline no-underline">
                    Batal
                </a>
                <button type="submit" class="btn btn-solid">
                    <i class="fas fa-sync-alt mr-2"></i> Eksekusi Tukar Data
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
