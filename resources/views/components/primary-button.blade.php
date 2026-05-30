<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-on-primary shadow-elevation-1 transition duration-150 ease-in-out hover:bg-primary-hover hover:shadow-elevation-2 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 active:brightness-95 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
