<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeEditModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-tight" id="modal-title">Update Akun</h3>
                <button type="button" onclick="closeEditModal()" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-slate-900 hover:bg-slate-100 transition-colors focus:outline-none" aria-label="Tutup modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?= site_url('email/update_details/' . $email['user']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="p-6 max-h-[60vh] overflow-y-auto custom-scrollbar space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="name" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama Lengkap (Tanpa Gelar)</label>
                            <input type="text" name="name" id="name" value="<?= esc($email['name']) ?>" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all" required>
                        </div>
                        <div>
                            <label for="gelar_depan" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Gelar Depan</label>
                            <input type="text" name="gelar_depan" id="gelar_depan" value="<?= esc($email['gelar_depan'] ?? '') ?>" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                        </div>
                        <div>
                            <label for="gelar_belakang" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Gelar Belakang</label>
                            <input type="text" name="gelar_belakang" id="gelar_belakang" value="<?= esc($email['gelar_belakang'] ?? '') ?>" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                        </div>
                        <div class="md:col-span-2">
                            <label for="email" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Alamat Email Utama</label>
                            <input type="email" name="email" id="email" value="<?= esc($email['email']) ?>" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all lowercase" required>
                        </div>
                        <div class="md:col-span-2">
                            <label for="password" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Kata Sandi (Hanya jika ingin diubah)</label>
                            <input type="text" name="password" id="password" value="<?= esc($email['password']) ?>" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-bold text-slate-900 transition-all font-mono tracking-wider">
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="nik" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">NIK (16 Digit)</label>
                            <input type="text" name="nik" id="nik" value="<?= esc($email['nik']) ?>" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all font-mono">
                        </div>
                        <div>
                            <label for="nip" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">NIP (18 Digit)</label>
                            <input type="text" name="nip" id="nip" value="<?= esc($email['nip']) ?>" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all font-mono">
                        </div>
                        <div>
                            <label for="tempat_lahir" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" id="tempat_lahir" value="<?= esc($email['tempat_lahir']) ?>" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                        </div>
                        <div>
                            <label for="tanggal_lahir" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" id="tanggal_lahir" value="<?= esc($email['tanggal_lahir']) ?>" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="pendidikan" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Pendidikan Terakhir</label>
                            <input type="text" name="pendidikan" id="pendidikan" value="<?= esc($email['pendidikan']) ?>" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                        </div>
                        <div>
                            <label for="status_asn" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Status ASN</label>
                            <select name="status_asn" id="status_asn" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                                <option value="">-- PILIH STATUS --</option>
                                <?php foreach ($status_asn_options as $option): ?>
                                    <option value="<?= esc($option['id']) ?>" <?= ($email['status_asn_id'] == $option['id']) ? 'selected' : '' ?>><?= esc($option['nama_status_asn']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="jabatan" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Jabatan Struktural / Fungsional</label>
                            <input type="text" name="jabatan" id="jabatan" value="<?= esc($email['jabatan']) ?>" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                        </div>
                        <div>
                            <label for="eselon" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Eselon</label>
                            <select name="eselon" id="eselon" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                                <option value="">-- TANPA ESELON --</option>
                                <?php foreach ($eselon_options as $option): ?>
                                    <option value="<?= esc($option['id']) ?>" <?= ($email['eselon_id'] == $option['id']) ? 'selected' : '' ?>><?= esc($option['nama_eselon']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="unit_kerja_id" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Unit Kerja</label>
                            <select id="unit_kerja_id" name="unit_kerja_id" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all uppercase">
                                <option value="">-- PILIH UNIT KERJA --</option>
                                <?php foreach ($unit_kerja_options as $unit): ?>
                                    <option value="<?= esc($unit['id']) ?>" <?= ($unit['id'] == $email['unit_kerja_id']) ? 'selected' : '' ?>><?= esc($unit['nama_unit_kerja']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="pimpinan" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Pimpinan OPD</label>
                            <select name="pimpinan" id="pimpinan" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                                <option value="0" <?= ($email['pimpinan'] ?? 0) == 0 ? 'selected' : '' ?>>Tidak</option>
                                <option value="1" <?= ($email['pimpinan'] ?? 0) == 1 ? 'selected' : '' ?>>Ya (Pimpinan)</option>
                            </select>
                        </div>
                        <div>
                            <label for="pimpinan_desa" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Pimpinan Desa</label>
                            <select name="pimpinan_desa" id="pimpinan_desa" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                                <option value="0" <?= ($email['pimpinan_desa'] ?? 0) == 0 ? 'selected' : '' ?>>Tidak</option>
                                <option value="1" <?= ($email['pimpinan_desa'] ?? 0) == 1 ? 'selected' : '' ?>>Ya (Kepala Desa)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 flex flex-col sm:flex-row justify-end gap-3 border-t border-slate-200">
                    <button type="button" onclick="closeEditModal()" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-lg font-bold text-xs text-slate-600 uppercase tracking-wider hover:bg-slate-50 hover:text-slate-900 transition-all focus:outline-none">
                        Batal
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center px-6 py-2 bg-blue-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-wider hover:bg-blue-700 active:bg-blue-800 transition-all shadow-md focus:outline-none">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>