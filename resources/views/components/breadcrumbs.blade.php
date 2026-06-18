@props(['items' => []])

<nav class="flex mb-4" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 text-sm text-slate-500">
        <li class="inline-flex items-center">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
        </li>
        @foreach ($items as $item)
            <li class="inline-flex items-center">
                <svg class="w-3 h-3 mx-1 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/>
                </svg>
                @if ($loop->last)
                    <span class="text-slate-800 font-medium">{{ $item['label'] }}</span>
                @else
                    <a href="{{ $item['url'] }}" class="hover:text-primary">{{ $item['label'] }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
