<header class="sticky top-4 z-30 bg-white rounded-2xl border border-gray-100/80 shadow-premium-xl mx-6 lg:mx-8">
    <div class="flex items-center justify-between h-[64px] px-5">
        {{-- Left: Mobile Toggle + Breadcrumb --}}
        <div class="flex items-center gap-4">
            <button x-data @click="$dispatch('toggle-sidebar')" class="lg:hidden p-2.5 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <div>
                <nav class="flex items-center gap-1.5 text-sm">
                    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </a>
                    @isset($header)
                        <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        <span class="text-gray-800 font-medium truncate max-w-[200px] lg:max-w-xs">{{ $header }}</span>
                    @endisset
                </nav>
            </div>
        </div>

        {{-- Right: Clock, Notifications, Profile --}}
        <div class="flex items-center gap-2" x-data="clock">
            {{-- Clock --}}
            <div class="hidden md:flex items-center gap-2 px-3 py-1.5 bg-gray-50 rounded-xl text-xs">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-text="date" class="text-gray-500 hidden lg:inline"></span>
                <span x-text="time" class="font-mono text-gray-700 font-medium"></span>
            </div>

            {{-- Notifications --}}
            <div class="relative" x-data="notifications" @click.outside="close">
                <button @click="toggle"
                        class="relative p-2.5 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span x-show="unreadCount > 0"
                          x-text="unreadCount"
                          class="absolute top-1.5 right-1.5 w-4 h-4 bg-danger text-white text-[9px] font-bold rounded-full flex items-center justify-center ring-2 ring-white"></span>
                </button>
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-premium-lg border border-gray-100 py-1 z-50"
                     style="display: none;">
                    <div class="px-5 py-3 border-b border-gray-50">
                        <p class="text-sm font-semibold text-gray-900">{{ __('Notifications') }}</p>
                    </div>
                    <div class="max-h-72 overflow-y-auto">
                        <template x-for="notification in notifications" :key="notification.id">
                            <div class="px-5 py-3.5 hover:bg-gray-50 cursor-pointer flex items-start gap-3 transition-colors"
                                 :class="{'bg-primary-50/30': !notification.read}"
                                 @click="markRead(notification.id)">
                                <div class="w-2 h-2 mt-1.5 rounded-full shrink-0"
                                     :class="notification.read ? 'bg-transparent' : 'bg-primary'"></div>
                                <div>
                                    <p class="text-sm text-gray-700" x-text="notification.message"></p>
                                    <p class="text-xs text-gray-400 mt-0.5" x-text="notification.time"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="px-5 py-3 border-t border-gray-50 text-center">
                        <a href="#" class="text-xs font-medium text-primary hover:text-primary-600 transition-colors">{{ __('View all notifications') }}</a>
                    </div>
                </div>
            </div>

            {{-- Theme Toggle Placeholder --}}
            <button class="hidden lg:flex p-2.5 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            </button>

            {{-- User Profile --}}
            <div class="relative" x-data="dropdown" @click.outside="close">
                <button @click="toggle"
                        class="flex items-center gap-2.5 p-1.5 pr-3 rounded-xl hover:bg-gray-100 transition-all group">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white text-xs font-bold shadow-sm">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <span class="hidden md:block text-sm font-medium text-gray-700">{{ Auth::user()->name }}</span>
                    <svg class="w-4 h-4 text-gray-300 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                     class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-premium-lg border border-gray-100 py-1.5 z-50"
                     style="display: none;">
                    <div class="px-4 py-3 border-b border-gray-50">
                        <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-400">{{ Auth::user()->email }}</p>
                    </div>
                    <a href="{{ route("profile.edit") }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        {{ __("Profile") }}
                    </a>
                    <form method="POST" action="{{ route("logout") }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            {{ __("Log Out") }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
