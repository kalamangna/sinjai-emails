<?php

namespace App\Controllers;

use App\Libraries\CpanelApi;
use App\Models\EmailModel;
use App\Models\AppSettingModel;
use App\Models\UnitKerjaModel;
use CodeIgniter\Controller;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;

class Email extends BaseController
{
    private $cpanelApi;
    private $emailModel;
    private $appSettingModel;
    private $unitKerjaModel;

    public function __construct()
    {
        $this->cpanelApi = new CpanelApi();
        $this->emailModel = new EmailModel();
        $this->appSettingModel = new AppSettingModel();
        $this->unitKerjaModel = new UnitKerjaModel();
    }

    public function index()
    {
        // load time helper
        helper('time');

        try {
            $search = $this->request->getGet('search');
            $status = $this->request->getGet('status');
            $perPage = $this->request->getGet('per_page') ?? 100;
            $sort = $this->request->getGet('sort') ?? 'newest'; // Default to 'newest' (mtime DESC)

            $builder = $this->emailModel;

            if (!empty($search)) {
                $builder->like('email', $search);
            }

            if (!empty($status)) {
                if ($status == 'active') {
                    $builder->where('suspended_login', 0);
                } elseif ($status == 'suspended') {
                    $builder->where('suspended_login', 1);
                }
            }

            $this->apply_sorting($builder, $sort);

            $emails = $builder->paginate($perPage);
            $pager = $builder->pager;

            $counts = $this->emailModel->allowCallbacks(false)->select('COUNT(*) as total_emails, SUM(CASE WHEN suspended_login = 0 THEN 1 ELSE 0 END) as active_count, SUM(CASE WHEN suspended_login = 1 THEN 1 ELSE 0 END) as suspended_count')->first();

            $lastSync = $this->appSettingModel->where('key', 'last_sync_time')->first();

            // Fetch all parent unit_kerja and count their total emails (including children)
            $unitKerjaList = $this->unitKerjaModel
                ->select('unit_kerja.id, unit_kerja.nama_unit_kerja, COUNT(emails.id) as email_count')
                ->join('unit_kerja as child', 'child.parent_id = unit_kerja.id', 'left')
                ->join('emails', 'emails.unit_kerja_id = unit_kerja.id OR emails.unit_kerja_id = child.id', 'left')
                ->where('unit_kerja.parent_id IS NULL')
                ->groupBy('unit_kerja.id, unit_kerja.nama_unit_kerja')
                ->findAll();

            $data = [
                'emails' => $emails,
                'total_emails' => $counts['total_emails'],
                'filtered_count' => $pager->getTotal(),
                'active_count' => $counts['active_count'],
                'suspended_count' => $counts['suspended_count'],
                'per_page' => $perPage,
                'sort' => $sort,
                'pagination' => $pager,
                'search' => $search,
                'status' => $status,
                'last_sync_time' => $lastSync['value'] ?? null,
                'unit_kerja_list' => $unitKerjaList,
            ];

            return view('email/index', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('email/error', $data);
        }
    }

    public function batch()
    {
        $data['unit_kerja'] = $this->unitKerjaModel->findAll();
        return view('email/batch', $data);
    }

    public function batch_update()
    {
        $data['unit_kerja'] = $this->unitKerjaModel->findAll();
        return view('email/batch_update', $data);
    }

    public function batch_update_process()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $data = $this->request->getJSON(true);
        if (empty($data) || !isset($data['identifiers']) || !is_array($data['identifiers'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No identifiers provided.']);
        }

        $mode = $data['mode'] ?? 'email';
        $identifiers = $data['identifiers'];
        $newNames = $data['names'] ?? [];
        $newPasswords = $data['passwords'] ?? [];
        $newNikNips = $data['nik_nips'] ?? [];
        $newUnitKerja = $data['unit_kerja'] ?? null;
        $newSubUnitKerja = $data['sub_unit_kerja'] ?? [];

        $results = [];
        foreach ($identifiers as $index => $identifier) {
            $emailRecord = null;
            if ($mode === 'email') {
                $emailRecord = $this->emailModel->where('email', $identifier)->first();
            } else { // nik_nip mode
                $emailRecord = $this->emailModel->where('nik_nip', $identifier)->first();
            }

            if (!$emailRecord) {
                $results[] = ['identifier' => $identifier, 'success' => false, 'message' => 'Record not found in local database.'];
                continue;
            }

            $updateData = [];
            if (isset($newNames[$index]) && !empty($newNames[$index])) {
                $updateData['name'] = $newNames[$index];
            }
            if (isset($newPasswords[$index]) && !empty($newPasswords[$index])) {
                $updateData['password'] = $newPasswords[$index];
            }
            if (isset($newNikNips[$index]) && !empty($newNikNips[$index])) {
                $updateData['nik_nip'] = $newNikNips[$index];
            }
            if (!empty($newUnitKerja)) {
                $updateData['unit_kerja'] = $newUnitKerja;
            }
            if (isset($newSubUnitKerja[$index]) && !empty($newSubUnitKerja[$index])) {
                $updateData['sub_unit_kerja'] = $newSubUnitKerja[$index];
            }

            if (empty($updateData)) {
                $results[] = ['identifier' => $identifier, 'success' => true, 'message' => 'No update data provided, skipped.'];
                continue;
            }

            try {
                $updated = $this->emailModel->update($emailRecord['id'], $updateData);
                if ($updated) {
                    $results[] = ['identifier' => $identifier, 'success' => true, 'message' => 'Successfully updated.'];
                } else {
                    $results[] = ['identifier' => $identifier, 'success' => false, 'message' => 'Failed to update (no changes or database error).'];
                }
            } catch (Exception $e) {
                $results[] = ['identifier' => $identifier, 'success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
        }

        return $this->response->setJSON(['success' => true, 'results' => $results]);
    }

    public function batch_create()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/email');
        }

        $data = $this->request->getJSON();
        if (empty($data)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No data provided.']);
        }

        $emails = array_map(function ($item) {
            return $item->email;
        }, $data);

        try {
            $existing_emails = $this->emailModel->whereIn('email', $emails)->findColumn('email');

            if (!empty($existing_emails)) {
                $message = 'The following email(s) already exist in the local database: ' . implode(', ', $existing_emails) . '. Please remove them from the list and try again.';
                return $this->response->setJSON(['success' => false, 'message' => $message]);
            }

            $results = [];
            foreach ($data as $item) {
                try {
                    $this->cpanelApi->create_email_account($item->email, $item->password, $item->quota);

                    // Save the new email with its unit_kerja to the local DB
                    $this->emailModel->insert([
                        'email'      => $item->email,
                        'user'       => explode('@', $item->email)[0],
                        'domain'     => explode('@', $item->email)[1],
                        'unit_kerja' => $item->unitKerja ?? null,
                        'password'   => $item->password ?? null,
                        'nik_nip'    => $item->nikNip ?? null,
                        'name'       => $item->name ?? null,
                    ]);

                    $results[] = ['email' => $item->email, 'success' => true];
                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();
                    if (strpos($errorMessage, 'already exists') !== false) {
                        $results[] = ['email' => $item->email, 'success' => false, 'message' => 'Email already exists on cPanel.'];
                    } else {
                        $results[] = ['email' => $item->email, 'success' => false, 'message' => $errorMessage];
                    }
                }
            }

            return $this->response->setJSON(['success' => true, 'results' => $results]);
        } catch (Exception $e) {
            log_message('error', 'Batch creation failed: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'An unexpected error occurred during the process.']);
        }
    }

    public function sync()
    {
        try {
            $all_emails = $this->cpanelApi->get_email_accounts_detailed();
            $this->emailModel->upsertBatch($all_emails);

            // Save last sync time
            $this->appSettingModel->where('key', 'last_sync_time')->set(['value' => date('Y-m-d H:i:s')])->update();
            if ($this->appSettingModel->affectedRows() == 0) {
                $this->appSettingModel->insert(['key' => 'last_sync_time', 'value' => date('Y-m-d H:i:s')]);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Email data synchronization from cPanel was successful.']);
        } catch (Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to synchronize: ' . $e->getMessage()]);
        }
    }

    public function detail($username)
    {
        try {
            // The beforeFind callback in EmailModel automatically joins unit_kerja
            $email_detail = $this->emailModel->where('user', $username)->first();

            if (!$email_detail) {
                throw new Exception('Email tidak ditemukan di database lokal.');
            }

            $data['email'] = $email_detail;
            $data['unit_kerja_options'] = $this->unitKerjaModel->where('parent_id IS NULL')->findAll();
            $data['back_url'] = site_url('email');

            // Get the full unit_kerja object for the email
            $currentUnitKerja = $this->unitKerjaModel->find($email_detail['unit_kerja_id']);
            $data['current_unit_kerja'] = $currentUnitKerja;

            // Find the parent unit if the current unit is a sub-unit
            if (!empty($currentUnitKerja['parent_id'])) {
                $data['parent_unit_kerja'] = $this->unitKerjaModel->find($currentUnitKerja['parent_id']);
            } else {
                $data['parent_unit_kerja'] = null;
            }

            return view('email/detail', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function update_unit_kerja($username)
    {
        if ($this->request->getMethod() === 'POST') {
            $unitKerjaId = $this->request->getPost('unit_kerja_id');

            $email = $this->emailModel->where('user', $username)->first();
            if (!$email) {
                return redirect()->to('email')->with('error', 'Email account not found.');
            }

            $updated = $this->emailModel->update($email['id'], ['unit_kerja_id' => $unitKerjaId]);

            if ($updated) {
                return redirect()->to('email/detail/' . $username)->with('success', 'Unit Kerja has been updated successfully.');
            } else {
                return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update Unit Kerja. No changes were detected.');
            }
        }

        return redirect()->to('email');
    }

    public function update_name($username)
    {
        if (strtolower($this->request->getMethod()) === 'post') {
            $newName = $this->request->getPost('name');

            $email = $this->emailModel->where('user', $username)->first();
            if (!$email) {
                return redirect()->to('email/detail/' . $username)->with('error', 'Email account not found.');
            }

            // Check if the new name is actually different from the current name
            if ($newName === $email['name']) {
                return redirect()->to('email/detail/' . $username)->with('info', 'No changes detected. Name is already up to date.');
            }

            // Validate newName (e.g., not empty)
            if (empty($newName)) {
                return redirect()->to('email/detail/' . $username)->with('error', 'Name cannot be empty.');
            }

            try {
                $updated = $this->emailModel->update($email['id'], ['name' => $newName]);

                if ($updated) {
                    return redirect()->to('email/detail/' . $username)->with('success', 'Name has been updated successfully.');
                } else {
                    // This case might not be reachable if the name check above is thorough,
                    // but it's good for robustness.
                    return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update name. The database did not report any changes.');
                }
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                log_message('error', 'Database error during name update: ' . $e->getMessage());
                return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update name due to a database error: ' . $e->getMessage());
            }
        }

        return redirect()->to('email/detail/' . $username);
    }

    public function unit_kerja_detail($unitKerjaId)
    {
        try {
            $unitKerja = $this->unitKerjaModel->find($unitKerjaId);
            if (!$unitKerja) {
                throw new Exception('Unit Kerja not found.');
            }

            // Find children of the current unit
            $children = $this->unitKerjaModel->where('parent_id', $unitKerjaId)->findAll();
            $childrenIds = array_column($children, 'id');

            // Find all emails belonging to this unit AND all its children
            $allUnitIds = array_merge([$unitKerjaId], $childrenIds);
            $emails = $this->emailModel->whereIn('unit_kerja_id', $allUnitIds)->orderBy('unit_kerja_name', 'ASC')->orderBy('name', 'ASC')->findAll();

            $data = [
                'unit_kerja' => $unitKerja,
                'parent_unit' => !empty($unitKerja['parent_id']) ? $this->unitKerjaModel->find($unitKerja['parent_id']) : null,
                'child_units' => $children,
                'emails' => $emails,
                'total_emails' => count($emails),
                'back_url' => site_url('email'),
            ];

            return view('email/unit_kerja_detail', $data);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('email/error', $data);
        }
    }

    public function export_unit_kerja_csv($unitKerjaId)
    {
        try {
            $unitKerja = $this->unitKerjaModel->find($unitKerjaId);

            if (!$unitKerja) {
                throw new Exception('Unit Kerja not found.');
            }

            $unitKerjaName = $unitKerja['nama_unit_kerja'];
            $emails = $this->emailModel->where('unit_kerja', $unitKerjaName)->findAll();
            $totalEmails = count($emails);
            $limit = 50;

            if ($totalEmails <= $limit) {
                // Original logic for a single file
                $filename = url_title($unitKerjaName, '_', true) . '.csv';

                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $filename . '"');

                $output = fopen('php://output', 'w');
                fputcsv($output, ['nama', 'emailAddress'], ',');
                foreach ($emails as $email) {
                    fputcsv($output, [$email['name'], $email['email']], ',');
                }
                fclose($output);
                exit();
            } else {
                // New logic for multiple files (ZIP archive)
                $zip = new \ZipArchive();
                $zipFileName = url_title($unitKerjaName, '_', true) . '.zip';
                $tempZipPath = WRITEPATH . 'uploads/' . $zipFileName;

                if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                    throw new Exception('Cannot create ZIP archive.');
                }

                $chunks = array_chunk($emails, $limit);
                $fileCount = 1;

                foreach ($chunks as $chunk) {
                    $csvFileName = url_title($unitKerjaName, '_', true) . '_part_' . $fileCount . '.csv';

                    // Using memory stream to avoid creating temporary CSV files on disk
                    $stream = fopen('php://memory', 'w+');
                    fputcsv($stream, ['nama', 'emailAddress'], ',');
                    foreach ($chunk as $email) {
                        fputcsv($stream, [$email['name'], $email['email']], ',');
                    }
                    rewind($stream);
                    $csvContent = stream_get_contents($stream);
                    fclose($stream);

                    $zip->addFromString($csvFileName, $csvContent);
                    $fileCount++;
                }

                $zip->close();

                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
                header('Content-Length: ' . filesize($tempZipPath));

                readfile($tempZipPath);

                // Clean up the temporary zip file
                unlink($tempZipPath);

                exit();
            }
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('templates/header') .
                view('email/error', $data) .
                view('templates/footer');
        }
    }

    public function export_unit_kerja_pdf($unitKerjaId)
    {
        try {
            $unitKerja = $this->unitKerjaModel->find($unitKerjaId);

            if (!$unitKerja) {
                throw new Exception('Unit Kerja not found.');
            }

            $unitKerjaName = $unitKerja['nama_unit_kerja'];
            $emails = $this->emailModel->where('unit_kerja', $unitKerjaName)->findAll();

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);

            // Fungsi esc() untuk keamanan
            if (!function_exists('esc')) {
                function esc($str)
                {
                    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
                }
            }

            $html = '<!DOCTYPE html>
                <html>
                <head>
                    <title>Akun Email - ' . esc($unitKerjaName) . '</title>
                    <style>
                        body { 
                            font-family: Arial, sans-serif; 
                            margin: 10px; 
                            font-size: 12px; 
                        }
                        h1 { color: #333; text-align: center; margin-bottom: 20px; }
                        table { 
                            width: 100%; 
                            border-collapse: collapse; 
                            margin-bottom: 20px; 
                        }
                        th, td { 
                            border: 1px solid #ddd; 
                            padding: 8px; 
                            text-align: left; 
                            word-wrap: break-word;
                            overflow-wrap: break-word;
                        }
                        th { background-color: #f2f2f2; }
                        
                        /* Kolom No. */
                        th:nth-child(1), td:nth-child(1) { 
                            text-align: center;
                        } 
                        
                        /* Kolom NIK/NIP */
                        th:nth-child(2), td:nth-child(2) { }
                        
                        /* Kolom Nama */
                        th:nth-child(3), td:nth-child(3) { } 
                        
                        /* Kolom Email */
                        th:nth-child(4), td:nth-child(4) { }
                        
                        /* Kolom Password */
                        th:nth-child(5), td:nth-child(5) { }

                        .footer { 
                            text-align: center;
                            font-size: 10px; 
                            color: #777; 
                            position: fixed; 
                            bottom: 20px; 
                            right: 20px; 
                            left: 20px;
                            line-height: 1.4; 
                        }
                        
                        .instruction {
                            text-align: center;
                            font-weight: bold;
                            font-size: 1.1em;
                            margin-bottom: 25px;
                            padding: 10px;
                            border: 1px solid #ddd;
                            background-color: #f9f9f9;
                        }
                    </style>
                </head>
                <body>
                    <h1>Daftar Akun Email<br>' . esc($unitKerjaName) . '</h1>

                    <p class="instruction">
                        Untuk AKTIVASI AKUN, login menggunakan EMAIL dan PASSWORD melalui halaman sinjaikab.go.id/webmail
                    </p>

                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>NIK/NIP</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Password</th>
                            </tr>
                        </thead>
                        <tbody>';

            // MODIFIKASI PHP: Inisialisasi nomor
            $nomor = 1;
            foreach ($emails as $email) {
                $html .= '<tr>
                                <td>' . $nomor . '</td> 
                                <td>' . esc($email['nik_nip'] ?? 'N/A') . '</td>
                                <td>' . esc($email['name'] ?? 'N/A') . '</td>
                                <td>' . esc($email['email'] ?? 'N/A') . '</td>
                                <td>' . esc($email['password'] ?? 'N/A') . '</td>
                            </tr>';
                // MODIFIKASI PHP: Increment nomor
                $nomor++;
            }

            $html .= '</tbody>
                    </table>
                    
                    <div class="footer">
                        Bidang Aplikasi dan Informatika<br>
                        Dinas Komunikasi Informatika dan Persandian<br>
                        Kabupaten Sinjai<br><br>
                        Dibuat pada ' . date('Y-m-d H:i:s') . '
                    </div>
                </body>
                </html>';

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'email_accounts_' . url_title($unitKerjaName, '_', true) . '.pdf';
            $dompdf->stream($filename, ["Attachment" => true]);
            exit();
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('templates/header') .
                view('email/error', $data) .
                view('templates/footer');
        }
    }

    private function apply_sorting($builder, $sort_type)
    {
        switch ($sort_type) {
            case 'newest':
                $builder->orderBy('mtime', 'DESC');
                break;
            case 'oldest':
                $builder->orderBy('mtime', 'ASC');
                break;
            case 'email_asc':
                $builder->orderBy('email', 'ASC');
                break;
            case 'email_desc':
                $builder->orderBy('email', 'DESC');
                break;
            case 'unit_kerja_asc':
                $builder->orderBy('unit_kerja.nama_unit_kerja', 'ASC');
                break;
            case 'unit_kerja_desc':
                $builder->orderBy('unit_kerja.nama_unit_kerja', 'DESC');
                break;
            case 'usage_asc':
                $builder->orderBy('diskusedpercent_float', 'ASC');
                break;
            case 'usage_desc':
                $builder->orderBy('diskusedpercent_float', 'DESC');
                break;
            default:
                $builder->orderBy('mtime', 'DESC');
                break;
        }
    }

    public function delete($id)
    {
        try {
            $email = $this->emailModel->find($id);

            if (!$email) {
                return redirect()->to('email')->with('error', 'Email account not found.');
            }

            // Delete from cPanel
            $this->cpanelApi->delete_email_account($email['email']);

            // Delete from local database
            $this->emailModel->delete($id);

            return redirect()->to('email')->with('success', 'Email account ' . $email['email'] . ' has been deleted successfully.');
        } catch (Exception $e) {
            // If cPanel deletion fails, we still might want to delete from local DB or handle differently
            // For now, just log the error and redirect with a generic error message.
            log_message('error', 'Failed to delete email: ' . $e->getMessage());
            // Optionally, attempt to delete from local DB even if cPanel fails
            $this->emailModel->delete($id);
            return redirect()->to('email')->with('error', 'Failed to delete email account from cPanel, but removed from local list. Please check cPanel manually.');
        }
    }

    public function create_single_email()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $data = $this->request->getJSON(true);
        if (empty($data) || !isset($data['email'])) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'No data provided.']);
        }

        try {
            // Check if email exists in local DB first
            $existing_email = $this->emailModel->where('email', $data['email'])->first();
            if ($existing_email) {
                return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'Email already exists in local database.']);
            }

            // Create on cPanel
            $this->cpanelApi->create_email_account($data['email'], $data['password'], $data['quota'] ?? 1024);

            // Save to local DB
            $this->emailModel->insert([
                'email'      => $data['email'],
                'user'       => explode('@', $data['email'])[0],
                'domain'     => explode('@', $data['email'])[1],
                'unit_kerja' => $data['unitKerja'] ?? null,
                'password'   => $data['password'] ?? null,
                'nik_nip'    => $data['nikNip'] ?? null,
                'name'       => $data['name'] ?? null,
            ]);

            return $this->response->setJSON(['success' => true, 'email' => $data['email']]);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            // Check for specific cPanel error messages
            if (strpos($errorMessage, 'already exists') !== false) {
                return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'Email already exists on cPanel.']);
            }
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $errorMessage]);
        }
    }
}
