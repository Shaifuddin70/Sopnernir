<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center rounded-lg border border-line bg-surface-secondary px-4 py-2 text-sm font-medium text-primary transition duration-150 ease-in-out hover:bg-primary-muted focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
