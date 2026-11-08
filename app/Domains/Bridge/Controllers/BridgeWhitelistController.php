<?php

namespace App\Domains\Bridge\Controllers;

use App\Domains\Bridge\Models\BridgeWhitelistModel;
use App\Shared\BaseController;

class BridgeWhitelistController extends BaseController
{
    protected BridgeWhitelistModel $whitelistModel;

    public function __construct()
    {
        $this->whitelistModel = new BridgeWhitelistModel();
    }

    /**
     * Tampilan Antarmuka Daftar Whitelist Bridge
     * GET /bridge/whitelist
     */
    public function index()
    {
        $search       = trim($this->request->getGet('search') ?? '');
        $filterStatus = trim($this->request->getGet('status') ?? '');

        // 1. Ringkasan Metrik
        $totalDomains    = $this->whitelistModel->countAllResults(false);
        $activeDomains   = (clone $this->whitelistModel)->where('is_active', 1)->countAllResults();
        $inactiveDomains = (clone $this->whitelistModel)->where('is_active', 0)->countAllResults();

        // 2. Kueri Data Whitelist
        $query = $this->whitelistModel;

        if ($search !== '') {
            $query->groupStart()
                ->like('domain', $search)
                ->orLike('label', $search)
                ->orLike('ip_address', $search)
                ->groupEnd();
        }

        if ($filterStatus !== '') {
            if ($filterStatus === '1') {
                $query->where('is_active', 1);
            } elseif ($filterStatus === '0') {
                $query->where('is_active', 0);
            }
        }

        $perPage = 20;
        $items   = $query->orderBy('created_at', 'DESC')->paginate($perPage);
        $pager   = $this->whitelistModel->pager;

        return view('bridge/whitelist', [
            'title'           => 'Whitelist Bridge',
            'items'           => $items,
            'pager'           => $pager,
            'search'          => $search,
            'filterStatus'    => $filterStatus,
            'totalDomains'    => $totalDomains,
            'activeDomains'   => $activeDomains,
            'inactiveDomains' => $inactiveDomains,
        ]);
    }

    /**
     * Tambah Entri Whitelist Domain Baru
     * POST /bridge/whitelist/store
     */
    public function store()
    {
        $rules = [
            'label'      => 'required|min_length[3]|max_length[255]',
            'domain'     => 'permit_empty|max_length[255]',
            'ip_address' => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Validasi gagal. Pastikan data instansi terisi benar.');
        }

        $label     = trim($this->request->getPost('label'));
        $rawDomain = trim($this->request->getPost('domain') ?? '');
        $rawIp     = trim($this->request->getPost('ip_address') ?? '');

        if (empty($rawDomain) && empty($rawIp)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Domain atau IP server wajib diisi minimal salah satu.');
        }

        $cleanHost = !empty($rawDomain) ? BridgeWhitelistModel::cleanHost($rawDomain) : '';
        $cleanIps  = !empty($rawIp) ? BridgeWhitelistModel::cleanIpList($rawIp) : null;

        // Jika domain tidak diisi tetapi IP diisi, gunakan IP sebagai domain identitas
        if (empty($cleanHost) && !empty($cleanIps)) {
            $firstIp = explode(',', $cleanIps)[0];
            $cleanHost = trim($firstIp);
        }

        // Cek duplikasi domain
        $existing = $this->whitelistModel->where('domain', $cleanHost)->first();
        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Domain {$cleanHost} sudah terdaftar pada whitelist.");
        }

        // Jika alamat IP tidak diisi manual dan domain bukan IP, lakukan resolusi DNS otomatis
        if (empty($cleanIps) && !filter_var($cleanHost, FILTER_VALIDATE_IP)) {
            $cleanIps = BridgeWhitelistModel::resolveHostToIps($cleanHost);
        } elseif (empty($cleanIps) && filter_var($cleanHost, FILTER_VALIDATE_IP)) {
            $cleanIps = $cleanHost;
        }

        $data = [
            'label'            => $label,
            'domain'           => $cleanHost,
            'ip_address'       => $cleanIps,
            'is_active'        => 1,
            'last_resolved_at' => $cleanIps ? date('Y-m-d H:i:s') : null,
        ];

        $this->whitelistModel->insert($data);

        if ($cleanIps) {
            return redirect()->to(site_url('bridge/whitelist'))
                ->with('success', "Domain {$cleanHost} berhasil ditambahkan dengan IP {$cleanIps}.");
        }

