<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex h-10 items-center justify-center rounded-full border border-white/70 bg-white/80 px-4 text-sm font-medium text-primary shadow-sm backdrop-blur-md transition duration-150 ease-in-out hover:bg-white focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-1 disabled:opacity-50 dark:border-white/10 dark:bg-white/10 dark:hover:bg-white/15']) }}>
    {{ $slot }}
</button>
