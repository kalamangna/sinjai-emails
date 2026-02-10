<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinjai Emails</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <?= $this->renderSection('styles') ?>
</head>

<body class="bg-light d-flex flex-column min-vh-100">

    <!-- Header Section -->
    <header class="bg-white shadow-sm py-3">
        <div class="container">
            <div class="row align-items-center gy-3">
                <div class="col-md-4 text-center text-md-start">
                    <h1 class="h3 text-primary mb-1">
                        <a href="<?= site_url('/') ?>" class="text-primary text-decoration-none">
                            <i class="fas fa-envelope fa-fw me-2"></i>Sinjai Emails
                        </a>
                    </h1>
                    <p class="text-muted mb-0 small">sinjaikab.go.id</p>
                </div>
                <div class="col-md-8">
                    <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-end">
                        <!-- Batch Operations Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-warning btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-layer-group fa-fw me-1"></i>Batch Ops
                            </button>
                            <ul class="dropdown-menu shadow">
                                <li><a class="dropdown-item" href="<?= site_url('email/batch') ?>"><i class="fas fa-plus-circle fa-fw me-2 text-info"></i>Batch Create</a></li>
                                <li><a class="dropdown-item" href="<?= site_url('email/batch_update') ?>"><i class="fas fa-edit fa-fw me-2 text-warning"></i>Batch Update</a></li>
                                <li><a class="dropdown-item" href="<?= site_url('email/batch_perjanjian_kerja') ?>"><i class="fas fa-file-contract fa-fw me-2 text-warning"></i>Batch Update PK</a></li>
                            </ul>
                        </div>

                        <!-- Pimpinan Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-tie fa-fw me-1"></i>Pimpinan
                            </button>
                            <ul class="dropdown-menu shadow">
                                <li><a class="dropdown-item" href="<?= site_url('email/pimpinan') ?>"><i class="fas fa-user-tie fa-fw me-2 text-success"></i>Pimpinan</a></li>
                                <li><a class="dropdown-item" href="<?= site_url('email/pimpinan_desa') ?>"><i class="fas fa-users fa-fw me-2 text-success"></i>Pimpinan Desa</a></li>
                            </ul>
                        </div>

                        <!-- Web Tracking Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-globe fa-fw me-1"></i>Web Tracking
                            </button>
                            <ul class="dropdown-menu shadow">
                                <li><a class="dropdown-item" href="<?= site_url('web_desa_kelurahan') ?>"><i class="fas fa-globe fa-fw me-2 text-primary"></i>Website Desa & Kelurahan</a></li>
                                <li><a class="dropdown-item" href="<?= site_url('web_opd') ?>"><i class="fas fa-building fa-fw me-2 text-primary"></i>Website OPD</a></li>
                            </ul>
                        </div>

                        <!-- Assistance Link -->
                        <a href="<?= site_url('assistance') ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-hands-helping fa-fw me-1"></i>Assistance
                        </a>

                        <!-- Master Data Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-database fa-fw me-1"></i>Master
                            </button>
                            <ul class="dropdown-menu shadow">
                                <li><a class="dropdown-item" href="<?= site_url('unit_kerja/manage') ?>"><i class="fas fa-building fa-fw me-2 text-secondary"></i>Unit Kerja</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container py-4 flex-grow-1">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer Section -->
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-3">
                        <i class="fas fa-envelope fa-fw me-2"></i>Sinjai Emails
                    </h5>
                    <p class="mb-0">sinjaikab.go.id</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="mb-2">
                        <span class="badge bg-info">
                            <i class="fas fa-database fa-fw me-1"></i>cPanel API
                        </span>
                    </div>
                    <p class="mb-0">
                        <small>
                            © <?php echo date('Y'); ?> Aptika Diskominfo Sinjai
                        </small>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Chart.js Datalabels Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>