@props(['category', 'depth' => 0])

<div class=\"mb-1\" style=\"margin-left: {{ \ * 20 }}px;\">
    <div class=\"flex items-center px-4 py-2 {{ \ === 0 ? 'bg-blue-50' : 'bg-slate-50' }} rounded-lg\">
        <svg class=\"w-4 h-4 mr-2 {{ \ === 0 ? 'text-blue-500' : 'text-slate-400' }}\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z\" />
        </svg>
        <span class=\"text-sm font-semibold text-slate-800\">{{ \->name }}</span>
        @if (\->children->isNotEmpty())
            <span class=\"ml-2 text-xs text-slate-500\">({{ \->children->count() }} children)</span>
        @endif
        @if (!\->is_active)
            <span class=\"ml-2 px-1.5 py-0.5 text-xs rounded-full bg-red-100 text-red-600\">Inactive</span>
        @endif
    </div>
    @if (\->children->isNotEmpty())
        <div class=\"mt-1 space-y-1\">
            @foreach (\->children as \)
                @include('categories._branch', ['category' => \, 'depth' => \ + 1])
            @endforeach
        </div>
    @endif
</div>
