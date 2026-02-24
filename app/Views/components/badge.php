<!-- Badge Component -->
<?php
/**
 * @param string $label
 * @param string $type (success, info, warning, danger, neutral)
 * @param bool $rounded (optional)
 */
$typeClasses = [
    'success' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
    'info'    => 'bg-blue-100 text-blue-700 border-blue-200',
    'warning' => 'bg-amber-100 text-amber-700 border-amber-200',
    'danger'  => 'bg-rose-100 text-rose-700 border-rose-200',
    'neutral' => 'bg-slate-100 text-slate-700 border-slate-200',
];

$class = $typeClasses[$type] ?? $typeClasses['neutral'];
$radius = isset($rounded) && $rounded ? 'rounded-full' : 'rounded-lg';
?>

<span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border <?= $class ?> <?= $radius ?>">
    <?= $label ?>
</span>
