<x-guest-layout>
    <div class="text-center mb-6">
        <h3 class="text-2xl font-bold text-slate-800">{{ __('Verify Email') }}</h3>
        <p class="text-sm text-slate-500 mt-1">{{ __('Thanks for signing up! Please verify your email address to continue.') }}</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 px-4 py-3 text-success-700 bg-success-50 border border-success-100 rounded-lg text-sm">
            {{ __('A new verification link has been sent to your email.') }}
        </div>
    @endif

    <div class="space-y-3 mt-6">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="w-full justify-center py-2.5">
                {{ __('Resend Verification Email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-center py-2.5 text-sm text-slate-500 hover:text-slate-700 transition">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
