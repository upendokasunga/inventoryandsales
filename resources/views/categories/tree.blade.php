<x-app-layout>
    <x-slot name="header">
        {{ __('Category Tree') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('categories.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-blue-200 rounded-lg font-semibold text-xs text-slate-700 hover:bg-blue-50 transition">
                List View
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-lg shadow-blue-500/5 border border-blue-100 p-6">
            @forelse ($categories as $category)
                <div class="mb-2">
                    <div class="flex items-center px-4 py-2 bg-blue-50 rounded-lg">
                        <span class="text-sm font-semibold text-slate-800">{{ $category->name }}</span>
                        <span class="ml-2 text-xs text-slate-500">({{ $category->children->count() }} children)</span>
                    </div>
                    @if ($category->children->isNotEmpty())
                        <div class="ml-8 mt-1 space-y-1">
                            @foreach ($category->children as $child)
                                <div class="flex items-center px-4 py-2 bg-slate-50 rounded-lg">
                                    <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                    <span class="text-sm text-slate-700">{{ $child->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-500 text-center py-8">No categories found. <a href="{{ route('categories.create') }}" class="text-blue-600 hover:text-blue-500">Create one</a>.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
