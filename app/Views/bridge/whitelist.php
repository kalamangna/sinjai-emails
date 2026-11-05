<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight"><?= esc($title) ?></h1>
        </div>
        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
            <button type="button" onclick="openModal('modal-add-domain')" class="flex-1 sm:flex-none btn btn-solid text-xs px-3 py-2">
                <i class="fas fa-plus mr-1.5 text-white/80"></i> Tambah Domain
            </button>
            <?php if ($totalDomains > 0): ?>
                <form action="<?= site_url('bridge/whitelist/sync-all') ?>" method="POST" class="inline" onsubmit="handleFormSubmit(this, 'Menyinkronkan IP...')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline text-xs px-3 py-2">
                        <i class="fas fa-sync-alt mr-1.5 text-slate-600"></i> Sinkronkan Semua IP
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ringkasan Metrik -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
        <div class="bg-white border border-slate-200 border-l-4 border-l-slate-700 rounded-2xl shadow-sm p-4 sm:p-6">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Domain</p>
            <h3 class="text-xl sm:text-2xl font-bold text-slate-800 mt-1"><?= number_format($totalDomains, 0, ',', '.') ?></h3>
        </div>
        <div class="bg-white border border-slate-200 border-l-4 border-l-emerald-600 rounded-2xl shadow-sm p-4 sm:p-6">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Domain Aktif</p>
            <h3 class="text-xl sm:text-2xl font-bold text-emerald-700 mt-1"><?= number_format($activeDomains, 0, ',', '.') ?></h3>
        </div>
        <div class="bg-white border border-slate-200 border-l-4 border-l-slate-400 rounded-2xl shadow-sm p-4 sm:p-6">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Domain Nonaktif</p>
            <h3 class="text-xl sm:text-2xl font-bold text-slate-700 mt-1"><?= number_format($inactiveDomains, 0, ',', '.') ?></h3>
        </div>
        <div class="bg-white border border-slate-200 border-l-4 <?= $activeDomains > 0 ? 'border-l-emerald-600' : 'border-l-amber-500' ?> rounded-2xl shadow-sm p-4 sm:p-6">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Pengamanan Akses</p>
            <h3 class="text-xl sm:text-2xl font-bold <?= $activeDomains > 0 ? 'text-emerald-700' : 'text-amber-700' ?> mt-1">
                <?= $activeDomains > 0 ? 'Terkunci' : 'Terbuka' ?>
            </h3>
        </div>
    </div>

    <!-- Filter & Tabel Data -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

        <!-- Bilah Filter -->
        <div class="p-4 sm:p-6 border-b border-slate-100 bg-slate-50">
            <form method="GET" action="<?= site_url('bridge/whitelist') ?>" class="grid grid-cols-1 md:grid-cols-12 gap-y-4 gap-x-4 items-end">
                
                <!-- Pencarian -->
                <div class="md:col-span-6">
                    <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-widest">Cari Entri</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= esc($search) ?>" class="block w-full pl-9 pr-3 py-2 bg-white border <?= !empty($search) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm text-slate-800 placeholder-slate-400 font-medium transition-all" placeholder="desa-tongketongke.sinjaikab.go.id atau Diskominfo">
                    </div>
                </div>

                <!-- Status Aktif atau Nonaktif -->
                <div class="md:col-span-4">
                    <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-widest">Status Whitelist</label>
                    <select name="status" class="block w-full px-3 py-2 bg-white border <?= !empty($filterStatus) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm appearance-none cursor-pointer transition-all">
                        <option value="">Semua Status</option>
                        <option value="1" <?= $filterStatus === '1' ? 'selected' : '' ?>>Domain Aktif</option>
                        <option value="0" <?= $filterStatus === '0' ? 'selected' : '' ?>>Domain Nonaktif</option>
                    </select>
                </div>

                <!-- Tombol Aksi Kontrol Tabel -->
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 btn btn-solid text-xs">
                        <i class="fas fa-filter mr-1.5 text-white/80"></i> Filter
                    </button>
                    <a href="<?= site_url('bridge/whitelist') ?>" class="btn btn-outline text-xs px-3" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Data -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm min-w-[700px]">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[10px] font-bold tracking-widest">
                    <tr>
                        <th class="px-6 py-3 border-b border-slate-200">Instansi atau Aplikasi</th>
                        <th class="px-6 py-3 border-b border-slate-200">Nama Domain</th>
                        <th class="px-6 py-3 border-b border-slate-200">Alamat IP Server</th>
                        <th class="px-6 py-3 border-b border-slate-200">Pembaruan Terakhir</th>
                        <th class="px-6 py-3 border-b border-slate-200 text-center">Status</th>
                        <th class="px-6 py-3 border-b border-slate-200 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                        <i class="fas fa-shield-alt text-slate-300 text-lg"></i>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Belum ada domain whitelist terdaftar</span>
                                    <div class="mt-4">
                                        <button type="button" onclick="openModal('modal-add-domain')" class="btn btn-solid text-xs">
                                            <i class="fas fa-plus mr-1.5"></i> Tambah Domain
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold text-slate-800 uppercase tracking-tight block">
                                        <?= esc($item['label']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-mono font-medium text-xs text-slate-700">
                                    <?= esc($item['domain']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if (!empty($item['ip_address'])): ?>
                                        <div class="flex flex-wrap gap-1 max-w-sm">
                                            <?php foreach (array_map('trim', explode(',', $item['ip_address'])) as $singleIp): ?>
                                                <?php if (!empty($singleIp)): ?>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <?= esc($singleIp) ?>
                                                    </span>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                            Belum teresolusi
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-xs">
                                    <?= !empty($item['last_resolved_at']) ? date('d M Y H:i', strtotime($item['last_resolved_at'])) : 'Belum pernah' ?>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <form action="<?= site_url('bridge/whitelist/toggle/' . $item['id']) ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-widest transition-colors <?= $item['is_active'] ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>" title="Ubah Status">
                                            <?= $item['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <!-- Tombol Sinkronkan IP -->
                                        <form action="<?= site_url('bridge/whitelist/sync/' . $item['id']) ?>" method="POST" class="inline" onsubmit="handleFormSubmit(this, 'Menyinkronkan IP...')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline btn-xs !p-1.5 text-slate-600" title="Sinkronkan IP" aria-label="Sinkronkan IP">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </form>

                                        <!-- Tombol Ubah Data -->
                                        <button type="button" 
                                                onclick="openEditModal(<?= esc(json_encode($item)) ?>)" 
                                                class="btn btn-outline btn-xs !p-1.5 text-slate-600" 
                                                title="Ubah Data" 
                                                aria-label="Ubah Data">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- Tombol Hapus Domain -->
                                        <form action="<?= site_url('bridge/whitelist/delete/' . $item['id']) ?>" method="POST" class="inline" onsubmit="return confirm('Hapus domain <?= esc($item['domain']) ?>? Akses API bridge dari server terkait akan ditolak sistem.');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline btn-xs !p-1.5 text-rose-600 border-rose-200 hover:bg-rose-50" title="Hapus Domain" aria-label="Hapus Domain">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginasi Menggunakan Komponen Standar -->
        <?= view('components/pagination', ['items' => $items, 'pager' => $pager, 'label' => 'Domain']) ?>
    </div>

</div>

<!-- Modal Tambah Domain -->
<?php
$addContent = '
<form id="form-add-domain" action="' . site_url('bridge/whitelist/store') . '" method="POST" onsubmit="handleFormSubmit(this, \'Menyimpan...\')">
    ' . csrf_field() . '
    <div class="space-y-4">
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Instansi atau Aplikasi</label>
            <input type="text" name="label" required maxlength="255" class="block w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm text-slate-800 placeholder-slate-400 font-medium transition-all" placeholder="Pemerintah Desa Tongke-tongke">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Domain</label>
            <input type="text" name="domain" maxlength="255" class="block w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm text-slate-800 placeholder-slate-400 font-mono transition-all" placeholder="tongketongke.desa.id">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat IP Server</label>
            <input type="text" name="ip_address" maxlength="255" class="block w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm text-slate-800 placeholder-slate-400 font-mono transition-all" placeholder="165.101.18.95">
            <p class="text-[11px] text-slate-500 mt-1.5">Dapat diisi manual jika server menggunakan Cloudflare atau memiliki IP berbeda dari DNS publik. Pisahkan dengan tanda koma jika lebih dari satu IP. Kosongkan untuk resolusi otomatis dari nama domain.</p>
        </div>
    </div>
</form>
';

$addFooter = '
<button type="button" onclick="closeModal(\'modal-add-domain\')" class="btn btn-outline text-xs">Batal</button>
<button type="submit" form="form-add-domain" class="btn btn-solid text-xs">Simpan Domain</button>
';

echo view('components/modal', [
    'id'         => 'modal-add-domain',
    'modalTitle' => 'Tambah Domain Whitelist',
    'size'       => 'md',
    'content'    => $addContent,
    'footer'     => $addFooter,
]);
?>

<!-- Modal Ubah Domain -->
<?php
$editContent = '
<form id="form-edit-domain" action="" method="POST" onsubmit="handleFormSubmit(this, \'Menyimpan Perubahan...\')">
    ' . csrf_field() . '
    <div class="space-y-4">
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Instansi atau Aplikasi</label>
            <input type="text" id="edit-label" name="label" required maxlength="255" class="block w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm text-slate-800 placeholder-slate-400 font-medium transition-all">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Domain</label>
            <input type="text" id="edit-domain" name="domain" maxlength="255" class="block w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm text-slate-800 placeholder-slate-400 font-mono transition-all">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat IP Server</label>
            <input type="text" id="edit-ip-address" name="ip_address" maxlength="255" class="block w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm text-slate-800 placeholder-slate-400 font-mono transition-all" placeholder="165.101.18.95">
            <p class="text-[11px] text-slate-500 mt-1.5">Alamat IP server yang diizinkan mengakses API bridge. Pisahkan dengan tanda koma jika lebih dari satu IP.</p>
        </div>
        <div class="pt-2">
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" id="edit-is-active" name="is_active" value="1" class="w-4 h-4 text-slate-700 border-slate-300 rounded focus:ring-slate-700">
                <span class="text-xs font-semibold text-slate-700">Aktifkan Pengamanan Whitelist untuk Domain Ini</span>
            </label>
        </div>
    </div>
</form>
';

$editFooter = '
<button type="button" onclick="closeModal(\'modal-edit-domain\')" class="btn btn-outline text-xs">Batal</button>
<button type="submit" form="form-edit-domain" class="btn btn-solid text-xs">Simpan Perubahan</button>
';

echo view('components/modal', [
    'id'         => 'modal-edit-domain',
    'modalTitle' => 'Ubah Data Domain',
    'size'       => 'md',
    'content'    => $editContent,
    'footer'     => $editFooter,
]);
?>

<script>
function openEditModal(data) {
    const form = document.getElementById('form-edit-domain');
    form.action = '<?= site_url('bridge/whitelist/update') ?>/' + data.id;

    document.getElementById('edit-label').value = data.label || '';
    document.getElementById('edit-domain').value = data.domain || '';
    document.getElementById('edit-ip-address').value = data.ip_address || '';
    document.getElementById('edit-is-active').checked = parseInt(data.is_active) === 1;

    openModal('modal-edit-domain');
}

function handleFormSubmit(form, loadingText) {
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
        const originalHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i> ' + loadingText;
    }
}
</script>
<?= $this->endSection() ?>
