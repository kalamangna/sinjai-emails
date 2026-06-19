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
                
                $msg = "⚠️ <b>PERINGATAN KUOTA EMAIL PENUH</b>\n";
                $msg .= "Ditemukan <b>$count</b> akun dengan penggunaan > 90%:\n";
                $msg .= "------------------------------------------\n\n";
                
                foreach (array_slice($highUsageAccounts, 0, 10) as $acc) {
                    $identitas = !empty($acc['nip']) ? "NIP: {$acc['nip']}" : (!empty($acc['nik']) ? "NIK: {$acc['nik']}" : "Tanpa NIP/NIK");
                    $jabatan = !empty($acc['jabatan']) ? $acc['jabatan'] : 'Jabatan Belum Diisi';
                    $unitKerja = !empty($acc['unit_name']) ? $acc['unit_name'] : 'Instansi Belum Diisi';
                    
                    $msg .= "👤 <b>" . $acc['name'] . "</b> ($identitas)\n";
                    $msg .= "💼 $jabatan\n";
                    $msg .= "🏛️ $unitKerja\n";
                    $msg .= "📧 " . $acc['email'] . "\n";
                    $msg .= "📊 Penggunaan: <b>" . $acc['humandiskused'] . "</b> (" . round($acc['diskusedpercent_float'], 1) . "%)\n\n";
                }
                
                if ($count > 10) {
                    $msg .= "<i>...dan " . ($count - 10) . " akun lainnya.</i>";
                }
                
                $this->telegram->sendMessage($msg);
            } else {
                if (is_cli()) CLI::write('No high usage accounts found.', 'green');
            }
        } catch (\Throwable $e) {
            if (is_cli()) CLI::error('Error checking quota alerts: ' . $e->getMessage());
        }
    }

    public function checkTteExpiredAlerts()
    {
        if (is_cli()) CLI::write('Checking for Expired TTE Status Alerts...', 'yellow');
        try {
            $emailModel = new EmailModel();
            
            // 1. Get TOTAL count of all leadership expired accounts
            $totalExpiredCount = $emailModel->where('bsre_status', 'EXPIRED')
                ->groupStart()
                    ->where('pimpinan', 1)
                    ->orWhere('pimpinan_desa', 1)
                ->groupEnd()
                ->countAllResults();
            
            if ($totalExpiredCount === 0) {
                if (is_cli()) CLI::write('No expired TTE accounts found.', 'green');
                return;
            }

            // 2. Get detailed data for LEADERSHIP only
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
            
            // 3. Construct Telegram Message
            $msg = "🔔 <b>LAPORAN TTE PIMPINAN EXPIRED</b>\n";
            $msg .= "Ditemukan <b>$pimpinanCount</b> pimpinan Expired:\n";
            $msg .= "------------------------------------------\n\n";

            if ($pimpinanCount > 0) {
                foreach (array_slice($expiredPimpinan, 0, 10) as $acc) {
                    $identitas = !empty($acc['nip']) ? "NIP: {$acc['nip']}" : (!empty($acc['nik']) ? "NIK: {$acc['nik']}" : "Tanpa NIP/NIK");
                    $jabatan = !empty($acc['jabatan']) ? $acc['jabatan'] : 'Jabatan Belum Diisi';
                    $unitKerja = !empty($acc['unit_name']) ? $acc['unit_name'] : 'Instansi Belum Diisi';
                    
                    $msg .= "👤 <b>" . $acc['name'] . "</b> ($identitas)\n";
                    $msg .= "💼 $jabatan\n";
                    $msg .= "🏛️ $unitKerja\n";
                    $msg .= "📧 " . $acc['email'] . "\n\n";
                }
                
                if ($pimpinanCount > 10) {
                    $msg .= "<i>...dan " . ($pimpinanCount - 10) . " pimpinan lainnya.</i>";
                }
            } else {
                $msg .= "✅ Seluruh TTE pimpinan dalam kondisi aman.";
            }
            
            $this->telegram->sendMessage($msg);

        } catch (\Throwable $e) {
            if (is_cli()) CLI::error('Error checking TTE expired alerts: ' . $e->getMessage());
        }
    }

    public function checkWebExpirationAlerts()
    {
        if (is_cli()) CLI::write('Checking for Expiring Website Domains...', 'yellow');
        try {
            $webDesaModel = new WebDesaKelurahanModel();
            
            // Look for domains expiring in 30 days or less
            $expiringWebs = $webDesaModel->where('sisa_hari <=', 30)
                                         ->orderBy('sisa_hari', 'ASC')
                                         ->findAll();
            
            if (!empty($expiringWebs)) {
                $count = count($expiringWebs);
                if (is_cli()) CLI::write("Found $count website domains expiring soon", 'red');
                
                $msg = "🌐 <b>PERINGATAN MASA AKTIF WEBSITE</b>\n";
                $msg .= "Ditemukan <b>$count</b> domain akan kadaluwarsa (< 30 Hari):\n";
                $msg .= "------------------------------------------\n\n";
                
                foreach (array_slice($expiringWebs, 0, 10) as $web) {
                    $msg .= "💻 <b>" . $web['domain'] . "</b>\n";
                    $msg .= "🏛️ " . $web['desa_kelurahan'] . "\n";
                    $msg .= "⏳ Sisa: <b>" . $web['sisa_hari'] . " Hari</b> (s.d " . date('d M Y', strtotime($web['tanggal_berakhir'])) . ")\n\n";
                }
                
                if ($count > 10) {
                    $msg .= "<i>...dan " . ($count - 10) . " domain lainnya.</i>";
                }
                
                $this->telegram->sendMessage($msg);
            } else {
                if (is_cli()) CLI::write('No expiring website domains found.', 'green');
            }
        } catch (\Throwable $e) {
            if (is_cli()) CLI::error('Error checking website expiration alerts: ' . $e->getMessage());
        }
    }
}
