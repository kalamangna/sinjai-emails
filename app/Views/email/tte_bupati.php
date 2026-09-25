<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .choices {
        margin-bottom: 0 !important;
        width: 100% !important;
    }
    .choices.is-open {
        z-index: 50 !important;
    }
    .choices__inner {
        min-height: 38px !important;
        padding: 4px 8px !important;
        border-radius: 0.5rem !important;
        display: flex;
        align-items: center;
    }
    .choices__list--single {
        padding: 0 !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        color: #1e293b !important;
    }
    .choices__list--dropdown {
        z-index: 50 !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
    }
    .choices__list--dropdown .choices__item {
        white-space: normal !important;
        word-break: normal !important;
        line-height: 1.4 !important;
        padding-top: 8px !important;
        padding-bottom: 8px !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight"><?= $title; ?></h1>
            <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest mt-1">
                Total: <span class="text-slate-800"><?= number_format($total_count, 0, ',', '.'); ?></span> Dokumen
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase bg-blue-50 text-blue-700 border border-blue-200">
                <i class="fas fa-clock mr-1.5"></i> <?= number_format($pending_count, 0, ',', '.') ?> Menunggu TTE
            </span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                <i class="fas fa-check-double mr-1.5"></i> <?= number_format($completed_total_count, 0, ',', '.') ?> Lengkap
            </span>
        </div>
    </div>

    <!-- Tabs Navigasi (Pending / Selesai) -->
    <?php $route = $base_route ?? 'tte-bupati'; ?>
    <div class="flex border-b border-slate-200 text-sm font-medium">
        <a href="<?= site_url($route . '?tab=pending' . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($unit_kerja_id) ? '&unit_kerja_id=' . $unit_kerja_id : '')) ?>" 
           class="py-3 px-6 border-b-2 font-bold text-xs uppercase tracking-wider transition-all <?= ($tab !== 'completed') ? 'border-slate-800 text-slate-900 bg-slate-50/50' : 'border-transparent text-slate-400 hover:text-slate-600' ?>">
            <i class="fas fa-clock mr-1.5"></i> Menunggu TTE (<?= number_format($pending_count, 0, ',', '.') ?>)
        </a>
        <a href="<?= site_url($route . '?tab=completed' . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($unit_kerja_id) ? '&unit_kerja_id=' . $unit_kerja_id : '')) ?>" 
           class="py-3 px-6 border-b-2 font-bold text-xs uppercase tracking-wider transition-all <?= ($tab === 'completed') ? 'border-slate-800 text-slate-900 bg-slate-50/50' : 'border-transparent text-slate-400 hover:text-slate-600' ?>">
            <i class="fas fa-check-double mr-1.5"></i> Lengkap (<?= number_format($completed_total_count, 0, ',', '.') ?>)
        </a>
    </div>

    <!-- Filter & Pencarian -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="p-4 sm:p-6 border-b border-slate-100 bg-slate-50 relative z-20 rounded-t-2xl">
            <form method="GET" action="<?= site_url($route) ?>" class="grid grid-cols-1 md:grid-cols-12 gap-y-4 gap-x-4 items-end">
                <input type="hidden" name="tab" value="<?= esc($tab) ?>">
                
                <div class="md:col-span-5">
                    <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-widest">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= esc($search ?? '') ?>" autocomplete="off" class="block w-full pl-9 pr-3 py-2 bg-white border <?= !empty($search) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm transition-all placeholder-slate-400" placeholder="Cari nama, NIP, no PK...">
                    </div>
                </div>

                <div class="md:col-span-5">
                    <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-widest">Unit Kerja</label>
                    <select name="unit_kerja_id" id="filter-unit-kerja" class="choices-search block w-full px-3 py-2 bg-white border <?= !empty($unit_kerja_id) ? 'border-slate-800 ring-1 ring-slate-800' : 'border-slate-200' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-slate-700 text-sm cursor-pointer transition-all" data-search-placeholder="Cari Unit Kerja...">
                        <option value="">Semua Unit Kerja</option>
                        <?php foreach ($unit_kerja_list as $uk): ?>
                            <option value="<?= $uk['id'] ?>" <?= ($unit_kerja_id == $uk['id']) ? 'selected' : '' ?>><?= esc($uk['nama_unit_kerja']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 btn btn-solid text-xs">
                        <i class="fas fa-filter mr-1.5 text-white/80"></i> Filter
                    </button>
                    <a href="<?= site_url($route . '?tab=' . esc($tab)) ?>" class="btn btn-outline text-xs px-3" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        <?php if ($tab !== 'completed'): ?>
            <!-- Batch Action Bar -->
            <div class="px-6 py-3 bg-slate-100/70 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" id="check-all-pk" class="rounded border-slate-300 text-slate-800 focus:ring-slate-700 w-4 h-4">
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-tight">Pilih Semua</span>
                    </label>
                    <span id="selected-badge" class="hidden px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-800">
                        <span id="selected-count">0</span> Terpilih
                    </span>
                </div>

                <button type="button" id="btn-batch-sign" onclick="openBatchModal()" disabled class="btn btn-solid btn-sm bg-slate-900 hover:bg-slate-800 text-white disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-signature mr-1.5"></i> TTE Massal
                </button>
            </div>
        <?php endif; ?>

        <!-- Tabel Dokumen PK -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[10px] font-bold tracking-widest">
                    <tr>
                        <?php if ($tab !== 'completed'): ?>
                            <th class="px-4 py-3 border-b border-slate-200 w-10 text-center">#</th>
                        <?php endif; ?>
                        <th class="px-6 py-3 border-b border-slate-200 w-44 whitespace-nowrap">No. PK</th>
                        <th class="px-6 py-3 border-b border-slate-200 min-w-[200px]">Pegawai PPPK</th>
                        <th class="px-6 py-3 border-b border-slate-200 min-w-[220px]">Jabatan / Unit Kerja</th>
                        <th class="px-6 py-3 border-b border-slate-200 w-36 whitespace-nowrap">Status Dokumen</th>
                        <th class="px-6 py-3 border-b border-slate-200 w-32 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $item): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <?php if ($tab !== 'completed'): ?>
                                    <td class="px-4 py-4 text-center">
                                        <input type="checkbox" name="selected_pk[]" value="<?= $item['id'] ?>" class="pk-checkbox rounded border-slate-300 text-slate-800 focus:ring-slate-700 w-4 h-4">
                                    </td>
                                <?php endif; ?>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 block w-max">
                                        <?= esc($item['nomor'] ?: '-') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <a href="<?= site_url('email/detail/' . $item['user']) ?>" class="font-bold text-slate-800 uppercase tracking-tight leading-tight hover:underline">
                                            <?= esc($item['name']) ?>
                                        </a>
                                        <span class="text-[10px] font-bold text-slate-500 font-mono mt-0.5">
                                            NIP: <?= esc($item['nip'] ?: '-') ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tight mb-1"><?= esc($item['jabatan'] ?: '-') ?></span>
                                        <?php if (!empty($item['parent_unit_kerja_name'])): ?>
                                            <span class="text-[10px] font-bold text-slate-700 uppercase leading-none"><?= esc($item['parent_unit_kerja_name']) ?></span>
                                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight mt-0.5"><?= esc($item['unit_kerja_name']) ?></span>
                                        <?php else: ?>
                                            <span class="text-xs font-bold text-slate-800 uppercase tracking-tight"><?= esc($item['unit_kerja_name'] ?: '-') ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($item['tte_status'] === 'completed'): ?>
                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 block w-max">
                                            Lengkap
                                        </span>
                                    <?php else: ?>
                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase bg-blue-50 text-blue-700 border border-blue-200 block w-max">
                                            Menunggu TTE Bupati
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center items-center gap-2">
                                        <a href="<?= site_url('email/export_single_perjanjian_kerja_pdf/' . $item['user']) ?>" target="_blank" class="btn btn-table" title="Pratinjau PDF">
                                            <i class="fas fa-file-pdf text-xs text-rose-600"></i>
                                        </a>

                                        <?php if ($tab !== 'completed'): ?>
                                            <button type="button" onclick="openSingleSignModal(<?= $item['id'] ?>, '<?= esc($item['name'], 'js') ?>', '<?= esc($item['nomor'] ?: '-', 'js') ?>')" class="btn btn-table" title="TTE Dokumen">
                                                <i class="fas fa-signature text-xs text-slate-700"></i>
                                            </button>
                                        <?php else: ?>
                                            <a href="<?= site_url('email/detail/' . $item['user']) ?>" class="btn btn-table" title="Detail Akun">
                                                <i class="fas fa-eye text-xs text-slate-700"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= ($tab !== 'completed') ? '6' : '5' ?>" class="px-6 py-20 text-center">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">
                                    <?= ($tab === 'completed') ? 'Belum ada dokumen yang selesai ditandatangani' : 'Tidak ada dokumen dalam antrean TTE Bupati' ?>
                                </span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php echo view('components/pagination', ['items' => $items, 'pager' => $pager, 'label' => 'Dokumen']); ?>
    </div>
</div>

<!-- Modal Single TTE Bupati -->
<?php
$singleModalContent = '
<form id="form-single-tte-bupati" method="POST" action="" class="space-y-4">
    ' . csrf_field() . '
    <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
        <p class="text-xs font-bold text-slate-800 uppercase tracking-tight" id="single-pegawai-name">-</p>
        <p class="text-[10px] font-mono text-slate-500 mt-0.5" id="single-pk-nomor">-</p>
    </div>

    <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-tight mb-1.5">NIK Penandatangan</label>
        <input type="text" name="nik" value="' . esc(env('BSRE_BUPATI_NIK', '')) . '" placeholder="16 digit NIK..." required maxlength="16" autocomplete="off" class="w-full text-xs font-mono font-semibold rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-800 focus:ring-0 transition-all p-2.5">
    </div>

    <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-tight mb-1.5">Passphrase BSrE</label>
        <div class="relative">
            <input type="password" id="single-passphrase" name="passphrase" required placeholder="Masukkan passphrase sertifikat elektronik..." autocomplete="new-password" class="w-full text-xs rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-800 focus:ring-0 transition-all p-2.5 pr-10">
            <button type="button" onclick="togglePasswordVisibility(\'single-passphrase\', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                <i class="fas fa-eye text-xs"></i>
            </button>
        </div>
    </div>

    <div class="pt-2 flex justify-end gap-2">
        <button type="button" onclick="closeModal(\'modal-single-tte\')" class="btn btn-outline btn-sm">Batal</button>
        <button type="submit" id="btn-submit-single-tte" class="btn btn-solid btn-sm">
            <i class="fas fa-signature mr-1.5"></i> Proses TTE
        </button>
    </div>
</form>';

echo view('components/modal', [
    'id'        => 'modal-single-tte',
    'title'     => 'Tanda Tangan Elektronik',
    'content'   => $singleModalContent,
    'size'      => 'sm',
    'showClose' => true,
], ['saveData' => false]);
?>

<!-- Modal Batch TTE Bupati -->
<?php
$batchModalContent = '
<div class="space-y-4">
    <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
        <p class="text-xs text-slate-600">Menandatangani <span id="batch-selected-count-label" class="font-bold text-slate-800">0</span> dokumen terpilih secara massal.</p>
    </div>

    <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-tight mb-1.5">NIK Penandatangan</label>
        <input type="text" id="batch-nik" value="' . esc(env('BSRE_BUPATI_NIK', '')) . '" placeholder="16 digit NIK..." required maxlength="16" autocomplete="off" class="w-full text-xs font-mono font-semibold rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-800 focus:ring-0 transition-all p-2.5">
    </div>

    <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-tight mb-1.5">Passphrase BSrE</label>
        <div class="relative">
            <input type="password" id="batch-passphrase" required placeholder="Masukkan passphrase sertifikat elektronik..." autocomplete="new-password" class="w-full text-xs rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-800 focus:ring-0 transition-all p-2.5 pr-10">
            <button type="button" onclick="togglePasswordVisibility(\'batch-passphrase\', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                <i class="fas fa-eye text-xs"></i>
            </button>
        </div>
    </div>

    <!-- Progress Container -->
    <div id="batch-progress-container" class="hidden space-y-2 pt-2 border-t border-slate-100">
        <div class="flex justify-between text-xs font-bold text-slate-700">
            <span id="batch-progress-status"><i class="fas fa-spinner fa-spin mr-1"></i> Sedang memproses...</span>
            <span id="batch-progress-text">0%</span>
        </div>
        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
            <div id="batch-progress-bar" class="bg-slate-900 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
        <p id="batch-progress-detail" class="text-[10px] text-slate-500 italic"></p>
    </div>

    <div class="pt-2 flex justify-end gap-2" id="batch-modal-buttons">
        <button type="button" onclick="closeModal(\'modal-batch-tte\')" class="btn btn-outline btn-sm">Batal</button>
        <button type="button" id="btn-submit-batch-tte" onclick="executeBatchTte()" class="btn btn-solid btn-sm">
            <i class="fas fa-signature mr-1.5"></i> Mulai TTE Massal
        </button>
    </div>
</div>';

echo view('components/modal', [
    'id'        => 'modal-batch-tte',
    'title'     => 'TTE Massal',
    'content'   => $batchModalContent,
    'size'      => 'sm',
    'showClose' => true,
], ['saveData' => false]);
?>

<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input && icon) {
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
}

function openSingleSignModal(pkId, pegawaiName, pkNomor) {
    document.getElementById('single-pegawai-name').textContent = pegawaiName;
    document.getElementById('single-pk-nomor').textContent = pkNomor;
    document.getElementById('form-single-tte-bupati').action = '<?= site_url('tte-pk/sign-single') ?>/' + pkId;
    document.getElementById('single-passphrase').value = '';
    openModal('modal-single-tte');
}

document.getElementById('form-single-tte-bupati')?.addEventListener('submit', function() {
    const btn = document.getElementById('btn-submit-single-tte');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i> Menghubungi BSrE...';
    }
});

