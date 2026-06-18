<x-guest-layout>
    <div class="text-center mb-6">
        <h3 class="text-2xl font-bold text-slate-800">{{ __('Forgot Password?') }}</h3>
        <p class="text-sm text-slate-500 mt-1">{{ __('No problem. Enter your email and we\'ll send you a reset link.') }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email" :value="old('email')" required autofocus placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center py-2.5">
                {{ __('Send Reset Link') }}
            </x-primary-button>
        </div>

        <p class="text-center text-sm text-slate-500 mt-4">
            <a href="{{ route('login') }}" class="text-primary hover:text-primary-600 font-medium">{{ __('Back to login') }}</a>
        </p>
    </form>
</x-guest-layout>
