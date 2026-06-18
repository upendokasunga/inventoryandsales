@props(['disabled' => false])

<button {{ $attributes->merge(['type' => 'button', 'class' => 'erp-btn-secondary']) }}>
    {{ $slot }}
</button>
