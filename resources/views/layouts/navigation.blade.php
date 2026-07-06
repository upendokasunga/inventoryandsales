@php
    $modules = config('erp-modules.modules');
    $currentRoute = request()->route()?->getName();

    $activeModuleIndex = null;
    foreach ($modules as $idx => $module) {
        if ($module['route'] && request()->routeIs($module['route'] . '*')) {
            $activeModuleIndex = $idx;
            break;
        }
        foreach ($module['children'] as $child) {
            if ($child['route'] && request()->routeIs($child['route'] . '*')) {
                $activeModuleIndex = $idx;
                break 2;
            }
        }
    }

    $user = Auth::user();
@endphp

<aside x-data="erpSidebar({{ $activeModuleIndex ?? 'null' }})"
       @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
       :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
       class="fixed top-4 left-4 bottom-4 z-40 w-[280px] bg-white rounded-2xl border border-gray-100/80 shadow-premium-xl flex flex-col overflow-hidden transition-all duration-300 lg:translate-x-0">
    {{-- Logo Area --}}
    <div class="flex items-center h-[72px] px-6 border-b border-sidebar-border shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white text-sm font-bold shadow-lg shadow-primary-500/20">
                W
            </div>
            <div>
                <span class="text-lg font-bold text-gray-900 tracking-tight">{{ config('app.name', 'WholesaleTZ') }}</span>
                <span class="block text-[10px] font-medium text-gray-400 uppercase tracking-wider">Enterprise ERP</span>
            </div>
        </a>
    </div>

    {{-- Navigation Label --}}
    <div class="px-6 pt-6 pb-3">
        <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-400">Main Menu</p>
    </div>

    {{-- Sidebar Navigation --}}
    <div class="flex-1 overflow-y-auto px-4 pb-4 sidebar-scrollbar">
        <nav class="space-y-1">
            @foreach ($modules as $idx => $module)
                @php
                    $firstChild = collect($module['children'])->firstWhere('route');
                    $targetRoute = $module['route'] ?? ($firstChild['route'] ?? null);
                @endphp
                <div>
                    @if ($targetRoute)
                        <a href="{{ route($targetRoute) }}"
                           class="flex items-center gap-3 w-full px-4 py-3 text-sm font-medium rounded-xl transition-all duration-150 group"
                           :class="isActive({{ $idx }}) ? 'bg-sidebar-active text-sidebar-text-active' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-gray-900'">
                            <span class="nav-icon" :class="isActive({{ $idx }}) ? 'text-primary-500' : 'text-gray-400 group-hover:text-gray-600'">
                                @include('layouts.nav-icons', ['route' => null, 'name' => $module['name']])
                            </span>
                            {{ __($module['name']) }}
                            @if ($isActive = $idx === $activeModuleIndex)
                                <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                            @endif
                        </a>
                    @else
                        <span class="flex items-center gap-3 w-full px-4 py-3 text-sm font-medium rounded-xl text-gray-300 cursor-not-allowed">
                            <span class="nav-icon text-gray-300">
                                @include('layouts.nav-icons', ['route' => null, 'name' => $module['name']])
                            </span>
                            {{ __($module['name']) }}
                        </span>
                    @endif
                </div>
            @endforeach
        </nav>
    </div>

    {{-- User Profile Footer --}}
    <div class="shrink-0 border-t border-sidebar-border px-4 py-4"
         x-data="dropdown"
         @click.outside="close">
        <button @click="toggle"
                class="flex items-center gap-3 w-full text-left rounded-xl px-3 py-2.5 hover:bg-sidebar-hover transition-all duration-150 group">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white text-sm font-medium shrink-0 shadow-sm">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">{{ $user->name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ $user->email }}</p>
            </div>
            <svg class="w-4 h-4 shrink-0 text-gray-300 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="mt-2 bg-white rounded-xl shadow-premium-lg border border-gray-100 py-1.5"
             style="display: none;">
            <a href="{{ route('profile.edit') }}"
               class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                {{ __('Profile') }}
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </div>
</aside>
