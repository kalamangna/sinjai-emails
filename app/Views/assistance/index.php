<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <h1 class="text-2xl font-semibold text-gray-900">Log Layanan</h1>
        
        <?php if (session()->get('role') === 'super_admin'): ?>
        <div class="flex items-center gap-2 w-full lg:w-auto">
            <a href="<?= site_url('assistance/create') ?>" class="flex-1 lg:flex-none inline-flex items-center justify-center px-4 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-lg font-bold text-xs uppercase tracking-widest transition-all shadow-sm no-underline">
                <i class="fas fa-plus mr-2"></i> Tambah
            </a>
            <a href="<?= site_url('assistance/export_pdf') ?>" class="flex-1 lg:flex-none inline-flex items-center justify-center px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg font-bold text-xs uppercase tracking-widest transition-all shadow-sm no-underline">
                <i class="fas fa-file-pdf mr-2 text-red-600"></i> Unduh PDF
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <select name="category" class="block w-full px-3 py-2 bg-white border <?= !empty($filterCategory) ? 'border-gray-900 ring-1 ring-gray-900' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm appearance-none cursor-pointer">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categoryMap as $id => $label): ?>
                            <option value="<?= $id ?>" <?= $filterCategory == $id ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                    <select name="month" class="block w-full px-3 py-2 bg-white border <?= !empty($filterMonth) ? 'border-gray-900 ring-1 ring-gray-900' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm appearance-none cursor-pointer">
                        <option value="">Semua Bulan</option>
                        <?php foreach ($monthNames as $id => $name): ?>
                            <option value="<?= $id ?>" <?= $filterMonth == $id ? 'selected' : '' ?>><?= esc($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                    <select name="year" class="block w-full px-3 py-2 bg-white border <?= !empty($filterYear) ? 'border-gray-900 ring-1 ring-gray-900' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm appearance-none cursor-pointer">
                        <?php foreach ($yearOptions as $year): ?>
                            <option value="<?= $year ?>" <?= $filterYear == $year ? 'selected' : '' ?>><?= $year ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-lg font-bold text-xs uppercase tracking-widest transition-all">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="<?= site_url('assistance') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg transition-all shadow-sm" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-3 border-b border-gray-200">Tanggal</th>
                        <th class="px-6 py-3 border-b border-gray-200">Unit Kerja</th>
                        <th class="px-6 py-3 border-b border-gray-200">Layanan</th>
                        <th class="px-6 py-3 border-b border-gray-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (!empty($activities)): ?>
                        <?php foreach ($activities as $activity): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-medium text-gray-900"><?= date('d/m/Y', strtotime($activity['tanggal_kegiatan'])) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900 uppercase tracking-tight"><?= esc($activity['agency_name']) ?></span>
                                        <span class="text-[10px] text-gray-500 uppercase font-bold tracking-widest mt-0.5"><?= esc($activity['method']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-700 uppercase text-xs tracking-tight">
                                            <?= is_array($activity['services']) ? implode(', ', $activity['services']) : esc($activity['services']) ?>
                                        </span>
                                        <span class="text-[10px] text-gray-400 uppercase font-medium mt-0.5">
                                            <?= esc($activity['category_label'] ?? 'Tidak Diketahui') ?> &mdash; <?= esc($activity['keterangan']) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php if (session()->get('role') === 'super_admin'): ?>
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= site_url('assistance/edit/' . $activity['id']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-300 text-gray-400 hover:text-gray-900 shadow-sm transition-all" title="Edit">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <a href="<?= site_url('assistance/delete/' . $activity['id']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-300 text-gray-400 hover:text-red-600 shadow-sm transition-all" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </a>
                                    </div>
                                    <?php else: ?>
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest italic">Hanya Lihat</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">Data tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (isset($pager)): ?>
        <div class="px-6 py-4 border-t border-gray-100">
            <?= $pager->links() ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
