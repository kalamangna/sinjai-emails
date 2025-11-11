<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?? 'Sinjai Emails' ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light d-flex flex-column min-vh-100">
    <!-- Header Section -->
    <header class="bg-white shadow-sm py-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="h3 text-primary mb-1">
                        <a href="<?= site_url('/') ?>" class="text-primary text-decoration-none">
                            <i class="fas fa-envelope me-2"></i>Sinjai Emails
                        </a>
                    </h1>
                    <p class="text-muted mb-0">sinjaikab.go.id</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="badge bg-info">
                        <i class="fas fa-check-circle me-1"></i>CURL Installed
                    </span>
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