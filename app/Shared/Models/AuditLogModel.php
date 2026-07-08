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

    public function applyFilters(string $action = '', string $entity = '', string $search = ''): static
    {
        $this->select('audit_logs.*, users.name as user_name, users.username')
             ->join('users', 'users.id = audit_logs.user_id', 'left')
             ->orderBy('audit_logs.created_at', 'DESC');

        if ($action !== '') {
            $this->where('audit_logs.action', $action);
        }

        if ($entity !== '') {
            $this->where('audit_logs.entity', $entity);
        }

        if ($search !== '') {
            $this->groupStart()
                 ->like('users.name', $search)
                 ->orLike('users.username', $search)
                 ->groupEnd();
        }

        return $this;
    }

    public function getDistinctActions(): array
    {
        return $this->db->table('audit_logs')
                        ->distinct()
                        ->select('action')
                        ->orderBy('action', 'ASC')
                        ->get()->getResultArray();
    }

    public function getDistinctEntities(): array
    {
        return $this->db->table('audit_logs')
                        ->distinct()
                        ->select('entity')
                        ->orderBy('entity', 'ASC')
                        ->get()->getResultArray();
    }

    public function getActionSummary(): array
    {
        return $this->select('action, COUNT(id) as count')
                    ->groupBy('action')
                    ->orderBy('count', 'DESC')
                    ->findAll();
    }

    public function getEntitySummary(): array
    {
        return $this->select('entity, COUNT(id) as count')
                    ->groupBy('entity')
                    ->orderBy('count', 'DESC')
                    ->findAll();
    }
}

