import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
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
                    DEFAULT: '#F5F7FA',
                    secondary: '#FFFFFF',
                    card: '#FFFFFF',
                    variant: '#EEF1F6',
                },
                primary: {
                    DEFAULT: '#1976D2',
                    hover: '#1565C0',
                    muted: '#E3F2FD',
                    dark: '#0D47A1',
                },
                success: {
                    DEFAULT: '#2E7D32',
                    muted: '#E8F5E9',
                },
                warning: {
                    DEFAULT: '#ED6C02',
                    muted: '#FFF3E0',
                },
                error: {
                    DEFAULT: '#D32F2F',
                    muted: '#FFEBEE',
                },
                foreground: {
                    DEFAULT: '#1A1C1E',
                    muted: '#5F6368',
                },
                line: '#E0E0E0',
                on: {
                    primary: '#FFFFFF',
                },
            },
            boxShadow: {
                'elevation-1': '0 1px 2px 0 rgb(0 0 0 / 0.06), 0 1px 3px 0 rgb(0 0 0 / 0.1)',
                'elevation-2': '0 2px 4px -1px rgb(0 0 0 / 0.06), 0 4px 6px -1px rgb(0 0 0 / 0.1)',
                'elevation-3': '0 4px 6px -2px rgb(0 0 0 / 0.05), 0 10px 15px -3px rgb(0 0 0 / 0.1)',
            },
            ringOffsetColor: {
                DEFAULT: '#FFFFFF',
            },
        },
    },

    plugins: [
        forms({
            strategy: 'class',
        }),
    ],
};
