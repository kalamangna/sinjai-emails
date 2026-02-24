<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-5">
            <div class="w-14 h-14 bg-amber-600 rounded-2xl flex items-center justify-center shadow-lg shadow-amber-200">
                <i class="fas fa-history text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Log Layanan</h1>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1 flex items-center">
                    <span class="w-2 h-2 bg-amber-500 rounded-full mr-2 animate-pulse"></span>
                    Dokumentasi Dukungan Teknis
                </p>
            </div>
        </div>
        
        <div class="flex items-center gap-3 w-full lg:w-auto">
            <a href="<?= site_url('assistance/export_pdf') . '?' . http_build_query(['category' => $filterCategory, 'month' => $filterMonth, 'year' => $filterYear]) ?>" class="flex-1 lg:flex-none inline-flex items-center justify-center px-6 py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-xs uppercase tracking-widest transition-all shadow-md no-underline group">
                <i class="fas fa-file-pdf mr-2"></i> PDF
            </a>
            <?php if (session()->get('role') === 'super_admin'): ?>
            <a href="<?= site_url('assistance/create') ?>" class="flex-1 lg:flex-none inline-flex items-center justify-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs uppercase tracking-widest transition-all shadow-md no-underline">
                <i class="fas fa-plus mr-2"></i> Tambah
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="p-8 bg-slate-50/50 border-b border-slate-100">
            <form method="GET" action="<?= site_url('assistance') ?>" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                <div class="md:col-span-2">
                    <label for="year" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2.5 ml-1">Tahun</label>
                    <select name="year" id="year" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-semibold text-slate-700 transition-all appearance-none cursor-pointer">
                        <option value="">Semua</option>
                        <?php foreach ($yearOptions as $year): ?>
                            <option value="<?= $year ?>" <?= ($filterYear == $year) ? 'selected' : '' ?>><?= $year ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label for="month" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2.5 ml-1">Bulan</label>
                    <select name="month" id="month" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-semibold text-slate-700 transition-all appearance-none cursor-pointer">
                        <option value="">Semua</option>
                        <?php foreach ($monthNames as $num => $name): ?>
                            <option value="<?= $num ?>" <?= ($filterMonth == $num) ? 'selected' : '' ?>><?= esc($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-4">
                    <label for="category" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2.5 ml-1">Kategori</label>
                    <select name="category" id="category" class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-semibold text-slate-700 transition-all appearance-none cursor-pointer">
                        <option value="">Semua</option>
                        <?php foreach ($categoryMap as $id => $label): ?>
                            <option value="<?= $id ?>" <?= ($filterCategory == $id) ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-3 flex gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center h-[46px] bg-slate-900 hover:bg-emerald-600 text-white rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="<?= site_url('assistance') ?>" class="inline-flex items-center justify-center w-[46px] h-[46px] bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 rounded-xl transition-all" title="Reset">
                        <i class="fas fa-redo text-xs"></i>
                    </a>
                </div>
            </form>
        </div>

        <?php if (session()->getFlashdata('message')): ?>
            <div class="m-8 bg-emerald-50 border border-emerald-100 text-emerald-700 px-6 py-4 rounded-2xl flex items-center shadow-sm">
                <i class="fas fa-check-circle mr-3 text-emerald-500 text-lg"></i>
                <span class="font-bold text-xs uppercase tracking-wider"><?= session()->getFlashdata('message') ?></span>
            </div>
        <?php endif; ?>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-50/80">
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest w-12">ID</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Waktu</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Unit Kerja</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Layanan</th>
                        <th class="px-8 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 bg-white">
                    <?php if (!empty($activities)): ?>
                        <?php foreach ($activities as $key => $item): ?>
                            <tr class="hover:bg-emerald-50/30 transition-all group">
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <span class="text-xs font-bold text-slate-300 font-mono">#<?= $item['id'] ?></span>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="text-[13px] font-bold text-slate-900"><?= date('d M Y', strtotime($item['tanggal_kegiatan'])) ?></div>
                                        <?php
                                            $method = $item['method'];
                                            $type = ($method == 'Online') ? 'info' : 'warning';
                                            echo view('components/badge', ['label' => $method, 'type' => $type, 'rounded' => true]);
                                        ?>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 group-hover:bg-white border border-slate-200 flex items-center justify-center transition-colors">
                                            <i class="fas fa-building text-slate-400 group-hover:text-emerald-600 text-sm"></i>
                                        </div>
                                        <div>
                                            <div class="text-[12px] font-bold text-slate-900 leading-tight uppercase group-hover:text-emerald-700 transition-colors"><?= esc($item['agency_name']) ?></div>
                                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mt-1"><?= esc($item['agency_type']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-2">
                                        <div class="text-[11px] font-black text-emerald-600 uppercase tracking-tight">
                                            <?= esc($categoryMap[$item['category']] ?? 'Unknown') ?>
                                        </div>
                                        <?php
                                            $services = json_decode($item['services'], true);
                                            $serviceStr = is_array($services) ? implode(', ', $services) : $item['services'];
                                        ?>
                                        <div class="text-[11px] font-bold text-slate-700 uppercase tracking-tight leading-relaxed">
                                            <?= esc($serviceStr) ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-center">
                                    <?php if (session()->get('role') === 'super_admin'): ?>
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= site_url('assistance/edit/' . $item['id']) ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 transition-all shadow-sm" title="Edit">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <a href="<?= site_url('assistance/delete/' . $item['id']) ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-rose-600 transition-all shadow-sm" onclick="return confirm('Hapus log?')" title="Hapus">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </a>
                                    </div>
                                    <?php else: ?>
                                        <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest italic">View Only</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 border border-dashed border-slate-200">
                                        <i class="fas fa-history text-slate-300 text-xl"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Belum ada data</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
