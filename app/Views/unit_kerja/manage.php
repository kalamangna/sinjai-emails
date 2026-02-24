<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-10">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-emerald-50 rounded-full blur-3xl opacity-50"></div>
        
        <div class="relative z-10 flex items-center gap-5">
            <div class="w-14 h-14 bg-emerald-600 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-200">
                <i class="fas fa-cog text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight uppercase">Settings</h1>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1 flex items-center">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2 animate-pulse"></span> Struktur Organisasi
                </p>
            </div>
        </div>
        
        <?php if (session()->get('role') === 'super_admin'): ?>
        <div class="relative z-10 flex items-center gap-3">
            <a href="<?= site_url('unit_kerja/add') ?>" class="inline-flex items-center justify-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black text-xs uppercase tracking-widest transition-all shadow-md no-underline group">
                <i class="fas fa-plus mr-2"></i> Tambah
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-[2.5rem] shadow-sm overflow-hidden flex flex-col">
        <div class="p-8 bg-slate-50/50 border-b border-slate-100">
            <form method="GET" action="" class="flex flex-col lg:flex-row gap-4 items-end max-w-4xl">
                <div class="flex-grow w-full">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 ml-1">Cari Unit</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                            <i class="fas fa-search text-sm"></i>
                        </span>
                        <input type="text" name="search" value="<?= isset($search) ? esc($search) : '' ?>" class="block w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-semibold text-slate-700 transition-all placeholder:text-slate-400 shadow-inner" placeholder="Cari...">
                    </div>
                </div>
                <div class="flex gap-2 w-full lg:w-auto">
                    <button type="submit" class="flex-1 lg:flex-none inline-flex items-center justify-center h-[46px] px-8 bg-slate-900 hover:bg-emerald-600 text-white rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all shadow-md">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="<?= site_url('unit_kerja/manage') ?>" class="inline-flex items-center justify-center w-[46px] h-[46px] bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 rounded-xl transition-all shadow-sm no-underline" title="Reset">
                        <i class="fas fa-redo text-xs"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-50/80">
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest w-24">ID</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Unit Kerja</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Induk</th>
                        <th class="px-8 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 bg-white">
                    <?php if (!empty($unit_kerja_list)): ?>
                        <?php foreach ($unit_kerja_list as $unit): ?>
                            <tr class="hover:bg-emerald-50/30 transition-all group">
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <span class="text-xs font-black text-slate-300 font-mono group-hover:text-emerald-400 transition-colors">#<?= str_pad($unit['id'], 3, '0', STR_PAD_LEFT) ?></span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 group-hover:bg-white border border-slate-200 flex items-center justify-center transition-colors">
                                            <i class="fas fa-building text-slate-400 group-hover:text-emerald-600 text-sm"></i>
                                        </div>
                                        <div class="text-[13px] font-black text-slate-900 uppercase group-hover:text-emerald-700 transition-colors tracking-tight">
                                            <?= esc($unit['nama_unit_kerja']) ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <?php if (!empty($unit['parent_name'])): ?>
                                        <span class="text-[11px] font-bold text-slate-600 uppercase tracking-tight"><?= esc($unit['parent_name']) ?></span>
                                    <?php else: ?>
                                        <span class="text-[10px] font-black text-slate-300 uppercase italic">Root</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-center">
                                    <?php if (session()->get('role') === 'super_admin'): ?>
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= site_url('unit_kerja/edit/' . $unit['id']) ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 transition-all no-underline shadow-sm" title="Edit">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <a href="<?= site_url('unit_kerja/delete/' . $unit['id']) ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-rose-600 transition-all no-underline shadow-sm" onclick="return confirm('Hapus?')" title="Hapus">
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
                            <td colspan="4" class="px-8 py-24 text-center text-slate-400 uppercase text-xs font-bold italic tracking-widest">Kosong</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>