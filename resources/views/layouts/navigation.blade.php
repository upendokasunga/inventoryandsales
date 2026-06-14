@php
    $user = Auth::user();
    $navMenus = $user->getCachedMenus();
@endphp

<nav x-data="{ open: false }" class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-blue-800 to-blue-950 flex flex-col shadow-xl shadow-blue-500/10">
    {{-- Logo --}}
    <div class="flex items-center justify-between h-16 px-4 border-b border-blue-700/30">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
            <x-application-logo class="h-8 w-auto" />
        </a>
        <button @click="open = ! open" class="lg:hidden text-blue-200 hover:text-white">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Nav links --}}
    <div class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            {{ __('Dashboard') }}
        </x-nav-link>

        @foreach ($navMenus->where('can_view', true)->sortBy('sort_order') as $menu)
            @if ($menu['route'] !== 'dashboard' && $menu['route'] !== '#' && \Illuminate\Support\Facades\Route::has($menu['route']))
                <x-nav-link :href="route($menu['route'])" :active="request()->routeIs($menu['route'] . '*')">
                    @if ($menu['icon'])
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    @endif
                    {{ __($menu['name']) }}
                </x-nav-link>
            @endif
        @endforeach
    </div>

    {{-- User --}}
    <div class="border-t border-blue-700/30 p-4" x-data="{ userOpen: false }" @click.outside="userOpen = false">
        <button @click="userOpen = ! userOpen" class="flex items-center w-full text-left text-blue-200 hover:text-white">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-sky-400 flex items-center justify-center text-white text-sm font-medium shadow-lg">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div class="ml-3 flex-1 min-w-0">
                <p class="text-sm font-medium truncate">{{ $user->name }}</p>
                <p class="text-xs text-blue-300 truncate">{{ $user->email }}</p>
            </div>
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="userOpen"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="mt-2 bg-white/10 backdrop-blur-xl border border-blue-700/30 rounded-lg py-1 shadow-xl"
             style="display: none;"
             @click="userOpen = false">
            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-blue-200 hover:text-white hover:bg-white/10">
                {{ __('Profile') }}
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-blue-200 hover:text-white hover:bg-white/10">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </div>
</nav>
