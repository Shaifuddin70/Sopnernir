@if (session()->has('status'))
    @php
        $status = (string) session('status');
        $message = match ($status) {
            'profile-updated' => __('Profile updated.'),
            'password-updated' => __('Password updated.'),
            'verification-link-sent' => __('A new verification link has been sent to your email address.'),
            default => $status,
        };
    @endphp

    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 4500)"
        x-show="show"
        x-transition:enter="transform transition ease-out duration-300"
        x-transition:enter-start="translate-x-full opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transform transition ease-in duration-200"
        x-transition:leave-start="translate-x-0 opacity-100"
        x-transition:leave-end="translate-x-full opacity-0"
        class="fixed right-0 top-0 z-50 w-full max-w-md p-3 sm:p-6"
        role="status"
        aria-live="polite"
    >
        <div class="flex items-start justify-between gap-3 rounded-lg bg-success px-4 py-3 text-sm text-on-primary shadow-elevation-3">
            <p class="leading-5">{{ $message }}</p>
            <button
                type="button"
                @click="show = false"
                class="rounded p-1 text-on-primary/80 hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/50"
                aria-label="{{ __('Dismiss') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="h-4 w-4 fill-current" aria-hidden="true">
                    <path d="M11.414 10l4.293-4.293a1 1 0 00-1.414-1.414L10 8.586 5.707 4.293a1 1 0 00-1.414 1.414L8.586 10l-4.293 4.293a1 1 0 101.414 1.414L10 11.414l4.293 4.293a1 1 0 001.414-1.414L11.414 10z"/>
                </svg>
            </button>
        </div>
    </div>
@endif
