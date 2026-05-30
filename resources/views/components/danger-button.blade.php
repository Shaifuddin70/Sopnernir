<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-lg bg-error px-4 py-2 text-sm font-medium text-on-primary shadow-elevation-1 transition duration-150 ease-in-out hover:brightness-110 hover:shadow-elevation-2 focus:outline-none focus:ring-2 focus:ring-error focus:ring-offset-2 active:brightness-95 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
