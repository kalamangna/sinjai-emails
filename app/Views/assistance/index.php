<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Log Layanan</h1>
        
        <?php if (session()->get('role') === 'super_admin'): ?>
        <div class="flex items-center gap-2 w-full lg:w-auto">
            <a href="<?= site_url('assistance/create') ?>" class="flex-1 lg:flex-none inline-flex items-center justify-center px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-bold text-xs uppercase tracking-widest transition-all shadow-sm no-underline">
                <i class="fas fa-plus mr-2 text-white/80"></i> Tambah
            </a>
            <a href="<?= site_url('assistance/export_pdf') ?>" class="flex-1 lg:flex-none inline-flex items-center justify-center px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-lg font-bold text-xs uppercase tracking-widest transition-all shadow-sm no-underline">
                <i class="fas fa-file-pdf mr-2 text-red-600"></i> Unduh PDF
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Kategori</label>
                    <select name="category" class="block w-full px-3 py-2 bg-white border <?= !empty($filterCategory) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categoryMap as $id => $label): ?>
                            <option value="<?= $id ?>" <?= $filterCategory == $id ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Bulan</label>
                    <select name="month" class="block w-full px-3 py-2 bg-white border <?= !empty($filterMonth) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua Bulan</option>
                        <?php foreach ($monthNames as $id => $name): ?>
                            <option value="<?= $id ?>" <?= $filterMonth == $id ? 'selected' : '' ?>><?= esc($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-slate-700 mb-1 uppercase tracking-tight">Tahun</label>
                    <select name="year" class="block w-full px-3 py-2 bg-white border <?= !empty($filterYear) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm appearance-none cursor-pointer transition-all">
                        <?php foreach ($yearOptions as $year): ?>
                            <option value="<?= $year ?>" <?= $filterYear == $year ? 'selected' : '' ?>><?= $year ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-bold text-xs uppercase tracking-widest transition-all">
                        <i class="fas fa-filter mr-2 text-white/80"></i> Filter
                    </button>
                    <a href="<?= site_url('assistance') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-lg transition-all shadow-sm" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-700 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200">Tanggal</th>
                        <th class="px-6 py-3 border-b border-slate-200">Unit Kerja</th>
                        <th class="px-6 py-3 border-b border-slate-200">Layanan</th>
                        <th class="px-6 py-3 border-b border-slate-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($activities)): ?>
                        <?php foreach ($activities as $activity): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-medium text-slate-800"><?= formatSingkat($activity['tanggal_kegiatan']) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-800 uppercase tracking-tight"><?= esc($activity['agency_name']) ?></span>
                                        <span class="text-[10px] text-slate-700 uppercase font-bold tracking-widest mt-0.5"><?= esc($activity['method']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-700 uppercase text-xs tracking-tight">
                                            <?= is_array($activity['services']) ? implode(', ', $activity['services']) : esc($activity['services']) ?>
                                        </span>
                                        <span class="text-[10px] text-slate-700 uppercase font-medium mt-0.5">
                                            <?= esc($activity['category_label'] ?? 'Tidak Diketahui') ?> &mdash; <?= esc($activity['keterangan']) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php if (session()->get('role') === 'super_admin'): ?>
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= site_url('assistance/edit/' . $activity['id']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-700 hover:text-slate-800 shadow-sm transition-all" title="Edit">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <a href="<?= site_url('assistance/delete/' . $activity['id']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-700 hover:text-red-600 shadow-sm transition-all" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </a>
                                    </div>
                                    <?php else: ?>
                                        <span class="text-[10px] font-bold text-slate-700 uppercase tracking-widest italic">Hanya Lihat</span>
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
        
        <?php if (isset($pager)): ?>
        <div class="px-6 py-4 border-t border-slate-100">
            <?= $pager->links() ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>