<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Master Data Unit Kerja</h1>

        <?php if (session()->get('role') === 'super_admin'): ?>
            <div class="flex items-center gap-2 w-full lg:w-auto">
                <a href="<?= site_url('unit_kerja/add') ?>" class="flex-1 lg:flex-none btn btn-solid no-underline">
                    <i class="fas fa-plus mr-2 text-white/80"></i> Tambah
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-lg p-6 shadow-sm">
            <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">Total Unit Kerja</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($total_units, 0, ',', '.') ?></h3>
        </div>
        <div class="bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-lg p-6 shadow-sm">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Unit Induk</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($total_parents, 0, ',', '.') ?></h3>
        </div>
        <div class="bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-lg p-6 shadow-sm">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Sub Unit</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($total_children, 0, ',', '.') ?></h3>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Daftar Unit Kerja</h3>
            <span class="text-[10px] font-bold text-slate-700 bg-white border border-slate-200 px-2 py-0.5 rounded-full shadow-sm">
                TOTAL: <?= number_format(count($unit_kerja_list), 0, ',', '.') ?>
            </span>
        </div>

        <div class="p-6 border-b border-slate-100 bg-slate-50">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= isset($search) ? esc($search) : '' ?>" class="block w-full pl-9 pr-3 py-2 bg-white border <?= !empty($search) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm transition-all" placeholder="Cari nama unit kerja...">
                    </div>
                </div>

                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Unit Induk</label>
                    <select name="parent_id" class="block w-full px-3 py-2 bg-white border <?= !empty($parent_id_filter) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm cursor-pointer transition-all">
                        <option value="">Semua Unit Induk</option>
                        <?php foreach ($parents_with_children as $parent): ?>
                            <option value="<?= esc($parent['id']) ?>" <?= ($parent_id_filter == $parent['id']) ? 'selected' : '' ?>><?= esc($parent['nama_unit_kerja']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 btn btn-solid">
                        <i class="fas fa-filter mr-2 text-white/80"></i> Filter
                    </button>
                    <a href="<?= site_url('unit_kerja/manage') ?>" class="btn btn-outline" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200 w-20">ID</th>
                        <th class="px-6 py-3 border-b border-slate-200">Nama Unit Kerja</th>
                        <th class="px-6 py-3 border-b border-slate-200">Induk</th>
                        <th class="px-6 py-3 border-b border-slate-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($unit_kerja_list)): ?>
                        <?php foreach ($unit_kerja_list as $unit): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-xs font-mono text-slate-700">#<?= str_pad($unit['id'], 3, '0', STR_PAD_LEFT) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-700 shrink-0">
                                            <i class="fas fa-building text-xs"></i>
                                        </div>
                                        <span class="font-medium text-slate-800 tracking-tight"><?= esc($unit['nama_unit_kerja']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if (!empty($unit['parent_name'])): ?>
                                        <span class="text-xs font-medium text-slate-700 tracking-tight"><?= esc($unit['parent_name']) ?></span>
                                    <?php else: ?>
                                        <span class="text-[10px] text-slate-700 uppercase font-bold">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php if (session()->get('role') === 'super_admin'): ?>
                                        <div class="flex justify-center gap-2">
                                            <a href="<?= site_url('unit_kerja/edit/' . $unit['id']) ?>" class="btn btn-table" title="Edit">
                                                <i class="fas fa-edit text-xs"></i>
                                            </a>
                                            <a href="<?= site_url('unit_kerja/delete/' . $unit['id']) ?>" class="btn btn-table" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-20 text-center">
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

        <?= view('components/pagination', ['items' => $unit_kerja_list, 'pager' => $pager, 'label' => 'unit kerja']) ?>
    </div>
</div>
<?= $this->endSection() ?>