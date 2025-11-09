<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailModel extends Model
{
    protected $table = 'emails';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'email',
        'domain',
        'unit_kerja',
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
    ];
    protected $useTimestamps = true;

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

        $email_addresses = array_column($emails, 'email');
        $existing_emails = $this->whereIn('email', $email_addresses)->findColumn('email') ?? [];
        $existing_emails_map = array_flip($existing_emails);

        $to_insert = [];
        $to_update = [];

        foreach ($emails as $emailData) {
            $data = [
                'email' => $emailData['email'],
                'domain' => $emailData['domain'] ?? null,
                'mtime' => $emailData['mtime'] ?? null,
                'suspended_login' => $emailData['suspended_login'] ?? 0,
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
                // Don't update unit_kerja during sync
                unset($data['unit_kerja']);
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
