<x-guest-layout>
    <div class="mx-auto w-full max-w-md" x-data="{ tab: '{{ $errors->has('phone') ? 'phone' : 'login' }}' }">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-7">
            <div class="mb-6">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                    {{ __('Welcome back') }}
                </h1>
                <p class="mt-2 text-sm text-gray-600">
                    {{ __('Sign in with password, or use your phone to view your investment details.') }}
                </p>
            </div>

            <div class="mb-5 grid grid-cols-2 rounded-lg bg-gray-100 p-1 text-sm">
                <button type="button" @click="tab = 'login'"
                    class="rounded-md px-3 py-2 font-medium transition"
                    :class="tab === 'login' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'">
                    {{ __('Password login') }}
                </button>
                <button type="button" @click="tab = 'phone'"
                    class="rounded-md px-3 py-2 font-medium transition"
                    :class="tab === 'phone' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'">
                    {{ __('Phone access') }}
                </button>
            </div>

            <form x-show="tab === 'login'" method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="mt-1 block w-full rounded-lg border-gray-300" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <x-input-label for="password" :value="__('Password')" />
                            @if (Route::has('password.request'))
                                <a class="text-xs font-medium text-indigo-600 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-md"
                                    href="{{ route('password.request') }}">
                                    {{ __('Forgot password?') }}
                                </a>
                            @endif
                        </div>

                        <x-text-input id="password" class="mt-1 block w-full rounded-lg border-gray-300"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-gray-600">
                        <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            name="remember">
                        <span>{{ __('Remember me') }}</span>
                    </label>

                    <x-primary-button class="w-full justify-center py-2.5 text-sm">
                        {{ __('Log in') }}
                    </x-primary-button>
            </form>

            <form x-show="tab === 'phone'" method="POST" action="{{ route('phone-access.store') }}" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="phone" :value="__('Phone number')" />
                    <x-text-input id="phone" class="mt-1 block w-full rounded-lg border-gray-300" type="text" name="phone" :value="old('phone')" autocomplete="tel" />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    <p class="mt-2 text-xs text-gray-500">
                        {{ __('This mode only shows your investment summary and your own investment details.') }}
                    </p>
                </div>

                <x-primary-button class="w-full justify-center py-2.5 text-sm">
                    {{ __('View my investments') }}
                </x-primary-button>
            </form>
        </div>
    </div>
</x-guest-layout>
