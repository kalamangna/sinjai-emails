<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="robots" content="<?= $meta_robots ?? 'noindex, follow' ?>">
    <title><?= $title ?? 'Helpdesk' ?> | Sistem Identitas Digital</title>

    <meta name="description" content="<?= $meta_description ?? 'Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai' ?>">
    <link rel="canonical" href="<?= current_url() ?>">

    <!-- Meta Tags -->
    <meta property="og:site_name" content="Sistem Identitas Digital Sinjai">
    <meta property="og:title" content="<?= $title ?? 'Helpdesk' ?> | Sistem Identitas Digital">
    <meta property="og:description" content="<?= $meta_description ?? 'Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai' ?>">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:image" content="<?= $meta_image ?? base_url('meta.png') ?>">
    <meta property="og:type" content="<?= $meta_type ?? 'website' ?>">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title ?? 'Helpdesk' ?> | Sistem Identitas Digital">
    <meta name="twitter:description" content="<?= $meta_description ?? 'Sistem Identitas Digital & Sertifikat Elektronik Pemerintah Kabupaten Sinjai' ?>">
    <meta name="twitter:image" content="<?= $meta_image ?? base_url('meta.png') ?>">

    <link rel="icon" type="image/png" href="<?= base_url('logo.png') ?>">
    <link href="<?= base_url('css/output.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-xl bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden flex flex-col my-auto">
        <!-- Header Success -->
        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 py-10 flex flex-col items-center justify-center border-b border-emerald-500/20 relative overflow-hidden text-white">
            <div class="absolute inset-0 opacity-10 pointer-events-none">
                <i class="fas fa-check-circle text-white text-[120px] absolute -right-6 -bottom-8 rotate-12"></i>
            </div>
            <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center text-white mb-4 border border-white/20 shadow-inner relative z-10">
                <i class="fas fa-check text-2xl animate-bounce"></i>
            </div>
            <h2 class="text-lg font-bold uppercase tracking-tight relative z-10">Laporan Terkirim!</h2>
            <p class="text-[9px] font-bold text-emerald-200 uppercase tracking-widest relative z-10">Tiket Bantuan Layanan Berhasil Dibuat</p>
        </div>
        
        <!-- Content Success -->
        <div class="p-6 sm:p-8 space-y-6">
            <div>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1 block">Nomor Tiket Anda</span>
                <span class="text-2xl font-bold text-slate-800 font-mono tracking-wider bg-slate-50 border border-slate-200 px-4 py-2 rounded-lg inline-block shadow-sm">
                    <?= esc($ticket['tiket_id']) ?>
                </span>
            </div>

            <!-- Detail Laporan -->
            <div class="bg-slate-50/50 border border-slate-200 rounded-lg p-4 text-left space-y-3 text-[11px] leading-relaxed">
                <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest w-20 shrink-0">Pemohon:</span>
                    <span class="font-bold text-slate-800 uppercase text-right truncate" title="<?= esc($ticket['nama_pemohon']) ?>"><?= esc($ticket['nama_pemohon']) ?></span>
                </div>
                <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest w-20 shrink-0">Instansi:</span>
                    <span class="font-semibold text-slate-800 uppercase text-right truncate w-2/3" title="<?= esc($ticket['agency_name']) ?>"><?= esc($ticket['agency_name']) ?></span>
                </div>
                <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest w-20 shrink-0">Layanan:</span>
                    <span class="font-semibold text-slate-850 uppercase text-right truncate" title="<?= esc($ticket['service']) ?>"><?= esc($ticket['service']) ?></span>
                </div>
                <div class="flex justify-between items-start">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest w-20 shrink-0">Masalah:</span>
                    <span class="font-semibold text-slate-850 uppercase text-right truncate" title="<?= esc($ticket['kategori_layanan']) ?>"><?= esc($ticket['kategori_layanan']) ?></span>
                </div>
            </div>

            <p class="text-[11px] text-slate-550 leading-relaxed">Terima kasih. Tim Helpdesk Diskominfo-SP akan segera memproses laporan Anda. Kami akan menghubungi Anda melalui nomor WhatsApp yang terdaftar untuk koordinasi lebih lanjut.</p>
            
            <div class="pt-4">
                <a href="<?= site_url('helpdesk') ?>" class="w-full btn btn-solid !bg-slate-850 hover:!bg-slate-900 !text-white !py-2.5 rounded-lg flex items-center justify-center gap-2 hover:scale-[1.01] active:scale-[0.99] transition-all no-underline text-xs font-bold uppercase tracking-wider">
                    <i class="fas fa-plus text-xs text-white/80"></i> Buat Laporan Baru
                </a>
            </div>
        </div>
    </div>
</div>
</body>

</html>
