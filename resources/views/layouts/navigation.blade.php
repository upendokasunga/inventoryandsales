@php
    $user = Auth::user();
    $navMenus = $user->getCachedMenus();
    $grouped = $navMenus->where('can_view', true)->sortBy('sort_order')->groupBy('module');
    $moduleOrder = ['Dashboard', 'Point of Sale', 'Master Data', 'Inventory', 'Purchasing', 'Sales', 'Pricing', 'Authentication', 'System', 'Reporting'];
@endphp

{{-- Sidebar --}}
<aside x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
       @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
       :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
       class="fixed inset-y-0 left-0 z-40 w-64 bg-sidebar flex flex-col shadow-2xl overflow-hidden transition-transform duration-300 lg:translate-x-0">
    {{-- Logo --}}
    <div class="flex items-center h-16 px-4 border-b border-white/5 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
            <span class="text-white font-bold text-xl tracking-wide whitespace-nowrap">{{ config('app.name', 'WholesaleTZ') }}</span>
        </a>
    </div>

    {{-- Nav links --}}
    <div class="flex-1 overflow-y-auto px-3 py-4 space-y-5 sidebar-scrollbar">
        @foreach ($moduleOrder as $module)
            @php
                $menus = $grouped->get($module)?->where('route', '!=', '#')->filter(fn($m) => Route::has($m['route']));
            @endphp
            @if ($menus && $menus->isNotEmpty())
                <div>
                    @if ($module !== 'Dashboard')
                        <p class="px-3 text-[11px] font-semibold uppercase tracking-widest text-sidebar-text/50 mb-2 whitespace-nowrap">{{ $module }}</p>
                    @endif
                    <div class="space-y-0.5">
                        @foreach ($menus as $menu)
                            @php
                                $isActive = request()->routeIs($menu['route'] . '*');
                                $linkClasses = $isActive
                                    ? 'bg-sidebar-active text-sidebar-text-active'
                                    : 'text-sidebar-text hover:text-sidebar-text-active hover:bg-sidebar-hover';
                            @endphp
                            <a href="{{ route($menu['route']) }}"
                               class="flex items-center w-full px-3 py-2.5 text-sm font-medium rounded-lg transition duration-150 {{ $linkClasses }}">
                                <span class="shrink-0">
                                    @include('layouts.nav-icons', ['route' => $menu['route']])
                                </span>
                                <span class="ml-3 whitespace-nowrap">{{ __($menu['name']) }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    {{-- User section --}}
    <div class="border-t border-white/5 shrink-0"
         x-data="dropdown"
         @click.outside="close">
        <div class="p-3">
            <button @click="toggle"
                    class="flex items-center w-full text-left text-sidebar-text hover:text-sidebar-text-active rounded-lg px-3 py-2.5 hover:bg-sidebar-hover transition">
                <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-white text-sm font-medium shrink-0">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div class="ml-3 flex-1 min-w-0">
                    <p class="text-sm font-medium truncate text-sidebar-text-active">{{ $user->name }}</p>
                    <p class="text-xs text-sidebar-text truncate">{{ $user->email }}</p>
                </div>
                <svg class="w-4 h-4 ml-2 shrink-0 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </div>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="mx-3 mb-2 bg-sidebar-hover border border-white/5 rounded-lg py-1 shadow-xl"
             style="display: none;">
            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-sidebar-text hover:text-sidebar-text-active hover:bg-white/5">
                {{ __('Profile') }}
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-sidebar-text hover:text-sidebar-text-active hover:bg-white/5">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- Mobile overlay --}}
<div x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
     @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
     x-show="sidebarOpen"
     @click="sidebarOpen = false"
     class="fixed inset-0 z-30 bg-black/40 lg:hidden"
     style="display: none;">
</div>
