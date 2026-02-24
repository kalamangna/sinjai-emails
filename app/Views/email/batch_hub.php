<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto space-y-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="space-y-1">
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight uppercase">Layanan Batch</h2>
            <p class="text-slate-500 text-xs font-medium uppercase tracking-wider">Lakukan pembuatan, pembaruan akun, dan pembaruan data PK massal.</p>
        </div>
        <a href="<?= site_url('/') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all shadow-sm no-underline group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> Kembali
        </a>
    </div>

    <!-- Grid Menu -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Pembuatan Akun -->
        <a href="<?= site_url('email/batch') ?>" class="group bg-white border border-slate-200 rounded-2xl p-8 hover:border-blue-300 hover:shadow-md transition-all no-underline">
            <div class="space-y-6">
                <div class="w-14 h-14 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-600 transition-colors duration-300">
                    <i class="fas fa-plus-circle text-blue-600 text-2xl group-hover:text-white transition-colors"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Buat Akun</h3>
                    <p class="text-slate-500 text-sm leading-relaxed font-medium">Buat akun email baru secara massal.</p>
                </div>
                <div class="pt-2 flex items-center text-[11px] font-bold text-blue-600 uppercase tracking-wider">
                    Mulai Proses <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
        </a>

        <!-- Pembaruan Data -->
        <a href="<?= site_url('email/batch_update') ?>" class="group bg-white border border-slate-200 rounded-2xl p-8 hover:border-blue-300 hover:shadow-md transition-all no-underline">
            <div class="space-y-6">
                <div class="w-14 h-14 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-600 transition-colors duration-300">
                    <i class="fas fa-edit text-blue-600 text-2xl group-hover:text-white transition-colors"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Update Data</h3>
                    <p class="text-slate-500 text-sm leading-relaxed font-medium">Perbarui data profil, jabatan, dan status ASN banyak akun sekaligus.</p>
                </div>
                <div class="pt-2 flex items-center text-[11px] font-bold text-blue-600 uppercase tracking-wider">
                    Mulai Proses <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
        </a>

        <!-- Dokumen PK -->
        <a href="<?= site_url('email/batch_perjanjian_kerja') ?>" class="group bg-white border border-slate-200 rounded-2xl p-8 hover:border-blue-300 hover:shadow-md transition-all no-underline">
            <div class="space-y-6">
                <div class="w-14 h-14 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-600 transition-colors duration-300">
                    <i class="fas fa-file-contract text-blue-600 text-2xl group-hover:text-white transition-colors"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors">PK Massal</h3>
                    <p class="text-slate-500 text-sm leading-relaxed font-medium">Perbarui data Perjanjian Kerja (PK) untuk PPPK Paruh Waktu.</p>
                </div>
                <div class="pt-2 flex items-center text-[11px] font-bold text-blue-600 uppercase tracking-wider">
                    Mulai Proses <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
        </a>
    </div>
</div>
<?= $this->endSection() ?>