// Batch Selection Logic
const checkAll = document.getElementById('check-all-pk');
const itemCheckboxes = document.querySelectorAll('.pk-checkbox');
const batchBtn = document.getElementById('btn-batch-sign');
const selectedBadge = document.getElementById('selected-badge');
const selectedCountLabel = document.getElementById('selected-count');

function updateBatchUI() {
    const checked = Array.from(itemCheckboxes).filter(cb => cb.checked);
    const count = checked.length;
    
    if (selectedCountLabel) selectedCountLabel.textContent = count;
    
    if (count > 0) {
        selectedBadge?.classList.remove('hidden');
        batchBtn?.removeAttribute('disabled');
    } else {
        selectedBadge?.classList.add('hidden');
        batchBtn?.setAttribute('disabled', 'true');
    }

    if (checkAll && itemCheckboxes.length > 0) {
        checkAll.checked = (count === itemCheckboxes.length);
    }
}

if (checkAll) {
    checkAll.addEventListener('change', function() {
        itemCheckboxes.forEach(cb => {
            cb.checked = checkAll.checked;
        });
        updateBatchUI();
    });
}

itemCheckboxes.forEach(cb => {
    cb.addEventListener('change', updateBatchUI);
});

function openBatchModal() {
    const checked = Array.from(itemCheckboxes).filter(cb => cb.checked);
    if (checked.length === 0) {
        alert('Silakan pilih minimal 1 dokumen untuk ditandatangani.');
        return;
    }

    document.getElementById('batch-selected-count-label').textContent = checked.length;
    document.getElementById('batch-passphrase').value = '';
    document.getElementById('batch-progress-container').classList.add('hidden');
    document.getElementById('btn-submit-batch-tte').disabled = false;
    document.getElementById('btn-submit-batch-tte').innerHTML = '<i class="fas fa-signature mr-1.5"></i> Mulai TTE Massal';
    openModal('modal-batch-tte');
}

