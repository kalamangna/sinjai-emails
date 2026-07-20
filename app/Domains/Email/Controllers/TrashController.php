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
            'title' => 'Kotak Sampah',
            'emails' => $emails,
        ];

        return view('email/trash', $data);
    }

    public function restore($id)
    {
        try {
            $emailModel = new EmailModel();
            
            // Fix: Use direct query builder to bypass global soft delete scope
            $email = $emailModel->onlyDeleted()->find($id);
            
            if (!$email) {
                return redirect()->to('/email/trash')->with('error', 'Akun tidak ditemukan di tempat sampah.');
            }

            // 1. Mandatory Unsuspend in cPanel
            $cpanelApi = new \App\Shared\Libraries\CpanelApi();
            $cpanelApi->unsuspend_email_login($email['email']);

            // 2. Restore DB record
            $emailModel->builder()
                       ->set('deleted_at', null)
                       ->set('suspended_login', 0)
                       ->set('pensiun_at', null)
                       ->where('id', $id)
                       ->update();
            
            helper('audit');
            log_audit('RESTORE', 'Email', $id, 'Restored email from trash: ' . $email['email']);
            
            // Send Telegram Notification
            try {
                $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
                $builder->setTitle('AKUN EMAIL DIPULIHKAN', '✅')
                        ->addDivider()
                        ->addUserProfile(
                            $email['name'] ?? '',
                            !empty($email['nip']) ? 'NIP: ' . $email['nip'] : (!empty($email['nik']) ? 'NIK: ' . $email['nik'] : ''),
                            '',
                            '',
                            $email['email']
                        )
                        ->addText("✅ <i>Akses login cPanel di-unsuspend dan akun telah dikembalikan dari Tempat Sampah.</i>");

                $telegram = new \App\Shared\Libraries\TelegramLibrary();
                $telegram->sendMessage($builder->build());
            } catch (\Throwable $te) {
                log_message('error', 'Failed to send Telegram notification for restore: ' . $te->getMessage());
            }

            return redirect()->to('/email/trash')->with('success', 'Akun berhasil dipulihkan dan login cPanel telah di-unsuspend.');
        } catch (\Throwable $e) {
            return redirect()->to('/email/trash')->with('error', 'Gagal mempulihkan akun: ' . $e->getMessage());
        }
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
            
            \App\Shared\Services\CacheService::invalidateDashboard();

            helper('audit');
            log_audit('FORCE_DELETE', 'Email', $id, 'Permanently deleted email: ' . $email['email']);
            
            // Send Telegram Notification
            try {
                $builder = new \App\Shared\Libraries\TelegramMessageBuilder();
                $builder->setTitle('AKUN EMAIL DIHAPUS PERMANEN', '🔥')
                        ->addDivider()
                        ->addUserProfile(
                            $email['name'] ?? '',
                            !empty($email['nip']) ? 'NIP: ' . $email['nip'] : (!empty($email['nik']) ? 'NIK: ' . $email['nik'] : ''),
                            '',
                            '',
                            $email['email']
                        )
                        ->addText("⚠️ <i>Data telah dihapus secara permanen dari Database dan cPanel.</i>");

                $telegram = new \App\Shared\Libraries\TelegramLibrary();
                $telegram->sendMessage($builder->build());
            } catch (\Throwable $te) {
                log_message('error', 'Failed to send Telegram notification for force delete: ' . $te->getMessage());
            }
            
            return redirect()->to('/email/trash')->with('success', 'Akun berhasil dihapus permanen.');
        }
        
        return redirect()->to('/email/trash')->with('error', 'Akun tidak ditemukan.');
    }
}
