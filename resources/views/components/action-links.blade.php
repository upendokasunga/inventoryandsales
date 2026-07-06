@props([
    'view' => null,
    'edit' => null,
    'delete' => null,
    'extra' => '',
])

<div {{ $attributes->merge(['class' => 'flex items-center gap-2 text-sm font-medium']) }}>
    @if ($view)
        <a href="{{ $view }}" class="text-blue-600 hover:text-blue-500 transition">View</a>
    @endif
    @if ($edit)
        <a href="{{ $edit }}" class="text-blue-600 hover:text-blue-500 transition">Edit</a>
    @endif
    {{ $extra }}
    @if ($delete)
        <form action="{{ $delete }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this item?');">
            @csrf @method('DELETE')
            <button type="submit" class="text-danger hover:text-danger/80 transition">Delete</button>
        </form>
    @endif
</div>
