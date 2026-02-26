<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <h1 class="text-2xl font-semibold text-gray-900">Unit Kerja</h1>

        <div class="w-full lg:w-80">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <i class="fas fa-search text-xs"></i>
                </span>
                <input type="text" id="unitSearch" class="block w-full pl-9 pr-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm" placeholder="Cari unit kerja...">
            </div>
        </div>
    </div>

    <!-- Grid Unit Kerja -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="unitGrid">
        <?php foreach ($unit_kerja as $unit): ?>
            <a href="<?= site_url('email/unit_kerja/' . $unit['id']) ?>" class="group bg-white border border-gray-200 rounded-xl p-6 hover:border-gray-400 hover:shadow-md transition-all no-underline unit-card flex flex-col" data-name="<?= esc(strtoupper($unit['nama_unit_kerja'])) ?>">
                <div class="flex items-start justify-between mb-6">
                    <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center text-gray-400 group-hover:bg-gray-900 group-hover:text-white transition-all duration-300">
                        <i class="fas fa-building text-xl"></i>
                    </div>
                    <div class="text-right">
                        <span class="block text-2xl font-bold text-gray-900"><?= $unit['email_count'] ?></span>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Email</span>
                    </div>
                </div>

                <div class="flex-grow">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-tight leading-snug"><?= esc($unit['nama_unit_kerja']) ?></h3>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-50 flex items-center justify-between text-[10px] font-bold text-gray-400 group-hover:text-gray-900 uppercase tracking-widest transition-colors">
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