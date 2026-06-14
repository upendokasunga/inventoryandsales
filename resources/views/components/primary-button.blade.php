@props(['disabled' => false])

<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-sky-500 border border-transparent rounded-lg font-semibold text-xs text-white hover:from-blue-500 hover:to-sky-400 focus:from-blue-500 focus:to-sky-400 active:from-blue-700 active:to-sky-600 shadow-lg shadow-blue-500/20 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150']) }}>
    {{ $slot }}
</button>
