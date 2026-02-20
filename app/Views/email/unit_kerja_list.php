<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="space-y-1">
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Perangkat Daerah</h2>
            <p class="text-slate-500 text-xs font-medium uppercase tracking-wider">Navigasi email berdasarkan struktur unit kerja</p>
        </div>
        <a href="<?= site_url('/') ?>" class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all shadow-sm no-underline group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> Kembali
        </a>
    </div>

    <!-- Search Section -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <div class="max-w-md relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                <i class="fas fa-search text-xs"></i>
            </span>
            <input type="text" id="unitSearch" class="block w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all" placeholder="Cari nama unit kerja...">
        </div>
    </div>

    <!-- Grid Layout -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="unitGrid">
        <?php foreach ($unit_kerja as $unit): ?>
            <a href="<?= site_url('email/unit_kerja/' . $unit['id']) ?>" class="group bg-white border border-slate-200 rounded-xl p-6 hover:border-blue-300 hover:shadow-md transition-all no-underline unit-card" data-name="<?= esc(strtoupper($unit['nama_unit_kerja'])) ?>">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 bg-slate-50 border border-slate-100 rounded-lg flex items-center justify-center group-hover:bg-blue-50 transition-colors">
                        <i class="fas fa-building text-slate-400 group-hover:text-blue-600 transition-colors"></i>
                    </div>
                    <span class="px-2 py-1 bg-slate-100 text-slate-500 rounded text-[9px] font-bold uppercase tracking-wider group-hover:bg-blue-50 group-hover:text-blue-600 transition-colors">
                        <?= $unit['email_count'] ?> Akun
                    </span>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors line-clamp-2 leading-snug"><?= esc($unit['nama_unit_kerja']) ?></h3>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest flex items-center">
                        Rincian Unit <i class="fas fa-chevron-right ml-1.5 text-[8px] group-hover:translate-x-1 transition-transform"></i>
                    </p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-20 bg-white border border-slate-200 rounded-xl border-dashed">
        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-search text-slate-300 text-xl"></i>
        </div>
        <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Unit kerja tidak ditemukan</p>
    </div>
</div>

<script>
    document.getElementById('unitSearch').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('.unit-card');
        const emptyState = document.getElementById('emptyState');
        let found = 0;

        cards.forEach(card => {
            const name = card.getAttribute('data-name').toLowerCase();
            if (name.includes(term)) {
                card.style.display = 'block';
                found++;
            } else {
                card.style.display = 'none';
            }
        });

        emptyState.classList.toggle('hidden', found > 0);
        document.getElementById('unitGrid').classList.toggle('hidden', found === 0);
    });
</script>
<?= $this->endSection() ?>