<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-10">
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <div class="space-y-2">
            <h2 class="text-3xl font-black text-slate-100 uppercase tracking-tighter">Daftar Perangkat Daerah</h2>
            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">Pilih Unit Kerja untuk melihat rincian akun email</p>
        </div>
        <a href="<?= site_url('/') ?>" class="inline-flex items-center px-6 py-3 bg-slate-900 border border-slate-800 rounded-2xl text-xs font-black text-slate-400 uppercase tracking-widest hover:bg-slate-800 hover:text-slate-200 transition-all shadow-xl no-underline group">
            <i class="fas fa-arrow-left mr-3 group-hover:-translate-x-1 transition-transform"></i> Kembali ke Beranda
        </a>
    </div>

    <!-- Search / Filter -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden p-8">
        <div class="relative max-w-xl">
            <span class="absolute inset-y-0 left-0 pl-5 flex items-center text-slate-600">
                <i class="fas fa-search text-xs"></i>
            </span>
            <input type="text" id="unitSearch" class="block w-full pl-12 pr-5 py-4 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all uppercase tracking-tight placeholder-slate-800" placeholder="CARI NAMA PERANGKAT DAERAH / KECAMATAN...">
        </div>
    </div>

    <!-- Grid Layout -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="unitGrid">
        <?php foreach ($unit_kerja as $unit): ?>
            <a href="<?= site_url('email/unit_kerja/' . $unit['id']) ?>" class="group bg-slate-900 border border-slate-800 rounded-[2rem] p-6 hover:border-blue-500/50 hover:bg-slate-900/50 transition-all shadow-xl unit-card" data-name="<?= esc(strtoupper($unit['nama_unit_kerja'])) ?>">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-12 h-12 bg-blue-500/10 border border-blue-500/20 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                        <i class="fas fa-building text-blue-500 text-xl"></i>
                    </div>
                    <div class="px-3 py-1 bg-slate-950 rounded-lg border border-slate-800 text-[10px] font-black text-slate-500 group-hover:text-blue-400 group-hover:border-blue-500/30 transition-all uppercase tracking-widest">
                        <?= $unit['email_count'] ?> AKUN
                    </div>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-black text-slate-200 uppercase tracking-tight leading-snug group-hover:text-white transition-colors"><?= esc($unit['nama_unit_kerja']) ?></h3>
                    <span class="inline-flex items-center text-[9px] font-black text-slate-600 uppercase tracking-[0.2em]">
                        Lihat Rincian <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-24 bg-slate-900/50 border border-slate-800 rounded-[2.5rem] border-dashed">
        <div class="w-20 h-20 bg-slate-950 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-slate-800">
            <i class="fas fa-search text-4xl text-slate-800"></i>
        </div>
        <h5 class="text-sm font-black text-slate-500 uppercase tracking-widest">Tidak ada Unit Kerja yang sesuai</h5>
    </div>
</div>

<script>
    document.getElementById('unitSearch').addEventListener('input', function(e) {
        const term = e.target.value.toUpperCase();
        const cards = document.querySelectorAll('.unit-card');
        const emptyState = document.getElementById('emptyState');
        let found = 0;

        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            if (name.includes(term)) {
                card.classList.remove('hidden');
                found++;
            } else {
                card.classList.add('hidden');
            }
        });

        if (found === 0) {
            emptyState.classList.remove('hidden');
            document.getElementById('unitGrid').classList.add('hidden');
        } else {
            emptyState.classList.add('hidden');
            document.getElementById('unitGrid').classList.remove('hidden');
        }
    });
</script>
<?= $this->endSection() ?>
