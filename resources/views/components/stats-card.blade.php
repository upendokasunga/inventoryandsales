@props([
    'title' => '',
    'value' => '',
    'icon' => '',
    'color' => 'primary',
    'trend' => null,
    'trendLabel' => '',
    'subtitle' => '',
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
    <div class="flex items-center justify-between">
        <div class="min-w-0">
            <p class="text-sm font-medium text-slate-500">{{ $title }}</p>
            <p class="mt-1 text-[32px] font-bold text-slate-800 truncate">{{ $value }}</p>
            @if ($subtitle)
                <p class="text-xs text-slate-400 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
        @if ($icon)
            <div class="w-12 h-12 rounded-xl {{ $iconBg }} flex items-center justify-center shrink-0">
                {!! $icon !!}
            </div>
        @endif
    </div>
    @if ($trend !== null)
        <div class="mt-2 flex items-center gap-1">
            @if ($trend > 0)
                <svg class="w-3.5 h-3.5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                <span class="text-xs font-medium text-success">+{{ $trend }}%</span>
            @elseif ($trend < 0)
                <svg class="w-3.5 h-3.5 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                <span class="text-xs font-medium text-danger">{{ $trend }}%</span>
            @endif
            @if ($trendLabel)
                <span class="text-xs text-slate-400">{{ $trendLabel }}</span>
            @endif
        </div>
    @endif
</div>
