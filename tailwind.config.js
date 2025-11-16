// tailwind.config.js

// ✅ PERBAIKAN KRITIS: Gunakan 'import' untuk mengimpor plugin. 
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
// ✅ PERBAIKAN KRITIS: Gunakan 'export default'
export default { 
    // 1. CONTENT
    content: [
        './resources/views/**/*.blade.php', 
        './resources/js/**/*.js',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
    
    // 2. THEME - BETA GYM COLOR SCHEME
    theme: {
        extend: {
            // A. WARNA TEMA - BOLD & POWERFUL
            colors: {
                // Primary Colors (Navy Dark)
                'primary': {
                    DEFAULT: '#16213e',
                    50: '#e8eaf0',
                    100: '#d1d5e1',
                    200: '#a3abc3',
                    300: '#7581a5',
                    400: '#475787',
                    500: '#16213e',
                    600: '#121a32',
                    700: '#0e1425',
                    800: '#0a0e19',
                    900: '#06070c',
                },
                
                // Secondary Colors (Antique Gold)
                'gold': {
                    DEFAULT: '#c8a870',
                    50: '#faf8f3',
                    100: '#f5f1e7',
                    200: '#ebe3cf',
                    300: '#e0d5b7',
                    400: '#d6c79f',
                    500: '#c8a870',
                    600: '#b8945a',
                    700: '#9a7a4a',
                    800: '#7c603a',
                    900: '#5e462a',
                },
                
                // Accent Colors (Red Vibrant)
                'accent': {
                    DEFAULT: '#e63946',
                    50: '#fef2f2',
                    100: '#fee2e2',
                    200: '#fecaca',
                    300: '#fca5a5',
                    400: '#f87171',
                    500: '#e63946',
                    600: '#dc2626',
                    700: '#b91c1c',
                    800: '#991b1b',
                    900: '#7f1d1d',
                },
                
                // Background Colors
                'dark-background': '#0a0e27',      // Main dark background
                'dark-card': '#16213e',            // Card/Section background
                'dark-surface': '#1a2847',         // Elevated surfaces
                'light-background': '#f8f9fa',     // Light mode background
                
                // Text Colors
                'text-primary': '#ecf0f1',         // Light text on dark
                'text-secondary': '#a8b2d1',       // Muted text
                'text-dark': '#1a1d29',            // Dark text on light
                
                // Status Colors
                'success': '#2ecc71',              // Progress/Success
                'warning': '#f39c12',              // Warning states
                'danger': '#e63946',               // Error/Delete
                'info': '#3498db',                 // Information
            },

            // B. TYPOGRAPHY
            fontFamily: {
                'heading': ['Oswald', 'Impact', 'sans-serif'],          // Bold headings
                'body': ['Roboto', 'Helvetica Neue', 'sans-serif'],     // Body text
                'display': ['Bebas Neue', 'Oswald', 'sans-serif'],      // Large display text
            },
            
            fontSize: {
                'display': ['3.5rem', { lineHeight: '1', fontWeight: '900' }],
                'hero': ['2.5rem', { lineHeight: '1.1', fontWeight: '800' }],
            },

            // C. SHADOWS
            boxShadow: {
                'premium': '0 0 20px rgba(200, 168, 112, 0.3)',        // Gold glow
                'gold': '0 4px 20px rgba(200, 168, 112, 0.4)',         // Strong gold
                'gold-lg': '0 10px 40px rgba(200, 168, 112, 0.5)',     // Extra gold
                'accent': '0 4px 20px rgba(230, 57, 70, 0.3)',         // Red glow
                'dark': '0 4px 20px rgba(0, 0, 0, 0.5)',               // Dark depth
                'inner-gold': 'inset 0 2px 10px rgba(200, 168, 112, 0.2)', // Inner glow
            },
            
            // D. BORDER RADIUS
            borderRadius: {
                'gym': '0.5rem',                   // Standard gym elements
                'premium': '1rem',                 // Premium cards
            },
            
            // E. GRADIENTS
            backgroundImage: {
                'gold-gradient': 'linear-gradient(135deg, #c8a870 0%, #9a7a4a 100%)',
                'dark-gradient': 'linear-gradient(180deg, #0a0e27 0%, #16213e 100%)',
                'hero-gradient': 'linear-gradient(135deg, #16213e 0%, #0a0e27 50%, #16213e 100%)',
                'accent-gradient': 'linear-gradient(135deg, #e63946 0%, #b91c1c 100%)',
                'progress-gradient': 'linear-gradient(90deg, #c8a870 0%, #e63946 100%)',
            },
            
            // F. SPACING (untuk consistent spacing)
            spacing: {
                '18': '4.5rem',
                '88': '22rem',
                '128': '32rem',
            },
            
            // G. ANIMATIONS
            keyframes: {
                'pulse-gold': {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.7' },
                },
                'glow': {
                    '0%, 100%': { boxShadow: '0 0 10px rgba(200, 168, 112, 0.3)' },
                    '50%': { boxShadow: '0 0 20px rgba(200, 168, 112, 0.6)' },
                },
            },
            animation: {
                'pulse-gold': 'pulse-gold 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'glow': 'glow 2s ease-in-out infinite',
            },
        },
    },
    
    // 3. PLUGINS
    plugins: [
        forms,
    ],
};