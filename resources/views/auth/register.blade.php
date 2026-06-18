<x-guest-layout>
    <div class="text-center mb-6">
        <h3 class="text-2xl font-bold text-slate-800">{{ __('Create Account') }}</h3>
        <p class="text-sm text-slate-500 mt-1">{{ __('Get started with your free account') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1.5 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Your full name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1.5 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password"
                            placeholder="Create a strong password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1.5 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password"
                            placeholder="Confirm your password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center py-2.5 bg-success hover:bg-success-600">
                {{ __('Create Account') }}
            </x-primary-button>
        </div>

        <p class="text-center text-sm text-slate-500 mt-4">
            {{ __('Already have an account?') }}
            <a href="{{ route('login') }}" class="text-primary hover:text-primary-600 font-medium">{{ __('Sign in') }}</a>
        </p>
    </form>
</x-guest-layout>
