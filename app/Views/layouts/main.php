<!DOCTYPE html>
<html lang="id" class="dark">

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
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #0f172a;
        }

        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 10px;
            border: 2px solid #0f172a;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }

        /* Smooth transitions for interactive elements */
        .transition-all {
            transition-duration: 200ms;
        }
    </style>

    <?= $this->renderSection('styles') ?>
</head>

<body class="bg-slate-950 flex flex-col min-h-screen text-slate-300 font-sans antialiased selection:bg-blue-500/30 selection:text-blue-200">
    <!-- Header Section -->
    <header class="bg-slate-900/80 backdrop-blur-md border-b border-slate-800 sticky top-0 z-40">
        <div class="max-w-screen-2xl mx-auto px-6 lg:px-10">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between min-h-[80px] py-4 lg:py-0 gap-y-6">
                <?= $this->include('layouts/partials/header') ?>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow">
        <div class="max-w-screen-2xl mx-auto px-6 lg:px-10 py-12">
            <?= $this->renderSection('content') ?>
        </div>
    </main>

    <?= $this->include('layouts/partials/footer') ?>

    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>