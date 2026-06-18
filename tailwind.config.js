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
                primary: {
                    DEFAULT: '#1E4A92',
                    50: '#EBF0F8',
                    100: '#D6E1F0',
                    200: '#ADC3E1',
                    300: '#85A5D2',
                    400: '#5C87C3',
                    500: '#1E4A92',
                    600: '#183B75',
                    700: '#122C58',
                    800: '#0C1E3B',
                    900: '#060F1D',
                },
                sidebar: {
                    DEFAULT: '#08162F',
                    hover: '#0F2248',
                    active: '#1E4A92',
                    text: '#8896AB',
                    'text-active': '#FFFFFF',
                },
                success: {
                    DEFAULT: '#18B87A',
                    50: '#E8F8F1',
                    100: '#D1F1E3',
                    500: '#18B87A',
                    600: '#139362',
                    700: '#0E6E49',
                },
                warning: {
                    DEFAULT: '#F5A623',
                    50: '#FEF6E7',
                    100: '#FDEDD0',
                    500: '#F5A623',
                    600: '#C4851C',
                    700: '#936415',
                },
                danger: {
                    DEFAULT: '#EF4444',
                    50: '#FDE8E8',
                    100: '#FBD1D1',
                    500: '#EF4444',
                    600: '#DC2626',
                    700: '#B91C1C',
                },
                surface: {
                    DEFAULT: '#F5F7FB',
                    card: '#FFFFFF',
                },
            },
        },
    },

    plugins: [forms],
};
