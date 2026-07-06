@php
    $modules = config('erp-modules.modules');
    $currentRoute = request()->route()?->getName();

    $moduleData = [];
    foreach ($modules as $idx => $module) {
        $children = [];
        foreach ($module['children'] as $child) {
            $routeExists = !is_null($child['route']) && Route::has($child['route']);
            $children[] = [
                'name' => $child['name'],
                'route' => $child['route'],
                'url' => $routeExists ? route($child['route']) : '#',
                'routeExists' => $routeExists,
            ];
        }
        $moduleData[] = [
            'name' => $module['name'],
            'children' => $children,
        ];
    }

    $initialActive = null;
    foreach ($modules as $idx => $module) {
        if ($module['route'] && request()->routeIs($module['route'] . '*')) {
            $initialActive = $idx;
            break;
        }
        foreach ($module['children'] as $child) {
            if ($child['route'] && request()->routeIs($child['route'] . '*')) {
                $initialActive = $idx;
                break 2;
            }
        }
    }
@endphp

<div x-data="erpSubmenu"
     x-show="$store.erp.activeModule !== null"
     class="bg-white border-b border-slate-200/60 shadow-sm"
     style="{{ $initialActive !== null ? '' : 'display: none;' }}">
    <div class="px-4 lg:px-6">
        <div class="flex items-center h-10">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                <span x-text="activeModuleName"></span>
            </div>
        </div>

        <div class="flex items-center gap-1 pb-2 overflow-x-auto scrollbar-none">
            <template x-for="(child, idx) in children" :key="idx">
                <template x-if="child.routeExists">
                    <a :href="child.url"
                       class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-md transition whitespace-nowrap"
                       :class="activeChild === idx
                           ? 'bg-primary text-white shadow-sm'
                           : 'text-slate-600 hover:text-primary hover:bg-primary-50'"
                       @click="setActiveChild(idx)">
                        <span x-text="child.name"></span>
                    </a>
                </template>
                <template x-if="!child.routeExists">
                    <span class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-md text-slate-300 cursor-not-allowed whitespace-nowrap"
                          x-text="child.name"
                          title="Coming soon"></span>
                </template>
            </template>
        </div>
    </div>
</div>

<script>
    window._erpModulesData = @json($moduleData);
    window._erpInitialModule = @json($initialActive);

    if (window._erpInitialModule !== null) {
        localStorage.setItem('erp-active-module', JSON.stringify(window._erpInitialModule));
    }
    localStorage.setItem('erp-modules-data', JSON.stringify(window._erpModulesData));

    document.addEventListener('alpine:init', () => {
        Alpine.data('erpSubmenu', () => ({
            activeChild: 0,
            modules: window._erpModulesData || [],

            init() {
                if (this.$store.erp.activeModule !== null && this.hasChildren) {
                    this.setActiveFromRoute();
                }
            },

            get hasChildren() {
                const am = this.$store.erp.activeModule;
                if (am === null) return false;
                return this.modules[am]?.children?.length > 0;
            },

            get children() {
                const am = this.$store.erp.activeModule;
                if (am === null) return [];
                return this.modules[am]?.children || [];
            },

            get activeModuleName() {
                const am = this.$store.erp.activeModule;
                if (am === null) return '';
                return this.modules[am]?.name || '';
            },

            setActiveChild(idx) {
                this.activeChild = idx;
            },

            setActiveFromRoute() {
                const cr = '{{ $currentRoute }}';
                this.children.forEach((child, idx) => {
                    if (child.route && cr && cr.startsWith(child.route)) {
                        this.activeChild = idx;
                    }
                });
            },
        }));
    });
</script>
