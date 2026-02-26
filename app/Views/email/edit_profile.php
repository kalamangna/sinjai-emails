<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="inline-flex items-center justify-center w-10 h-10 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all shadow-sm no-underline">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-semibold text-gray-900">Perbarui Profil</h1>
    </div>

    <!-- Card Utama -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <form action="<?= site_url('email/update_details/' . $email['user']) ?>" method="post">
            <?= csrf_field() ?>
            <div class="p-8 space-y-10">

                <!-- Bagian: Informasi Personal -->
                <div class="space-y-6">
                    <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-1">Data Pribadi</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-gray-400 font-normal">(Tanpa Gelar)</span></label>
                            <input type="text" name="name" id="name" value="<?= esc($email['name']) ?>" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm font-medium text-gray-900 uppercase" required>
                        </div>
                        <div>
                            <label for="gelar_depan" class="block text-sm font-medium text-gray-700 mb-1">Gelar Depan</label>
                            <input type="text" name="gelar_depan" id="gelar_depan" value="<?= esc($email['gelar_depan'] ?? '') ?>" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm font-medium text-gray-900" placeholder="Contoh: Dr.">
                        </div>
                        <div>
                            <label for="gelar_belakang" class="block text-sm font-medium text-gray-700 mb-1">Gelar Belakang</label>
                            <input type="text" name="gelar_belakang" id="gelar_belakang" value="<?= esc($email['gelar_belakang'] ?? '') ?>" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm font-medium text-gray-900" placeholder="Contoh: S.Kom">
                        </div>
                        <div class="md:col-span-2">
                            <label for="nik" class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
                            <input type="text" name="nik" id="nik" value="<?= esc($email['nik']) ?>" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm font-medium text-gray-900 font-mono" placeholder="16 Digit">
                        </div>
                        <div>
                            <label for="tempat_lahir" class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" id="tempat_lahir" value="<?= esc($email['tempat_lahir']) ?>" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm font-medium text-gray-900 uppercase">
                        </div>
                        <div>
                            <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" id="tanggal_lahir" value="<?= esc($email['tanggal_lahir']) ?>" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm font-medium text-gray-900">
                        </div>
                        <div class="md:col-span-2">
                            <label for="pendidikan" class="block text-sm font-medium text-gray-700 mb-1">Pendidikan Terakhir</label>
                            <input type="text" name="pendidikan" id="pendidikan" value="<?= esc($email['pendidikan']) ?>" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm font-medium text-gray-900 uppercase" placeholder="Contoh: S1 TEKNIK INFORMATIKA">
                        </div>
                    </div>
                </div>

                <!-- Bagian: Kepegawaian & Jabatan -->
                <div class="space-y-6">
                    <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-1">Kepegawaian</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="nip" class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                            <input type="text" name="nip" id="nip" value="<?= esc($email['nip']) ?>" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm font-medium text-gray-900 font-mono" placeholder="18 Digit">
                        </div>
                        <div>
                            <label for="status_asn" class="block text-sm font-medium text-gray-700 mb-1">Status ASN</label>
                            <select name="status_asn" id="status_asn" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm font-medium text-gray-900 appearance-none cursor-pointer">
                                <option value="">Pilih Status...</option>
                                <?php foreach ($status_asn_options as $option): ?>
                                    <option value="<?= esc($option['id']) ?>" <?= ($email['status_asn_id'] == $option['id']) ? 'selected' : '' ?>><?= esc($option['nama_status_asn']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="eselon" class="block text-sm font-medium text-gray-700 mb-1">Eselon</label>
                            <select name="eselon" id="eselon" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm font-medium text-gray-900 appearance-none cursor-pointer">
                                <option value="">Tanpa Eselon</option>
                                <?php foreach ($eselon_options as $option): ?>
                                    <option value="<?= esc($option['id']) ?>" <?= ($email['eselon_id'] == $option['id']) ? 'selected' : '' ?>><?= esc($option['nama_eselon']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="jabatan" class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                            <input type="text" name="jabatan" id="jabatan" value="<?= esc($email['jabatan']) ?>" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm font-medium text-gray-900 uppercase">
                        </div>
                        <div class="md:col-span-2">
                            <label for="unit_kerja_id" class="block text-sm font-medium text-gray-700 mb-1">Unit Kerja</label>
                            <select id="unit_kerja_id" name="unit_kerja_id" class="choices-search block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm font-medium text-gray-900 appearance-none cursor-pointer">
                                <option value="">Pilih Unit Kerja...</option>
                                <?php foreach ($unit_kerja_options as $unit): ?>
                                    <option value="<?= esc($unit['id']) ?>" <?= ($unit['id'] == $email['unit_kerja_id']) ? 'selected' : '' ?>><?= esc($unit['nama_unit_kerja']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Peran Khusus</label>
                            <div class="grid grid-cols-2 gap-4">
                                <select name="pimpinan" id="pimpinan" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-xs font-bold text-gray-900 uppercase appearance-none cursor-pointer">
                                    <option value="0" <?= ($email['pimpinan'] ?? 0) == 0 ? 'selected' : '' ?>>Bukan Pimpinan</option>
                                    <option value="1" <?= ($email['pimpinan'] ?? 0) == 1 ? 'selected' : '' ?>>Pimpinan</option>
                                </select>
                                <select name="pimpinan_desa" id="pimpinan_desa" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-xs font-bold text-gray-900 uppercase appearance-none cursor-pointer">
                                    <option value="0" <?= ($email['pimpinan_desa'] ?? 0) == 0 ? 'selected' : '' ?>>Bukan Kepala Desa</option>
                                    <option value="1" <?= ($email['pimpinan_desa'] ?? 0) == 1 ? 'selected' : '' ?>>Kepala Desa</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="bg-gray-50 px-8 py-4 flex flex-col sm:flex-row justify-end gap-3 border-t border-gray-200">
                <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="order-2 sm:order-1 inline-flex items-center justify-center px-6 py-2 bg-white border border-gray-300 rounded-lg font-bold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 shadow-sm transition-all">
                    <i class="fas fa-times mr-2"></i> Batal
                </a>
                <button type="submit" class="order-1 sm:order-2 inline-flex items-center justify-center px-8 py-2 bg-gray-900 text-white rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-gray-800 shadow-sm transition-all">
                    <i class="fas fa-save mr-2"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>