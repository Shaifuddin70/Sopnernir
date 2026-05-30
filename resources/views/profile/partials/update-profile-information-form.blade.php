<section class="space-y-8">
    <header>
        <h2 class="text-lg font-medium text-foreground">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-foreground-muted">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="max-w-6xl space-y-8">
        @csrf
        @method('patch')

        @include('partials.user-nominee-fields', ['user' => $user])

        <div class="space-y-6 border-t border-gray-200 pt-8">
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm text-foreground">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" type="submit" class="underline text-sm text-foreground-muted hover:text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif

            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Save') }}</x-primary-button>

                @if (session('status') === 'profile-updated')
                    <p
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-foreground-muted"
                    >{{ __('Saved.') }}</p>
                @endif
            </div>
        </div>
    </form>
</section>
