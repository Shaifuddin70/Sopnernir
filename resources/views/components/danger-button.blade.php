<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex h-10 items-center justify-center rounded-full bg-error px-4 text-sm font-medium text-on-primary shadow-sm transition duration-150 ease-in-out hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-error focus:ring-offset-1 active:brightness-95 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
