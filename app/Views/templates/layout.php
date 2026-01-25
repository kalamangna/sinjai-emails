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
</head>

<body class="bg-light d-flex flex-column min-vh-100">

    <!-- Header Section -->
<header class="bg-white shadow-sm py-3">
    <div class="container">
        <div class="row align-items-center gy-3">
            <div class="col-md-4 text-center text-md-start">
                <h1 class="h3 text-primary mb-1">
                    <a href="<?= site_url('/') ?>" class="text-primary text-decoration-none">
                        <i class="fas fa-envelope me-2"></i>Sinjai Emails
                    </a>
                </h1>
                <p class="text-muted mb-0 small">sinjaikab.go.id</p>
            </div>
            <div class="col-md-8">
                <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-end">
                    <a href="<?= site_url('email/batch') ?>" class="btn btn-info btn-sm text-white">
                        <i class="fas fa-plus-circle me-1"></i>Batch Create
                    </a>
                    <a href="<?= site_url('email/batch_update') ?>" class="btn btn-warning btn-sm text-dark">
                        <i class="fas fa-edit me-1"></i>Batch Update
                    </a>
                    <a href="<?= site_url('email/batch_perjanjian_kerja') ?>" class="btn btn-warning btn-sm text-dark">
                        <i class="fas fa-file-contract me-1"></i>Batch Update PK
                    </a>
                    <a href="<?= site_url('email/pimpinan') ?>" class="btn btn-success btn-sm text-white">
                        <i class="fas fa-user-tie me-1"></i>Pimpinan
                    </a>
                    <a href="<?= site_url('email/pimpinan_desa') ?>" class="btn btn-success btn-sm text-white">
                        <i class="fas fa-users me-1"></i>Pimpinan Desa
                    </a>
                    <a href="<?= site_url('web_desa_kelurahan') ?>" class="btn btn-primary btn-sm text-white">
                        <i class="fas fa-globe me-1"></i>Website Desa
                    </a>
                    <a href="<?= site_url('web_opd') ?>" class="btn btn-primary btn-sm text-white">
                        <i class="fas fa-building me-1"></i>Website OPD
                    </a>
                    <a href="<?= site_url('unit_kerja/manage') ?>" class="btn btn-secondary btn-sm text-white">
                        <i class="fas fa-building me-1"></i>Unit Kerja
                    </a>
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
                    <i class="fas fa-envelope me-2"></i>Sinjai Emails
                </h5>
                <p class="mb-0">sinjaikab.go.id</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="mb-2">
                    <span class="badge bg-info">
                        <i class="fas fa-database me-1"></i>cPanel API
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

    <?= $this->renderSection('scripts') ?>
</body>

</html>