import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    // Safelist for dynamic classes that might be missed during purging
    safelist: [
        'bg-blue-100', 'bg-green-100', 'bg-purple-100',
        'text-blue-800', 'text-green-800', 'text-purple-800',
        'text-blue-900', 'text-green-900', 'text-purple-900',
        'text-red-600', 'text-green-600', 'text-blue-700',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],

    // Optimize for production
    future: {
        hoverOnlyWhenSupported: true,
    },

    // Experimental features for better optimization
    experimental: {
        optimizeUniversalDefaults: true,
    },
};
