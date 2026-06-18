@props(['disabled' => false])

<button {{ $attributes->merge(['type' => 'submit', 'class' => 'erp-btn-danger']) }}>
    {{ $slot }}
</button>
