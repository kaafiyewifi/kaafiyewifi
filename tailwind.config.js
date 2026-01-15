import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },

            colors: {
                /* =====================
                 | BRAND COLORS (MY COLORS)
                 |===================== */
                brand: {
                    primary: '#5b146b',   // Purple
                    secondary: '#ff5b39', // Orange Red
                    orange: '#ff9f45',
                    peach: '#ffb07c',
                    pink: '#fbe6e0',
                    lavender: '#c6a0c9',
                },

                /* =====================
                 | LIGHT MODE
                 |===================== */
                light: {
                    bg: '#f8fafc',
                    card: '#ffffff',
                    border: '#e5e7eb',
                    text: '#1f2937',
                    muted: '#6b7280',
                },

                /* =====================
                 | DARK MODE
                 |===================== */
                dark: {
                    bg: '#0f0a14',
                    card: '#15101d',
                    border: '#2a1f33',
                    text: '#e5e7eb',
                    muted: '#9ca3af',
                },
            },

            boxShadow: {
                card: '0 10px 25px rgba(0,0,0,0.05)',
                darkCard: '0 10px 25px rgba(0,0,0,0.4)',
            },

            borderRadius: {
                xl: '14px',
                '2xl': '18px',
            },
        },
    },

    plugins: [],
};
