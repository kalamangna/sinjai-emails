<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center">
                <i class="fas fa-building mr-2 text-blue-500 opacity-50"></i>Unit Kerja
            </h5>
            <div class="flex flex-wrap gap-2">

                <a href="<?= site_url('unit_kerja/add') ?>" class="inline-flex items-center justify-center px-3 py-2 bg-blue-600 border border-transparent rounded-lg font-bold text-[10px] text-white uppercase tracking-wider hover:bg-blue-700 active:bg-blue-800 transition-all shadow-sm no-underline">
                    <i class="fas fa-plus mr-1.5"></i> Tambah
                </a>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Search -->
            <form method="GET" action="" class="mb-8">
                <div class="flex flex-col sm:flex-row gap-2 max-w-xl">
                    <div class="flex-grow relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= isset($search) ? esc($search) : '' ?>" class="block w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all" placeholder="Cari unit kerja...">
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white font-bold py-2 px-4 rounded-lg text-[11px] uppercase tracking-wider shadow-sm transition-all focus:outline-none">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="<?= site_url('unit_kerja/manage') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-lg font-bold text-[11px] text-slate-600 uppercase tracking-wider hover:bg-slate-50 hover:text-slate-900 active:bg-slate-100 transition-all shadow-sm no-underline" title="Reset Filter">
                        <i class="fas fa-redo mr-2"></i> Reset
                    </a>
                </div>
            </form>

            <!-- Table -->
            <?php if (!empty($unit_kerja_list)): ?>
                <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest w-20">ID</th>
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nama Unit Kerja</th>
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Induk / Parent</th>
                                <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php foreach ($unit_kerja_list as $unit): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-slate-400 font-mono">#<?= $unit['id'] ?></td>
                                    <td class="px-6 py-4">
                                        <div class="text-[13px] font-bold text-slate-900 uppercase leading-tight group-hover:text-blue-600 transition-colors"><?= esc($unit['nama_unit_kerja']) ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-[11px] font-semibold <?= !empty($unit['parent_name']) ? 'text-blue-600' : 'text-slate-300 italic' ?> uppercase tracking-tight">
                                            <?= !empty($unit['parent_name']) ? esc($unit['parent_name']) : 'Tidak Ada' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex justify-center space-x-2">
                                            <a href="<?= site_url('unit_kerja/edit/' . $unit['id']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 transition-all no-underline shadow-sm" title="Edit">
                                                <i class="fas fa-edit text-xs"></i>
                                            </a>
                                            <a href="<?= site_url('unit_kerja/delete/' . $unit['id']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-300 hover:bg-rose-50 transition-all no-underline shadow-sm" onclick="return confirm('Hapus unit kerja ini?')" title="Hapus">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-20 bg-slate-50/50 rounded-xl border border-slate-200 border-dashed">
                    <div class="w-16 h-16 bg-white border border-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm text-slate-200">
                        <i class="fas fa-building text-2xl"></i>
                    </div>
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Belum ada data unit kerja</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>