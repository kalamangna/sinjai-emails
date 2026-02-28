<!-- Badge Component -->
<?php
/**
 * @param string $label
 * @param string $type (success, info, warning, danger, neutral)
 * @param bool $rounded (optional)
 */
$typeClasses = [
    'success' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
    'info'    => 'bg-blue-50 text-blue-600 border-blue-200',
    'warning' => 'bg-amber-50 text-amber-500 border-amber-200',
    'danger'  => 'bg-red-50 text-red-600 border-red-200',
    'neutral' => 'bg-slate-50 text-slate-700 border-slate-200',
];

$class = $typeClasses[$type] ?? $typeClasses['neutral'];
$radius = isset($rounded) && $rounded ? 'rounded-full' : 'rounded-lg';
?>

<span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border <?= $class ?> <?= $radius ?>">
    <?= $label ?>
</span>
