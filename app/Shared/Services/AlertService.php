<?php

namespace App\Shared\Services;

use App\Domains\Email\Models\EmailModel;
use App\Domains\Website\Models\WebDesaKelurahanModel;
use App\Shared\Libraries\TelegramLibrary;
use CodeIgniter\CLI\CLI;

class AlertService
{
    protected $telegram;

    public function __construct()
    {
        $this->telegram = new TelegramLibrary();
    }

    public function checkQuotaAlerts()
    {
        if (is_cli()) CLI::write('Checking for High Quota Usage Alerts...', 'yellow');
        try {
            $emailModel = new EmailModel();
            $highUsageAccounts = $emailModel->select('emails.*, unit_kerja.nama_unit_kerja as unit_name')
                                            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                                            ->where('emails.diskusedpercent_float >=', 90)
                                            ->orderBy('emails.diskusedpercent_float', 'DESC')
                                            ->findAll();
            
            if (!empty($highUsageAccounts)) {
                $count = count($highUsageAccounts);
                if (is_cli()) CLI::write("Found $count accounts with high usage (>90%)", 'red');
                
                $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
                $builder->setTitle('PERINGATAN KUOTA EMAIL PENUH', '⚠️')
                        ->addText("<b>$count Akun Kuota Hampir Penuh (>90%):</b>")
                        ->addDivider();
                
                foreach (array_slice($highUsageAccounts, 0, 10) as $acc) {
                    $identitas = !empty($acc['nip']) ? "NIP: {$acc['nip']}" : (!empty($acc['nik']) ? "NIK: {$acc['nik']}" : "Tanpa NIP/NIK");
                    $jabatan = !empty($acc['jabatan']) ? $acc['jabatan'] : 'Jabatan Belum Diisi';
                    $unitKerja = !empty($acc['unit_name']) ? $acc['unit_name'] : 'Instansi Belum Diisi';
                    $extraData = "📊 Penggunaan: " . $acc['humandiskused'] . " (" . round($acc['diskusedpercent_float'], 1) . "%)";
                    
                    $builder->addUserProfile($acc['name'], $identitas, $jabatan, $unitKerja, $acc['email'], $extraData);
                }
                
                if ($count > 10) {
                    $builder->addItalicText("...dan " . ($count - 10) . " akun lainnya.");
                }
                
                $this->telegram->sendMessage($builder->build());
            } else {
                if (is_cli()) CLI::write('No high usage accounts found.', 'green');
            }
        } catch (\Throwable $e) {
            if (is_cli()) CLI::error('Error checking quota alerts: ' . $e->getMessage());
        }
    }

    public function checkTteExpiredAlerts($sendIfSafe = true)
    {
        if (is_cli()) CLI::write('Checking for Expired TTE Alerts...', 'yellow');
        try {
            $emailModel = new EmailModel();
            
            $totalExpiredCount = $emailModel->where('bsre_status', 'EXPIRED')
                ->groupStart()
                    ->where('pimpinan', 1)
                    ->orWhere('pimpinan_desa', 1)
                ->groupEnd()
                ->countAllResults();
            
            if ($totalExpiredCount === 0) {
                if (is_cli()) CLI::write('No expired TTE accounts found.', 'green');
                
                if ($sendIfSafe) {
                    $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
                    $builder->setTitle('LAPORAN TTE PIMPINAN EXPIRED', '🔔')
                            ->addDivider()
                            ->addText("✅ Seluruh TTE pimpinan dalam kondisi aman.");
                    $this->telegram->sendMessage($builder->build());
                }
                return;
            }

            $expiredPimpinan = $emailModel->select('emails.email, emails.name, emails.nip, emails.nik, emails.jabatan, unit_kerja.nama_unit_kerja as unit_name')
                                          ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                                          ->where('emails.bsre_status', 'EXPIRED')
                                          ->groupStart()
                                              ->where('emails.pimpinan', 1)
                                              ->orWhere('emails.pimpinan_desa', 1)
                                          ->groupEnd()
                                          ->findAll();
            
            $pimpinanCount = count($expiredPimpinan);
            if (is_cli()) CLI::write("Total Expired: $totalExpiredCount, Pimpinan Expired: $pimpinanCount", 'cyan');
            
            $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
            $builder->setTitle('LAPORAN TTE PIMPINAN EXPIRED', '🔔')
                    ->addText("<b>$pimpinanCount Pimpinan TTE Expired:</b>")
                    ->addDivider();

            foreach (array_slice($expiredPimpinan, 0, 10) as $acc) {
                $identitas = ''; // Dihapus sesuai permintaan pengguna
                $jabatan = !empty($acc['jabatan']) ? $acc['jabatan'] : 'Jabatan Belum Diisi';
                $unitKerja = !empty($acc['unit_name']) ? $acc['unit_name'] : 'Instansi Belum Diisi';
                
                $builder->addUserProfile($acc['name'], $identitas, $jabatan, $unitKerja, $acc['email']);
            }
            
            if ($pimpinanCount > 10) {
                $builder->addItalicText("...dan " . ($pimpinanCount - 10) . " pimpinan lainnya.");
            }
            
            $this->telegram->sendMessage($builder->build());

        } catch (\Throwable $e) {
            if (is_cli()) CLI::error('Error checking TTE expired alerts: ' . $e->getMessage());
        }
    }

    public function checkWebExpirationAlerts()
    {
        if (is_cli()) CLI::write('Checking for Expiring Website Domains...', 'yellow');
        try {
            $webDesaModel = new WebDesaKelurahanModel();
            
            $expiringWebs = $webDesaModel->where('sisa_hari <=', 30)
                                         ->orderBy('sisa_hari', 'ASC')
                                         ->findAll();
            
            if (!empty($expiringWebs)) {
                $count = count($expiringWebs);
                if (is_cli()) CLI::write("Found $count website domains expiring soon", 'red');
                
                $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
                $builder->setTitle('PERINGATAN MASA AKTIF WEBSITE', '🌐')
                        ->addText("<b>$count Domain Akan Kedaluwarsa (<30 Hari):</b>")
                        ->addDivider();
                
                foreach (array_slice($expiringWebs, 0, 10) as $web) {
                    $item = "💻 <b>" . $web['domain'] . "</b>\n";
                    $item .= "🏛️ " . $web['desa_kelurahan'] . "\n";
                    $item .= "⏳ Sisa: " . $web['sisa_hari'] . " hari (" . date('d M Y', strtotime($web['tanggal_berakhir'])) . ")\n";
                    $builder->addText($item);
                }
                
                if ($count > 10) {
                    $builder->addItalicText("...dan " . ($count - 10) . " domain lainnya.");
                }
                
                $this->telegram->sendMessage($builder->build());
            } else {
                if (is_cli()) CLI::write('No expiring website domains found.', 'green');
            }
        } catch (\Throwable $e) {
            if (is_cli()) CLI::error('Error checking website expiration alerts: ' . $e->getMessage());
        }
    }
}
