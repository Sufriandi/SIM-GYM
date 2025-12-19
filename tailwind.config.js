// tailwind.config.js
import defaultTheme from "tailwindcss/defaultTheme";

import aspectRatio from "@tailwindcss/aspect-ratio";
import containerQueries from "@tailwindcss/container-queries";
import forms from "@tailwindcss/forms";
import typography from "@tailwindcss/typography";
import animate from "tailwindcss-animate";

const cssVar = (name) => `rgb(var(${name}) / <alpha-value>)`;

/** @type {import('tailwindcss').Config} */
export default {
  darkMode: "class",

  content: [
    "./resources/views/**/*.blade.php",
    "./resources/js/**/*.js",
    "./resources/js/**/*.vue",
    "./resources/js/**/*.jsx",
    "./resources/js/**/*.tsx",
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
  ],

  theme: {
    container: {
      center: true,
      padding: "1.25rem",
      screens: {
        sm: "640px",
        md: "768px",
        lg: "1120px",
        xl: "1280px",
        "2xl": "1440px",
      },
    },

    extend: {
      fontFamily: {
        sans: ["Roboto", ...defaultTheme.fontFamily.sans],
        heading: ["Oswald", "Bebas Neue", "system-ui", "sans-serif"],
        display: ["Bebas Neue", "Oswald", "system-ui", "sans-serif"],
      },

      fontSize: {
        display: ["3.5rem", { lineHeight: "1", fontWeight: "900" }],
        hero: ["2.5rem", { lineHeight: "1.1", fontWeight: "800" }],
      },

      colors: {
        gold: {
          50: "#FFF8E7",
          100: "#FBEECF",
          200: "#F3D79C",
          300: "#EAC176",
          400: "#E1AA5F",
          500: "#D4A757",
          600: "#BE8C3E",
          700: "#A67C39",
          800: "#88602E",
          900: "#6B4B24",
          DEFAULT: "#D4A757",
        },

        // BRAND => pakai CSS variables (biar otomatis berubah saat .dark)
        brand: {
          bg: cssVar("--brand-bg"),
          shell: cssVar("--brand-shell"),
          nav: cssVar("--brand-nav"),
          sidebar: cssVar("--brand-sidebar"),
          footer: cssVar("--brand-footer"),

          card: cssVar("--brand-card"),
          cardSoft: cssVar("--brand-cardSoft"),

          surface: {
            50: cssVar("--brand-surface-50"),
            100: cssVar("--brand-surface-100"),
            200: cssVar("--brand-surface-200"),
          },

          borderSoft: cssVar("--brand-borderSoft"),
          borderStrong: cssVar("--brand-borderStrong"),

          // kalau kamu pakai brand.text / brand.textSoft di beberapa tempat
          text: cssVar("--text-main"),
          textSoft: cssVar("--text-muted"),

          white: "#FFFFFF",
          black: "#0C0C0C",
          gunmetal: "#2C2C2C",
          steel: "#4B4B4B",
          silver: "#C8C8C8",
        },

        accent: {
          50: "#FFF5F5",
          100: "#FEE4E4",
          200: "#FECDCD",
          300: "#FDA4A4",
          400: "#F97373",
          500: "#C73527",
          600: "#A92A20",
          700: "#8E261D",
          800: "#6E1C16",
          900: "#4C1510",
          DEFAULT: "#C73527",
        },

        primary: {
          DEFAULT: "#D4A757",
          dark: "#A67C39",
          soft: "#F8F2E7",
        },
        secondary: { DEFAULT: "#2C2C2C" },
        success: { DEFAULT: "#22C55E", soft: "#DCFCE7" },
        warning: { DEFAULT: "#EAB308", soft: "#FEF9C3" },
        danger: { DEFAULT: "#C73527", soft: "#FEE2E2" },
        info: { DEFAULT: "#0EA5E9", soft: "#E0F2FE" },

        neutral: {
          50: "#F9FAFB",
          100: "#F3F4F6",
          200: "#E5E7EB",
          300: "#D1D5DB",
          400: "#9CA3AF",
          500: "#6B7280",
          600: "#4B5563",
          700: "#374151",
          800: "#1F2933",
          900: "#111827",
        },

        // Alias helper => juga pakai CSS vars
        "text-main": cssVar("--text-main"),
        "text-muted": cssVar("--text-muted"),
      },

      boxShadow: {
        header: "0 4px 18px rgba(0,0,0,0.06)",
        card: "0 10px 30px rgba(0,0,0,0.06)",
        "card-strong": "0 18px 45px rgba(0,0,0,0.12)",
        sidebar: "0 0 40px rgba(0,0,0,0.35)",
        "btn-primary": "0 10px 25px rgba(199,53,39,0.35)",
        "btn-primary-hover": "0 16px 40px rgba(199,53,39,0.55)",
        "btn-soft": "0 8px 20px rgba(0,0,0,0.12)",
        "gold-glow": "0 0 25px rgba(212,167,87,0.45)",
      },

      borderRadius: {
        lg: "0.75rem",
        xl: "1rem",
        "2xl": "1.3rem",
        "3xl": "1.8rem",
        pill: "999px",
      },

      borderWidth: { 3: "3px" },

      ringColor: {
        primary: "#D4A757",
        accent: "#C73527",
        danger: "#C73527",
      },

      backgroundImage: {
        "brand-gold":
          "linear-gradient(135deg, #A67C39 0%, #D4A757 40%, #F3D79C 100%)",
        "brand-gold-soft":
          "linear-gradient(145deg, #FBF7EE 0%, #F3D79C 45%, #FBEECF 100%)",
        "brand-dark":
          "linear-gradient(145deg, #000000 0%, #0C0C0C 45%, #2C2C2C 100%)",
        "brand-shell":
          "radial-gradient(circle at top, rgba(212,167,87,0.18), transparent 60%)",
        "brand-ember":
          "linear-gradient(135deg, #8E261D 0%, #C73527 35%, #D4A757 100%)",
        "brand-sand":
          "linear-gradient(135deg, #F8F2E7 0%, #FCFCFA 40%, #FFFFFF 100%)",
        "brand-diagonal-light":
          "linear-gradient(120deg, #FBF7EE 0%, #F4E6D4 45%, #FBF7EE 100%)",
        "brand-overlay-dark":
          "linear-gradient(180deg, rgba(0,0,0,0.65) 0%, rgba(0,0,0,0.2) 60%, transparent 100%)",
        "brand-radial-spot":
          "radial-gradient(circle at 20% 0%, rgba(212,167,87,0.32), transparent 55%)",
        "accent-gradient":
          "linear-gradient(135deg, #C73527 0%, #8E261D 100%)",
        "accent-fire":
          "linear-gradient(120deg, #6E1C16 0%, #C73527 40%, #F97373 100%)",
      },

      transitionDuration: { fast: "150ms", normal: "220ms", slow: "350ms" },
      transitionTimingFunction: { smooth: "cubic-bezier(0.22, 0.61, 0.36, 1)" },
      scale: { 98: "0.98", 101: "1.01", 102: "1.02" },
      opacity: { 15: "0.15", 35: "0.35" },

      keyframes: {
        "pulse-gold": {
          "0%, 100%": { opacity: "1", transform: "translateY(0)" },
          "50%": { opacity: "0.85", transform: "translateY(-1px)" },
        },
        "soft-pop": {
          "0%": { transform: "scale(0.96)", opacity: "0" },
          "100%": { transform: "scale(1)", opacity: "1" },
        },
        "gradient-move": {
          "0%": { backgroundPosition: "0% 50%" },
          "50%": { backgroundPosition: "100% 50%" },
          "100%": { backgroundPosition: "0% 50%" },
        },
      },
      animation: {
        "pulse-gold": "pulse-gold 1.8s smooth infinite",
        "soft-pop": "soft-pop 200ms ease-out",
        "gradient-move": "gradient-move 6s ease-in-out infinite",
      },
    },
  },

  plugins: [forms, typography, aspectRatio, containerQueries, animate],
};
