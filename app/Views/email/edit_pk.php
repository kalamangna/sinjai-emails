<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="inline-flex items-center justify-center w-10 h-10 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-all shadow-sm no-underline">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-tight">Edit Perjanjian Kerja (PK)</h1>
    </div>

    <!-- Card Utama -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden flex flex-col">
        <form action="<?= site_url('email/update_pk/' . $email['user']) ?>" method="post">
            <?= csrf_field() ?>
            <div class="p-8 space-y-10">

                <!-- Bagian: Informasi Akun -->
                <div class="space-y-6">
                    <div class="flex justify-between items-center border-b border-gray-100 pb-1">
                        <h4 class="text-[10px] font-bold text-gray-700 uppercase tracking-widest">Informasi Pegawai</h4>
                        <div class="text-[10px] font-bold text-gray-700 tracking-tight bg-gray-50 px-2 py-0.5 rounded border border-gray-200">
                            <?= esc($email['email']) ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Nama Lengkap</label>
                            <input type="text" value="<?= esc($email['name']) ?>" class="block w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-500 uppercase cursor-not-allowed" readonly>
                        </div>
                        <div>
                            <label for="nomor" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Nomor PK</label>
                            <input type="text" name="nomor" id="nomor" value="<?= esc($pk_data['nomor'] ?? '') ?>" class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm font-medium text-gray-800 transition-all font-mono" placeholder="Contoh: 001/PK/2026" required>
                        </div>
                        <div></div>
                        <div>
                            <label for="tanggal_kontrak_awal" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Kontrak Mulai</label>
                            <input type="date" name="tanggal_kontrak_awal" id="tanggal_kontrak_awal" value="<?= esc($pk_data['tanggal_kontrak_awal'] ?? '') ?>" class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm font-medium text-gray-800 transition-all" required>
                        </div>
                        <div>
                            <label for="tanggal_kontrak_akhir" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Kontrak Selesai</label>
                            <input type="date" name="tanggal_kontrak_akhir" id="tanggal_kontrak_akhir" value="<?= esc($pk_data['tanggal_kontrak_akhir'] ?? '') ?>" class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm font-medium text-gray-800 transition-all" required>
                        </div>
                    </div>
                </div>

                <!-- Bagian: Penghasilan -->
                <div class="space-y-6">
                    <h4 class="text-[10px] font-bold text-gray-700 uppercase tracking-widest border-b border-gray-100 pb-1">Gaji</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="gaji_nominal" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Gaji Nominal (Rp)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-sm">Rp</span>
                                <input type="text" name="gaji_nominal" id="gaji_nominal" value="<?= number_format($pk_data['gaji_nominal'] ?? 0, 0, ',', '.') ?>" class="block w-full pl-10 pr-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm font-bold text-gray-800 transition-all" required>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label for="gaji_terbilang" class="block text-sm font-medium text-gray-700 mb-1 uppercase tracking-tight">Gaji Terbilang (Rupiah)</label>
                            <textarea name="gaji_terbilang" id="gaji_terbilang" rows="2" class="block w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 text-sm font-medium text-gray-800 transition-all" placeholder="CONTOH: TIGA JUTA DUA RATUS TIGA RIBU ENAM RATUS" required><?= esc($pk_data['gaji_terbilang'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="bg-gray-50 px-8 py-4 flex flex-col sm:flex-row justify-end gap-3 border-t border-gray-200">
                <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="order-2 sm:order-1 inline-flex items-center justify-center px-6 py-2 bg-white border border-gray-200 rounded-lg font-bold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 shadow-sm transition-all">
                    <i class="fas fa-times mr-2"></i> Batal
                </a>
                <button type="submit" class="order-1 sm:order-2 inline-flex items-center justify-center px-8 py-2 bg-gray-800 text-white rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-gray-700 shadow-sm transition-all">
                    <i class="fas fa-save mr-2 text-white/80"></i> Simpan PK
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const gajiInput = document.getElementById('gaji_nominal');

        if (gajiInput) {
            gajiInput.addEventListener('input', function(e) {
                // Hapus semua karakter kecuali angka
                let value = this.value.replace(/[^0-9]/g, '');

                // Format ke rupiah
                if (value !== '') {
                    this.value = parseInt(value).toLocaleString('id-ID');
                } else {
                    this.value = '';
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>