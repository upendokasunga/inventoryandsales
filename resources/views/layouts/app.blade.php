<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config("app.name", "WholesaleTZ") }} — {{ $header ?? "Dashboard" }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        <style>[x-cloak] { display: none !important; }</style>
        @vite(["resources/css/app.css", "resources/js/app.js"])
        @stack("styles")
    </head>
    <body class="font-sans antialiased"
          data-current-route="{{ request()->route()?->getName() ?? '' }}">
        <div class="min-h-screen">
            {{-- Sidebar --}}
            @include("layouts.navigation")

            {{-- Main Area --}}
            <div class="lg:pl-[296px] min-h-screen flex flex-col">
                {{-- Top Header --}}
                @include("layouts.topbar")

                {{-- Horizontal Submenu --}}
                @include("layouts.submenu")

                {{-- Page Content --}}
                <div class="flex-1 px-6 lg:px-8 pt-6 pb-8">
                    @if (session("success"))
                        <div class="mb-6 erp-alert-success">{{ session("success") }}</div>
                    @endif
                    @if (session("error"))
                        <div class="mb-6 erp-alert-error">{{ session("error") }}</div>
                    @endif

                    {{-- Page Header Slot --}}
                    @isset($header)
                        @if(request()->route()?->getName() === 'dashboard')
                            <div class="erp-page-header mb-8">
                                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                    <div>
                                        <h1 class="text-3xl lg:text-4xl font-bold text-white">{{ $header }}</h1>
                                        @isset($headerDescription)
                                            <p class="mt-1.5 text-white/70 text-sm lg:text-base">{{ $headerDescription }}</p>
                                        @endisset
                                    </div>
                                    @isset($headerActions)
                                        <div class="flex items-center gap-3">
                                            {{ $headerActions }}
                                        </div>
                                    @endisset
                                </div>
                            </div>
                        @else
                            <div class="mb-6">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div>
                                        <h1 class="text-2xl font-bold text-slate-800">{{ $header }}</h1>
                                        @isset($headerDescription)
                                            <p class="mt-1 text-sm text-slate-500">{{ $headerDescription }}</p>
                                        @endisset
                                    </div>
                                    @isset($headerActions)
                                        <div class="flex items-center gap-3 shrink-0">
                                            {{ $headerActions }}
                                        </div>
                                    @endisset
                                </div>
                            </div>
                        @endif
                    @endisset

                    {{-- Main Slot --}}
                    {{ $slot }}
                </div>

                {{-- Footer --}}
                <footer class="px-6 lg:px-8 py-4 border-t border-gray-100 bg-white">
                    <div class="flex items-center justify-between text-xs text-gray-400">
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
                 class="fixed inset-0 z-30 bg-gray-900/30 backdrop-blur-sm lg:hidden"
                 style="display: none;">
            </div>
        </div>

        @stack("scripts")
    </body>
</html>
