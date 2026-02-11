<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="flex justify-center">
    <div class="w-full max-w-2xl">
        <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden relative group">
            <div class="bg-slate-800/30 px-10 py-8 border-b border-slate-800">
                <h5 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                    <i class="fas fa-file-csv mr-3 text-blue-500 opacity-50"></i>Batch Create Unit Kerja
                </h5>
            </div>
            
            <div class="p-10">
                <form action="<?= site_url('unit_kerja/batch_store') ?>" method="post" class="space-y-8">
                    <?= csrf_field() ?>

                    <div>
                        <label for="parent_id" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Induk Unit Kerja (Opsional)</label>
                        <select name="parent_id" id="parent_id" class="block w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-300 uppercase cursor-pointer transition-all">
                            <option value="">-- TANPA INDUK (TOP LEVEL) --</option>
                            <?php foreach ($parent_options as $parent): ?>
                                <option value="<?= $parent['id'] ?>"><?= esc(strtoupper($parent['nama_unit_kerja'])) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="mt-3 text-[10px] text-slate-500 font-bold italic uppercase tracking-tight">Pilih unit induk jika unit-unit baru ini adalah sub-unit. Kosongkan jika unit utama.</p>
                    </div>

                    <div>
                        <label for="unit_kerja_names" class="block text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] mb-3 ml-1">Daftar Nama Unit Kerja</label>
                        <textarea class="block w-full px-6 py-5 bg-slate-950 border border-slate-800 rounded-3xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-200 transition-all min-h-[300px] font-mono uppercase placeholder-slate-800 custom-scrollbar" id="unit_kerja_names" name="unit_kerja_names" placeholder="CONTOH:&#10;DINAS KESEHATAN&#10;DINAS PENDIDIKAN" required></textarea>
                        <p class="mt-3 text-[10px] text-slate-500 font-bold italic uppercase tracking-tight">Masukkan satu nama unit kerja per baris.</p>
                    </div>

                    <div class="flex flex-col md:flex-row justify-between items-center gap-4 pt-8 border-t border-slate-800">
                        <a href="<?= site_url('unit_kerja/manage') ?>" class="w-full md:w-auto px-10 py-4 bg-slate-800 hover:bg-slate-700 text-slate-300 font-black rounded-2xl shadow-sm transition-all text-[10px] uppercase tracking-widest no-underline flex items-center justify-center">
                            <i class="fas fa-arrow-left mr-3"></i> Batal
                        </a>
                        <button type="submit" class="w-full md:w-auto px-10 py-4 bg-green-600 hover:bg-green-700 text-white font-black rounded-2xl shadow-xl shadow-green-900/20 transition-all text-[10px] uppercase tracking-widest flex items-center justify-center">
                            <i class="fas fa-save mr-3"></i> Buat Unit Kerja
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>