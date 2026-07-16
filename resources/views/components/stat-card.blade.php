@props([
    'label' => '',
    'value' => '',
    'icon' => '',
    'color' => 'primary',
])

@php
$colors = [
    'primary' => 'bg-primary-50 text-primary',
    'success' => 'bg-success-50 text-success',
    'warning' => 'bg-warning-50 text-warning-600',
    'danger' => 'bg-danger-50 text-danger',
    'info' => 'bg-info-50 text-info',
    'purple' => 'bg-purple-50 text-purple-600',
    'slate' => 'bg-slate-100 text-slate-600',
];
$iconBg = $colors[$color] ?? $colors['primary'];
@endphp

<div {{ $attributes->merge(['class' => 'erp-card']) }}>
    <div class="flex items-center justify-between gap-3">
        <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
            <p class="mt-1 text-[clamp(1.25rem,3.5vw,2rem)] font-bold text-slate-800 leading-tight break-all">{{ $value }}</p>
        </div>
        @if ($icon)
            <div class="w-12 h-12 rounded-xl {{ $iconBg }} flex items-center justify-center shrink-0">
                {!! $icon !!}
            </div>
        @endif
    </div>
</div>
