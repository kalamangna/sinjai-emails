<?php

namespace App\Domains\Email;

use CodeIgniter\Model;

class EmailModel extends Model
{
    protected $table = 'emails';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'email',
        'domain',
        'unit_kerja_id',
        'mtime',
        'suspended_login',
        'diskquota',
        'humandiskquota',
        '_diskquota',
        'diskused',
        'humandiskused',
        '_diskused',
        'diskusedpercent',
        'diskusedpercent_float',
        'user',
        'password',
        'nik',
        'name',
        'gelar_depan',
        'gelar_belakang',
        'nip',
        'tempat_lahir',
        'tanggal_lahir',
        'pendidikan',
        'jabatan',
        'golongan',
        'pangkat_golruang',
        'pangkat_nama',
        'status_asn_id',
        'eselon_id',
        'bsre_status',
        'pimpinan',
        'pimpinan_desa',
        'pensiun_at',
        'nik_hash',
        'nip_hash',
    ];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $deletedField  = 'deleted_at';
    protected $returnType = 'array';
    
    // Callbacks for encryption and blind index
    protected $beforeInsert = ['hashAndEncrypt'];
    protected $beforeUpdate = ['hashAndEncrypt'];
    protected $afterFind    = ['decryptData'];

    protected function normalize($value)
    {
        if (empty($value)) return $value;
        return str_replace([' ', '.', '-', '\''], '', $value);
    }

    protected function hashAndEncrypt(array $data)
    {
        if (isset($data['data'])) {
            $encrypter = \Config\Services::encrypter();

            // Blind Index (Hash)
            if (isset($data['data']['nik'])) {
                $cleanNik = $this->normalize($data['data']['nik']);
                $data['data']['nik_hash'] = hash('sha256', $cleanNik);
                $data['data']['nik'] = base64_encode($encrypter->encrypt($cleanNik));
            }
            
            if (isset($data['data']['nip'])) {
                $cleanNip = $this->normalize($data['data']['nip']);
                $data['data']['nip_hash'] = hash('sha256', $cleanNip);
                $data['data']['nip'] = base64_encode($encrypter->encrypt($cleanNip));
            }

            if (isset($data['data']['password']) && !empty($data['data']['password'])) {
                $data['data']['password'] = base64_encode($encrypter->encrypt($data['data']['password']));
            }
        }
        return $data;
    }

    protected function decryptData(array $data)
    {
        if (empty($data['data'])) return $data;
        
        $encrypter = \Config\Services::encrypter();
        
        $decryptRow = function(&$row) use ($encrypter) {
            $isArray = is_array($row);
            foreach (['nik', 'nip', 'password'] as $field) {
                $value = $isArray ? ($row[$field] ?? null) : ($row->$field ?? null);
                if (!empty($value)) {
                    try {
                        $decrypted = $encrypter->decrypt(base64_decode($value));
                        if ($isArray) {
                            $row[$field] = $decrypted;
                        } else {
                            $row->$field = $decrypted;
                        }
                    } catch (\Throwable $e) {
                        // Skip if decryption fails (e.g. data is not encrypted yet)
                        log_message('debug', "Decryption failed for field $field: " . $e->getMessage());
                    }
                }
            }
        };

        // Determine if it is a single row or array of rows
        $isSingleton = isset($data['singleton']) ? $data['singleton'] : !isset($data['data'][0]);

        if ($isSingleton) {
            // Single result
            $decryptRow($data['data']);
        } else {
            // Multiple results
            foreach ($data['data'] as &$row) {
                $decryptRow($row);
            }
        }
        
        return $data;
    }
    
    // Default columns to fetch for the email dashboard/detail
    protected $standardColumns = 'emails.*';

    public function __construct()
    {
        parent::__construct();
    }

    public function withDetails()
    {
        return $this->select($this->standardColumns . ', unit_kerja.nama_unit_kerja as unit_kerja_name, parent_unit_kerja.nama_unit_kerja as parent_unit_kerja_name, status_asn.nama_status_asn as status_asn, eselon.nama_eselon as eselon_name')
            ->join('unit_kerja', 'unit_kerja.id = emails.unit_kerja_id', 'left')
            ->join('unit_kerja as parent_unit_kerja', 'parent_unit_kerja.id = unit_kerja.parent_id', 'left')
            ->join('status_asn', 'status_asn.id = emails.status_asn_id', 'left')
            ->join('eselon', 'eselon.id = emails.eselon_id', 'left');
    }

    public function getPimpinanDesaBuilder()
    {
        return $this->withDetails()
            ->where('pimpinan_desa', 1)
            ->where('unit_kerja.nama_unit_kerja NOT LIKE', '%Kelurahan%');
    }

    public function getPimpinanBuilder()
    {
        return $this->withDetails()
            ->where('pimpinan', 1);
    }

    public function email_exists($email)
    {
        return $this->where('email', $email)->countAllResults() > 0;
    }

    /**
     * Upserts an array of email data.
     *
     * @param array $emails
     * @return void
     */
    public function upsertBatch(array $emails)
    {
        if (empty($emails)) {
            return;
        }

        // Process in chunks to avoid extremely large SQL queries
        foreach (array_chunk($emails, 500) as $chunk) {
            $email_addresses = array_column($chunk, 'email');
            $existing_emails = $this->whereIn('email', $email_addresses)->findColumn('email') ?? [];
            $existing_emails_map = array_flip($existing_emails);

            $to_insert = [];
            $to_update = [];

            foreach ($chunk as $emailData) {
                $data = [
                    'email' => $emailData['email'],
                    'domain' => $emailData['domain'] ?? null,
                    'mtime' => $emailData['mtime'] ?? null,
                    'suspended_login' => $emailData['suspended_login'] ?? ($emailData['suspended'] ?? 0),
                    'diskquota' => $emailData['diskquota'] ?? null,
                    'humandiskquota' => $emailData['humandiskquota'] ?? null,
                    '_diskquota' => $emailData['_diskquota'] ?? null,
                    'diskused' => $emailData['diskused'] ?? null,
                    'humandiskused' => $emailData['humandiskused'] ?? null,
                    '_diskused' => $emailData['_diskused'] ?? null,
                    'diskusedpercent' => $emailData['diskusedpercent'] ?? null,
                    'diskusedpercent_float' => $emailData['diskusedpercent_float'] ?? null,
                    'user' => explode('@', $emailData['email'])[0] ?? null,
                ];

                if (isset($existing_emails_map[$emailData['email']])) {
                    // Don't update unit_kerja, identity, and password during sync
                    unset($data['unit_kerja_id']);
                    unset($data['password']);
                    unset($data['nik']);
                    unset($data['nip']);
                    unset($data['name']);
                    $to_update[] = $data;
                } else {
                    $to_insert[] = $data;
                }
            }

            if (!empty($to_insert)) {
                $this->insertBatch($to_insert);
            }

            if (!empty($to_update)) {
                $this->updateBatch($to_update, 'email');
            }
        }
    }
}
