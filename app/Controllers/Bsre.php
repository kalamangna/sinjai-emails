<?php

namespace App\Controllers;

use App\Libraries\BsreApi;
use CodeIgniter\Controller;

class Bsre extends BaseController
{
    public function check()
    {
        $email = $this->request->getVar('email');

        if (empty($email)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Email is required.',
            ])->setStatusCode(400);
        }

        $bsreApi = new BsreApi();
        $response = $bsreApi->checkStatusByEmail($email);

        return $this->response->setJSON($response);
    }
}
