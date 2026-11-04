<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight"><?= esc($title) ?></h1>
        </div>
        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
            <a href="<?= site_url('admin/bridge-log/download') ?>" class="flex-1 sm:flex-none btn btn-outline text-xs px-3 py-2 no-underline">
                <i class="fas fa-download mr-1.5 text-slate-600"></i> Unduh Berkas Log
            </a>
            <?php if (session()->get('role') === 'super_admin'): ?>
                <form action="<?= site_url('admin/bridge-log/clear') ?>" method="POST" onsubmit="return confirm('Kosongkan seluruh berkas log koneksi bridge? Data riwayat akan dihapus permanen.');" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline text-xs px-3 py-2 text-rose-600 hover:bg-rose-50 border-rose-200">
                        <i class="fas fa-trash-alt mr-1.5"></i> Kosongkan Log
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ringkasan Metrik -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
        <div class="bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-2xl shadow-sm p-4 sm:p-6">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Permintaan</p>
            <h3 class="text-xl sm:text-2xl font-bold text-slate-800 mt-1"><?= number_format($totalRequests, 0, ',', '.') ?></h3>
        </div>
        <div class="bg-white border border-slate-200 border-l-4 border-l-emerald-600 rounded-2xl shadow-sm p-4 sm:p-6">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Permintaan Berhasil</p>
            <h3 class="text-xl sm:text-2xl font-bold text-emerald-700 mt-1"><?= number_format($successCount, 0, ',', '.') ?></h3>
        </div>
        <div class="bg-white border border-slate-200 border-l-4 border-l-amber-500 rounded-2xl shadow-sm p-4 sm:p-6">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Permintaan Ditolak</p>
            <h3 class="text-xl sm:text-2xl font-bold text-amber-700 mt-1"><?= number_format($rejectedCount, 0, ',', '.') ?></h3>
        </div>
        <div class="bg-white border border-slate-200 border-l-4 border-l-rose-600 rounded-2xl shadow-sm p-4 sm:p-6">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Kendala Server</p>
            <h3 class="text-xl sm:text-2xl font-bold text-rose-700 mt-1"><?= number_format($serverErrCount, 0, ',', '.') ?></h3>
        </div>
    </div>

    <!-- Filter & Tabel -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

        <!-- Bilah Filter -->
        <div class="p-4 sm:p-6 border-b border-slate-100 bg-slate-50">
            <form method="GET" action="<?= site_url('admin/bridge-log') ?>" class="grid grid-cols-1 md:grid-cols-12 gap-y-4 gap-x-4 items-end">
                
                <!-- Pencarian -->
                <div class="md:col-span-5">
                    <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-widest">Cari Entri</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= esc($search) ?>" class="block w-full pl-9 pr-3 py-2 bg-white border <?= !empty($search) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm text-slate-800 placeholder-slate-400 font-medium transition-all" placeholder="192.168.1.1 atau Tongketongke">
                    </div>
                </div>

                <!-- Status Kode -->
                <div class="md:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-widest">Status Kode</label>
                    <select name="status" class="block w-full px-3 py-2 bg-white border <?= !empty($filterStatus) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua Status</option>
                        <option value="2xx" <?= $filterStatus === '2xx' ? 'selected' : '' ?>>Status Berhasil 2xx</option>
                        <option value="4xx" <?= $filterStatus === '4xx' ? 'selected' : '' ?>>Status Ditolak 4xx</option>
                        <option value="5xx" <?= $filterStatus === '5xx' ? 'selected' : '' ?>>Status Kendala 5xx</option>
                        <option value="401" <?= $filterStatus === '401' ? 'selected' : '' ?>>401 Unauthorized</option>
                        <option value="403" <?= $filterStatus === '403' ? 'selected' : '' ?>>403 Forbidden</option>
                        <option value="413" <?= $filterStatus === '413' ? 'selected' : '' ?>>413 Payload Too Large</option>
                        <option value="429" <?= $filterStatus === '429' ? 'selected' : '' ?>>429 Too Many Requests</option>
                        <option value="502" <?= $filterStatus === '502' ? 'selected' : '' ?>>502 Bad Gateway</option>
                    </select>
                </div>

                <!-- Metode HTTP -->
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-widest">Metode</label>
                    <select name="method" class="block w-full px-3 py-2 bg-white border <?= !empty($filterMethod) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua Metode</option>
                        <option value="GET" <?= $filterMethod === 'GET' ? 'selected' : '' ?>>GET</option>
                        <option value="POST" <?= $filterMethod === 'POST' ? 'selected' : '' ?>>POST</option>
                    </select>
                </div>

                <!-- Tombol Aksi Kontrol Tabel -->
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 btn btn-solid text-xs">
                        <i class="fas fa-filter mr-1.5 text-white/80"></i> Filter
                    </button>
                    <a href="<?= site_url('admin/bridge-log') ?>" class="btn btn-outline text-xs px-3" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Data -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm min-w-[650px]">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[10px] font-bold tracking-widest">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200 whitespace-nowrap">Waktu</th>
                        <th class="px-6 py-3 border-b border-slate-200 whitespace-nowrap">Instansi</th>
                        <th class="px-6 py-3 border-b border-slate-200 whitespace-nowrap">Alamat IP</th>
                        <th class="px-6 py-3 border-b border-slate-200">Endpoint</th>
                        <th class="px-6 py-3 border-b border-slate-200 whitespace-nowrap text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                        <i class="fas fa-search text-slate-300 text-lg"></i>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Belum ada catatan koneksi bridge</span>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $row): ?>
                            <?php
                            $status = (int)$row['status'];
                            if ($status >= 200 && $status < 300) {
                                $badgeClass = 'bg-emerald-100 text-emerald-800';
                            } elseif ($status >= 400 && $status < 500) {
                                $badgeClass = 'bg-amber-100 text-amber-800';
                            } else {
                                $badgeClass = 'bg-red-100 text-red-700';
                            }
                            ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                    <?= esc($row['timestamp']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if (!empty($row['agency'])): ?>
                                        <span class="font-bold text-slate-800 uppercase tracking-tight">
                                            <?= esc($row['agency']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 font-mono">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 font-mono text-[11px] font-bold text-slate-800 whitespace-nowrap">
                                    <?= esc($row['ip']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold font-mono <?= $row['method'] === 'POST' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-700' ?>">
                                            <?= esc($row['method']) ?>
                                        </span>
                                        <span class="font-mono text-[11px] font-semibold text-slate-800">
                                            <?= esc($row['endpoint']) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-widest <?= $badgeClass ?>">
                                        <?= esc((string)$row['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        <?php if (!empty($logs) && $totalFiltered > 0): ?>
            <?php
            $currentPage = $page ?? 1;
            $start = ($currentPage - 1) * $perPage + 1;
            $end = min($start + count($logs) - 1, $totalFiltered);
            ?>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">
                    Menampilkan <span class="text-slate-800"><?= number_format($start, 0, ',', '.') ?> - <?= number_format($end, 0, ',', '.') ?></span> dari <span class="text-slate-800"><?= number_format($totalFiltered, 0, ',', '.') ?></span> Log
                </div>
                <?php if ($totalFiltered > $perPage && !empty($pager)): ?>
                    <div class="pagination-container">
                        <?= $pager ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>

</div>
<?= $this->endSection() ?>
