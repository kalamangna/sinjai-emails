<?php

namespace App\Controllers;

use App\Libraries\CpanelApi;
use App\Models\EmailModel;
use App\Models\AppSettingModel;
use App\Models\UnitKerjaModel;
use CodeIgniter\Controller;
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
            $all_emails = $this->emailModel->findAll();

            $filename = 'email_addresses_' . date('Y-m-d') . '.csv';

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');

            fputcsv($output, ['email']);

            foreach ($all_emails as $email) {
                fputcsv($output, [$email['email']]);
            }

            fclose($output);
            exit();
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
            $emails = $this->emailModel->where('unit_kerja', $unitKerjaName)->findAll();

            $data = [
                'unit_kerja_name' => $unitKerjaName,
                'emails' => $emails,
                'back_url' => site_url('email'),
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
