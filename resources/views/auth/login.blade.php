<x-guest-layout>
    <div class="mx-auto w-full max-w-md" x-data="{ tab: '{{ $errors->has('phone') ? 'phone' : 'login' }}' }">
        <div class="ui-auth-card">
            <div class="ui-auth-card-accent" aria-hidden="true"></div>
            <div class="p-6 sm:p-7">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">
                        {{ __('Welcome back') }}
                    </h1>
                    <p class="mt-2 text-sm text-foreground-muted">
                        {{ __('Sign in with password, or use your phone to view your investment details.') }}
                    </p>
                </div>

                <div class="mb-5 grid grid-cols-2 rounded-xl bg-surface-variant p-1 text-sm">
                    <button type="button" @click="tab = 'login'"
                        class="rounded-lg px-3 py-2 font-medium transition"
                        :class="tab === 'login' ? 'bg-primary text-on-primary shadow-sm' : 'text-foreground-muted hover:bg-surface-secondary hover:text-foreground'">
                        {{ __('Password login') }}
                    </button>
                    <button type="button" @click="tab = 'phone'"
                        class="rounded-lg px-3 py-2 font-medium transition"
                        :class="tab === 'phone' ? 'bg-primary text-on-primary shadow-sm' : 'text-foreground-muted hover:bg-surface-secondary hover:text-foreground'">
                        {{ __('Phone access') }}
                    </button>
                </div>

                <form x-show="tab === 'login'" method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <x-input-label for="password" :value="__('Password')" />
                            @if (Route::has('password.request'))
                                <a class="rounded-md text-xs font-semibold text-primary hover:text-primary-hover focus:outline-none focus:ring-2 focus:ring-primary"
                                    href="{{ route('password.request') }}">
                                    {{ __('Forgot password?') }}
                                </a>
                            @endif
                        </div>

                        <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-foreground-muted">
                        <input id="remember_me" type="checkbox" class="rounded border-line bg-surface-secondary text-primary shadow-sm focus:ring-primary"
                            name="remember">
                        <span>{{ __('Remember me') }}</span>
                    </label>

                    <x-primary-button class="w-full justify-center py-2.5 text-sm normal-case tracking-normal">
                        {{ __('Log in') }}
                    </x-primary-button>
                </form>

                <form x-show="tab === 'phone'" method="POST" action="{{ route('phone-access.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="phone" :value="__('Phone number')" />
                        <x-text-input id="phone" class="mt-1 block w-full" type="text" name="phone" :value="old('phone')" autocomplete="tel" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        <p class="mt-2 text-xs text-foreground-muted">
                            {{ __('This mode only shows your investment summary and your own investment details.') }}
                        </p>
                    </div>

                    <x-primary-button class="w-full justify-center py-2.5 text-sm normal-case tracking-normal">
                        {{ __('View my investments') }}
                    </x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
