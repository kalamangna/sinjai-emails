<!-- Redesigned Edit Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" aria-hidden="true" onclick="closeEditModal()"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-slate-200">
            <!-- Modal Header -->
            <div class="bg-slate-50 px-8 py-6 border-b border-slate-100 flex justify-between items-center relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-10 -mt-10 w-32 h-32 bg-emerald-500/5 rounded-full blur-2xl"></div>
                <div class="relative z-10 flex items-center gap-4">
                    <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                        <i class="fas fa-user-edit text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 uppercase tracking-tight" id="modal-title">Edit Akun</h3>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal()" class="relative z-10 inline-flex items-center justify-center w-10 h-10 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form action="<?= site_url('email/update_details/' . $email['user']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="p-8 max-h-[70vh] overflow-y-auto custom-scrollbar space-y-10">
                    
                    <!-- Section: Personal Info -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="w-1.5 h-6 bg-emerald-500 rounded-full"></span>
                            <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Data Diri</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="md:col-span-2">
                                <label for="name" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Nama Lengkap</label>
                                <input type="text" name="name" id="name" value="<?= esc($email['name']) ?>" class="block w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-bold text-slate-700 transition-all placeholder:text-slate-300" required>
                            </div>
                            <div>
                                <label for="gelar_depan" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Gelar Depan</label>
                                <input type="text" name="gelar_depan" id="gelar_depan" value="<?= esc($email['gelar_depan'] ?? '') ?>" class="block w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-bold text-slate-700 transition-all placeholder:text-slate-300">
                            </div>
                            <div>
                                <label for="gelar_belakang" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Gelar Belakang</label>
                                <input type="text" name="gelar_belakang" id="gelar_belakang" value="<?= esc($email['gelar_belakang'] ?? '') ?>" class="block w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-bold text-slate-700 transition-all placeholder:text-slate-300">
                            </div>
                        </div>
                    </div>

                    <!-- Section: Security -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="w-1.5 h-6 bg-blue-500 rounded-full"></span>
                            <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Kredensial</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="md:col-span-2">
                                <label for="email" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Email</label>
                                <input type="email" name="email" id="email" value="<?= esc($email['email']) ?>" class="block w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-bold text-slate-700 transition-all lowercase" required>
                            </div>
                            <div class="md:col-span-2">
                                <label for="password" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Password Baru</label>
                                <div class="relative group">
                                    <span class="absolute inset-y-0 left-0 pl-5 flex items-center text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="text" name="password" id="password" value="<?= esc($email['password']) ?>" class="block w-full pl-12 pr-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-black text-slate-900 transition-all font-mono tracking-wider">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Employment Info -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="w-1.5 h-6 bg-amber-500 rounded-full"></span>
                            <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Kepegawaian</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label for="nik" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2.5 ml-1">NIK</label>
                                <input type="text" name="nik" id="nik" value="<?= esc($email['nik']) ?>" class="block w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-bold text-slate-700 transition-all font-mono tracking-widest" placeholder="16 Digit">
                            </div>
                            <div>
                                <label for="nip" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2.5 ml-1">NIP</label>
                                <input type="text" name="nip" id="nip" value="<?= esc($email['nip']) ?>" class="block w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-bold text-slate-700 transition-all font-mono tracking-widest" placeholder="18 Digit">
                            </div>
                            <div>
                                <label for="tempat_lahir" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" id="tempat_lahir" value="<?= esc($email['tempat_lahir']) ?>" class="block w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-bold text-slate-700 transition-all">
                            </div>
                            <div>
                                <label for="tanggal_lahir" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" id="tanggal_lahir" value="<?= esc($email['tanggal_lahir']) ?>" class="block w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-bold text-slate-700 transition-all">
                            </div>
                            <div class="md:col-span-2">
                                <label for="jabatan" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Jabatan</label>
                                <input type="text" name="jabatan" id="jabatan" value="<?= esc($email['jabatan']) ?>" class="block w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-bold text-slate-700 transition-all uppercase">
                            </div>
                        </div>
                    </div>

                    <!-- Section: Organization -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="w-1.5 h-6 bg-indigo-500 rounded-full"></span>
                            <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Organisasi</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label for="unit_kerja_id" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Unit Kerja</label>
                                <select id="unit_kerja_id" name="unit_kerja_id" class="block w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-bold text-slate-700 transition-all uppercase appearance-none cursor-pointer">
                                    <option value="">Pilih...</option>
                                    <?php foreach ($unit_kerja_options as $unit): ?>
                                        <option value="<?= esc($unit['id']) ?>" <?= ($unit['id'] == $email['unit_kerja_id']) ? 'selected' : '' ?>><?= esc($unit['nama_unit_kerja']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="status_asn" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Status ASN</label>
                                <select name="status_asn" id="status_asn" class="block w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-bold text-slate-700 transition-all appearance-none cursor-pointer">
                                    <option value="">Pilih...</option>
                                    <?php foreach ($status_asn_options as $option): ?>
                                        <option value="<?= esc($option['id']) ?>" <?= ($email['status_asn_id'] == $option['id']) ? 'selected' : '' ?>><?= esc($option['nama_status_asn']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="eselon" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Eselon</label>
                                <select name="eselon" id="eselon" class="block w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-bold text-slate-700 transition-all appearance-none cursor-pointer">
                                    <option value="">Tanpa Eselon</option>
                                    <?php foreach ($eselon_options as $option): ?>
                                        <option value="<?= esc($option['id']) ?>" <?= ($email['eselon_id'] == $option['id']) ? 'selected' : '' ?>><?= esc($option['nama_eselon']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Role Khusus</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="relative">
                                        <select name="pimpinan" id="pimpinan" class="block w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-[11px] font-black text-slate-700 transition-all appearance-none cursor-pointer">
                                            <option value="0" <?= ($email['pimpinan'] ?? 0) == 0 ? 'selected' : '' ?>>Bukan Pimpinan</option>
                                            <option value="1" <?= ($email['pimpinan'] ?? 0) == 1 ? 'selected' : '' ?>>Pimpinan</option>
                                        </select>
                                    </div>
                                    <div class="relative">
                                        <select name="pimpinan_desa" id="pimpinan_desa" class="block w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-[11px] font-black text-slate-700 transition-all appearance-none cursor-pointer">
                                            <option value="0" <?= ($email['pimpinan_desa'] ?? 0) == 0 ? 'selected' : '' ?>>Bukan Kades</option>
                                            <option value="1" <?= ($email['pimpinan_desa'] ?? 0) == 1 ? 'selected' : '' ?>>Kades</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-slate-50 px-8 py-6 flex flex-col sm:flex-row justify-end gap-4 border-t border-slate-100">
                    <button type="button" onclick="closeEditModal()" class="order-2 sm:order-1 inline-flex items-center justify-center px-8 py-3 bg-white border border-slate-200 rounded-xl font-black text-xs text-slate-500 uppercase tracking-widest hover:bg-slate-100 transition-all focus:outline-none">
                        Batal
                    </button>
                    <button type="submit" class="order-1 sm:order-2 inline-flex items-center justify-center px-10 py-3 bg-emerald-600 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-lg focus:outline-none group">
                        <i class="fas fa-save mr-2 group-hover:scale-110 transition-transform"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>