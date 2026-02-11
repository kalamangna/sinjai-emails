<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-10">
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
        <div class="bg-slate-800/30 px-10 py-8 border-b border-slate-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <h5 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                <i class="fas fa-building mr-3 text-blue-500 opacity-50"></i>Kelola Data Unit Kerja
            </h5>
            <div class="flex flex-wrap gap-3">
                <a href="<?= site_url('unit_kerja/batch_create') ?>" class="inline-flex items-center px-5 py-2.5 bg-green-600 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-green-700 transition-all shadow-lg shadow-green-900/20 no-underline">
                    <i class="fas fa-file-csv mr-2"></i> Buat Massal
                </a>
                <a href="<?= site_url('unit_kerja/add') ?>" class="inline-flex items-center px-5 py-2.5 bg-blue-600 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-900/20 no-underline">
                    <i class="fas fa-plus mr-2"></i> Tambah Unit Kerja
                </a>
            </div>
        </div>
        <div class="p-10">
            <!-- Search Form -->
            <form method="GET" action="" class="mb-10">
                <div class="flex flex-col md:flex-row gap-4 max-w-2xl">
                    <div class="flex-grow relative">
                        <span class="absolute inset-y-0 left-0 pl-5 flex items-center text-slate-600">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" class="block w-full pl-12 pr-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all uppercase tracking-tight placeholder-slate-800" name="search" placeholder="CARI NAMA ATAU INDUK..." value="<?= isset($search) ? esc($search) : '' ?>">
                    </div>
                    <button type="submit" class="px-6 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-2xl shadow-xl shadow-blue-900/20 transition-all text-[10px] uppercase tracking-widest flex items-center justify-center">
                        Cari
                    </button>
                    <a href="<?= site_url('unit_kerja/manage') ?>" class="px-6 py-3.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-black border border-transparent rounded-2xl shadow-sm transition-all text-[10px] uppercase tracking-widest no-underline flex items-center justify-center text-center">
                        Reset
                    </a>
                </div>
            </form>

            <!-- Unit Kerja List -->
            <?php if (!empty($unit_kerja_list)): ?>
                <div class="overflow-x-auto rounded-3xl border border-slate-800">
                    <table class="min-w-full divide-y divide-slate-800">
                        <thead class="bg-slate-950/50">
                            <tr>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] w-24">ID</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Nama Unit Kerja</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Induk (Parent)</th>
                                <th class="px-8 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] w-40">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 bg-slate-900/30">
                            <?php foreach ($unit_kerja_list as $unit): ?>
                                <tr class="hover:bg-slate-800/30 transition-colors group">
                                    <td class="px-8 py-5 whitespace-nowrap text-xs font-black text-slate-600 font-mono">#<?= $unit['id'] ?></td>
                                    <td class="px-8 py-5 align-middle">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-center mr-4 group-hover:border-blue-500/30 transition-all">
                                                <i class="fas fa-building text-slate-600 group-hover:text-blue-500 text-sm"></i>
                                            </div>
                                            <div class="text-sm font-bold text-slate-300 uppercase tracking-tight group-hover:text-slate-100 transition-colors"><?= esc($unit['nama_unit_kerja']) ?></div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap text-xs font-bold text-slate-500 uppercase tracking-tight">
                                        <?= !empty($unit['parent_name']) ? '<span class="text-blue-400">' . esc($unit['parent_name']) . '</span>' : '<span class="text-slate-700 italic">TIDAK ADA</span>' ?>
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap text-center text-sm font-medium align-middle">
                                        <div class="flex justify-center space-x-3">
                                            <a href="<?= site_url('unit_kerja/edit/' . $unit['id']) ?>" class="w-10 h-10 flex items-center justify-center bg-slate-950 text-slate-400 border border-slate-800 rounded-xl hover:bg-blue-600 hover:text-white hover:border-transparent transition-all shadow-sm" title="Edit">
                                                <i class="fas fa-pencil-alt text-xs"></i>
                                            </a>
                                            <a href="<?= site_url('unit_kerja/delete/' . $unit['id']) ?>" class="w-10 h-10 flex items-center justify-center bg-slate-950 text-slate-400 border border-slate-800 rounded-xl hover:bg-red-600 hover:text-white hover:border-transparent transition-all shadow-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus unit kerja ini?');" title="Hapus">
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
                <div class="text-center py-20 bg-slate-950/30 rounded-[2rem] border border-slate-800 border-dashed">
                    <div class="w-20 h-20 bg-slate-900 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-slate-800">
                        <i class="fas fa-building text-4xl text-slate-700"></i>
                    </div>
                    <h5 class="text-sm font-black text-slate-500 uppercase tracking-widest">Belum ada data unit kerja.</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>