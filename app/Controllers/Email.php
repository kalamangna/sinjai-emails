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
            $sort = $this->request->getGet('sort') ?? 'newest';

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

            $counts = $this->emailModel->select('COUNT(*) as total_emails, SUM(CASE WHEN suspended_login = 0 THEN 1 ELSE 0 END) as active_count, SUM(CASE WHEN suspended_login = 1 THEN 1 ELSE 0 END) as suspended_count')->first();

            $lastSync = $this->appSettingModel->where('key', 'last_sync_time')->first();

            // Fetch all unit_kerja and their email counts
            $unitKerjaList = $this->unitKerjaModel->select('unit_kerja.id, unit_kerja.nama_unit_kerja, COUNT(emails.id) as email_count')
                ->join('emails', 'emails.unit_kerja = unit_kerja.nama_unit_kerja', 'left')
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

            return view('templates/header') .
                view('email/index', $data) .
                view('templates/footer');
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return view('templates/header') .
                view('email/error', $data) .
                view('templates/footer');
        }
    }

    public function batch()
    {
        $data['unit_kerja'] = $this->unitKerjaModel->findAll();
        return view('templates/header') .
            view('email/batch', $data) .
            view('templates/footer');
    }

    public function batch_update()
    {
        $data['unit_kerja'] = $this->unitKerjaModel->findAll();
        return view('templates/header') .
            view('email/batch_update', $data) .
            view('templates/footer');
    }

    public function batch_update_process()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $data = $this->request->getJSON();
        if (empty($data) || !isset($data->emails) || !is_array($data->emails)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No email data provided.']);
        }

        $emailsToUpdate = $data->emails;
        $newNames = $data->names ?? [];
        $newPasswords = $data->passwords ?? [];
        $newNikNips = $data->nik_nips ?? [];
        $newUnitKerja = $data->unit_kerja ?? null;

        $results = [];
        foreach ($emailsToUpdate as $index => $emailAddress) {
            $emailRecord = $this->emailModel->where('email', $emailAddress)->first();

            if (!$emailRecord) {
                $results[] = ['email' => $emailAddress, 'success' => false, 'message' => 'Email not found in local database.'];
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

            if (empty($updateData)) {
                $results[] = ['email' => $emailAddress, 'success' => false, 'message' => 'No update data provided.'];
                continue;
            }

            try {
                $updated = $this->emailModel->update($emailRecord['id'], $updateData);
                if ($updated) {
                    $results[] = ['email' => $emailAddress, 'success' => true, 'message' => 'Successfully updated.'];
                } else {
                    $results[] = ['email' => $emailAddress, 'success' => false, 'message' => 'Failed to update (no changes or database error).'];
                }
            } catch (Exception $e) {
                $results[] = ['email' => $emailAddress, 'success' => false, 'message' => 'Database error: ' . $e->getMessage()];
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
            $email_detail = $this->emailModel->where('user', $username)->first();

            if (!$email_detail) {
                throw new Exception('Email tidak ditemukan di database lokal.');
            }

            $data['email'] = $email_detail;
            $data['unit_kerja_options'] = $this->unitKerjaModel->findAll();
            $data['back_url'] = site_url('email');

            // Get the ID of the current unit_kerja if it exists
            $currentUnitKerja = $this->unitKerjaModel->where('nama_unit_kerja', $email_detail['unit_kerja'])->first();
            $data['current_unit_kerja_id'] = $currentUnitKerja['id'] ?? null;

            return view('templates/header') .
                view('email/detail', $data) .
                view('templates/footer');
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('templates/header') .
                view('email/error', $data) .
                view('templates/footer');
        }
    }

    public function update_unit_kerja($username)
    {

        if ($this->request->getMethod() === 'POST') {
            $unitKerja = $this->request->getPost('unit_kerja');

            $email = $this->emailModel->where('user', $username)->first();
            if (!$email) {
                return redirect()->to('email')->with('error', 'Email account not found.');
            }

            $updated = $this->emailModel->update($email['id'], ['unit_kerja' => $unitKerja]);

            if ($updated) {
                return redirect()->to('email/detail/' . $username)->with('success', 'Unit Kerja has been updated successfully.');
            } else {
                return redirect()->to('email/detail/' . $username)->with('error', 'Failed to update Unit Kerja.');
            }
        }

        return redirect()->to('email');
    }

    public function export_csv()
    {
        try {
            $all_data = $this->emailModel->findAll();
            $totalEmails = count($all_data);
            $limit = 50;

            if ($totalEmails <= $limit) {
                $filename = 'email_addresses_' . date('Y-m-d') . '.csv';

                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $filename . '"');

                $output = fopen('php://output', 'w');
                fputcsv($output, ['name', 'email'], ','); // Fixed headers and delimiter

                foreach ($all_data as $row) {
                    fputcsv($output, [$row['name'], $row['email']], ','); // Fixed keys
                }

                fclose($output);
                exit();
            } else {
                $zip = new \ZipArchive();
                $zipFileName = 'email_addresses_' . date('Y-m-d') . '.zip';
                $tempZipPath = WRITEPATH . 'uploads/' . $zipFileName;

                if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                    throw new Exception('Cannot create ZIP archive.');
                }

                $chunks = array_chunk($all_data, $limit);
                $fileCount = 1;

                foreach ($chunks as $chunk) {
                    $csvFileName = 'email_addresses_part_' . $fileCount . '.csv';
                    
                    $stream = fopen('php://memory', 'w+');
                    fputcsv($stream, ['name', 'email'], ','); // Fixed headers and delimiter
                    foreach ($chunk as $row) {
                        fputcsv($stream, [$row['name'], $row['email']], ','); // Fixed keys
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

    public function unit_kerja_detail($unitKerjaId)
    {
        try {
            $unitKerja = $this->unitKerjaModel->find($unitKerjaId);

            if (!$unitKerja) {
                throw new Exception('Unit Kerja not found.');
            }

            $unitKerjaName = $unitKerja['nama_unit_kerja'];
            $emails = $this->emailModel->where('unit_kerja', $unitKerjaName)->orderBy('name', 'ASC')->findAll();
            $totalEmails = count($emails);

            $data = [
                'unit_kerja_name' => $unitKerjaName,
                'emails' => $emails,
                'back_url' => site_url('email'),
                'unit_kerja_id' => $unitKerjaId,
                'total_emails' => $totalEmails,
            ];

            return view('templates/header') .
                view('email/unit_kerja_detail', $data) .
                view('templates/footer');
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            $data['back_url'] = site_url('email');
            return view('templates/header') .
                view('email/error', $data) .
                view('templates/footer');
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
                $filename = 'export_' . url_title($unitKerjaName, '_', true) . '_' . date('Y-m-d') . '.csv';

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
                $zipFileName = 'export_' . url_title($unitKerjaName, '_', true) . '_' . date('Y-m-d') . '.zip';
                $tempZipPath = WRITEPATH . 'uploads/' . $zipFileName;

                if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                    throw new Exception('Cannot create ZIP archive.');
                }

                $chunks = array_chunk($emails, $limit);
                $fileCount = 1;

                foreach ($chunks as $chunk) {
                    $csvFileName = 'export_' . url_title($unitKerjaName, '_', true) . '_part_' . $fileCount . '.csv';
                    
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
            case 'usage_asc':
                $builder->orderBy('diskusedpercent_float', 'ASC');
                break;
            case 'usage_desc':
                $builder->orderBy('diskusedpercent_float', 'DESC');
                break;
            case 'status':
                $builder->orderBy('suspended_login', 'ASC');
                break;
            default:
                $builder->orderBy('mtime', 'DESC');
                break;
        }
    }
}
