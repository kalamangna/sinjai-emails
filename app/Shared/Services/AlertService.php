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

    public function getHighQuotaAccounts(float $threshold = 90.0)
    {
        $emailModel = new EmailModel();
        return $emailModel->select('emails.*, unit_kerja.nama_unit_kerja as unit_name')
                          ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
                          ->where('emails.diskusedpercent_float >=', $threshold)
                          ->orderBy('emails.diskusedpercent_float', 'DESC')
                          ->findAll();
    }

    public function getExpiredTtePimpinan()
    {
        $emailModel = new EmailModel();
        return $emailModel->select('emails.email, emails.name, emails.nip, emails.nik, emails.jabatan, unit_kerja.nama_unit_kerja as unit_name')
                          ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'inner')
                          ->where('emails.bsre_status', 'EXPIRED')
                          ->where('emails.deleted_at IS NULL')
                          ->where('emails.unit_kerja_id IS NOT NULL')
                          ->groupStart()
                              ->where('emails.pimpinan', 1)
                              ->orWhere('emails.pimpinan_desa', 1)
                          ->groupEnd()
                          ->findAll();
    }

    public function getExpiredTtePegawaiGroupedByUnitKerja()
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT 
                COALESCE(p.nama_unit_kerja, u.nama_unit_kerja) as unit_name,
                COUNT(e.id) as total
            FROM emails e
            INNER JOIN unit_kerja u ON u.id = e.unit_kerja_id
            LEFT JOIN unit_kerja p ON p.id = COALESCE(u.parent_id, u.id)
            WHERE e.bsre_status = 'EXPIRED'
              AND e.deleted_at IS NULL
              AND e.unit_kerja_id IS NOT NULL
              AND (e.pimpinan = 0 OR e.pimpinan IS NULL)
              AND (e.pimpinan_desa = 0 OR e.pimpinan_desa IS NULL)
              AND e.nip IS NOT NULL
              AND e.nip != ''
            GROUP BY unit_name
            ORDER BY total DESC, unit_name ASC
        ")->getResultArray();
    }

    public function getExpiringWebsites(int $days = 30)
    {
        $webDesaModel = new WebDesaKelurahanModel();
        return $webDesaModel->where('sisa_hari <=', $days)
                            ->orderBy('sisa_hari', 'ASC')
                            ->findAll();
    }

    public function appendTteReport(\App\Shared\Libraries\TelegramMessageBuilder $builder, string $mode = 'HARIAN')
    {
        $isHarian = strtoupper($mode) === 'HARIAN';

        if ($isHarian) {
            $expired = $this->getExpiredTtePimpinan();
            $count = count($expired);

            if ($count === 0) {
                return;
            }

            $builder->addText("⚠️ <b>$count TTE Pimpinan Expired:</b>");
            foreach (array_slice($expired, 0, 5) as $acc) {
                $builder->addUserProfile(
                    $acc['name'] ?? $acc['email'],
                    '',
                    $acc['jabatan'] ?? '',
                    $acc['unit_name'] ?? '',
                    $acc['email'] ?? ''
                );
            }

            if ($count > 5) {
                $builder->addItalicText("...dan " . ($count - 5) . " lainnya.");
            }
        } else {
            // Mode Bulanan / Penuh: Tampilkan rekap per unit kerja
            $grouped = $this->getExpiredTtePegawaiGroupedByUnitKerja();
            if (empty($grouped)) {
                return;
            }

            $totalExpired = array_sum(array_column($grouped, 'total'));
            $builder->addText("⚠️ <b>$totalExpired TTE Pegawai Expired:</b>");

            foreach (array_slice($grouped, 0, 6) as $row) {
                $unitName = mb_strtoupper(trim($row['unit_name']));
                $total = $row['total'];
                $builder->addBullet("<b>{$unitName}</b> ({$total} Akun)");
            }

            $totalUnits = count($grouped);
            if ($totalUnits > 6) {
                $builder->addItalicText("...dan " . ($totalUnits - 6) . " unit kerja lainnya.");
            }
        }
    }

    public function appendQuotaReport(\App\Shared\Libraries\TelegramMessageBuilder $builder)
    {
        $highUsage = $this->getHighQuotaAccounts(90.0);
        $count = count($highUsage);

        if ($count === 0) {
            return;
        }

        $builder->addText("⚠️ <b>$count Akun Kuota Hampir Penuh (>90%):</b>");
        foreach (array_slice($highUsage, 0, 5) as $acc) {
            $extra = "📊 " . round($acc['diskusedpercent_float'], 1) . "% (" . $acc['humandiskused'] . ")";
            $builder->addUserProfile(
                $acc['name'] ?? $acc['email'],
                '',
                $acc['jabatan'] ?? '',
                $acc['unit_name'] ?? '',
                $acc['email'] ?? '',
                $extra
            );
        }

        if ($count > 5) {
            $builder->addItalicText("...dan " . ($count - 5) . " lainnya.");
        }
    }

    public function appendWebExpirationReport(\App\Shared\Libraries\TelegramMessageBuilder $builder)
    {
        $expiring = $this->getExpiringWebsites(30);
        $count = count($expiring);

        if ($count === 0) {
            return;
        }

        $builder->addText("⚠️ <b>$count Domain Akan Kedaluwarsa (<30 Hari):</b>");
        foreach (array_slice($expiring, 0, 5) as $web) {
            $builder->addBullet("<b>{$web['domain']}</b> ({$web['desa_kelurahan']}) — Sisa {$web['sisa_hari']} hari");
        }

        if ($count > 5) {
            $builder->addItalicText("...dan " . ($count - 5) . " lainnya.");
        }
    }

    public function checkQuotaAlerts()
    {
        if (is_cli()) CLI::write('Checking for High Quota Usage Alerts...', 'yellow');
        try {
            $highUsageAccounts = $this->getHighQuotaAccounts(90.0);
            if (!empty($highUsageAccounts)) {
                $count = count($highUsageAccounts);
                if (is_cli()) CLI::write("Found $count accounts with high usage (>90%)", 'red');
                
                $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
                $builder->setTitle('PERINGATAN KUOTA EMAIL PENUH', '⚠️')
                        ->addDivider();
                $this->appendQuotaReport($builder);
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
            $expiredPimpinan = $this->getExpiredTtePimpinan();
            $count = count($expiredPimpinan);

            if ($count === 0) {
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

            if (is_cli()) CLI::write("Total Pimpinan Expired: $count", 'cyan');
            $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
            $builder->setTitle('LAPORAN TTE PIMPINAN EXPIRED', '🔔')
                    ->addDivider();
            $this->appendTteReport($builder);
            $this->telegram->sendMessage($builder->build());

        } catch (\Throwable $e) {
            if (is_cli()) CLI::error('Error checking TTE expired alerts: ' . $e->getMessage());
        }
    }

    public function checkWebExpirationAlerts()
    {
        if (is_cli()) CLI::write('Checking for Expiring Website Domains...', 'yellow');
        try {
            $expiringWebs = $this->getExpiringWebsites(30);
            if (!empty($expiringWebs)) {
                $count = count($expiringWebs);
                if (is_cli()) CLI::write("Found $count website domains expiring soon", 'red');
                
                $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
                $builder->setTitle('PERINGATAN MASA AKTIF WEBSITE', '🌐')
                        ->addDivider();
                $this->appendWebExpirationReport($builder);
                $this->telegram->sendMessage($builder->build());
            } else {
                if (is_cli()) CLI::write('No expiring website domains found.', 'green');
            }
        } catch (\Throwable $e) {
            if (is_cli()) CLI::error('Error checking website expiration alerts: ' . $e->getMessage());
        }
    }
}
