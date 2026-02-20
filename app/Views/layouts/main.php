<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinjai Emails - Portal Manajemen Identitas Digital</title>

    <!-- Tailwind CSS (Local Build) -->
    <link href="<?= base_url('css/output.css') ?>" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .group:hover .group-hover\:block {
            display: block;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .transition-all {
            transition-duration: 200ms;
        }
    </style>

    <?= $this->renderSection('styles') ?>
</head>

<body class="bg-slate-50 flex flex-col min-h-screen text-slate-800 font-sans antialiased selection:bg-blue-100 selection:text-blue-900">
    <!-- Header Section -->
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-screen-2xl mx-auto px-6 lg:px-10">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between min-h-[72px] py-4 lg:py-0 gap-y-4">
                <?= $this->include('layouts/partials/header') ?>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow">
        <div class="max-w-screen-2xl mx-auto px-6 lg:px-10 py-8 lg:py-12">
            <?= $this->renderSection('content') ?>
        </div>
    </main>

    <?= $this->include('layouts/partials/footer') ?>

    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>