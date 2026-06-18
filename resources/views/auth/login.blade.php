<x-guest-layout>
    <div class="text-center mb-6">
        <h3 class="text-2xl font-bold text-slate-800">{{ __('Welcome Back') }}</h3>
        <p class="text-sm text-slate-500 mt-1">{{ __('Sign in to your account to continue') }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1.5 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            placeholder="Enter your password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded erp-input" name="remember">
                <span class="ms-2 text-sm text-slate-600">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-primary hover:text-primary-600 font-medium" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center py-2.5">
                {{ __('Sign In') }}
            </x-primary-button>
        </div>

        @if (Route::has('register'))
            <p class="text-center text-sm text-slate-500 mt-4">
                {{ __("Don't have an account?") }}
                <a href="{{ route('register') }}" class="text-primary hover:text-primary-600 font-medium">{{ __('Create one') }}</a>
            </p>
        @endif
    </form>
</x-guest-layout>
