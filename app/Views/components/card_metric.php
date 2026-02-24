<!-- Metric Card Component -->
<?php
/**
 * @param string $label
 * @param string|int $value
 * @param string $icon
 * @param string $color (emerald, blue, amber, rose, indigo)
 * @param string|null $trend (optional)
 * @param string|null $link (optional)
 */
$colorClasses = [
    'emerald' => ['bg' => 'bg-emerald-50', 'icon' => 'bg-emerald-600', 'text' => 'text-emerald-600', 'border' => 'border-emerald-100', 'ring' => 'group-hover:ring-emerald-500'],
    'blue'    => ['bg' => 'bg-blue-50',    'icon' => 'bg-blue-600',    'text' => 'text-blue-600',    'border' => 'border-blue-100',    'ring' => 'group-hover:ring-blue-500'],
    'amber'   => ['bg' => 'bg-amber-50',   'icon' => 'bg-amber-600',   'text' => 'text-amber-600',   'border' => 'border-amber-100',   'ring' => 'group-hover:ring-amber-500'],
    'rose'    => ['bg' => 'bg-rose-50',    'icon' => 'bg-rose-600',    'text' => 'text-rose-600',    'border' => 'border-rose-100',    'ring' => 'group-hover:ring-rose-500'],
    'indigo'  => ['bg' => 'bg-indigo-50',  'icon' => 'bg-indigo-600',  'text' => 'text-indigo-600',  'border' => 'border-indigo-100',  'ring' => 'group-hover:ring-indigo-500'],
];

$theme = $colorClasses[$color] ?? $colorClasses['emerald'];
?>

<div class="group bg-white border border-slate-200 rounded-2xl p-6 transition-all hover:shadow-lg hover:shadow-slate-200/50 hover:border-slate-300">
    <div class="flex items-center justify-between mb-4">
        <div class="w-10 h-10 <?= $theme['bg'] ?> border <?= $theme['border'] ?> rounded-xl flex items-center justify-center transition-colors group-hover:bg-slate-900 group-hover:border-slate-900 shadow-sm">
            <i class="<?= $icon ?> <?= $theme['text'] ?> text-lg group-hover:text-white transition-colors"></i>
        </div>
        <?php if (isset($trend)): ?>
        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 border border-slate-200">
            <?= $trend ?>
        </span>
        <?php endif; ?>
    </div>
    
    <div>
        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1"><?= $label ?></p>
        <div class="flex items-baseline justify-between">
            <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight"><?= $value ?></h3>
            <?php if (isset($link)): ?>
            <a href="<?= $link ?>" class="text-[10px] font-bold <?= $theme['text'] ?> uppercase tracking-wider hover:underline">
                Kelola <i class="fas fa-chevron-right ml-1"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>