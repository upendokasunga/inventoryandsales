@props([
    'tabs' => [],
    'current' => 'all',
])

<div class="flex gap-1 mb-4 p-1 bg-slate-100 rounded-xl overflow-x-auto">
    @foreach ($tabs as $key => $tab)
        @php
            $count = $tab['count'] ?? null;
            $href = request()->fullUrlWithQuery(['tab' => $key]);
            $isActive = $current === $key;
        @endphp
        <a href="{{ $href }}"
           class="px-4 py-2 text-sm font-medium rounded-lg whitespace-nowrap transition
                  {{ $isActive ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            {{ $tab['label'] }}
            @if ($count !== null)
                <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full {{ $isActive ? 'bg-primary-50 text-primary' : 'bg-slate-200 text-slate-500' }}">
                    {{ $count }}
                </span>
            @endif
        </a>
    @endforeach
</div>
