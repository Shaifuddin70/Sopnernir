<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex h-10 items-center justify-center rounded-full bg-primary px-4 text-sm font-medium text-on-primary shadow-sm transition duration-150 ease-in-out hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-1 active:brightness-95 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
