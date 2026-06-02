<?php

namespace App\Shared\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'action', 'entity', 'entity_id', 'details'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    
    public function getLogsWithUser($limit = 100)
    {
        return $this->select('audit_logs.*, users.name as user_name, users.username')
                    ->join('users', 'users.id = audit_logs.user_id', 'left')
                    ->orderBy('audit_logs.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}
