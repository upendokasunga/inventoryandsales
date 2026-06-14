<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'WERP') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-blue-600 via-blue-800 to-sky-900">
        <div class="min-h-screen flex flex-col">
            <header class="px-6 py-4">
                <div class="max-w-7xl mx-auto flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <x-application-logo class="h-8 w-auto" />
                    </div>
                    <nav class="flex items-center space-x-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-sm text-blue-200 hover:text-white transition">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm text-blue-200 hover:text-white transition">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="text-sm bg-gradient-to-r from-blue-500 to-sky-400 hover:from-blue-400 hover:to-sky-300 text-white px-4 py-2 rounded-lg shadow-lg shadow-blue-500/25 transition">Register</a>
                            @endif
                        @endauth
                    </nav>
                </div>
            </header>

            <div class="flex-1 flex items-center justify-center px-6">
                <div class="max-w-2xl text-center">
                    <h1 class="text-4xl sm:text-5xl font-bold text-white mb-4">
                        Wholesale ERP System
                    </h1>
                    <p class="text-lg text-blue-200 mb-8">
                        Manage inventory, sales, purchases, customers, and reporting — all in one place.
                    </p>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-block bg-gradient-to-r from-blue-500 to-sky-400 hover:from-blue-400 hover:to-sky-300 text-white font-medium px-8 py-3 rounded-lg shadow-lg shadow-blue-500/30 transition">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-block bg-gradient-to-r from-blue-500 to-sky-400 hover:from-blue-400 hover:to-sky-300 text-white font-medium px-8 py-3 rounded-lg shadow-lg shadow-blue-500/30 transition">
                            Get Started
                        </a>
                    @endauth
                </div>
            </div>

            <footer class="px-6 py-4 text-center text-sm text-blue-300">
                &copy; {{ date('Y') }} {{ config('app.name', 'WERP') }}. All rights reserved.
            </footer>
        </div>
    </body>
</html>