        return redirect()->to(site_url('bridge/whitelist'))
            ->with('info', "Domain {$cleanHost} berhasil disimpan. DNS belum berhasil diresolusi ke alamat IP.");
    }

    /**
     * Perbarui Data Entri Whitelist
     * POST /bridge/whitelist/update/(:num)
     */
    public function update(int $id)
    {
        $entry = $this->whitelistModel->find($id);
        if (!$entry) {
            return redirect()->to(site_url('bridge/whitelist'))
                ->with('error', 'Data whitelist tidak ditemukan.');
        }

        $rules = [
            'label'      => 'required|min_length[3]|max_length[255]',
            'domain'     => 'permit_empty|max_length[255]',
            'ip_address' => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Validasi gagal. Pastikan data terisi benar.');
        }

        $label     = trim($this->request->getPost('label'));
        $rawDomain = trim($this->request->getPost('domain') ?? '');
        $rawIp     = trim($this->request->getPost('ip_address') ?? '');
        $isActive  = $this->request->getPost('is_active') ? 1 : 0;

        if (empty($rawDomain) && empty($rawIp)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Domain atau IP server wajib diisi minimal salah satu.');
        }

        $cleanHost = !empty($rawDomain) ? BridgeWhitelistModel::cleanHost($rawDomain) : '';
        $cleanIps  = !empty($rawIp) ? BridgeWhitelistModel::cleanIpList($rawIp) : null;

        if (empty($cleanHost) && !empty($cleanIps)) {
            $firstIp = explode(',', $cleanIps)[0];
            $cleanHost = trim($firstIp);
        }

        // Cek duplikasi dengan entri lain
        $existing = $this->whitelistModel->where('domain', $cleanHost)->where('id !=', $id)->first();
        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Domain {$cleanHost} sudah digunakan oleh data lain.");
        }

        $lastResolved = $entry['last_resolved_at'];

        if (!empty($cleanIps)) {
            $resolvedIps  = $cleanIps;
            $lastResolved = date('Y-m-d H:i:s');
        } elseif ($cleanHost !== $entry['domain'] || empty($entry['ip_address'])) {
            $resolvedIps = BridgeWhitelistModel::resolveHostToIps($cleanHost);
            if ($resolvedIps) {
                $lastResolved = date('Y-m-d H:i:s');
            }
        } else {
            $resolvedIps = $entry['ip_address'];
        }

        $this->whitelistModel->update($id, [
            'label'            => $label,
            'domain'           => $cleanHost,
            'ip_address'       => $resolvedIps,
            'is_active'        => $isActive,
            'last_resolved_at' => $lastResolved,
        ]);

        return redirect()->to(site_url('bridge/whitelist'))
            ->with('success', "Data domain {$cleanHost} berhasil diperbarui.");
    }

    /**
     * Ubah Status Aktif atau Nonaktif Entri Whitelist
     * POST /bridge/whitelist/toggle/(:num)
     */
    public function toggle(int $id)
    {
        $entry = $this->whitelistModel->find($id);
        if (!$entry) {
            return redirect()->to(site_url('bridge/whitelist'))
                ->with('error', 'Data whitelist tidak ditemukan.');
        }

        $newStatus = $entry['is_active'] ? 0 : 1;
        $this->whitelistModel->update($id, ['is_active' => $newStatus]);

        $statusLabel = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->to(site_url('bridge/whitelist'))
            ->with('success', "Domain {$entry['domain']} berhasil {$statusLabel}.");
    }

    /**
     * Sinkronkan Ulang Alamat IP DNS untuk Satu Entri
     * POST /bridge/whitelist/sync/(:num)
     */
    public function sync(int $id)
    {
        $entry = $this->whitelistModel->find($id);
        if (!$entry) {
            return redirect()->to(site_url('bridge/whitelist'))
                ->with('error', 'Data whitelist tidak ditemukan.');
        }

        $resolvedIps = BridgeWhitelistModel::resolveHostToIps($entry['domain']);

        if ($resolvedIps) {
            // Gabungkan IP hasil resolusi dengan IP yang sudah tersimpan sebelumnya
            $currentIps = !empty($entry['ip_address']) ? array_map('trim', explode(',', $entry['ip_address'])) : [];
            $newIps     = array_map('trim', explode(',', $resolvedIps));
            $mergedIps  = array_values(array_unique(array_filter(array_merge($currentIps, $newIps))));
            $finalIps   = implode(', ', $mergedIps);

            $this->whitelistModel->update($id, [
                'ip_address'       => $finalIps,
                'last_resolved_at' => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to(site_url('bridge/whitelist'))
                ->with('success', "Alamat IP domain {$entry['domain']} berhasil disinkronkan: {$finalIps}.");
        }

        return redirect()->to(site_url('bridge/whitelist'))
            ->with('error', "Gagal resolusi DNS domain {$entry['domain']}. Pastikan domain aktif.");
    }

    /**
     * Sinkronkan Seluruh Alamat IP Whitelist yang Terdaftar
     * POST /bridge/whitelist/sync-all
     */
    public function syncAll()
    {
        $entries = $this->whitelistModel->findAll();
        $successCount = 0;
        $now = date('Y-m-d H:i:s');

        foreach ($entries as $entry) {
            $resolvedIps = BridgeWhitelistModel::resolveHostToIps($entry['domain']);
            if ($resolvedIps) {
                $currentIps = !empty($entry['ip_address']) ? array_map('trim', explode(',', $entry['ip_address'])) : [];
                $newIps     = array_map('trim', explode(',', $resolvedIps));
                $mergedIps  = array_values(array_unique(array_filter(array_merge($currentIps, $newIps))));
                $finalIps   = implode(', ', $mergedIps);

                $this->whitelistModel->update($entry['id'], [
                    'ip_address'       => $finalIps,
                    'last_resolved_at' => $now,
                ]);
                $successCount++;
            }
        }

        return redirect()->to(site_url('bridge/whitelist'))
            ->with('success', "Sinkronisasi selesai. Sebanyak {$successCount} alamat IP domain berhasil diperbarui.");
    }

    /**
     * Hapus Entri Whitelist
     * POST /bridge/whitelist/delete/(:num)
     */
    public function delete(int $id)
    {
        $entry = $this->whitelistModel->find($id);
        if (!$entry) {
            return redirect()->to(site_url('bridge/whitelist'))
                ->with('error', 'Data whitelist tidak ditemukan.');
        }

        $this->whitelistModel->delete($id);

        return redirect()->to(site_url('bridge/whitelist'))
            ->with('success', "Domain {$entry['domain']} berhasil dihapus dari whitelist.");
    }
}
