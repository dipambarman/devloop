import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                background: '#0A0A0F',
                surface: '#13131A',
                'surface-hover': '#1A1A24',
                border: '#2A2A3C',
                'border-hover': '#3A3A50',
                
                // Text Colors
                'primary-text': '#F5F5F7',
                'secondary-text': '#9CA3AF',
                'tertiary-text': '#6B7280',
                
                // Cosmic Theme Palette
                primary: {
                    DEFAULT: '#6366F1', // Indigo 500
                    hover: '#818CF8',   // Indigo 400
                    muted: 'rgba(99, 102, 241, 0.1)',
                },
                accent: {
                    DEFAULT: '#8B5CF6', // Violet 500
                    hover: '#A78BFA',   // Violet 400
                },
                teal: {
                    DEFAULT: '#14B8A6', // Interactive teal/cyan accent
                    hover: '#2DD4BF',
                },
            },
            animation: {
                'gradient-x': 'gradient-x 3s ease infinite',
            },
            keyframes: {
                'gradient-x': {
                    '0%, 100%': {
                        'background-size': '200% 200%',
                        'background-position': 'left center',
                    },
                    '50%': {
                        'background-size': '200% 200%',
                        'background-position': 'right center',
                    },
                },
            },
        },
    },

    plugins: [forms],
};
