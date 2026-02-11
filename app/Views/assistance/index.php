<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-10">
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <h2 class="text-3xl font-black text-slate-100 uppercase tracking-tighter">Log Pendampingan Teknis</h2>
        <div class="flex flex-wrap gap-3">
            <a href="<?= site_url('assistance/export_pdf') . '?' . $_SERVER['QUERY_STRING'] ?>" class="inline-flex items-center px-5 py-2.5 bg-red-600 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-red-700 transition-all shadow-lg shadow-red-900/20 no-underline" target="_blank">
                <i class="fas fa-file-pdf mr-2"></i> Ekspor PDF
            </a>
            <a href="<?= site_url('assistance/create') ?>" class="inline-flex items-center px-5 py-2.5 bg-blue-600 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-900/20 no-underline">
                <i class="fas fa-plus mr-2"></i> Tambah Log
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden p-8">
        <form method="GET" action="<?= site_url('assistance') ?>" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
            <div class="md:col-span-3">
                <label for="year" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Tahun</label>
                <select name="year" id="year" class="block w-full px-5 py-3 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all" onchange="this.form.submit()">
                    <option value="">SEMUA TAHUN</option>
                    <?php foreach ($yearOptions as $year): ?>
                        <option value="<?= $year ?>" <?= ($filterYear == $year) ? 'selected' : '' ?>><?= $year ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-3">
                <label for="month" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Bulan</label>
                <select name="month" id="month" class="block w-full px-5 py-3 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all" onchange="this.form.submit()">
                    <option value="">SEMUA BULAN</option>
                    <?php foreach ($monthNames as $num => $name): ?>
                        <option value="<?= $num ?>" <?= ($filterMonth == $num) ? 'selected' : '' ?>><?= esc(strtoupper($name)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-4">
                <label for="category" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Kategori Layanan</label>
                <select name="category" id="category" class="block w-full px-5 py-3 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all" onchange="this.form.submit()">
                    <option value="">SEMUA KATEGORI</option>
                    <?php foreach ($categoryMap as $id => $label): ?>
                        <option value="<?= $id ?>" <?= ($filterCategory == $id) ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-2">
                <a href="<?= site_url('assistance') ?>" class="block w-full py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-black border border-transparent rounded-2xl shadow-sm transition-all text-[10px] text-center no-underline uppercase tracking-widest">
                    <i class="fas fa-sync-alt mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="bg-green-500/10 border border-green-500/20 text-green-400 px-6 py-4 rounded-2xl flex items-center shadow-2xl" role="alert">
            <i class="fas fa-check-circle mr-4 text-xl"></i>
            <span class="font-bold text-sm uppercase tracking-widest"><?= session()->getFlashdata('message') ?></span>
        </div>
    <?php endif; ?>

    <!-- Table Section -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800/50">
                <thead class="bg-slate-950/30">
                    <tr>
                        <th class="px-10 py-8 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] w-12">No</th>
                        <th class="px-10 py-8 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Tanggal</th>
                        <th class="px-10 py-8 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Unit Kerja / Agency</th>
                        <th class="px-10 py-8 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Kategori & Metode</th>
                        <th class="px-10 py-8 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Detail Layanan</th>
                        <th class="px-10 py-8 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50 bg-slate-900/20">
                    <?php if (!empty($activities)): ?>
                        <?php foreach ($activities as $key => $item): ?>
                            <tr class="hover:bg-slate-800/30 transition-colors group">
                                <td class="px-10 py-8 whitespace-nowrap align-middle text-xs font-black text-slate-600 font-mono tracking-tighter">#<?= $key + 1 ?></td>
                                <td class="px-10 py-8 whitespace-nowrap align-middle">
                                    <div class="text-sm font-black text-slate-300 tracking-tight leading-none"><?= date('d M Y', strtotime($item['tanggal_kegiatan'])) ?></div>
                                </td>
                                <td class="px-10 py-8 align-middle">
                                    <div class="text-base font-bold text-slate-100 uppercase tracking-tight leading-snug mb-1"><?= esc($item['agency_name']) ?></div>
                                    <div class="text-[9px] text-slate-500 font-black uppercase tracking-[0.2em] opacity-70"><?= esc($item['agency_type']) ?></div>
                                </td>
                                <td class="px-10 py-8 align-middle">
                                    <div class="text-[10px] font-black text-blue-400/80 uppercase tracking-widest mb-2.5"><?= esc($categoryMap[$item['category']] ?? 'Unknown') ?></div>
                                    <?php
                                        $method = $item['method'];
                                        $mClass = ($method == 'Online') ? 'bg-indigo-500/5 text-indigo-400 border-indigo-500/20' : 'bg-amber-500/5 text-amber-400 border-amber-500/20';
                                    ?>
                                    <span class="inline-flex items-center px-3.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border transition-all <?= $mClass ?>"><?= esc($method) ?></span>
                                </td>
                                <td class="px-10 py-8 align-middle">
                                    <?php
                                    $services = json_decode($item['services'], true);
                                    if (!empty($services)): ?>
                                        <div class="flex flex-wrap gap-2 mb-2.5">
                                            <?php foreach ($services as $svc): ?>
                                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest bg-slate-950/50 px-2.5 py-1 rounded-lg border border-slate-800/50"><?= esc($svc) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-medium italic leading-relaxed line-clamp-2 opacity-80"><?= esc($item['keterangan']) ?></div>
                                    <?php else: ?>
                                        <span class="text-slate-700">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-10 py-8 whitespace-nowrap text-center align-middle">
                                    <div class="flex justify-center space-x-4">
                                        <a href="<?= site_url('assistance/edit/' . $item['id']) ?>" class="w-10 h-10 flex items-center justify-center bg-slate-950/50 text-slate-500 border border-slate-800/50 rounded-xl hover:bg-blue-600 hover:text-white hover:border-transparent transition-all no-underline shadow-sm">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <a href="<?= site_url('assistance/delete/' . $item['id']) ?>" class="w-10 h-10 flex items-center justify-center bg-slate-950/50 text-slate-500 border border-slate-800/50 rounded-xl hover:bg-rose-600 hover:text-white hover:border-transparent transition-all no-underline shadow-sm" onclick="return confirm('Hapus log kegiatan ini?')">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-10 py-24 text-center align-middle">
                                <div class="w-20 h-20 bg-slate-900 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-slate-800 shadow-inner">
                                    <i class="fas fa-history text-4xl text-slate-700"></i>
                                </div>
                                <h5 class="text-sm font-black text-slate-500 uppercase tracking-widest">Belum ada data ditemukan</h5>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>