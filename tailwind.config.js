import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Roboto', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                surface: {
                    DEFAULT: 'rgb(var(--tw-surface) / <alpha-value>)',
                    secondary: 'rgb(var(--tw-surface-secondary) / <alpha-value>)',
                    card: 'rgb(var(--tw-surface-card) / <alpha-value>)',
                    variant: 'rgb(var(--tw-surface-variant) / <alpha-value>)',
                },
                primary: {
                    DEFAULT: 'rgb(var(--tw-primary) / <alpha-value>)',
                    hover: 'rgb(var(--tw-primary-hover) / <alpha-value>)',
                    muted: 'rgb(var(--tw-primary-muted) / <alpha-value>)',
                    dark: 'rgb(var(--tw-primary-dark) / <alpha-value>)',
                },
                success: {
                    DEFAULT: 'rgb(var(--tw-success) / <alpha-value>)',
                    muted: 'rgb(var(--tw-success-muted) / <alpha-value>)',
                },
                warning: {
                    DEFAULT: 'rgb(var(--tw-warning) / <alpha-value>)',
                    muted: 'rgb(var(--tw-warning-muted) / <alpha-value>)',
                },
                error: {
                    DEFAULT: 'rgb(var(--tw-error) / <alpha-value>)',
                    muted: 'rgb(var(--tw-error-muted) / <alpha-value>)',
                },
                foreground: {
                    DEFAULT: 'rgb(var(--tw-foreground) / <alpha-value>)',
                    muted: 'rgb(var(--tw-foreground-muted) / <alpha-value>)',
                },
                line: 'rgb(var(--tw-line) / <alpha-value>)',
                on: {
                    primary: 'rgb(var(--tw-on-primary) / <alpha-value>)',
                },
            },
            boxShadow: {
                'elevation-1': 'var(--shadow-elevation-1)',
                'elevation-2': 'var(--shadow-elevation-2)',
                'elevation-3': 'var(--shadow-elevation-3)',
            },
            ringOffsetColor: {
                DEFAULT: 'rgb(var(--tw-ring-offset) / <alpha-value>)',
            },
        },
    },

    plugins: [
        forms({
            strategy: 'class',
        }),
    ],
};
