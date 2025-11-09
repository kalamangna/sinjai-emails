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
}
