@props([
    'title' => '',
    'description' => '',
])

<div {{ $attributes->merge(['class' => 'erp-card']) }}>
    @if ($title || $description)
        <div class="px-6 py-4 border-b border-slate-100">
            @if ($title)
                <h3 class="text-base font-semibold text-slate-800">{{ $title }}</h3>
            @endif
            @if ($description)
                <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
            @endif
        </div>
    @endif
    <div class="px-6 py-6 space-y-6">
        {{ $slot }}
    </div>
</div>
