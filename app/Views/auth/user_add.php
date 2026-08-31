<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Tambah User</h1>
        <a href="<?= site_url('auth/users') ?>" class="btn btn-outline">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm max-w-2xl mx-auto">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Informasi User Baru (Berbasis NIP)</h3>
        </div>
        <form action="<?= site_url('auth/users/store') ?>" method="POST" class="p-6 space-y-4" id="form-add-user">
            <?= csrf_field() ?>
            <input type="hidden" name="name" id="hidden_name" value="<?= old('name') ?>">

            <div class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">NIP Pegawai</label>
                    <div class="flex gap-2">
                        <input type="text" id="username" name="username" value="<?= old('username') ?>" required class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 transition-all placeholder-slate-400" placeholder="Masukkan NIP...">
                        <button type="button" id="btn-check-nip" class="btn btn-solid shrink-0">
                            <i class="fas fa-search mr-2"></i> Cek Data
                        </button>
                    </div>
                </div>

                <!-- Info Pegawai (Awalnya Tersembunyi) -->
                <div id="info-pegawai" class="<?= empty(old('name')) ? 'hidden' : '' ?> p-4 bg-slate-50 border border-slate-200 rounded-lg">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-slate-700 border border-slate-200">
                            <i class="fas fa-user text-xl"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Nama Pegawai</p>
                            <p id="display_name" class="text-sm font-bold text-slate-800 uppercase"><?= old('name') ?: '-' ?></p>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Role Akses</label>
                    <select id="role" name="role" required class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm font-medium text-slate-800 transition-all cursor-pointer">
                        <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="super_admin" <?= old('role') === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button type="submit" id="btn-save" class="btn btn-solid" <?= empty(old('name')) ? 'disabled' : '' ?>>
                    <i class="fas fa-save mr-2 text-white/80"></i> Simpan User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnCheck = document.getElementById('btn-check-nip');
        const nipInput = document.getElementById('username');
        const infoBox = document.getElementById('info-pegawai');
        const displayName = document.getElementById('display_name');
        const hiddenName = document.getElementById('hidden_name');
        const btnSave = document.getElementById('btn-save');

        btnCheck.addEventListener('click', async function() {
            const nip = nipInput.value.trim();
            if (!nip) {
                showGlobalAlert('Perhatian', 'Masukkan NIP terlebih dahulu.', 'warning');
                return;
            }

            // State: Loading
            btnCheck.disabled = true;
            btnCheck.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memeriksa...';
            infoBox.classList.add('hidden');
            btnSave.disabled = true;

            try {
                const response = await fetch('<?= site_url('auth/users/check_nip') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'nip=' + encodeURIComponent(nip) + '&<?= csrf_token() ?>=<?= csrf_hash() ?>'
                });

                const result = await response.json();

                if (result.success) {
                    displayName.textContent = result.data.nama;
                    hiddenName.value = result.data.nama;
                    infoBox.classList.remove('hidden');
                    btnSave.disabled = false;
                } else {
                    showGlobalAlert('Informasi', result.message || 'Data pegawai tidak ditemukan.', 'warning');
                }
            } catch (error) {
                showGlobalError('Koneksi Gagal', 'Gagal menghubungi API Pegawai. Silakan coba lagi nanti.');
            } finally {
                btnCheck.disabled = false;
                btnCheck.innerHTML = '<i class="fas fa-search mr-2"></i> Cek Data';
            }
        });
    });
</script>
<?= $this->endSection() ?>