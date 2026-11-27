import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        // Blade sources only: compiled view cache (storage/framework/views) is a
        // build artifact — scanning it makes the CSS depend on cache state.
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Montserrat', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    navy: '#0B1F46',
                    cobalt: '#2F80FF',
                    'cobalt-strong': '#1E6BE6',
                    'cobalt-deep': '#1552B8',
                    light: '#E8F2FF',
                    slate: '#667085',
                    border: '#DCE4EF',
                    surface: '#F7FAFF',
                },
                // Existing views use `indigo-*` for accents. Map that scale to the
                // JURI.ON cobalt ramp so the palette stays tokenised (PRD BR-005).
                indigo: {
                    50: '#E8F2FF',
                    100: '#D8E9FF',
                    200: '#BFDBFF',
                    300: '#93C3FF',
                    400: '#5C9CFF',
                    500: '#2F80FF',
                    600: '#1E6BE6',
                    700: '#1552B8',
                    800: '#0E3E88',
                    900: '#0B2A6B',
                    950: '#081A45',
                },
                // Dark chrome in existing views uses slate-950; move it to navy.
                slate: {
                    ...defaultTheme.colors.slate,
                    950: '#0B1F46',
                },
            },
        },
    },

    plugins: [forms],
};
