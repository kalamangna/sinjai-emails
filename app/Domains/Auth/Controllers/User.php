<?php

namespace App\Domains\Auth\Controllers;

use App\Shared\BaseController;
use App\Domains\Email\Models\EmailModel;
use App\Domains\Auth\Models\UserModel;
use CodeIgniter\Controller;
use Exception;

class User extends BaseController
{
    public function changePassword()
    {
        $data = [
            'title' => 'Ganti Password'
        ];
        return view('auth/change_password', $data);
    }

    public function updatePassword()
    {
        $session = session();
        $userModel = new UserModel();

        $oldPassword = $this->request->getPost('old_password');
        $newPassword = $this->request->getPost('new_password');
        $confirmPassword = $this->request->getPost('confirm_password');

        if ($newPassword !== $confirmPassword) {
            return redirect()->back()->with('error', 'Konfirmasi password baru tidak cocok.');
        }

        $user = $userModel->find($session->get('id'));

        if (!password_verify($oldPassword, $user['password'])) {
            return redirect()->back()->with('error', 'Password lama tidak valid.');
        }

        $userModel->update($user['id'], [
            'password' => $newPassword
        ]);

        return redirect()->to('/')->with('success', 'Password berhasil diubah.');
    }

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
            } catch (\Throwable $e) {
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

        } catch (\Throwable $e) {
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
        $nik = isset($input->nik) ? str_replace([' ', '.', '-', '\''], '', $input->nik) : null;
        $nip = isset($input->nip) ? str_replace([' ', '.', '-', '\''], '', $input->nip) : null;

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

        } catch (\Throwable $e) {
            log_message('error', '[User Controller] Check NIK/NIP availability failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['exists' => false, 'message' => 'An unexpected error occurred while checking NIK/NIP availability.']);
        }
    }

    public function batch_check_availability()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Method not allowed.']);
        }

        $data = [];
        if (strpos($this->request->getHeaderLine('Content-Type'), 'application/json') !== false) {
            try {
                $data = $this->request->getJSON(true) ?? [];
            } catch (\Exception $e) {}
        }
        if (empty($data)) {
            $data = $this->request->getPost() ?? [];
        }
        
        $base64Payload = $this->request->getPost('payload');
        if (!empty($base64Payload)) {
            $decodedJson = base64_decode($base64Payload);
            if ($decodedJson !== false) {
                $parsedData = json_decode($decodedJson, true);
                if (is_array($parsedData)) {
                    $data = array_merge($data, $parsedData);
                }
            }
        }
        
        // Handle File payload WAF bypass (Ultimate Bypass)
        $payloadFile = $this->request->getFile('payload_file');
        if ($payloadFile && $payloadFile->isValid()) {
            $fileContent = file_get_contents($payloadFile->getTempName());
            if ($fileContent) {
                $parsedData = json_decode($fileContent, true);
                if (is_array($parsedData)) {
                    $data = array_merge($data, $parsedData);
                }
            }
        }

        $emails = $data['emails'] ?? [];
        $niks = array_map(fn($n) => str_replace([' ', '.', '-', '\''], '', $n), array_filter($data['niks'] ?? []));
        $nips = array_map(fn($n) => str_replace([' ', '.', '-', '\''], '', $n), array_filter($data['nips'] ?? []));

        $emailModel = new EmailModel();
        
        $results = [
            'emails' => [],
            'niks' => [],
            'nips' => []
        ];

        try {
            if (!empty($emails)) {
                $existingEmails = $emailModel->whereIn('email', $emails)->findColumn('email') ?: [];
                
                // Also check UserModel if it exists
                $existingUserEmails = [];
                try {
                    $userModel = new UserModel();
                    if ($userModel->db->tableExists($userModel->getTable())) {
                        $existingUserEmails = $userModel->whereIn('user_email', $emails)->findColumn('user_email') ?: [];
                    }
                } catch (\Throwable $e) {}

                $allExistingEmails = array_unique(array_merge($existingEmails, $existingUserEmails));
                $allExistingEmailsMap = array_flip($allExistingEmails);

                foreach ($emails as $email) {
                    $results['emails'][$email] = isset($allExistingEmailsMap[$email]);
                }
            }

            if (!empty($niks)) {
                $nikHashes = array_map(fn($n) => $n, $niks);
                $existingNikHashes = $emailModel->whereIn('nik', $nikHashes)->findColumn('nik') ?: [];
                $existingHashesMap = array_flip($existingNikHashes);
                foreach ($niks as $nik) {
                    $results['niks'][$nik] = isset($existingHashesMap[$nik]);
                }
            }

            if (!empty($nips)) {
                $nipHashes = array_map(fn($n) => $n, $nips);
                $existingNipHashes = $emailModel->whereIn('nip', $nipHashes)->findColumn('nip') ?: [];
                $existingHashesMap = array_flip($existingNipHashes);
                foreach ($nips as $nip) {
                    $results['nips'][$nip] = isset($existingHashesMap[$nip]);
                }
            }

            return $this->response->setJSON(['success' => true, 'results' => $results]);
        } catch (\Throwable $e) {
            log_message('error', '[User Controller] Batch check failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
