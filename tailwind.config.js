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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                sapta: {
                    50: '#f0f4fa',
                    100: '#e0e9f4',
                    200: '#c5d7e9',
                    300: '#9dbddb',
                    400: '#6f9dca',
                    500: '#4e82b7',
                    600: '#3c679a',
                    700: '#31537e',
                    800: '#2a466a',
                    900: '#1e3a5f', // Brand dark blue
                    accent: '#d83b3b', // Brand red
                }
            }
        },
    },

    plugins: [forms],
};
