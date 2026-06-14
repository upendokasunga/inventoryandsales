<x-app-layout>
    <x-slot name="header">
        {{ __('Edit User') }}: {{ $user->name }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf @method('PATCH')

            <div class="bg-white rounded-xl shadow-lg shadow-blue-500/5 border border-blue-100 mb-6">
                <div class="p-6">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                            class="mt-1 block w-full rounded-lg border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                            class="mt-1 block w-full rounded-lg border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                class="rounded border-blue-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="ml-2 text-sm text-slate-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg shadow-blue-500/5 border border-blue-100 mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">Group Assignment</h3>
                    <p class="text-sm text-slate-500 mb-4">Select which groups this user belongs to.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach ($groups as $group)
                            <label class="flex items-center p-3 rounded-lg border border-blue-100 hover:bg-blue-50 cursor-pointer">
                                <input type="checkbox" name="groups[]" value="{{ $group->id }}"
                                    {{ $user->groups->contains($group->id) ? 'checked' : '' }}
                                    class="rounded border-blue-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-slate-700">{{ $group->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('users.index') }}" class="mr-4 inline-flex items-center px-4 py-2 bg-white border border-blue-200 rounded-lg font-semibold text-xs text-slate-700 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">Cancel</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-sky-500 hover:from-blue-500 hover:to-sky-400 border border-transparent rounded-lg font-semibold text-xs text-white shadow-lg shadow-blue-500/20 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">Update User</button>
            </div>
        </form>
    </div>
</x-app-layout>
