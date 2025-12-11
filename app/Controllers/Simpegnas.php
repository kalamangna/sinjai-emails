<?php

namespace App\Controllers;

use App\Libraries\SimpegnasApi;
use CodeIgniter\API\ResponseTrait;

class Simpegnas extends BaseController
{
    use ResponseTrait;

    protected $simpegnas;

    public function __construct()
    {
        $this->simpegnas = new SimpegnasApi();
    }

    /**
     * Test the Simpegnas API connection
     * Route: GET /simpegnas/check/(:any)
     */
    public function check($nip = null)
    {
        if (!$nip) {
            return $this->fail('NIP parameter is required', 400);
        }

        $result = $this->simpegnas->getDataUtamaAsn($nip);

        if ($result['success']) {
            return $this->respond([
                'status' => 'success',
                'data' => $result['data']
            ], 200);
        } else {
            return $this->respond([
                'status' => 'error',
                'message' => $result['message'],
                'details' => $result['data'] ?? null
            ], $result['code']);
        }
    }
}
