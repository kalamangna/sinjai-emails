<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Domains\Auth\UserModel;

class UserSeeder extends Seeder
{
    public function run()
    {
        $userModel = new UserModel();

        $users = [
            [
                'username'   => 'kalamangna',
                'user_email' => 'superadmin@sinjaikab.go.id',
                'password'   => 'Syazani',
                'role'       => 'super_admin',
            ],
            [
                'username'   => 'aptika',
                'user_email' => 'admin@sinjaikab.go.id',
                'password'   => 'Kominfo101',
                'role'       => 'admin',
            ],
        ];

        foreach ($users as $user) {
            $userModel->insert($user);
        }
    }
}