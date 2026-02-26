<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Authentication Routes
$routes->get('login', 'Auth::login');
$routes->post('auth/attemptLogin', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');

// Protected Routes
$routes->group('', ['filter' => 'auth'], function ($routes) {
    
    // Portal Utama
    $routes->get('/', 'Home::index');

    // Manajemen Email
    $routes->group('email', function ($routes) {
        // View Routes (Admin & Super Admin)
        $routes->get('/', 'Email::index');
        $routes->get('unit_kerja', 'Email::unit_kerja_list');
        $routes->get('detail/(:any)', 'Email::detail/$1');
        $routes->get('unit_kerja/(:num)', 'Email::unit_kerja_detail/$1');
        $routes->get('eselon_detail/(:num)', 'Email::eselon_detail/$1');
        $routes->get('pimpinan', 'Email::pimpinan');
        $routes->get('pimpinan_desa', 'Email::pimpinan_desa');
        $routes->get('eselon_list', 'Email::eselon_list');
        $routes->get('export_pimpinan_pdf', 'Email::export_pimpinan_pdf');
        $routes->get('export_pimpinan_desa_pdf', 'Email::export_pimpinan_desa_pdf');
        $routes->get('export_unit_kerja_csv/(:num)', 'Email::export_unit_kerja_csv/$1');
        $routes->get('export_unit_kerja_pdf/(:num)', 'Email::export_unit_kerja_pdf/$1');
        $routes->get('export_account_detail_pdf/(:num)', 'Email::export_account_detail_pdf/$1');
        $routes->get('export_perjanjian_kerja_pdf/(:num)', 'Email::export_perjanjian_kerja_pdf/$1');
        $routes->get('export_single_perjanjian_kerja_pdf/(:any)', 'Email::export_single_perjanjian_kerja_pdf/$1');
        $routes->get('api_unit_emails/(:num)', 'Email::api_unit_emails/$1');
        $routes->get('api_download_zip/(:num)', 'Email::api_download_zip/$1');
        $routes->get('download_zip_file/(:any)', 'Email::download_zip_file/$1');

        // Mutation Routes (Super Admin Only)
        $routes->group('', ['filter' => 'role:super_admin'], function ($routes) {
            $routes->get('sync', 'Email::sync');
            $routes->get('edit_profile/(:any)', 'Email::edit_profile/$1');
            $routes->post('update_details/(:any)', 'Email::update_details/$1');
            $routes->get('edit_password/(:any)', 'Email::edit_password/$1');
            $routes->post('update_password/(:any)', 'Email::update_password/$1');
            $routes->post('delete/(:num)', 'Email::delete/$1');
            $routes->post('create_single', 'Email::create_single_email');
            $routes->post('api_generate_pdf', 'Email::api_generate_pdf');
        });
    });

    // Batch Operations (Super Admin Only)
    $routes->group('batch', ['filter' => 'role:super_admin'], function ($routes) {
        $routes->get('/', 'BatchController::index');
        $routes->get('update', 'BatchController::update');
        $routes->get('pk', 'BatchController::pk');
        $routes->post('process_update', 'BatchController::process_update');
        $routes->post('process_create', 'BatchController::process_create');
    });

    // Manajemen Data Induk (Unit Kerja)
    $routes->group('unit_kerja', function ($routes) {
        $routes->get('manage', 'UnitKerja::manage');
        
        // Mutation Routes (Super Admin Only)
        $routes->group('', ['filter' => 'role:super_admin'], function ($routes) {
            $routes->get('add', 'UnitKerja::add');
            $routes->post('store', 'UnitKerja::store');
            $routes->get('batch_create', 'UnitKerja::batch_create');
            $routes->post('batch_store', 'UnitKerja::batch_store');
            $routes->get('edit/(:num)', 'UnitKerja::edit/$1');
            $routes->post('update/(:num)', 'UnitKerja::update/$1');
            $routes->get('delete/(:num)', 'UnitKerja::delete/$1');
        });
    });

    // Pemantauan Website
    $routes->group('web_desa_kelurahan', function ($routes) {
        $routes->get('/', 'WebDesaKelurahan::index');
        $routes->get('export_pdf', 'WebDesaKelurahan::export_pdf');
        
        // Mutation Routes (Super Admin Only)
        $routes->group('', ['filter' => 'role:super_admin'], function ($routes) {
            $routes->get('edit/(:num)', 'WebDesaKelurahan::edit/$1');
            $routes->post('update/(:num)', 'WebDesaKelurahan::update/$1');
            $routes->get('sync_expiration/(:num)', 'WebDesaKelurahan::sync_expiration/$1');
        });
    });

    $routes->group('web_opd', function ($routes) {
        $routes->get('/', 'WebOpd::index');
        $routes->get('export_pdf', 'WebOpd::export_pdf');
        
        // Mutation Routes (Super Admin Only)
        $routes->group('', ['filter' => 'role:super_admin'], function ($routes) {
            $routes->get('edit/(:num)', 'WebOpd::edit/$1');
            $routes->post('update/(:num)', 'WebOpd::update/$1');
        });
    });

    // Log Pendampingan
    $routes->group('assistance', function ($routes) {
        $routes->get('/', 'Assistance::index');
        $routes->get('export_pdf', 'Assistance::export_pdf');
        
        // Mutation Routes (Super Admin Only)
        $routes->group('', ['filter' => 'role:super_admin'], function ($routes) {
            $routes->get('create', 'Assistance::create');
            $routes->post('store', 'Assistance::store');
            $routes->get('edit/(:num)', 'Assistance::edit/$1');
            $routes->post('update/(:num)', 'Assistance::update/$1');
            $routes->get('delete/(:num)', 'Assistance::delete/$1');
        });
    });

    // Utilitas User & BSrE
    $routes->post('user/check_email', 'User::checkEmailAvailability');
    $routes->post('user/check_niknip', 'User::check_niknip');
    $routes->get('bsre/check-status', 'Bsre::checkStatus');
    $routes->post('bsre/sync-status', 'Bsre::syncStatus');
});