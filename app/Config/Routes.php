<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Portal Utama
$routes->get('/', 'Home::index');

// Manajemen Email
$routes->group('email', function ($routes) {
    $routes->get('/', 'Email::index');
    $routes->get('unit_kerja', 'Email::unit_kerja_list');
    $routes->get('sync', 'Email::sync');
    $routes->get('detail/(:any)', 'Email::detail/$1');
    $routes->get('unit_kerja/(:num)', 'Email::unit_kerja_detail/$1');
    $routes->get('eselon_detail/(:num)', 'Email::eselon_detail/$1');
    $routes->post('update_details/(:any)', 'Email::update_details/$1');
    $routes->post('delete/(:num)', 'Email::delete/$1');
    $routes->get('batch', 'Email::batch');
    $routes->get('batch_update', 'Email::batch_update');
    $routes->get('batch_perjanjian_kerja', 'Email::batch_perjanjian_kerja');
    $routes->post('batch_update_process', 'Email::batch_update_process');
    $routes->post('batch_create', 'Email::batch_create');
    $routes->post('create_single', 'Email::create_single_email');

    $routes->get('pimpinan', 'Email::pimpinan');
    $routes->get('pimpinan_desa', 'Email::pimpinan_desa');
    $routes->get('pimpinan_hub', 'Email::pimpinan_hub');
    $routes->get('eselon_list', 'Email::eselon_list'); // New route for Eselon list
    $routes->get('batch_hub', 'Email::batch_hub');
    $routes->get('export_pimpinan_pdf', 'Email::export_pimpinan_pdf');
    $routes->get('export_pimpinan_desa_pdf', 'Email::export_pimpinan_desa_pdf');

    $routes->get('export_unit_kerja_csv/(:num)', 'Email::export_unit_kerja_csv/$1');
    $routes->get('export_unit_kerja_pdf/(:num)', 'Email::export_unit_kerja_pdf/$1');
    $routes->get('export_account_detail_pdf/(:num)', 'Email::export_account_detail_pdf/$1');
    $routes->get('export_perjanjian_kerja_pdf/(:num)', 'Email::export_perjanjian_kerja_pdf/$1');
    $routes->get('export_single_perjanjian_kerja_pdf/(:any)', 'Email::export_single_perjanjian_kerja_pdf/$1');

    // API PDF Massal
    $routes->get('api_unit_emails/(:num)', 'Email::api_unit_emails/$1');
    $routes->post('api_generate_pdf', 'Email::api_generate_pdf');
    $routes->get('api_download_zip/(:num)', 'Email::api_download_zip/$1');
    $routes->get('download_zip_file/(:any)', 'Email::download_zip_file/$1');
});

$routes->get('website_hub', 'Home::website_hub');

// Manajemen Data Induk (Unit Kerja)
$routes->group('unit_kerja', function ($routes) {
    $routes->get('manage', 'UnitKerja::manage');
    $routes->get('add', 'UnitKerja::add');
    $routes->post('store', 'UnitKerja::store');
    $routes->get('batch_create', 'UnitKerja::batch_create');
    $routes->post('batch_store', 'UnitKerja::batch_store');
    $routes->get('edit/(:num)', 'UnitKerja::edit/$1');
    $routes->post('update/(:num)', 'UnitKerja::update/$1');
    $routes->get('delete/(:num)', 'UnitKerja::delete/$1');
});

// Pemantauan Website
$routes->group('web_desa_kelurahan', function ($routes) {
    $routes->get('/', 'WebDesaKelurahan::index');
    $routes->get('export_pdf', 'WebDesaKelurahan::export_pdf');
    $routes->get('edit/(:num)', 'WebDesaKelurahan::edit/$1');
    $routes->post('update/(:num)', 'WebDesaKelurahan::update/$1');
    $routes->get('sync_expiration/(:num)', 'WebDesaKelurahan::sync_expiration/$1');
});

$routes->group('web_opd', function ($routes) {
    $routes->get('/', 'WebOpd::index');
    $routes->get('export_pdf', 'WebOpd::export_pdf');
    $routes->get('edit/(:num)', 'WebOpd::edit/$1');
    $routes->post('update/(:num)', 'WebOpd::update/$1');
});

// Log Pendampingan
$routes->group('assistance', function ($routes) {
    $routes->get('/', 'Assistance::index');
    $routes->get('export_pdf', 'Assistance::export_pdf');
    $routes->get('create', 'Assistance::create');
    $routes->post('store', 'Assistance::store');
    $routes->get('edit/(:num)', 'Assistance::edit/$1');
    $routes->post('update/(:num)', 'Assistance::update/$1');
    $routes->get('delete/(:num)', 'Assistance::delete/$1');
});

// Utilitas User & BSrE
$routes->post('user/check_email', 'User::checkEmailAvailability');
$routes->post('user/check_niknip', 'User::check_niknip');
$routes->get('bsre/check-status', 'Bsre::checkStatus');
$routes->post('bsre/sync-status', 'Bsre::syncStatus');

