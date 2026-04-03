<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Navigasi dan Aksi -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <button onclick="history.back()" class="btn btn-outline">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </button>
        <div class="flex flex-wrap gap-2">
            <a href="<?= site_url('email/export_pimpinan_desa_pdf') ?>" class="btn btn-outline no-underline">
                <i class="fas fa-file-pdf mr-2"></i> Export PDF
            </a>
            <?php if (in_array(session()->get('role'), ['super_admin', 'admin'])): ?>
                <!-- Dropdown Sinkronisasi -->
                <div class="relative group">
                    <button id="mainSyncBtn" class="btn btn-solid">
                        <i class="fas fa-sync-alt mr-2 text-white/80"></i> Sync <i class="fas fa-chevron-down ml-2 text-[8px] opacity-50 transition-transform duration-300 group-hover:rotate-180"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 overflow-hidden">
                        <button id="syncAllTteBtn" onclick="syncAllBsreStatus()" class="w-full px-4 py-3 text-left text-[10px] font-bold text-slate-700 uppercase tracking-widest hover:bg-slate-50 transition-colors focus:outline-none">
                            <i class="fas fa-fw fa-fingerprint mr-2 text-slate-500"></i> Sync TTE
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Header Halaman -->
    <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-slate-100 border border-slate-200 rounded-lg flex items-center justify-center text-slate-700">
                    <i class="fas fa-users-cog text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Kepala Desa</h1>
            </div>
            <div class="bg-slate-50 px-6 py-2 rounded-lg border border-slate-200 text-center">
                <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">Total Email</p>
                <p class="text-xl font-bold text-slate-800"><?= number_format($total_emails, 0, ',', '.') ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
        <form action="<?= current_url() ?>" method="get" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-7">
                <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Pencarian</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" name="search" value="<?= esc($search ?? '') ?>" class="block w-full pl-9 pr-4 py-2 bg-white border <?= !empty($search) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm transition-all" placeholder="Cari nama, NIP, atau NIK...">
                </div>
            </div>
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Status TTE</label>
                <select name="bsre_status" class="block w-full px-3 py-2 bg-white border <?= !empty($bsre_status) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm appearance-none cursor-pointer transition-all">
                    <option value="">Semua Status</option>
                    <?php foreach ($bsre_status_options as $key => $label): ?>
                        <option value="<?= esc($key) ?>" <?= ($bsre_status === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 btn btn-solid">
                    <i class="fas fa-filter mr-2 text-white/80"></i> Filter
                </button>
                <a href="<?= site_url('email/pimpinan_desa') ?>" class="btn btn-outline" title="Reset">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel -->
    <div id="email-table-container" class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-4 border-b border-slate-200">Email</th>
                        <th class="px-6 py-4 border-b border-slate-200">Jabatan</th>
                        <th class="px-6 py-4 border-b border-slate-200">Unit Kerja</th>
                        <th class="px-6 py-4 border-b border-slate-200">Status TTE</th>
                        <th class="px-6 py-4 border-b border-slate-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php if (!empty($emails)): ?>
                        <?php foreach ($emails as $email): ?>
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-800 lowercase leading-tight"><?= esc($email['email']) ?></span>
                                        <span class="text-[10px] font-bold text-slate-700 uppercase tracking-tight mt-0.5"><?= esc($email['name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1" id="pegawai-container-<?= esc($email['user']) ?>" data-nip="<?= esc($email['nip']) ?>">
                                        <span class="text-xs font-medium text-slate-700 uppercase tracking-tight jabatan-text leading-snug"><?= esc($email['jabatan']) ?: '-' ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-800 uppercase tracking-tight"><?= esc($email['unit_kerja_name']) ?></span>
                                        <span class="text-[10px] font-bold text-slate-700 uppercase tracking-widest mt-0.5"><?= esc(trim(str_ireplace('KANTOR', '', $email['parent_unit_kerja_name'] ?? '-'))) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div id="bsre-status-<?= esc($email['user']) ?>" data-email="<?= esc($email['email']) ?>">
                                        <?php
                                        $st = $email['bsre_status'] ?? '';
                                        $colorClass = 'bg-slate-100 text-slate-700 border-transparent';
                                        $label = $st ?: 'NOT_SYNCED';

                                        if ($st === 'ISSUE') $colorClass = 'bg-emerald-100 text-emerald-800 border-transparent';
                                        elseif (in_array($st, ['EXPIRED', 'REVOKE', 'SUSPEND'])) $colorClass = 'bg-red-100 text-red-700 border-transparent';
                                        elseif (in_array($st, ['WAITING_FOR_VERIFICATION', 'RENEW', 'NO_CERTIFICATE'])) $colorClass = 'bg-amber-50 text-amber-500 border-amber-200';
                                        elseif ($st === 'NEW') $colorClass = 'bg-blue-100 text-slate-700 border-transparent';
                                        ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase border <?= $colorClass ?>">
                                            <?= $label ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="btn btn-table" title="Detail">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <?php if (session()->get('role') === 'super_admin'): ?>
                                            <form action="<?= site_url('email/delete/' . $email['id']) ?>" method="post" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-table" title="Hapus">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                        <i class="fas fa-search text-slate-300 text-lg"></i>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Data tidak ditemukan</span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?= view('components/pagination', ['items' => $emails, 'pager' => $pager, 'label' => 'akun']) ?>
    </div>
</div>

<script>
    async function syncAllBsreStatus() {
        const containers = document.querySelectorAll('[id^="bsre-status-"]');
        if (!containers.length) return;

        if (!confirm(`Sinkronkan status sertifikat untuk ${containers.length} kepala desa yang tampil?`)) {
            return;
        }

        const mainBtn = document.getElementById('mainSyncBtn');
        const syncBtn = document.getElementById('syncAllTteBtn');
        const originalMainContent = mainBtn.innerHTML;
        const originalBtnContent = syncBtn.innerHTML;

        // 1. Scroll ke tabel secara smooth
        const tableContainer = document.getElementById('email-table-container');
        if (tableContainer) {
            tableContainer.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        // 2. Disable tombol dan beri feedback visual
        mainBtn.disabled = true;
        mainBtn.classList.add('opacity-75', 'cursor-not-allowed', 'bg-slate-700');
        syncBtn.disabled = true;
        syncBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sinkronisasi TTE...';

        // 3. Proses secara sekuensial
        let processed = 0;
        let success = 0;
        let failed = 0;

        for (const container of containers) {
            const email = container.getAttribute('data-email');
            
            // Scroll ke container yang sedang diproses
            container.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            // Set loading state untuk baris ini
            container.innerHTML = '<span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase border bg-slate-50 text-slate-400 border-slate-200 animate-pulse"><i class="fas fa-spinner fa-spin mr-1.5"></i> SYNCING</span>';

            try {
                const response = await fetch('<?= site_url('bsre/sync-status') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'email=' + encodeURIComponent(email)
                });

                const data = await response.json();

                if (data.status === 'success') {
                    const colorClass = getJsStatusColor(data.bsre_status);
                    container.innerHTML = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase border ${colorClass}">${data.bsre_status}</span>`;
                    success++;
                } else {
                    const errorMsg = data.message || 'Gagal';
                    container.innerHTML = `<button onclick="showGlobalError('Gagal Sinkronisasi', '${errorMsg.replace(/'/g, "\\'")}')" class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase border bg-red-50 text-red-600 border-red-200 hover:bg-red-100 transition-colors">ERROR</button>`;
                    failed++;
                }
            } catch (error) {
                console.error('Sync failed for ' + email, error);
                const errorMsg = 'Masalah Koneksi Jaringan';
                container.innerHTML = `<button onclick="showGlobalError('Kesalahan Jaringan', '${errorMsg}')" class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase border bg-red-50 text-red-600 border-red-200 hover:bg-red-100 transition-colors">ERROR</button>`;
                failed++;
            }

            processed++;
            mainBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> TTE: ${processed}/${containers.length}`;
            syncBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Sinkronisasi ${processed}/${containers.length}...`;
        }

        // 4. Restore tombol
        mainBtn.disabled = false;
        mainBtn.classList.remove('opacity-75', 'cursor-not-allowed', 'bg-slate-700');
        mainBtn.innerHTML = originalMainContent;
        syncBtn.disabled = false;
        syncBtn.innerHTML = originalBtnContent;

        alert(`Sinkronisasi Selesai!\nTotal: ${processed}\nBerhasil: ${success}\nGagal: ${failed}`);
    }
</script>
<?= $this->endSection() ?>