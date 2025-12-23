<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EmailSeeder extends Seeder
{
    public function run()
    {
        // No data to seed directly here, as test will create its own.
        // This seeder is mainly for ensuring the `DatabaseTestTrait` works correctly
        // and doesn't complain about a missing seeder when `refresh` is true.
        // Or if you want to add some common data for all email-related tests.
    }
}
