<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-10">
    <!-- Action Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-emerald-50 rounded-full blur-3xl opacity-50"></div>
        
        <div class="relative z-10 flex items-center gap-5">
            <div class="w-14 h-14 bg-emerald-600 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-200">
                <i class="fas fa-sitemap text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Daftar Unit Kerja</h1>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1 flex items-center">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2 animate-pulse"></span>
                    Manajemen Email Berdasarkan Struktur Organisasi
                </p>
            </div>
        </div>
        
        <div class="relative z-10 w-full lg:w-96">
            <div class="relative group">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                    <i class="fas fa-search text-sm"></i>
                </span>
                <input type="text" id="unitSearch" class="block w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-semibold text-slate-700 transition-all placeholder:text-slate-400 shadow-inner" placeholder="Cari nama unit kerja...">
            </div>
        </div>
    </div>

    <!-- Grid Layout -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="unitGrid">
        <?php foreach ($unit_kerja as $unit): ?>
            <a href="<?= site_url('email/unit_kerja/' . $unit['id']) ?>" class="group bg-white border border-slate-200 rounded-3xl p-8 hover:border-emerald-500 hover:shadow-2xl hover:shadow-emerald-100 transition-all duration-300 no-underline unit-card relative overflow-hidden" data-name="<?= esc(strtoupper($unit['nama_unit_kerja'])) ?>">
                <!-- Decorative element -->
                <div class="absolute top-0 right-0 w-24 h-24 bg-slate-50 group-hover:bg-emerald-50 rounded-bl-[4rem] -mr-10 -mt-10 transition-colors duration-300"></div>
                
                <div class="relative z-10 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-8">
                        <div class="w-12 h-12 bg-slate-100 group-hover:bg-emerald-600 rounded-2xl flex items-center justify-center transition-all duration-300 shadow-sm group-hover:shadow-lg group-hover:shadow-emerald-200">
                            <i class="fas fa-building text-slate-400 group-hover:text-white text-lg transition-colors duration-300"></i>
                        </div>
                        <div class="text-right">
                            <span class="block text-2xl font-black text-slate-900 group-hover:text-emerald-600 transition-colors"><?= $unit['email_count'] ?></span>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest group-hover:text-emerald-500 transition-colors">Akun Terdaftar</span>
                        </div>
                    </div>
                    
                    <div class="space-y-4 flex-grow">
                        <h3 class="text-[13px] font-black text-slate-900 group-hover:text-emerald-700 transition-colors uppercase leading-tight line-clamp-2"><?= esc($unit['nama_unit_kerja']) ?></h3>
                    </div>
                    
                    <div class="pt-6 mt-auto border-t border-slate-50 flex items-center justify-between">
                        <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-all translate-x-[-10px] group-hover:translate-x-0">Lihat Detail</span>
                        <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-emerald-600 group-hover:text-white transition-all">
                            <i class="fas fa-chevron-right text-[10px]"></i>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden py-32 bg-white border-2 border-slate-100 border-dashed rounded-[3rem] text-center">
        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-search-location text-slate-200 text-3xl"></i>
        </div>
        <h3 class="text-lg font-black text-slate-400 uppercase tracking-widest">Unit Kerja Tidak Ditemukan</h3>
        <p class="text-sm text-slate-300 font-medium mt-2">Coba gunakan kata kunci pencarian yang berbeda</p>
    </div>
</div>

<script>
    document.getElementById('unitSearch').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('.unit-card');
        const emptyState = document.getElementById('emptyState');
        const unitGrid = document.getElementById('unitGrid');
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

        if (found > 0) {
            emptyState.classList.add('hidden');
            unitGrid.classList.remove('hidden');
            unitGrid.classList.add('grid');
        } else {
            emptyState.classList.remove('hidden');
            unitGrid.classList.add('hidden');
            unitGrid.classList.remove('grid');
        }
    });
</script>
<?= $this->endSection() ?>