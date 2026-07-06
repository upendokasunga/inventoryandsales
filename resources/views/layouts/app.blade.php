<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config("app.name", "WholesaleTZ") }} — {{ $header ?? "Dashboard" }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <style>
            [x-cloak] { display: none !important; }
        </style>

        @vite(["resources/css/app.css", "resources/js/app.js"])
        @stack("styles")
    </head>
    <body class="font-sans antialiased"
          data-current-route="{{ request()->route()?->getName() ?? '' }}">
        <div class="min-h-screen flex flex-col">
            {{-- Sidebar --}}
            @include("layouts.navigation")

            {{-- Main Area --}}
            <div class="lg:ml-64 flex flex-col min-h-screen">
                {{-- Top Navbar --}}
                @include("layouts.topbar")

                {{-- Horizontal Submenu --}}
                @include("layouts.submenu")

                {{-- Page Content --}}
                <div class="flex-1 bg-surface">
                    <div class="px-4 lg:px-8 pt-6 pb-8">
                        @isset($header)
                            <h1 class="text-2xl font-bold text-slate-800 mb-6">{{ $header }}</h1>
                        @endisset

                        @if (session("success"))
                            <div class="mb-6 px-4 py-3 text-success-700 bg-success-50 border border-success-100 rounded-lg text-sm">{{ session("success") }}</div>
                        @endif
                        @if (session("error"))
                            <div class="mb-6 px-4 py-3 text-danger-700 bg-danger-50 border border-danger-100 rounded-lg text-sm">{{ session("error") }}</div>
                        @endif

                        {{ $slot }}
                    </div>
                </div>

                {{-- Footer --}}
                <footer class="px-4 lg:px-8 py-4 border-t border-slate-200/60 bg-white">
                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span>&copy; {{ date("Y") }} {{ config("app.name", "WholesaleTZ") }}. All rights reserved.</span>
                        <span>v{{ config("app.version", "1.0.0") }}</span>
                    </div>
                </footer>
            </div>

            {{-- Mobile Overlay --}}
            <div x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
                 @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
                 x-show="sidebarOpen"
                 @click="sidebarOpen = false"
                 class="fixed inset-0 z-30 bg-black/40 lg:hidden"
                 style="display: none;">
            </div>
        </div>

        @stack("scripts")
    </body>
</html>
