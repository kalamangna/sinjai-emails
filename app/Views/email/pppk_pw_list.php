<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight"><?= $title ?></h1>
            <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest mt-1">
                Total: <span class="text-slate-800"><?= number_format($total_count, 0, ',', '.') ?></span> Pegawai
            </p>
        </div>

        <div class="flex items-center gap-2 w-full lg:w-auto">
            <button onclick="syncAllOnPage()" id="batchSyncBtn" class="flex-1 lg:flex-none btn btn-solid group">
                <i class="fas fa-fingerprint mr-2 group-hover:scale-110 transition-transform"></i>
                <span>Sync TTE</span>
            </button>
        </div>
    </div>

    <!-- Tabel dan Filter -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50">
            <form action="<?= current_url() ?>" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-10">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Filter NIP</label>
                    <select name="has_nip" class="block w-full px-3 py-2 bg-white border <?= !empty($has_nip) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">SEMUA PEGAWAI</option>
                        <option value="yes" <?= ($has_nip ?? '') === 'yes' ? 'selected' : '' ?>>DENGAN NIP</option>
                        <option value="no" <?= ($has_nip ?? '') === 'no' ? 'selected' : '' ?>>TANPA NIP</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 btn btn-solid">
                        <i class="fas fa-filter mr-2 text-white/80"></i> Filter
                    </button>
                    <a href="<?= current_url() ?>" class="btn btn-outline" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200">No. PK</th>
                        <th class="px-6 py-3 border-b border-slate-200">Nama / NIP</th>
                        <th class="px-6 py-3 border-b border-slate-200">Jabatan / Unit Kerja</th>
                        <th class="px-6 py-3 border-b border-slate-200">Status TTE</th>
                        <th class="px-6 py-3 border-b border-slate-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($emails)): ?>
                        <?php
                        foreach ($emails as $email):
                        ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        <?= esc($email['nomor_pk'] ?: '-') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col" id="pegawai-container-<?= $email['id'] ?>" data-nip="<?= esc($email['nip']) ?>">
                                        <span class="font-bold text-slate-800 uppercase tracking-tight leading-tight"><?= esc($email['name']) ?></span>
                                        <span class="text-[10px] font-bold text-slate-500 mt-0.5">NIP: <?= esc($email['nip'] ?: '-') ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tight mb-1 jabatan-sync-target"><?= esc($email['jabatan'] ?: '-') ?></span>
                                        <?php if (!empty($email['parent_unit_kerja_name'])): ?>
                                            <span class="text-[10px] font-bold text-slate-700 uppercase leading-none"><?= esc($email['parent_unit_kerja_name']) ?></span>
                                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight mt-1"><?= esc($email['unit_kerja_name']) ?></span>
                                        <?php else: ?>
                                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight"><?= esc($email['unit_kerja_name'] ?: '-') ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div id="bsre-status-<?= $email['id'] ?>" class="bsre-status-container">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest animate-pulse">Checking...</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="btn btn-table" title="Detail">
                                            <i class="fas fa-eye text-xs text-slate-700"></i>
                                        </a>
                                        <a href="<?= site_url('email/edit_pk/' . $email['user']) ?>" class="btn btn-table" title="Edit PK">
                                            <i class="fas fa-file-contract text-xs text-slate-700"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Data tidak ditemukan</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?= view('components/pagination', ['items' => $emails, 'pager' => $pager, 'label' => 'Pegawai']) ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function renderBsreStatus(status, containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const colorClass = getJsStatusColor(status);
        const label = (status && status.toLowerCase() !== 'not_synced') ? status : 'NOT_SYNCED';

        container.innerHTML = `<span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border ${colorClass}">${label}</span>`;
    }

    async function syncBsreStatus(email, id) {
        const containerId = `bsre-status-${id}`;
        const container = document.getElementById(containerId);
        container.innerHTML = '<span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border bg-slate-50 text-slate-400 border-slate-200 animate-pulse"><i class="fas fa-spinner fa-spin mr-1"></i> SYNCING</span>';

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
                renderBsreStatus(data.bsre_status, containerId);
                return {
                    success: true,
                    status: data.bsre_status
                };
            } else {
                const errorMsg = data.message || 'Gagal';
                container.innerHTML = `<button onclick="showGlobalError('Gagal Sinkronisasi', '${errorMsg.replace(/'/g, "\\'")}')" class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border bg-red-50 text-red-600 border-red-200 hover:bg-red-100 transition-colors">ERROR</button>`;
                return {
                    success: false
                };
            }
        } catch (error) {
            const errorMsg = 'Masalah Koneksi Jaringan';
            container.innerHTML = `<button onclick="showGlobalError('Kesalahan Jaringan', '${errorMsg}')" class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border bg-red-50 text-red-600 border-red-200 hover:bg-red-100 transition-colors">ERROR</button>`;
            return {
                success: false
            };
        }
    }

    async function syncAllOnPage() {
        const btn = document.getElementById('batchSyncBtn');
        const originalContent = btn.innerHTML;

        if (!confirm('Sinkronisasi akan mengecek status TTE untuk semua akun di halaman ini satu per satu. Lanjutkan?')) return;

        const emails = <?= json_encode($emails) ?>;
        if (emails.length === 0) return;

        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');

        let processed = 0;
        let success = 0;
        let failed = 0;

        for (const email of emails) {
            processed++;

            const containerId = `bsre-status-${email.id}`;
            const element = document.getElementById(containerId);
            if (element) {
                element.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

            btn.innerHTML = `<i class="fas fa-fingerprint animate-pulse mr-2"></i> Sinkronisasi ${processed}/${emails.length}...`;
            const result = await syncBsreStatus(email.email, email.id);
            if (result.success) success++;
            else failed++;
        }

        btn.innerHTML = originalContent;
        btn.disabled = false;
        btn.classList.remove('opacity-75', 'cursor-not-allowed');
        alert(`Sinkronisasi Selesai!\nTotal: ${processed}\nBerhasil: ${success}\nGagal: ${failed}`);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const emails = <?= json_encode($emails) ?>;
        emails.forEach(email => {
            renderBsreStatus(email.bsre_status, `bsre-status-${email.id}`);
        });
    });
</script>
<?= $this->endSection() ?>