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
            <a href="<?= $back_url ?>" class="flex-1 lg:flex-none btn btn-outline no-underline">
                <i class="fas fa-arrow-left mr-2 text-slate-700"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Tabel -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200">No.</th>
                        <th class="px-6 py-3 border-b border-slate-200">Nama / NIP</th>
                        <th class="px-6 py-3 border-b border-slate-200">Unit Kerja</th>
                        <th class="px-6 py-3 border-b border-slate-200">Status TTE</th>
                        <th class="px-6 py-3 border-b border-slate-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($emails)): ?>
                        <?php
                        $perPage = 100;
                        $currentPage = $pager->getCurrentPage();
                        $i = ($currentPage - 1) * $perPage + 1;
                        foreach ($emails as $email):
                        ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-[10px] font-bold text-slate-700 font-mono">
                                        <?= $i++ ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-800 uppercase tracking-tight leading-tight"><?= esc($email['name']) ?></span>
                                        <span class="text-[10px] font-bold text-slate-500 mt-0.5">NIP: <?= esc($email['nip'] ?: '-') ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
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
                                        <button onclick="syncBsreStatus('<?= esc($email['email'], 'js') ?>', <?= $email['id'] ?>)" class="btn btn-table !w-6 !h-6" title="Sinkronisasi TTE">
                                            <i class="fas fa-fingerprint text-[10px]"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="btn btn-table" title="Detail">
                                            <i class="fas fa-eye text-xs text-slate-700"></i>
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

        <?php if (!empty($emails)): ?>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">
                    Menampilkan <span class="text-slate-800"><?= count($emails) ?></span> dari <span class="text-slate-800"><?= number_format($total_count, 0, ',', '.') ?></span> Pegawai
                </div>
                <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
                    <div class="pagination-container">
                        <?= $pager->links() ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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
        container.innerHTML = '<i class="fas fa-spinner fa-spin text-slate-700 text-[10px]"></i>';

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
                return true;
            } else {
                container.innerHTML = '<span class="text-[9px] text-red-600 font-bold uppercase">Gagal</span>';
                return false;
            }
        } catch (error) {
            container.innerHTML = '<span class="text-[9px] text-red-600 font-bold uppercase">Error</span>';
            return false;
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
        for (const email of emails) {
            processed++;

            // Scroll ke row yang sedang diproses
            const containerId = `bsre-status-${email.id}`;
            const element = document.getElementById(containerId);
            if (element) {
                element.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

            btn.innerHTML = `<i class="fas fa-fingerprint animate-pulse mr-2"></i> Sinkronisasi ${processed}/${emails.length}...`;
            await syncBsreStatus(email.email, email.id);
        }

        btn.innerHTML = originalContent;
        btn.disabled = false;
        btn.classList.remove('opacity-75', 'cursor-not-allowed');
        alert('Proses sinkronisasi halaman selesai.');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const emails = <?= json_encode($emails) ?>;
        emails.forEach(email => {
            renderBsreStatus(email.bsre_status, `bsre-status-${email.id}`);
        });
    });
</script>
<?= $this->endSection() ?>