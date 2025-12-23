<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Email::index');
$routes->get('email', 'Email::index');
$routes->get('email/sync', 'Email::sync');
$routes->get('email/detail/(:any)', 'Email::detail/$1');
$routes->get('/email/unit_kerja/(:num)', 'Email::unit_kerja_detail/$1');
$routes->get('/email/eselon/(:num)', 'Email::eselon_detail/$1');
$routes->post('email/update_details/(:any)', 'Email::update_details/$1');
$routes->post('/email/delete/(:num)', 'Email::delete/$1');
$routes->post('/email/create_single', 'Email::create_single_email');
$routes->post('/user/check_email', 'User::checkEmailAvailability');
$routes->post('/user/check_niknip', 'User::check_niknip');
$routes->get('email/batch', 'Email::batch');
$routes->post('email/create_single', 'Email::create_single_email');
$routes->get('email/batch_update', 'Email::batch_update');
$routes->post('email/batch_update_process', 'Email::batch_update_process');
$routes->post('email/batch_create', 'Email::batch_create');
$routes->post('email/delete/(:num)', 'Email::delete/$1');


$routes->get('email/export_unit_kerja_csv/(:num)', 'Email::export_unit_kerja_csv/$1');
$routes->get('email/export_unit_kerja_pdf/(:num)', 'Email::export_unit_kerja_pdf/$1');
$routes->get('email/export_perjanjian_kerja_pdf/(:num)', 'Email::export_perjanjian_kerja_pdf/$1');
$routes->get('email/export_single_perjanjian_kerja_pdf/(:any)', 'Email::export_single_perjanjian_kerja_pdf/$1');

// Batch Export PDF Routes
$routes->get('email/api/unit_emails/(:num)', 'Email::api_unit_emails/$1');
$routes->post('email/api/generate_pdf', 'Email::api_generate_pdf');
$routes->get('email/api/download_zip/(:num)', 'Email::api_download_zip/$1');

$routes->post('/user/check_email', 'User::checkEmailAvailability');
$routes->post('/user/check_niknip', 'User::check_niknip');
$routes->get('/bsre/check-status', 'Bsre::checkStatus');
$routes->post('/bsre/sync-status', 'Bsre::syncStatus');
$routes->get('/simpegnas/check/(:any)', 'Simpegnas::check/$1');

$routes->get('/test-pk', 'Email::test_perjanjian_kerja');

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');
$routes->get('unit_kerja/manage', 'UnitKerja::manage');
$routes->get('unit_kerja/add', 'UnitKerja::add');
$routes->post('unit_kerja/store', 'UnitKerja::store');
$routes->get('unit_kerja/edit/(:num)', 'UnitKerja::edit/$1');
$routes->post('unit_kerja/update/(:num)', 'UnitKerja::update/$1');
$routes->get('unit_kerja/delete/(:num)', 'UnitKerja::delete/$1');
