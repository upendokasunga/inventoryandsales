@php
    $user = Auth::user();
    $navMenus = $user->getCachedMenus();
    $menusById = $navMenus->keyBy('id');

    $allParents = $navMenus->where('is_parent', true)->sortBy('sort_order');

    $tree = collect();
    foreach ($allParents as $parent) {
        $children = $navMenus->where('parent_id', $parent['id'])
            ->where('can_view', true)
            ->where('is_visible', true)
            ->filter(fn($m) => is_null($m['route']) || Route::has($m['route']))
            ->sortBy('sort_order');

        if ($children->isNotEmpty()) {
            $sections = $children->groupBy('section');
            $tree->push([
                'parent' => $parent,
                'sections' => $sections,
            ]);
        }
    }

    $activeParentId = null;
    $currentRoute = request()->route()?->getName();
    foreach ($tree as $branch) {
        foreach ($branch['sections'] as $section => $items) {
            foreach ($items as $item) {
                if ($item['route'] && request()->routeIs($item['route'] . '*')) {
                    $activeParentId = $branch['parent']['id'];
                    break 3;
                }
            }
        }
    }
@endphp

<aside x-data="sidebarNav({{ $activeParentId ?? 'null' }})"
       @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
       :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
       class="fixed inset-y-0 left-0 z-40 w-64 bg-sidebar flex flex-col shadow-2xl overflow-hidden transition-transform duration-300 lg:translate-x-0">
    <div class="flex items-center h-16 px-4 border-b border-white/5 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
            <span class="text-white font-bold text-xl tracking-wide whitespace-nowrap">{{ config('app.name', 'WholesaleTZ') }}</span>
        </a>
    </div>

    <div class="flex-1 overflow-y-auto px-3 py-4 sidebar-scrollbar">
        <nav class="space-y-1">
            @foreach ($tree as $branch)
                @php
                    $parent = $branch['parent'];
                    $sections = $branch['sections'];
                    $hasActiveChild = $parent['id'] === $activeParentId;
                    $sectionCount = $sections->keys()->count();
                @endphp
                <div class="mb-1">
                    <button @click="toggleSection({{ $parent['id'] }})"
                            class="flex items-center w-full px-3 py-2.5 text-sm font-semibold rounded-lg transition duration-150
                                   {{ $hasActiveChild ? 'text-sidebar-text-active bg-sidebar-active' : 'text-sidebar-text hover:text-sidebar-text-active hover:bg-sidebar-hover' }}">
                        <span class="shrink-0">
                            @include('layouts.nav-icons', ['route' => null, 'name' => $parent['name']])
                        </span>
                        <span class="ml-3 flex-1 text-left whitespace-nowrap">{{ __($parent['name']) }}</span>
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-200"
                             :class="{ 'rotate-180': isOpen({{ $parent['id'] }}) }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="isOpen({{ $parent['id'] }})"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         class="mt-1 ml-2 space-y-0.5"
                         style="display: none;">
                        @foreach ($sections as $section => $items)
                            @if ($sectionCount > 1)
                                <p class="px-3 pt-2 pb-1 text-[10px] font-semibold uppercase tracking-widest text-sidebar-text/40 whitespace-nowrap">{{ __($section) }}</p>
                            @endif
                            @foreach ($items as $menu)
                                @php
                                    $isActive = $menu['route'] && request()->routeIs($menu['route'] . '*');
                                    $linkClasses = $isActive
                                        ? 'bg-sidebar-active/20 text-sidebar-text-active border-l-2 border-sidebar-active'
                                        : 'text-sidebar-text hover:text-sidebar-text-active hover:bg-sidebar-hover border-l-2 border-transparent';
                                @endphp
                                <a href="{{ $menu['route'] ? route($menu['route']) : '#' }}"
                                   class="flex items-center w-full px-3 py-2 text-sm font-medium rounded-lg transition duration-150 {{ $linkClasses }}">
                                    <span class="shrink-0">
                                        @include('layouts.nav-icons', ['route' => $menu['route'], 'name' => null])
                                    </span>
                                    <span class="ml-3 whitespace-nowrap">{{ __($menu['name']) }}</span>
                                </a>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>
    </div>

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

<div x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
     @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
     x-show="sidebarOpen"
     @click="sidebarOpen = false"
     class="fixed inset-0 z-30 bg-black/40 lg:hidden"
     style="display: none;">
</div>
