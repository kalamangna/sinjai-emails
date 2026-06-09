<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="flex items-center justify-center py-10">
    <div class="max-w-md w-full bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden text-center">
        <div class="bg-emerald-50 py-10 flex flex-col items-center justify-center border-b border-emerald-100">
            <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600 mb-4 shadow-sm">
                <i class="fas fa-check text-4xl"></i>
            </div>
            <h2 class="text-xl font-bold text-emerald-800 uppercase tracking-tight">Laporan Terkirim!</h2>
        </div>
        
        <div class="p-8 space-y-6">
            <div>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Nomor Tiket Anda</p>
                <p class="text-2xl font-bold text-slate-800 font-mono tracking-wider"><?= esc($ticket['tiket_id']) ?></p>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-lg p-4 text-left space-y-3">
                <div class="flex justify-between items-start border-b border-slate-200 pb-2">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Pemohon</span>
                    <span class="text-xs font-bold text-slate-800 uppercase text-right"><?= esc($ticket['nama_pemohon']) ?></span>
                </div>
                <div class="flex justify-between items-start border-b border-slate-200 pb-2">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Instansi</span>
                    <span class="text-xs font-bold text-slate-800 uppercase text-right w-2/3"><?= esc($ticket['agency_name']) ?></span>
                </div>
                <div class="flex justify-between items-start border-b border-slate-200 pb-2">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Layanan</span>
                    <span class="text-xs font-bold text-slate-800 uppercase text-right"><?= esc($ticket['service']) ?></span>
                </div>
                <div class="flex justify-between items-start">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Masalah</span>
                    <span class="text-xs font-bold text-slate-800 uppercase text-right"><?= esc($ticket['kategori_layanan']) ?></span>
                </div>
            </div>

            <p class="text-sm font-medium text-slate-600">Terima kasih. Tim Helpdesk kami akan memproses laporan Anda dan akan segera menghubungi Anda melalui nomor WhatsApp yang didaftarkan.</p>
            
            <div class="pt-4">
                <a href="<?= site_url('helpdesk') ?>" class="btn btn-outline w-full justify-center">
                    <i class="fas fa-plus mr-2 text-slate-700"></i> Buat Laporan Baru
                </a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
