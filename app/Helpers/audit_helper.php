<?php

if (!function_exists('log_audit')) {
    function log_audit($action, $entity, $entity_id = null, $details = null)
    {
        try {
            $userId = session()->get('id') ?? null;
            $auditModel = new \App\Shared\Models\AuditLogModel();
            $auditModel->insert([
                'user_id'   => $userId,
                'action'    => $action,
                'entity'    => $entity,
                'entity_id' => $entity_id,
                'details'   => is_array($details) ? json_encode($details) : $details,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to insert audit log: ' . $e->getMessage());
        }
    }
}
