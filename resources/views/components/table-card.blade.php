@props([
    'title' => '',
    'icon' => '',
    'actions' => '',
    'empty' => false,
    'emptyMessage' => 'No data found.',
    'colspan' => 1,
])

<div {{ $attributes->merge(['class' => 'erp-card overflow-hidden']) }}>
    @if ($title || $actions)
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                @if ($icon)
                    {!! $icon !!}
                @endif
                @if ($title)
                    <h3 class="text-sm font-semibold text-slate-800">{{ $title }}</h3>
                @endif
            </div>
            @if ($actions)
                <div class="flex items-center gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="erp-table">
            {{ $slot }}
            @if ($empty)
                <tfoot>
                    <tr>
                        <td colspan="{{ $colspan }}" class="px-6 py-12">
                            <div class="flex flex-col items-center justify-center text-center">
                                <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                <p class="text-sm text-slate-400">{{ $emptyMessage }}</p>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
