<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'WERP') }} — {{ $header ?? 'Dashboard' }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-blue-50 via-white to-sky-50">
        @include('layouts.navigation')

        <div class="lg:pl-64">
            @isset($header)
                <header class="bg-white/90 backdrop-blur-sm border-b border-blue-100 sticky top-0 z-40">
                    <div class="px-4 sm:px-6 lg:px-8 py-4">
                        <div class="flex items-center justify-between">
                            <h1 class="text-xl font-semibold text-slate-800">
                                {{ $header }}
                            </h1>
                            @if (session('success'))
                                <span class="text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
                                    {{ session('success') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </header>
            @endisset

            <main class="p-4 sm:p-6 lg:p-8">
                @if (!isset($header) && session('success'))
                    <div class="mb-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-2">
                        {{ session('success') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
