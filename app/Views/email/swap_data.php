<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="<?= site_url('email') ?>" class="btn btn-outline !w-10 !h-10 !p-0 no-underline">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Tukar Data Akun</h1>
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

            <div class="flex flex-col md:flex-row items-center gap-6">
                <!-- Akun 1 -->
                <div class="w-full flex-1">
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Akun Pertama</span>
                        <div class="w-12 h-12 bg-white rounded-full border border-slate-200 flex items-center justify-center mx-auto shadow-sm mb-3">
                            <i class="fas fa-user text-slate-400"></i>
                        </div>
                        <div class="relative text-left">
                            <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-2">Alamat Email 1</label>
                            <select id="email_1_select" name="email_1" class="w-full" required>
                                <option value="">Ketik NIP/Nama/Email...</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Icon Swap (Desktop & Mobile) -->
                <div class="flex justify-center z-10 my-2 md:my-0">
                    <div class="w-10 h-10 rounded-full bg-slate-800 text-white flex items-center justify-center shadow-lg transform hover:rotate-180 transition-transform duration-500">
                        <i class="fas fa-exchange-alt md:rotate-0 rotate-90"></i>
                    </div>
                </div>

                <!-- Akun 2 -->
                <div class="w-full flex-1">
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Akun Kedua</span>
                        <div class="w-12 h-12 bg-white rounded-full border border-slate-200 flex items-center justify-center mx-auto shadow-sm mb-3">
                            <i class="fas fa-user-friends text-slate-400"></i>
                        </div>
                        <div class="relative text-left">
                            <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-2">Alamat Email 2</label>
                            <select id="email_2_select" name="email_2" class="w-full" required>
                                <option value="">Ketik NIP/Nama/Email...</option>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const initAjaxSelect = (elementId) => {
        const element = document.getElementById(elementId);
        if (!element) return;

        const choices = new Choices(element, {
            searchEnabled: true,
            placeholder: true,
            placeholderValue: 'Ketik NIP/Nama/Email...',
            searchPlaceholderValue: 'Minimal 2 karakter...',
            shouldSort: false,
            loadingText: 'Memuat...',
            noResultsText: 'Tidak ditemukan',
            noChoicesText: 'Ketik untuk mencari...',
            searchResultLimit: 20
        });

        element.addEventListener('search', function(event) {
            const query = event.detail.value;
            if (query.length < 2) return;

            choices.setChoices(function() {
                return fetch(`<?= site_url('search') ?>?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        return data.map(item => {
                            const nipNik = item.nip ? item.nip : (item.nik ? item.nik : '');
                            const label = nipNik 
                                ? `${item.email} - ${item.name} (${nipNik})`
                                : `${item.email} - ${item.name}`;
                            return { value: item.email, label: label };
                        });
                    });
            }, 'value', 'label', true);
        });
    };

    initAjaxSelect('email_1_select');
    initAjaxSelect('email_2_select');
});
</script>

<?= $this->endSection() ?>
