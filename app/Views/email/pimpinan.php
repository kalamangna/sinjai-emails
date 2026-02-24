<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <!-- Nav & Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <a href="<?= site_url('email') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all shadow-sm no-underline group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> Kembali
        </a>
        <div class="flex flex-wrap gap-2">
            <a href="<?= site_url('email/export_pimpinan_pdf') ?>" class="inline-flex items-center justify-center px-3 py-2 bg-rose-600 text-white rounded-lg font-bold text-[10px] uppercase tracking-wider hover:bg-rose-700 active:bg-rose-800 transition-all shadow-sm no-underline">
                <i class="fas fa-file-pdf mr-1.5"></i> PDF
            </a>
            <button onclick="syncAllBsreStatus()" class="inline-flex items-center justify-center px-3 py-2 bg-amber-500 text-white rounded-lg font-bold text-[10px] uppercase tracking-wider hover:bg-amber-600 active:bg-amber-700 transition-all shadow-sm focus:outline-none">
                <i class="fas fa-sync-alt mr-1.5"></i> Sync TTE
            </button>
        </div>
    </div>

    <!-- Page Header -->
    <div class="bg-white border border-slate-200 rounded-xl p-8 shadow-sm relative overflow-hidden group">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
            <div class="flex items-center gap-5">
                <div class="w-14 h-14 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-center shadow-sm">
                    <i class="fas fa-user-tie text-blue-600 text-2xl"></i>
                </div>
                <div class="space-y-1">
                    <h2 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight leading-none uppercase">Pimpinan OPD</h2>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Data email Kepala Perangkat Daerah, Sekretaris, dan Pejabat Struktural.</p>
                </div>
            </div>
            <div class="bg-slate-50 px-6 py-3 rounded-lg border border-slate-100">
                <div class="text-center">
                    <div class="text-xl font-bold text-slate-900"><?= number_format($total_emails) ?></div>
                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">Total</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <form action="<?= current_url() ?>" method="get" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-5">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Pencarian</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" name="search" value="<?= esc($search ?? '') ?>" class="block w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all" placeholder="Nama, Email, NIP...">
                </div>
            </div>

            <div class="md:col-span-3 flex gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-slate-800 border border-transparent rounded-lg font-bold text-[11px] text-white uppercase tracking-wider hover:bg-slate-900 active:bg-slate-950 transition-all shadow-sm focus:outline-none">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                <a href="<?= site_url('email/pimpinan') ?>" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-lg font-bold text-[11px] text-slate-600 uppercase tracking-wider hover:bg-slate-50 hover:text-slate-900 active:bg-slate-100 transition-all shadow-sm no-underline" title="Reset Filter">
                    <i class="fas fa-redo mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Akun</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Jabatan</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Unit Kerja</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sertifikat</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php if (!empty($emails)): ?>
                        <?php foreach ($emails as $email): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-9 h-9 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center mr-3 group-hover:bg-blue-50 transition-all">
                                            <i class="fas fa-envelope text-slate-400 group-hover:text-blue-600 text-sm"></i>
                                        </div>
                                        <div>
                                            <div class="text-[13px] font-bold text-slate-900 lowercase leading-none mb-1"><?= esc($email['email']) ?></div>
                                            <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide opacity-80"><?= esc($email['name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-[11px] font-bold text-slate-700 leading-tight line-clamp-2"><?= esc($email['jabatan']) ?: '-' ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if (!empty($email['parent_unit_kerja_name'])): ?>
                                        <div class="text-[11px] font-bold text-slate-700 uppercase"><?= esc($email['parent_unit_kerja_name']) ?></div>
                                        <div class="text-[9px] font-semibold text-slate-400 uppercase tracking-tighter"><?= esc($email['unit_kerja_name']) ?></div>
                                    <?php else: ?>
                                        <div class="text-[11px] font-bold text-slate-700 uppercase"><?= esc($email['unit_kerja_name']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div id="bsre-status-<?= esc($email['user']) ?>" data-email="<?= esc($email['email']) ?>">
                                        <?php
                                        $st = $email['bsre_status'] ?? '';
                                        $cols = [
                                            'ISSUE' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                            'EXPIRED' => 'bg-rose-50 text-rose-700 border-rose-100',
                                            'RENEW' => 'bg-blue-50 text-blue-700 border-blue-100',
                                            'WAITING_FOR_VERIFICATION' => 'bg-amber-50 text-amber-700 border-amber-100',
                                            'NEW' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                            'NO_CERTIFICATE' => 'bg-slate-100 text-slate-600 border-slate-200',
                                        ];
                                        $c = $cols[$st] ?? 'bg-slate-50 text-slate-400 border-slate-100';
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[9px] font-bold uppercase tracking-wider border <?= $c ?>">
                                            <?= $st ?: 'NOT SYNCED' ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 transition-all no-underline shadow-sm" title="Rincian">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-xs font-medium italic">Data tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pagination): ?>
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                    Showing <span class="text-slate-900"><?= count($emails) ?></span> of <span class="text-slate-900"><?= number_format($total_emails) ?></span> accounts
                </div>
                <div class="pagination-container">
                    <?= $pagination->links() ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function syncAllBsreStatus() {
        const containers = document.querySelectorAll('[id^="bsre-status-"]');
        if (!containers.length || !confirm('Sinkronkan status TTE untuk pimpinan yang tampil?')) return;

        containers.forEach((c, i) => {
            setTimeout(() => {
                const user = c.id.replace('bsre-status-', '');
                const email = c.getAttribute('data-email');
                c.innerHTML = '<i class="fas fa-spinner fa-spin text-slate-400 text-[10px]"></i>';
                fetch('<?= site_url('bsre/sync-status') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'email=' + encodeURIComponent(email)
                }).then(r => r.json()).then(d => {
                    if (d.status === 'success') {
                        const colors = {
                            'ISSUE': 'bg-emerald-50 text-emerald-700 border-emerald-100',
                            'EXPIRED': 'bg-rose-50 text-rose-700 border-rose-100',
                            'RENEW': 'bg-blue-50 text-blue-700 border-blue-100',
                            'WAITING_FOR_VERIFICATION': 'bg-amber-50 text-amber-700 border-amber-100',
                            'NEW': 'bg-indigo-50 text-indigo-700 border-indigo-100',
                            'NO_CERTIFICATE': 'bg-slate-100 text-slate-600 border-slate-200',
                        };
                        const cls = colors[d.bsre_status] || 'bg-slate-50 text-slate-400 border-slate-100';
                        c.innerHTML = `<span class="inline-flex items-center px-2.5 py-1 rounded-md text-[9px] font-bold uppercase tracking-wider border ${cls}">${d.bsre_status}</span>`;
                    }
                });
            }, i * 250);
        });
    }
</script>
<?= $this->endSection() ?>