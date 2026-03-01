<!-- Badge Component -->
<?php
/**
 * @param string $label
 * @param string $type (success, info, warning, danger, neutral)
 * @param bool $rounded (optional)
 */
$typeClasses = [
    'success' => 'bg-emerald-100 text-emerald-800 border-transparent',
    'info'    => 'bg-blue-100 text-slate-700 border-transparent',
    'warning' => 'bg-amber-50 text-amber-500 border-amber-200',
    'danger'  => 'bg-red-100 text-red-700 border-transparent',
    'neutral' => 'bg-slate-100 text-slate-700 border-transparent',
];

$class = $typeClasses[$type] ?? $typeClasses['neutral'];
$radius = isset($rounded) && $rounded ? 'rounded-full' : 'rounded-lg';
?>

<span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border <?= $class ?> <?= $radius ?>">
    <?= $label ?>
</span>
