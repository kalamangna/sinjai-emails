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
    'emerald' => ['bg' => 'bg-emerald-50', 'icon' => 'bg-emerald-600', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200', 'ring' => 'group-hover:ring-emerald-600'],
    'blue'    => ['bg' => 'bg-white border-l-4 border-l-emerald-700',    'icon' => 'bg-emerald-500',    'text' => 'text-emerald-700',    'border' => 'border-gray-200',    'ring' => 'group-hover:ring-emerald-500'],
    'amber'   => ['bg' => 'bg-amber-50',   'icon' => 'bg-amber-500',   'text' => 'text-amber-500',   'border' => 'border-amber-200',   'ring' => 'group-hover:ring-amber-500'],
    'rose'    => ['bg' => 'bg-red-50',     'icon' => 'bg-red-600',     'text' => 'text-red-600',     'border' => 'border-red-200',     'ring' => 'group-hover:ring-red-600'],
];

$theme = $colorClasses[$color] ?? $colorClasses['blue'];
?>

<div class="group bg-white border border-gray-200 rounded-2xl p-6 transition-all hover:shadow-lg hover:shadow-gray-200/50 hover:border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <div class="w-10 h-10 <?= $theme['bg'] ?> border <?= $theme['border'] ?> rounded-lg flex items-center justify-center transition-colors group-hover:bg-gray-800 group-hover:border-gray-800 shadow-sm">
            <i class="<?= $icon ?> <?= $theme['text'] ?> text-lg group-hover:text-white transition-colors"></i>
        </div>
        <?php if (isset($trend)): ?>
        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
            <?= $trend ?>
        </span>
        <?php endif; ?>
    </div>
    
    <div>
        <p class="text-[11px] font-bold text-gray-700 uppercase tracking-widest mb-1"><?= $label ?></p>
        <div class="flex items-baseline justify-between">
            <h3 class="text-2xl font-bold text-gray-800 tracking-tight"><?= $value ?></h3>
            <?php if (isset($link)): ?>
            <a href="<?= $link ?>" class="text-[10px] font-bold <?= $theme['text'] ?> uppercase tracking-wider hover:underline">
                Kelola <i class="fas fa-chevron-right ml-1"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>