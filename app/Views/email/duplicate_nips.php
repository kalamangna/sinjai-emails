<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-200 flex justify-between items-center bg-amber-50">
        <div>
            <h3 class="text-lg font-bold text-slate-800">Daftar Akun NIP Ganda</h3>
            <p class="text-sm text-amber-700 mt-1">Ditemukan <b><?= count($emails) ?></b> akun yang memiliki NIP yang sama dengan akun lain.</p>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama & Email</th>
                    <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider">NIP</th>
                    <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Unit Kerja</th>
                    <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Jabatan</th>
                    <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                <?php if(empty($emails)): ?>
                <tr>
                    <td colspan="5" class="p-8 text-center text-slate-500">
                        <div class="flex flex-col items-center gap-2">
                            <i class="fas fa-check-circle text-green-500 text-3xl"></i>
                            <span>Tidak ada NIP ganda yang ditemukan.</span>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach($emails as $email): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4">
                            <div class="font-semibold text-slate-800"><?= esc($email['name']) ?></div>
                            <div class="text-xs text-slate-500"><?= esc($email['email']) ?></div>
                        </td>
                        <td class="p-4 font-mono text-sm text-slate-700"><?= esc($email['nip'] ?? '-') ?></td>
                        <td class="p-4 text-sm text-slate-600"><?= esc($email['nama_unit_kerja'] ?? '-') ?></td>
                        <td class="p-4 text-sm text-slate-600 font-mono text-[10px] uppercase"><?= esc($email['jabatan'] ?? '-') ?></td>
                        <td class="p-4 text-right">
                            <a href="<?= site_url('email/detail/' . esc($email['user'])) ?>" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-800 text-white rounded-lg text-xs font-bold hover:bg-slate-700 transition-all shadow-sm">
                                <i class="fas fa-eye"></i>
                                <span>Detail</span>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
