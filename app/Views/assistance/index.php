<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Log Layanan</h2>
        <div class="flex flex-wrap gap-2">
            <a href="<?= site_url('assistance/export_pdf') . '?' . http_build_query(['category' => $filterCategory, 'month' => $filterMonth, 'year' => $filterYear]) ?>" class="inline-flex items-center justify-center px-3 py-2 bg-rose-600 border border-transparent rounded-lg font-bold text-[10px] text-white uppercase tracking-wider hover:bg-rose-700 active:bg-rose-800 transition-all shadow-sm no-underline">
                <i class="fas fa-file-pdf mr-1.5"></i> PDF
            </a>
            <a href="<?= site_url('assistance/create') ?>" class="inline-flex items-center justify-center px-3 py-2 bg-blue-600 border border-transparent rounded-lg font-bold text-[10px] text-white uppercase tracking-wider hover:bg-blue-700 active:bg-blue-800 transition-all shadow-sm no-underline">
                <i class="fas fa-plus mr-1.5"></i> Tambah
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <form method="GET" action="<?= site_url('assistance') ?>" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-3">
                <label for="year" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Tahun</label>
                <select name="year" id="year" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                    <option value="">Semua Tahun</option>
                    <?php foreach ($yearOptions as $year): ?>
                        <option value="<?= $year ?>" <?= ($filterYear == $year) ? 'selected' : '' ?>><?= $year ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-3">
                <label for="month" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Bulan</label>
                <select name="month" id="month" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                    <option value="">Semua Bulan</option>
                    <?php foreach ($monthNames as $num => $name): ?>
                        <option value="<?= $num ?>" <?= ($filterMonth == $num) ? 'selected' : '' ?>><?= esc($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-4">
                <label for="category" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Kategori</label>
                <select name="category" id="category" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categoryMap as $id => $label): ?>
                        <option value="<?= $id ?>" <?= ($filterCategory == $id) ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-slate-800 border border-transparent rounded-lg font-bold text-[11px] text-white uppercase tracking-wider hover:bg-slate-900 active:bg-slate-950 transition-all shadow-sm focus:outline-none">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                <a href="<?= site_url('assistance') ?>" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-lg font-bold text-[11px] text-slate-600 uppercase tracking-wider hover:bg-slate-50 hover:text-slate-900 active:bg-slate-100 transition-all shadow-sm no-underline" title="Reset Filter">
                    <i class="fas fa-redo mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-5 py-3 rounded-lg flex items-center shadow-sm">
            <i class="fas fa-check-circle mr-3 text-emerald-500"></i>
            <span class="font-bold text-xs uppercase tracking-wider"><?= session()->getFlashdata('message') ?></span>
        </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest w-12">No</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tanggal</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Unit Kerja</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Metode</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kategori</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Layanan</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Keterangan</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php if (!empty($activities)): ?>
                        <?php foreach ($activities as $key => $item): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-slate-400 font-mono">#<?= $key + 1 ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-[13px] font-semibold text-slate-700">
                                    <?= date('d M Y', strtotime($item['tanggal_kegiatan'])) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-[13px] font-bold text-slate-900 leading-none mb-1 uppercase"><?= esc($item['agency_name']) ?></div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tight opacity-70"><?= esc($item['agency_type']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                        $method = $item['method'];
                                        $mCls = ($method == 'Online') ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'bg-amber-50 text-amber-700 border-amber-100';
                                    ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border <?= $mCls ?>">
                                        <?= esc($method) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-[11px] font-bold text-blue-600 uppercase tracking-tight">
                                    <?= esc($categoryMap[$item['category']] ?? 'Unknown') ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                        $services = json_decode($item['services'], true);
                                        $serviceStr = is_array($services) ? implode(', ', $services) : $item['services'];
                                    ?>
                                    <div class="text-[11px] font-bold text-slate-700 uppercase tracking-tight">
                                        <?= esc($serviceStr) ?>
                                    </div>
                                </td>
                                                                <td class="px-6 py-4">
                                                                    <div class="text-[11px] font-medium text-slate-600 line-clamp-2 italic" title="<?= esc($item['keterangan']) ?>">
                                                                        <?= esc($item['keterangan']) ?: '-' ?>
                                                                    </div>
                                                                </td>                                                                <td class="px-6 py-4 whitespace-nowrap text-center">                                    <div class="flex justify-center space-x-2">
                                        <a href="<?= site_url('assistance/edit/' . $item['id']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 transition-all no-underline shadow-sm">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <a href="<?= site_url('assistance/delete/' . $item['id']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-300 hover:bg-rose-50 transition-all no-underline shadow-sm" onclick="return confirm('Hapus log kegiatan ini?')">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-xs font-medium italic">Belum ada data log pendampingan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>