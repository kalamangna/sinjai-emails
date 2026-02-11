<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-10">
    <!-- Notifikasi -->
    <div id="flash-message-container" class="space-y-4 max-w-4xl mx-auto">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="bg-green-500/10 border border-green-500/20 text-green-400 px-6 py-4 rounded-2xl flex items-center shadow-2xl" role="alert">
                <i class="fas fa-check-circle mr-4 text-xl"></i>
                <span class="font-bold text-sm uppercase tracking-widest"><?= session()->getFlashdata('success') ?></span>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-6 py-4 rounded-2xl flex items-center shadow-2xl" role="alert">
                <i class="fas fa-exclamation-triangle mr-4 text-xl"></i>
                <span class="font-bold text-sm uppercase tracking-widest"><?= session()->getFlashdata('error') ?></span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Header & Sync -->
    <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-6 bg-slate-900/50 p-8 rounded-[2rem] border border-slate-800 shadow-xl">
        <div class="space-y-2">
            <h1 class="text-3xl font-black text-slate-100 uppercase tracking-tight">Manajemen Email</h1>
            <div class="flex items-center text-xs text-slate-500 font-black uppercase tracking-widest">
                <i class="fas fa-history mr-2.5 text-blue-500/50"></i>
                Update: <span class="text-slate-400 ml-1"><?php echo isset($last_sync_time) ? get_local_datetime(strtotime($last_sync_time)) : '-'; ?></span>
            </div>
        </div>
        <a href="<?= site_url('email/sync') ?>" class="inline-flex items-center px-8 py-4 bg-blue-600 border border-transparent rounded-2xl font-black text-xs text-white uppercase tracking-[0.2em] hover:bg-blue-700 transition-all shadow-xl shadow-blue-900/20 no-underline group" id="syncButton">
            <div class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full mr-3 hidden spinner-border"></div>
            <i class="fas fa-sync-alt mr-3 group-hover:rotate-180 transition-transform duration-500"></i>
            <span class="button-text">Sinkronisasi cPanel</span>
        </a>
    </div>

    <!-- Statistik Utama -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-slate-900 border border-slate-800 p-8 rounded-[2rem] flex flex-col items-center text-center shadow-xl group hover:border-blue-500/30 transition-all">
            <div class="w-14 h-14 bg-blue-500/5 text-blue-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                <i class="fas fa-envelope text-2xl"></i>
            </div>
            <div class="text-4xl font-black text-slate-100 tracking-tighter mb-2"><?= number_format($total_emails) ?></div>
            <div class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Total Akun</div>
        </div>

        <div class="bg-slate-900 border border-slate-800 p-8 rounded-[2rem] flex flex-col items-center text-center shadow-xl group hover:border-green-500/30 transition-all">
            <div class="w-14 h-14 bg-green-500/5 text-green-400 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                <i class="fas fa-check-circle text-2xl"></i>
            </div>
            <div class="text-4xl font-black text-slate-100 tracking-tighter mb-2"><?= number_format($active_count) ?></div>
            <div class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Status Aktif</div>
        </div>

        <div class="bg-slate-900 border border-slate-800 p-8 rounded-[2rem] flex flex-col items-center text-center shadow-xl group hover:border-red-500/30 transition-all">
            <div class="w-14 h-14 bg-red-500/5 text-red-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                <i class="fas fa-user-slash text-2xl"></i>
            </div>
            <div class="text-4xl font-black text-slate-100 tracking-tighter mb-2"><?= number_format($suspended_count) ?></div>
            <div class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Ditangguhkan</div>
        </div>
    </div>

    <!-- Statistik Detil (Grid) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Kepegawaian -->
        <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
            <div class="bg-slate-800/30 px-8 py-5 border-b border-slate-800">
                <h6 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                    <i class="fas fa-user-tag mr-3 text-blue-500 opacity-50"></i>Statistik Kepegawaian
                </h6>
            </div>
            <div class="p-10 flex flex-col sm:flex-row items-center gap-10">
                <div class="w-full sm:w-1/2 min-h-[220px] relative">
                    <div id="asnStatusChart"></div>
                </div>
                <div class="w-full sm:w-1/2 overflow-y-auto max-h-[220px] space-y-2 custom-scrollbar pr-2">
                    <?php foreach ($status_asn_counts as $index => $status): ?>
                        <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex justify-between items-center group hover:border-slate-700 transition-colors">
                            <div class="flex items-center">
                                <span class="w-2 h-2 rounded-full mr-3 shadow-sm asn-legend-dot" data-index="<?= $index ?>"></span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight group-hover:text-slate-200 transition-colors"><?= esc($status['name']) ?></span>
                            </div>
                            <span class="text-[10px] font-black text-slate-100"><?= number_format($status['count']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Sertifikat -->
        <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
            <div class="bg-slate-800/30 px-8 py-5 border-b border-slate-800">
                <h6 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                    <i class="fas fa-fingerprint mr-3 text-amber-500 opacity-50"></i>Status Sertifikat (TTE)
                </h6>
            </div>
            <div class="p-10 flex flex-col sm:flex-row items-center gap-10">
                <div class="w-full sm:w-1/2 min-h-[220px] relative">
                    <div id="tteStatusChart"></div>
                </div>
                <div class="w-full sm:w-1/2 overflow-y-auto max-h-[220px] space-y-2 custom-scrollbar pr-2">
                    <?php foreach ($bsre_status_counts as $status): ?>
                        <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex justify-between items-center group hover:border-slate-700 transition-colors">
                            <div class="flex items-center">
                                <span class="w-2 h-2 rounded-full mr-3 shadow-sm tte-legend-dot" data-status="<?= $status['status'] ?>"></span>
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight group-hover:text-slate-200 transition-colors leading-none"><?= esc($status['label']) ?></span>
                                </div>
                            </div>
                            <span class="text-[10px] font-black text-slate-100"><?= number_format($status['count']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Pencarian & Tabel Utama -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
        <div class="bg-slate-800/30 px-10 py-8 border-b border-slate-800">
            <form method="GET" action="" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end">
                <div class="lg:col-span-5">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-3 ml-1">Kata Kunci Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-600">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= isset($search) ? esc($search) : '' ?>" class="block w-full pl-11 pr-4 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm font-bold text-slate-200 transition-all placeholder-slate-700 uppercase tracking-tight" placeholder="Email, Nama, NIK, atau NIP...">
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-3 ml-1">Status Sertifikat</label>
                    <select name="bsre_status" class="block w-full px-4 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase tracking-tight cursor-pointer transition-all">
                        <option value="">Semua Status</option>
                        <?php foreach ($bsre_status_options as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= ($bsre_status === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                        <option value="not_synced" <?= ($bsre_status === 'not_synced') ? 'selected' : '' ?>>BELUM SINKRON</option>
                    </select>
                </div>

                <div class="lg:col-span-3 flex gap-3">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-black py-3.5 px-6 rounded-2xl shadow-lg shadow-blue-900/20 transition-all text-[10px] uppercase tracking-[0.2em]">
                        Filter
                    </button>
                    <a href="<?= current_url() ?>" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-200 font-black py-3.5 px-6 rounded-2xl shadow-sm transition-all text-[10px] text-center no-underline uppercase tracking-[0.2em]">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Akun -->
        <div class="p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-800">
                    <thead class="bg-slate-950/30">
                        <tr>
                            <th class="px-10 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Identitas Akun</th>
                            <th class="px-10 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Unit Kerja</th>
                            <th class="px-10 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Sertifikat</th>
                            <th class="px-10 py-6 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] w-40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 bg-slate-900/30">
                        <?php foreach ($emails as $email): ?>
                            <tr class="hover:bg-slate-800/30 transition-colors group">
                                <td class="px-10 py-6 whitespace-nowrap align-middle">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-center mr-5 group-hover:border-blue-500/30 transition-all">
                                            <i class="fas fa-envelope text-slate-600 group-hover:text-blue-500 text-lg"></i>
                                        </div>
                                        <div>
                                            <div class="text-base font-black text-slate-200 tracking-tighter leading-none mb-1.5 lowercase"><?= esc($email['email']) ?></div>
                                            <div class="text-[11px] text-slate-500 uppercase font-black tracking-widest"><?= esc($email['name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-10 py-6 align-middle">
                                    <?php if (!empty($email['parent_unit_kerja_name'])): ?>
                                        <div class="text-sm font-black text-slate-300 uppercase tracking-tight"><?= esc($email['parent_unit_kerja_name']) ?></div>
                                        <div class="text-[10px] text-slate-500 uppercase font-bold tracking-tight mt-0.5 opacity-60"><?= esc($email['unit_kerja_name']) ?></div>
                                    <?php else: ?>
                                        <div class="text-sm font-black text-slate-300 uppercase tracking-tight"><?= esc($email['unit_kerja_name']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-10 py-6 whitespace-nowrap align-middle">
                                    <div id="bsre-status-<?= esc($email['user']) ?>" 
                                         data-user="<?= esc($email['user']) ?>" 
                                         data-email="<?= esc($email['email']) ?>">
                                        <?php
                                            $status = $email['bsre_status'] ?? '';
                                            $badgeBase = 'inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border shadow-sm';
                                            
                                            // Define mapping for PHP rendering
                                            $colors = [
                                                'ISSUE' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                                'EXPIRED' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                                'RENEW' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                                'WAITING_FOR_VERIFICATION' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                                'NEW' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                                                'NO_CERTIFICATE' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                                                'NOT_REGISTERED' => 'bg-fuchsia-500/10 text-fuchsia-400 border-fuchsia-500/20',
                                                'SUSPEND' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                                'REVOKE' => 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20',
                                            ];
                                            
                                            $badgeClass = $colors[$status] ?? 'bg-slate-950 text-slate-600 border-slate-800';
                                            $badgeText = $status ?: 'BELUM SINKRON';
                                        ?>
                                        <span class="<?= $badgeBase ?> <?= $badgeClass ?>"><?= esc($badgeText) ?></span>
                                    </div>
                                </td>
                                <td class="px-10 py-6 whitespace-nowrap text-center text-sm font-medium align-middle">
                                    <div class="flex justify-center space-x-3">
                                        <a href="<?= site_url('email/detail/' . $email['user']) ?>" class="w-10 h-10 flex items-center justify-center bg-slate-950 text-slate-400 border border-slate-800 rounded-xl hover:bg-blue-600 hover:text-white hover:border-transparent transition-all no-underline shadow-sm" title="Rincian">
                                            <i class="fas fa-eye text-sm"></i>
                                        </a>
                                        <button type="button" class="w-10 h-10 flex items-center justify-center bg-slate-950 text-slate-400 border border-slate-800 rounded-xl hover:bg-red-600 hover:text-white hover:border-transparent transition-all shadow-sm" onclick="openDeleteModal(<?= $email['id'] ?>, '<?= esc($email['email']) ?>')" title="Hapus Akun">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginasi -->
            <?php if ($pagination): ?>
                <div class="bg-slate-950/50 px-10 py-10 border-t border-slate-800 flex flex-col md:flex-row justify-between items-center gap-8">
                    <div class="text-xs font-black text-slate-500 uppercase tracking-widest leading-loose">
                        Menampilkan <span class="text-blue-500 px-1"><?= ($pagination->getCurrentPage() - 1) * $pagination->getPerPage() + 1 ?></span> s/d <span class="text-blue-500 px-1"><?= min($pagination->getCurrentPage() * $pagination->getPerPage(), $filtered_count) ?></span> dari <span class="text-blue-500 px-1"><?= number_format($filtered_count) ?></span> entri
                    </div>
                    <div class="pagination-container font-black">
                        <?= $pagination->links() ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Hapus -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-slate-900 rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-800">
            <div class="p-10">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-14 w-14 rounded-2xl bg-red-500/10 text-red-500 border border-red-500/20 sm:mx-0">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                    <div class="mt-6 text-center sm:mt-0 sm:ml-8 sm:text-left">
                        <h3 class="text-xl font-black text-slate-100 uppercase tracking-tight mb-3">Konfirmasi Penghapusan</h3>
                        <p class="text-sm text-slate-400 leading-relaxed font-medium uppercase tracking-tight">
                            Akun <strong id="emailToDelete" class="text-red-400 font-black"></strong> akan dihapus <span class="underline decoration-red-500/50 decoration-2 underline-offset-4">permanen</span> dari server cPanel.
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-slate-950/50 px-10 py-8 flex flex-row-reverse gap-4">
                <form id="deleteForm" action="" method="post" class="flex-1">
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-black py-4 rounded-2xl shadow-xl shadow-red-900/20 transition-all text-xs uppercase tracking-widest">
                        Hapus Permanen
                    </button>
                </form>
                <button type="button" onclick="closeDeleteModal()" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-200 font-black py-4 rounded-2xl shadow-sm transition-all text-xs uppercase tracking-widest">
                    Batalkan
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function toggleVisibility(id) {
        const el = document.getElementById(id);
        const chevron = document.getElementById(id.replace('List', 'Chevron'));
        if (el.classList.contains('hidden')) {
            el.classList.remove('hidden');
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        } else {
            el.classList.add('hidden');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }
    }

    function openDeleteModal(id, email) {
        const modal = document.getElementById('deleteModal');
        const emailSpan = document.getElementById('emailToDelete');
        const form = document.getElementById('deleteForm');
        emailSpan.textContent = email;
        form.action = '<?= site_url('email/delete/') ?>' + id;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.getElementById('syncButton').addEventListener('click', function(e) {
        e.preventDefault();
        const button = this;
        if (button.classList.contains('opacity-50')) return;
        button.classList.add('opacity-50', 'cursor-not-allowed');
        button.querySelector('.spinner-border').classList.remove('hidden');
        button.querySelector('.fa-sync-alt').classList.add('hidden');
        button.querySelector('.button-text').textContent = 'Sinkronisasi...';

        fetch('<?= site_url('email/sync') ?>?t=' + new Date().getTime())
            .then(response => response.json())
            .then(data => {
                if (data.success) setTimeout(() => window.location.reload(), 1000);
                else alert(data.message);
            })
            .catch(() => alert('Terjadi kesalahan jaringan.'));
    });

    document.addEventListener("DOMContentLoaded", function() {
        const commonOptions = {
            chart: { type: 'donut', height: 220, foreColor: '#94a3b8' },
            stroke: { show: false },
            dataLabels: { enabled: false },
            legend: { show: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '75%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '12px', fontWeight: 900, offsetY: -10 },
                            value: { show: true, fontSize: '20px', fontWeight: 900, offsetY: 10, color: '#f1f5f9' },
                            total: { show: true, label: 'TOTAL', fontSize: '10px', fontWeight: 900, color: '#64748b' }
                        }
                    }
                }
            },
            tooltip: { theme: 'dark' }
        };

        // ASN Status Chart
        const asnStats = <?= json_encode($status_asn_counts) ?>;
        const asnColors = ['#6366f1', '#8b5cf6', '#3b82f6', '#0ea5e9', '#06b6d4', '#14b8a6', '#10b981', '#84cc16'];
        
        new ApexCharts(document.querySelector("#asnStatusChart"), {
            ...commonOptions,
            series: asnStats.map(s => parseInt(s.count)),
            labels: asnStats.map(s => s.name),
            colors: asnStats.map((_, i) => asnColors[i % asnColors.length])
        }).render();

        asnStats.forEach((_, i) => {
            const dot = document.querySelector(`.asn-legend-dot[data-index="${i}"]`);
            if (dot) dot.style.backgroundColor = asnColors[i % asnColors.length];
        });

        // TTE Status Chart
        const tteStats = <?= json_encode($bsre_status_counts) ?>;
        const tteColorMap = {
            'ISSUE': '#10b981', // Emerald
            'EXPIRED': '#ef4444', // Red
            'RENEW': '#3b82f6', // Blue
            'WAITING_FOR_VERIFICATION': '#f59e0b', // Amber
            'NEW': '#6366f1', // Indigo
            'NO_CERTIFICATE': '#eab308', // Yellow
            'NOT_REGISTERED': '#d946ef', // Fuchsia
            'SUSPEND': '#a855f7', // Purple
            'REVOKE': '#71717a', // Zinc
            'not_synced': '#334155' // Slate-700
        };

        new ApexCharts(document.querySelector("#tteStatusChart"), {
            ...commonOptions,
            series: tteStats.map(s => parseInt(s.count)),
            labels: tteStats.map(s => s.label),
            colors: tteStats.map(s => tteColorMap[s.status] || '#1e293b')
        }).render();

        tteStats.forEach(s => {
            const dot = document.querySelector(`.tte-legend-dot[data-status="${s.status}"]`);
            if (dot) dot.style.backgroundColor = tteColorMap[s.status] || '#1e293b';
        });
    });
</script>
<?= $this->endSection() ?>
