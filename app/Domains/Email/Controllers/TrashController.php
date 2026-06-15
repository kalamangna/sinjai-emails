<?php

namespace App\Domains\Email\Controllers;

use App\Domains\Email\Models\EmailModel;

use App\Shared\BaseController;

class TrashController extends BaseController
{
    public function index()
    {
        $emailModel = new EmailModel();
        
        // Only get deleted items
        $emails = $emailModel->withDetails()->onlyDeleted()->findAll();

        $data = [
            'title' => 'Manajemen Sampah (Soft Deleted)',
            'emails' => $emails,
        ];

        return view('email/trash', $data);
    }

    public function restore($id)
    {
        $emailModel = new EmailModel();
        
        // Fix: Use direct query builder to bypass global soft delete scope
        $emailModel->builder()->set('deleted_at', null)->where('id', $id)->update();
        
        helper('audit');
        log_audit('RESTORE', 'Email', $id, 'Restored email from trash');
        
        return redirect()->to('/email/trash')->with('success', 'Akun berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        $emailModel = new EmailModel();
        $cpanelApi = new \App\Shared\Libraries\CpanelApi();
        
        $email = $emailModel->onlyDeleted()->find($id);
        
        if ($email) {
            try {
                $cpanelApi->delete_email_account($email['email']);
            } catch (\Throwable $e) {
                // Ignore if it fails on cpanel, we just want to force delete it from DB
            }
            $emailModel->delete($id, true); // true for force delete
            
            $cache = \Config\Services::cache();
            $cache->delete('dashboard_summary_data_v3');
            $cache->delete('email_dashboard_summary');

            helper('audit');
            log_audit('FORCE_DELETE', 'Email', $id, 'Permanently deleted email: ' . $email['email']);
            
            return redirect()->to('/email/trash')->with('success', 'Akun berhasil dihapus permanen.');
        }
        
        return redirect()->to('/email/trash')->with('error', 'Akun tidak ditemukan.');
    }
}
