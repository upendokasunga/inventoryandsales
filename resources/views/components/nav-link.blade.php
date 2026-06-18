@props(['active' => false])

@php
$classes = $active
    ? 'flex items-center w-full px-3 py-2.5 text-sm font-medium text-sidebar-text-active bg-sidebar-active rounded-lg transition duration-150'
    : 'flex items-center w-full px-3 py-2.5 text-sm font-medium text-sidebar-text hover:text-sidebar-text-active hover:bg-sidebar-hover rounded-lg transition duration-150';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
