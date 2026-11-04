<?php

namespace App\Domains\Bridge\Models;

use CodeIgniter\Model;

class BridgeWhitelistModel extends Model
{
    protected $table            = 'bridge_whitelists';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'domain',
        'ip_address',
        'label',
        'is_active',
        'last_resolved_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Bersihkan input host atau URL menjadi nama domain/host murni
     */
    public static function cleanHost(string $input): string
    {
        $input = trim($input);

        // Jika diawali skema http:// atau https://
        if (preg_match('#^https?://#i', $input)) {
            $parsed = parse_url($input, PHP_URL_HOST);
            if ($parsed) {
                return strtolower($parsed);
            }
        }

        // Hapus path dan tanda garis miring jika disertakan
        $input = preg_replace('#/.*$#', '', $input);

        // Hapus port jika ada
        $input = preg_replace('#:\d+$#', '', $input);

        return strtolower(trim($input));
    }

    /**
     * Resolusi nama host menjadi daftar alamat IP IPv4
     */
    public static function resolveHostToIps(string $host): ?string
    {
        $host = self::cleanHost($host);
        if (empty($host)) {
            return null;
        }

        // Jika input sudah merupakan alamat IP valid
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $host;
        }

        // Resolusi DNS khusus A record (IPv4)
        $records = @dns_get_record($host, DNS_A);
        $ips = [];
        if (!empty($records)) {
            foreach ($records as $record) {
                if (!empty($record['ip']) && filter_var($record['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $ips[] = $record['ip'];
                }
            }
        }

        // Alternatif gethostbynamel jika dns_get_record tidak menghasilkan IP
        if (empty($ips)) {
            $resolved = @gethostbynamel($host);
            if (is_array($resolved)) {
                $ips = array_merge($ips, $resolved);
            }
        }

        $ips = array_values(array_unique(array_filter($ips)));

        return !empty($ips) ? implode(', ', $ips) : null;
    }

    /**
     * Periksa apakah alamat IP klien diizinkan berdasarkan entri whitelist aktif
     */
    public function isIpAllowed(string $clientIp): bool
    {
        $activeEntries = $this->where('is_active', 1)->findAll();

        // Jika tabel belum memiliki entri whitelist aktif, sistem terbuka dengan Basic Auth
        if (empty($activeEntries)) {
            return true;
        }

        $clientIp = trim($clientIp);

        foreach ($activeEntries as $entry) {
            // Cocokkan domain langsung jika diinput sebagai alamat IP
            if (trim($entry['domain']) === $clientIp) {
                return true;
            }

            // Cocokkan dengan IP yang telah tersinkronisasi
            if (!empty($entry['ip_address'])) {
                $ipList = array_map('trim', explode(',', $entry['ip_address']));
                if (in_array($clientIp, $ipList, true)) {
                    return true;
                }
            }
        }

        return false;
    }
}
