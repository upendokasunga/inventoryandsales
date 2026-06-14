@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex items-center px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-sky-500 rounded-lg shadow-lg shadow-blue-500/20 transition duration-150 ease-in-out'
            : 'flex items-center px-4 py-2.5 text-sm font-medium text-blue-200 hover:text-white hover:bg-white/10 rounded-lg transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
