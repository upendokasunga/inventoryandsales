@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'erp-input']) }}>
