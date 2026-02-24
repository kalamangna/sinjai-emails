<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sinjai Emails</title>

    <!-- Tailwind CSS (Local Build) -->
    <link href="<?= base_url('css/output.css') ?>" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex items-center justify-center p-6 selection:bg-emerald-100 selection:text-emerald-900">
    <div class="w-full max-w-md">
        <!-- Branding -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-600 rounded-2xl shadow-xl shadow-emerald-200 mb-6">
                <i class="fas fa-envelope-open-text text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight leading-none">
                SINJAI<span class="text-emerald-600">EMAILS</span>
            </h1>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em] mt-3">Identitas Digital</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white border border-slate-200 rounded-[2rem] p-8 lg:p-10 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 -mr-10 -mt-10 w-32 h-32 bg-emerald-50 rounded-full blur-3xl opacity-50"></div>
            
            <div class="relative z-10">
                <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-8">Masuk</h2>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="bg-rose-50 border border-rose-100 text-rose-700 px-4 py-3 rounded-xl flex items-center mb-6 text-xs font-bold uppercase tracking-wider">
                        <i class="fas fa-exclamation-circle mr-3 text-rose-500"></i>
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <form action="<?= site_url('auth/attemptLogin') ?>" method="POST" class="space-y-6">
                    <?= csrf_field() ?>
                    
                    <div class="space-y-2">
                        <label for="username" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Username</label>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                                <i class="fas fa-user text-sm"></i>
                            </span>
                            <input type="text" name="username" id="username" value="<?= old('username') ?>" required
                                class="block w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-bold text-slate-700 transition-all placeholder:text-slate-300"
                                placeholder="Username">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Password</label>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                                <i class="fas fa-lock text-sm"></i>
                            </span>
                            <input type="password" name="password" id="password" required
                                class="block w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-sm font-bold text-slate-700 transition-all placeholder:text-slate-300"
                                placeholder="Password">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full inline-flex items-center justify-center px-8 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] transition-all shadow-lg shadow-emerald-100 group">
                            Login <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-10 italic">
            Diskominfo Sinjai
        </p>
    </div>
</body>

</html>