async function executeBatchTte() {
    const nik = document.getElementById('batch-nik').value.trim();
    const passphrase = document.getElementById('batch-passphrase').value;
    const checked = Array.from(itemCheckboxes).filter(cb => cb.checked);
    const pkIds = checked.map(cb => parseInt(cb.value));

    if (!nik || !passphrase) {
        alert('NIK dan Passphrase BSrE wajib diisi.');
        return;
    }

    if (pkIds.length === 0) {
        alert('Tidak ada dokumen yang dipilih.');
        return;
    }

    const progressContainer = document.getElementById('batch-progress-container');
    const progressBar = document.getElementById('batch-progress-bar');
    const progressStatus = document.getElementById('batch-progress-status');
    const progressText = document.getElementById('batch-progress-text');
    const progressDetail = document.getElementById('batch-progress-detail');
    const submitBtn = document.getElementById('btn-submit-batch-tte');

    progressContainer.classList.remove('hidden');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i> Memproses TTE Massal...';
    progressBar.style.width = '30%';
    progressText.textContent = '30%';
    progressDetail.textContent = 'Mengirim ' + pkIds.length + ' dokumen ke BSrE Client Service...';

    try {
        const response = await fetch('<?= site_url('tte-pk/sign-batch') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                nik: nik,
                passphrase: passphrase,
                pk_ids: pkIds
            })
        });

        const res = await response.json();
        progressBar.style.width = '100%';
        progressText.textContent = '100%';

        if (res.success) {
            progressStatus.innerHTML = '<i class="fas fa-check-circle text-emerald-600 mr-1"></i> Selesai!';
            progressDetail.textContent = res.message;
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            progressStatus.innerHTML = '<i class="fas fa-exclamation-triangle text-rose-600 mr-1"></i> ' + (res.auth_aborted ? 'Autentikasi Gagal' : 'Gagal');
            progressDetail.textContent = res.message;
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-redo mr-1.5"></i> Coba Lagi';
        }
    } catch (err) {
        progressBar.style.width = '100%';
        progressStatus.innerHTML = '<i class="fas fa-times-circle text-rose-600 mr-1"></i> Terjadi Kesalahan';
        progressDetail.textContent = err.message || 'Gagal menghubungi server.';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-redo mr-1.5"></i> Coba Lagi';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const unitSelect = document.getElementById('filter-unit-kerja');
    if (unitSelect && typeof Choices !== 'undefined' && !unitSelect.closest('.choices')) {
        new Choices(unitSelect, {
            searchEnabled: true,
            itemSelectText: '',
            placeholder: true,
            searchPlaceholderValue: 'Cari Unit Kerja...',
            shouldSort: false,
            searchFields: ['label', 'value'],
            searchResultLimit: 100,
            noResultsText: 'Tidak ditemukan',
            noChoicesText: 'Tidak ada pilihan',
        });
    }
});
</script>
<?= $this->endSection() ?>
