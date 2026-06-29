<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-4">
            <a href="<?= site_url('admin/helpdesk') ?>" class="btn btn-outline !w-10 !h-10 shrink-0">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Detail Tiket</h1>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">ID: <?= esc($ticket['tiket_id']) ?> • <?= formatTanggalWaktu($ticket['created_at']) ?></p>
            </div>
        </div>

        <form action="<?= site_url('admin/helpdesk/delete/' . $ticket['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tiket ini secara permanen?');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline text-red-600 border-red-200 hover:bg-red-50 hover:border-red-300 btn-sm uppercase tracking-widest text-[10px] font-bold">
                <i class="fas fa-trash-alt mr-2"></i> Hapus Permohonan
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Informasi Pemohon</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Nama Lengkap</span>
                        <p class="text-sm font-bold text-slate-800 uppercase"><?= esc($ticket['nama_pemohon']) ?></p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">NIP / NIK</span>
                        <p class="text-sm font-semibold text-slate-800 font-mono"><?= esc($ticket['nip_pemohon']) ?: '-' ?></p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Instansi</span>
                        <p class="text-sm font-bold text-slate-800 uppercase"><?= esc($ticket['agency_name']) ?></p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Kontak WhatsApp</span>
                        <div class="flex items-center gap-3">
                            <p class="text-sm font-semibold text-slate-800 font-mono"><?= esc($ticket['kontak_whatsapp']) ?></p>
                            <a href="https://wa.me/<?= preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $ticket['kontak_whatsapp'])) ?>" target="_blank" class="text-emerald-600 hover:text-emerald-700">
                                <i class="fab fa-whatsapp text-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Detail Kendala & Layanan -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Detail Kendala & Layanan</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Kategori</span>
                        <p class="text-sm font-bold text-slate-850 uppercase"><?= esc($ticket['category']) ?></p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Layanan Spesifik</span>
                        <p class="text-sm font-semibold text-slate-800 uppercase"><?= esc($ticket['service']) ?></p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Jenis Masalah</span>
                        <p class="text-sm font-semibold text-slate-800 uppercase"><?= esc($ticket['kategori_layanan']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm sticky top-6">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Tindak Lanjut</h3>
                </div>
                <div class="p-6">
                    <form action="<?= site_url('admin/helpdesk/update_status/' . $ticket['id']) ?>" method="POST" class="space-y-5">
                        <?= csrf_field() ?>
                        
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-1.5 uppercase tracking-widest">Update Status</label>
                            <select name="status" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 text-sm font-medium text-slate-800 cursor-pointer transition-all">
                                <option value="Menunggu" <?= $ticket['status'] == 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                <option value="Diproses" <?= $ticket['status'] == 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                                <option value="Selesai" <?= $ticket['status'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                <option value="Ditolak" <?= $ticket['status'] == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-1.5 uppercase tracking-widest">Catatan Admin (Internal)</label>
                            <textarea name="admin_notes" rows="4" class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 text-sm font-medium text-slate-800 transition-all" placeholder="Catatan internal untuk tim..."><?= esc($ticket['admin_notes']) ?></textarea>
                        </div>

                        <?php if ($ticket['status'] !== 'Selesai'): ?>
                        <div class="p-3 bg-blue-50 border border-blue-100 rounded-lg flex gap-3 mt-4">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                            <p class="text-[10px] font-bold text-blue-700 leading-relaxed uppercase">Jika Anda mengubah status menjadi "Selesai", data ini akan otomatis tersalin ke modul <a href="<?= site_url('assistance') ?>" class="underline hover:text-blue-800">Log Pendampingan</a>.</p>
                        </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-solid w-full justify-center">
                            <i class="fas fa-save mr-2 text-white/80"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
