<?php

namespace App\Controllers;

use App\Models\EmailModel;
use App\Models\UserModel;
use CodeIgniter\Controller;
use Exception;

class User extends BaseController
{
    public function checkEmailAvailability()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['available' => false, 'message' => 'Method not allowed.']);
        }

        $email = $this->request->getJSON()->email ?? null;

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(400)->setJSON(['available' => false, 'message' => 'A valid email is required.']);
        }

        try {
            // Check in local users table, if it exists
            try {
                $userModel = new UserModel();
                if ($userModel->db->tableExists($userModel->getTable())) {
                    $existingUser = $userModel->where('user_email', $email)->first();
                    if ($existingUser) {
                        return $this->response->setJSON(['available' => false, 'message' => 'Email is already registered to a local user.']);
                    }
                }
            } catch (Exception $e) {
                log_message('error', '[User Controller] Failed to check UserModel: ' . $e->getMessage());
                // Don't fail the request, just log that this check couldn't be performed.
            }

            // Check in cPanel-synced emails table
            $emailModel = new EmailModel();
            $existingCpanelEmail = $emailModel->where('email', $email)->first();

            if ($existingCpanelEmail) {
                return $this->response->setJSON(['available' => false, 'message' => 'Email already exists in the cPanel list.']);
            }

            return $this->response->setJSON(['available' => true, 'message' => 'Email is available.']);

        } catch (Exception $e) {
            log_message('error', '[User Controller] Check email availability failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['available' => false, 'message' => 'An unexpected error occurred while checking email availability.']);
        }
    }

    public function check_niknip()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['exists' => false, 'message' => 'Method not allowed.']);
        }

        $input = $this->request->getJSON();
        $nik = $input->nik ?? null;
        $nip = $input->nip ?? null;

        if (empty($nik) && empty($nip)) {
            return $this->response->setStatusCode(400)->setJSON(['exists' => false, 'message' => 'A NIK or NIP is required.']);
        }

        try {
            $emailModel = new EmailModel();
            $exists = false;
            $message = '';

            if (!empty($nik)) {
                $existingNik = $emailModel->where('nik', $nik)->first();
                if ($existingNik) {
                    $exists = true;
                    $message = 'NIK already exists in the database.';
                } else {
                    $message = 'NIK is available.';
                }
            } elseif (!empty($nip)) {
                $existingNip = $emailModel->where('nip', $nip)->first();
                if ($existingNip) {
                    $exists = true;
                    $message = 'NIP already exists in the database.';
                } else {
                    $message = 'NIP is available.';
                }
            }

            return $this->response->setJSON(['exists' => $exists, 'message' => $message]);

        } catch (Exception $e) {
            log_message('error', '[User Controller] Check NIK/NIP availability failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['exists' => false, 'message' => 'An unexpected error occurred while checking NIK/NIP availability.']);
        }
    }
}
