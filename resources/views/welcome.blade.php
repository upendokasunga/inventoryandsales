<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'WholesaleTZ') }} — Enterprise ERP</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <style>
            [x-cloak] { display: none !important; }
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white">
        {{-- Header --}}
        <header class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <x-application-logo class="text-xl" />
                    <nav class="hidden md:flex items-center space-x-8">
                        <a href="#features" class="text-sm font-medium text-slate-600 hover:text-primary transition">Features</a>
                        <a href="#" class="text-sm font-medium text-slate-600 hover:text-primary transition">Products</a>
                        <a href="#" class="text-sm font-medium text-slate-600 hover:text-primary transition">Sales</a>
                        <a href="#" class="text-sm font-medium text-slate-600 hover:text-primary transition">Reports</a>
                    </nav>
                    <div class="flex items-center space-x-3">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="erp-btn-primary">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-primary transition px-4 py-2">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="erp-btn-primary">Get Started</a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        {{-- Hero --}}
        <section class="pt-32 pb-20 lg:pt-40 lg:pb-28 bg-gradient-to-br from-primary-50 via-white to-primary-50/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:flex items-center gap-12">
                    <div class="lg:w-1/2 mb-12 lg:mb-0">
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 leading-tight mb-6">
                            Manage Your<br>
                            <span class="text-primary">Wholesale Business</span><br>
                            With Confidence
                        </h1>
                        <p class="text-lg text-slate-600 mb-8 max-w-lg leading-relaxed">
                            A complete ERP system for inventory management, sales tracking, purchasing, customer credit, and reporting — designed for wholesale operations.
                        </p>
                        <div class="flex items-center gap-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="erp-btn-primary text-sm px-6 py-3">Go to Dashboard</a>
                            @else
                                <a href="{{ route('register') }}" class="erp-btn-primary text-sm px-6 py-3">Start Free Trial</a>
                                <a href="{{ route('login') }}" class="erp-btn-secondary text-sm px-6 py-3">Watch Demo</a>
                            @endauth
                        </div>
                        <div class="flex items-center gap-8 mt-8 text-sm text-slate-500">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                No credit card required
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Free updates
                            </span>
                        </div>
                    </div>
                    <div class="lg:w-1/2 flex justify-center">
                        <div class="relative w-full max-w-lg">
                            {{-- Dashboard Illustration --}}
                            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full bg-danger"></div>
                                        <div class="w-3 h-3 rounded-full bg-warning-500"></div>
                                        <div class="w-3 h-3 rounded-full bg-success"></div>
                                    </div>
                                    <span class="text-xs text-slate-400 font-mono">Dashboard</span>
                                </div>
                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <div class="bg-primary-50 rounded-lg p-3">
                                        <p class="text-xs text-primary-400">Products</p>
                                        <p class="text-xl font-bold text-primary">1,234</p>
                                    </div>
                                    <div class="bg-success-50 rounded-lg p-3">
                                        <p class="text-xs text-success">Revenue</p>
                                        <p class="text-xl font-bold text-success-700">TSh 45M</p>
                                    </div>
                                    <div class="bg-warning-50 rounded-lg p-3">
                                        <p class="text-xs text-warning-600">Orders</p>
                                        <p class="text-xl font-bold text-warning-700">89</p>
                                    </div>
                                    <div class="bg-danger-50 rounded-lg p-3">
                                        <p class="text-xs text-danger">Alerts</p>
                                        <p class="text-xl font-bold text-danger">3</p>
                                    </div>
                                </div>
                                <div class="h-24 bg-gradient-to-r from-primary-100 to-primary-50 rounded-lg flex items-end p-3 gap-1">
                                    @foreach([40,65,45,80,55,70,90] as $h)
                                        <div class="flex-1 bg-primary rounded-t-sm" style="height: {{ $h }}%"></div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-primary-100 rounded-2xl -z-10"></div>
                            <div class="absolute -top-4 -left-4 w-20 h-20 bg-warning-100 rounded-2xl -z-10"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Features --}}
        <section id="features" class="py-20 bg-surface">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-slate-900 mb-4">Everything You Need to Run Your Wholesale Business</h2>
                    <p class="text-lg text-slate-600 max-w-2xl mx-auto">From inventory management to sales analytics, we provide all the tools in one integrated platform.</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {{-- Feature: Products --}}
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-800 mb-2">Products</h3>
                        <p class="text-sm text-slate-600">Multi-unit products with SKU and barcode engine. Track inventory across units of measure.</p>
                    </div>

                    {{-- Feature: Barcode --}}
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-xl bg-success-50 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-800 mb-2">Barcode Scan</h3>
                        <p class="text-sm text-slate-600">Automatic barcode generation and label printing. Scan barcodes for fast checkout and receiving.</p>
                    </div>

                    {{-- Feature: Sales --}}
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-xl bg-warning-50 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-800 mb-2">Proforma Invoices</h3>
                        <p class="text-sm text-slate-600">Create and manage proforma invoices with customer credit tracking and multi-unit pricing.</p>
                    </div>

                    {{-- Feature: Reports --}}
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-xl bg-danger-50 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-800 mb-2">Reports</h3>
                        <p class="text-sm text-slate-600">Real-time analytics and reporting. Sales trends, inventory health, and purchase insights.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- CTA --}}
        <section class="py-20 bg-primary">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold text-white mb-4">Ready to Transform Your Wholesale Operations?</h2>
                <p class="text-primary-200 text-lg mb-8">Join businesses that trust {{ config('app.name', 'WholesaleTZ') }} for their inventory and sales management.</p>
                @auth
                    <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-8 py-3 bg-white text-primary font-semibold rounded-lg hover:bg-primary-50 transition">Go to Dashboard</a>
                @else
                    <a href="{{ route('register') }}" class="inline-flex items-center px-8 py-3 bg-white text-primary font-semibold rounded-lg hover:bg-primary-50 transition">Get Started Free</a>
                @endauth
            </div>
        </section>

        {{-- Footer --}}
        <footer class="bg-slate-900 text-slate-400 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <x-application-logo class="text-xl" />
                    <p class="text-sm">&copy; {{ date('Y') }} {{ config('app.name', 'WholesaleTZ') }}. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </body>
</html>
