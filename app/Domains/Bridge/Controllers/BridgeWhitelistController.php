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
     * GET /admin/bridge-whitelist
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
     * POST /admin/bridge-whitelist/store
     */
    public function store()
    {
        $rules = [
            'label'  => 'required|min_length[3]|max_length[255]',
            'domain' => 'required|min_length[3]|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Validasi gagal. Pastikan nama instansi dan nama domain telah diisi dengan benar.');
        }

        $rawDomain = $this->request->getPost('domain');
        $label     = trim($this->request->getPost('label'));
        $cleanHost = BridgeWhitelistModel::cleanHost($rawDomain);

        if (empty($cleanHost)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Format nama domain atau alamat IP tidak valid.');
        }

        // Cek duplikasi domain
        $existing = $this->whitelistModel->where('domain', $cleanHost)->first();
        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Domain {$cleanHost} sudah terdaftar pada whitelist.");
        }

        // Resolusi DNS ke IP
        $resolvedIps = BridgeWhitelistModel::resolveHostToIps($cleanHost);

        $data = [
            'label'            => $label,
            'domain'           => $cleanHost,
            'ip_address'       => $resolvedIps,
            'is_active'        => 1,
            'last_resolved_at' => $resolvedIps ? date('Y-m-d H:i:s') : null,
        ];

        $this->whitelistModel->insert($data);

        if ($resolvedIps) {
            return redirect()->to(site_url('admin/bridge-whitelist'))
                ->with('success', "Domain {$cleanHost} berhasil ditambahkan dengan IP {$resolvedIps}.");
        }

        return redirect()->to(site_url('admin/bridge-whitelist'))
            ->with('info', "Domain {$cleanHost} berhasil disimpan. DNS belum berhasil diresolusi ke alamat IP.");
    }

    /**
     * Perbarui Data Entri Whitelist
     * POST /admin/bridge-whitelist/update/(:num)
     */
    public function update(int $id)
    {
        $entry = $this->whitelistModel->find($id);
        if (!$entry) {
            return redirect()->to(site_url('admin/bridge-whitelist'))
                ->with('error', 'Data whitelist tidak ditemukan.');
        }

        $rules = [
            'label'  => 'required|min_length[3]|max_length[255]',
            'domain' => 'required|min_length[3]|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Validasi gagal. Pastikan data terisi dengan benar.');
        }

        $rawDomain = $this->request->getPost('domain');
        $label     = trim($this->request->getPost('label'));
        $isActive  = $this->request->getPost('is_active') ? 1 : 0;
        $cleanHost = BridgeWhitelistModel::cleanHost($rawDomain);

        // Cek duplikasi dengan entri lain
        $existing = $this->whitelistModel->where('domain', $cleanHost)->where('id !=', $id)->first();
        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Domain {$cleanHost} sudah digunakan oleh data lain.");
        }

        // Jika domain berubah atau IP belum ada, lakukan resolusi ulang DNS
        $resolvedIps = $entry['ip_address'];
        $lastResolved = $entry['last_resolved_at'];

        if ($cleanHost !== $entry['domain'] || empty($resolvedIps)) {
            $newIps = BridgeWhitelistModel::resolveHostToIps($cleanHost);
            if ($newIps) {
                $resolvedIps  = $newIps;
                $lastResolved = date('Y-m-d H:i:s');
            }
        }

        $this->whitelistModel->update($id, [
            'label'            => $label,
            'domain'           => $cleanHost,
            'ip_address'       => $resolvedIps,
            'is_active'        => $isActive,
            'last_resolved_at' => $lastResolved,
        ]);

        return redirect()->to(site_url('admin/bridge-whitelist'))
            ->with('success', "Data domain {$cleanHost} berhasil diperbarui.");
    }

    /**
     * Ubah Status Aktif atau Nonaktif Entri Whitelist
     * POST /admin/bridge-whitelist/toggle/(:num)
     */
    public function toggle(int $id)
    {
        $entry = $this->whitelistModel->find($id);
        if (!$entry) {
            return redirect()->to(site_url('admin/bridge-whitelist'))
                ->with('error', 'Data whitelist tidak ditemukan.');
        }

        $newStatus = $entry['is_active'] ? 0 : 1;
        $this->whitelistModel->update($id, ['is_active' => $newStatus]);

        $statusLabel = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->to(site_url('admin/bridge-whitelist'))
            ->with('success', "Domain {$entry['domain']} berhasil {$statusLabel}.");
    }

    /**
     * Sinkronkan Ulang Alamat IP DNS untuk Satu Entri
     * POST /admin/bridge-whitelist/sync/(:num)
     */
    public function sync(int $id)
    {
        $entry = $this->whitelistModel->find($id);
        if (!$entry) {
            return redirect()->to(site_url('admin/bridge-whitelist'))
                ->with('error', 'Data whitelist tidak ditemukan.');
        }

        $resolvedIps = BridgeWhitelistModel::resolveHostToIps($entry['domain']);

        if ($resolvedIps) {
            $this->whitelistModel->update($id, [
                'ip_address'       => $resolvedIps,
                'last_resolved_at' => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to(site_url('admin/bridge-whitelist'))
                ->with('success', "Alamat IP domain {$entry['domain']} berhasil disinkronkan: {$resolvedIps}.");
        }

        return redirect()->to(site_url('admin/bridge-whitelist'))
            ->with('error', "Gagal melakukan resolusi DNS untuk domain {$entry['domain']}. Pastikan domain aktif.");
    }

    /**
     * Sinkronkan Seluruh Alamat IP Whitelist yang Terdaftar
     * POST /admin/bridge-whitelist/sync-all
     */
    public function syncAll()
    {
        $entries = $this->whitelistModel->findAll();
        $successCount = 0;
        $now = date('Y-m-d H:i:s');

        foreach ($entries as $entry) {
            $resolvedIps = BridgeWhitelistModel::resolveHostToIps($entry['domain']);
            if ($resolvedIps) {
                $this->whitelistModel->update($entry['id'], [
                    'ip_address'       => $resolvedIps,
                    'last_resolved_at' => $now,
                ]);
                $successCount++;
            }
        }

        return redirect()->to(site_url('admin/bridge-whitelist'))
            ->with('success', "Sinkronisasi selesai. Sebanyak {$successCount} alamat IP domain berhasil diperbarui.");
    }

    /**
     * Hapus Entri Whitelist
     * POST /admin/bridge-whitelist/delete/(:num)
     */
    public function delete(int $id)
    {
        $entry = $this->whitelistModel->find($id);
        if (!$entry) {
            return redirect()->to(site_url('admin/bridge-whitelist'))
                ->with('error', 'Data whitelist tidak ditemukan.');
        }

        $this->whitelistModel->delete($id);

        return redirect()->to(site_url('admin/bridge-whitelist'))
            ->with('success', "Domain {$entry['domain']} berhasil dihapus dari whitelist.");
    }
}
