<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'WholesaleTZ') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex">
            {{-- Branding Side --}}
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-primary-50 to-primary-100 items-center justify-center p-12 relative overflow-hidden">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiMxRTRBOTIiIGZpbGwtb3BhY2l0eT0iMC4wMyI+PHBhdGggZD0iTTM2IDM0djItSDI0di0yaDEyek0zNiAyNHYySDI0di0yaDEyeiIvPjwvZz48L2c+PC9zdmc+')] opacity-50"></div>

                <div class="relative text-center max-w-md">
                    <div class="mb-8">
                        <x-application-logo class="h-16 w-auto mx-auto" />
                    </div>
                    <p class="text-primary-400 text-lg mb-8">Enterprise Inventory & Sales Management System</p>

                    {{-- Warehouse Illustration --}}
                    <div class="flex justify-center">
                        <svg class="w-64 h-64 text-primary-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="0.5">
                            <rect x="2" y="10" width="20" height="12" rx="1" stroke="currentColor" fill="currentColor" fill-opacity="0.1"/>
                            <path d="M2 14h20M2 18h20M6 22V14M10 22V14M14 22V14M18 22V14" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M3 10L12 3L21 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <rect x="16" y="4" width="3" height="4" rx="0.5" stroke="currentColor" stroke-width="1"/>
                            <circle cx="16.5" cy="6" r="0.5" fill="currentColor"/>
                            <rect x="8" y="15" width="8" height="3" rx="0.5" stroke="currentColor" stroke-width="1" fill="currentColor" fill-opacity="0.2"/>
                            <rect x="9" y="16" width="2" height="1" rx="0.25" fill="currentColor" fill-opacity="0.3"/>
                            <rect x="13" y="16" width="2" height="1" rx="0.25" fill="currentColor" fill-opacity="0.3"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Form Side --}}
            <div class="w-full lg:w-1/2 flex items-center justify-center p-6 bg-surface">
                <div class="w-full max-w-md">
                    <div class="text-center mb-8 lg:hidden">
                        <x-application-logo class="h-12 w-auto mx-auto mb-4" />
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
