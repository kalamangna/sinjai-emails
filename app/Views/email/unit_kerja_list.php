<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Unit Kerja</h1>

        <div class="w-full lg:w-80">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-700">
                    <i class="fas fa-search text-xs"></i>
                </span>
                <input type="text" id="unitSearch" class="block w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm transition-all" placeholder="Cari nama unit kerja...">
            </div>
        </div>
    </div>

    <!-- Grid Unit Kerja -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="unitGrid">
        <?php foreach ($unit_kerja as $unit): ?>
            <a href="<?= site_url('email/unit_kerja/' . $unit['id']) ?>" class="group bg-white border border-slate-200 rounded-xl p-6 hover:border-slate-800 hover:shadow-md transition-all no-underline unit-card flex flex-col" data-name="<?= esc(strtoupper($unit['nama_unit_kerja'])) ?>">
                <div class="flex items-start justify-between mb-6">
                    <div class="w-12 h-12 bg-slate-50 rounded-xl flex items-center justify-center text-slate-700 group-hover:bg-slate-800 group-hover:text-white transition-all duration-300">
                        <i class="fas fa-building text-xl"></i>
                    </div>
                    <div class="text-right">
                        <span class="block text-2xl font-bold text-slate-800"><?= $unit['email_count'] ?></span>
                        <span class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">Email</span>
                    </div>
                </div>

                <div class="flex-grow">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-tight leading-snug"><?= esc($unit['nama_unit_kerja']) ?></h3>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-[10px] font-bold text-slate-700 group-hover:text-slate-800 uppercase tracking-widest transition-colors">
                    <span>Lihat Detail</span>
                    <i class="fas fa-chevron-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<script>
    document.getElementById('unitSearch').addEventListener('input', function(e) {
        const search = e.target.value.toUpperCase();
        const cards = document.querySelectorAll('.unit-card');
        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            if (name.includes(search)) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    });
</script>
<?= $this->endSection() ?>