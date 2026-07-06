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
       class="fixed inset-y-0 left-0 z-40 w-64 bg-sidebar flex flex-col shadow-2xl overflow-hidden transition-transform duration-300 lg:translate-x-0">
    {{-- Logo --}}
    <div class="flex items-center h-16 px-4 border-b border-white/5 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
            <span class="text-white font-bold text-xl tracking-wide whitespace-nowrap">{{ config('app.name', 'WholesaleTZ') }}</span>
        </a>
    </div>

    {{-- Module Label --}}
    <div class="px-4 pt-4 pb-2">
        <p class="text-[10px] font-semibold uppercase tracking-widest text-sidebar-text/40">ERP Modules</p>
    </div>

    {{-- Sidebar Navigation --}}
    <div class="flex-1 overflow-y-auto px-3 py-2 sidebar-scrollbar">
        <nav class="space-y-1">
            @foreach ($modules as $idx => $module)
                @php
                    $hasRoute = !is_null($module['route']);
                @endphp
                <div>
                    @if ($hasRoute)
                        <a href="{{ route($module['route']) }}"
                           class="flex items-center w-full px-3 py-2.5 text-sm font-semibold rounded-lg transition duration-150"
                           :class="isActive({{ $idx }}) ? 'text-sidebar-text-active bg-sidebar-active' : 'text-sidebar-text hover:text-sidebar-text-active hover:bg-sidebar-hover'">
                            <span class="shrink-0">
                                @include('layouts.nav-icons', ['route' => null, 'name' => $module['name']])
                            </span>
                            <span class="ml-3 flex-1 text-left whitespace-nowrap">{{ __($module['name']) }}</span>
                        </a>
                    @else
                        <button @click="activateModule({{ $idx }})"
                                class="flex items-center w-full px-3 py-2.5 text-sm font-semibold rounded-lg transition duration-150"
                                :class="isActive({{ $idx }}) ? 'text-sidebar-text-active bg-sidebar-active' : 'text-sidebar-text hover:text-sidebar-text-active hover:bg-sidebar-hover'">
                            <span class="shrink-0">
                                @include('layouts.nav-icons', ['route' => null, 'name' => $module['name']])
                            </span>
                            <span class="ml-3 flex-1 text-left whitespace-nowrap">{{ __($module['name']) }}</span>
                            <svg class="w-4 h-4 shrink-0 text-sidebar-text/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    @endif
                </div>
            @endforeach
        </nav>
    </div>

    {{-- User Footer --}}
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
