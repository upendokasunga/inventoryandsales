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
    <body class="font-sans antialiased">
        <div class="min-h-screen">
            @include("layouts.navigation")

            {{-- Top Navbar --}}
            <div class="sticky top-0 z-30 bg-white border-b border-slate-200/60 shadow-sm lg:ml-64">
                <div class="flex items-center justify-between h-16 px-4 lg:px-6">
                    <div class="flex items-center gap-3 ml-auto" x-data="clock">
                        <div class="hidden md:flex items-center gap-3 text-sm text-slate-500">
                            <span x-text="date" class="hidden lg:inline"></span>
                            <span class="hidden lg:inline text-slate-300">|</span>
                            <span x-text="time" class="font-mono text-xs bg-slate-50 px-2 py-1 rounded-md border border-slate-200"></span>
                        </div>

                        <button class="relative p-2 rounded-lg text-slate-500 hover:text-primary hover:bg-primary-50 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-danger rounded-full"></span>
                        </button>

                        <div class="relative" x-data="dropdown" @click.outside="close">
                            <button @click="toggle" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-50 transition">
                                <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-white text-sm font-medium">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <span class="hidden md:block text-sm font-medium text-slate-700">{{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4 text-slate-400" :class="{"rotate-180": open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-200 py-1 z-50"
                                 style="display: none;">
                                <a href="{{ route("profile.edit") }}" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    {{ __("Profile") }}
                                </a>
                                <hr class="border-slate-100">
                                <form method="POST" action="{{ route("logout") }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        {{ __("Log Out") }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Content --}}
            <div class="min-h-[calc(100vh-4rem)] lg:ml-64">
                @isset($header)
                    <div class="px-4 lg:px-8 pt-6 pb-2">
                        <h1 class="text-[36px] font-bold text-slate-800">{{ $header }}</h1>
                    </div>
                @endisset

                <div class="px-4 lg:px-8 py-6">
                    @if (session("success"))
                        <div class="mb-6 px-4 py-3 text-success-700 bg-success-50 border border-success-100 rounded-lg text-sm">{{ session("success") }}</div>
                    @endif
                    @if (session("error"))
                        <div class="mb-6 px-4 py-3 text-danger-700 bg-danger-50 border border-danger-100 rounded-lg text-sm">{{ session("error") }}</div>
                    @endif

                    {{ $slot }}
                </div>
            </div>
        </div>

        @stack("scripts")
    </body>
</html>
