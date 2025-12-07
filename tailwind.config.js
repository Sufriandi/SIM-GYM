// tailwind.config.js

import defaultTheme from "tailwindcss/defaultTheme";

import aspectRatio from "@tailwindcss/aspect-ratio";
import containerQueries from "@tailwindcss/container-queries";
import forms from "@tailwindcss/forms";
import typography from "@tailwindcss/typography";
import animate from "tailwindcss-animate";

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
            /* =======================================
             *  FONT SYSTEM
             * ======================================= */
            fontFamily: {
                sans: ["Roboto", ...defaultTheme.fontFamily.sans],
                heading: ["Oswald", "Bebas Neue", "system-ui", "sans-serif"],
                display: ["Bebas Neue", "Oswald", "system-ui", "sans-serif"],
            },

            fontSize: {
                display: ["3.5rem", { lineHeight: "1", fontWeight: "900" }],
                hero: ["2.5rem", { lineHeight: "1.1", fontWeight: "800" }],
            },

            /* =======================================
             *  COLOR SYSTEM – BETA GYM
             * ======================================= */
            colors: {
                /* --- Brand Gold / Bronze --- */
                gold: {
                    50: "#FFF8E7",
                    100: "#FBEECF",
                    200: "#F3D79C",
                    300: "#EAC176",
                    400: "#E1AA5F",
                    500: "#D4A757", // Main gold
                    600: "#BE8C3E",
                    700: "#A67C39", // Dark bronze
                    800: "#88602E",
                    900: "#6B4B24",
                    DEFAULT: "#D4A757",
                },

                /* --- Brand Neutrals (warm) --- */
                brand: {
                    // background halaman (lebih hangat, tapi tetap lembut)
                    bg: "#FCFCFA", // hampir putih tapi warm
                    // lapisan “shell” umum (section besar, panel besar)
                    shell: "#F5E6D6",
                    // nav/footer/area kontras
                    nav: "#21160F", // untuk navbar / top bar gelap
                    sidebar: "#261810", // untuk sidebar gelap
                    footer: "#1B130D",

                    // card / panel
                    card: "#F8F2E7",
                    cardSoft: "#FBF7EE",

                    // surface netral (bisa untuk hover / striping tabel)
                    surface: {
                        50: "#FDFBF7",
                        100: "#F6EFE0",
                        200: "#ECE0C7",
                    },

                    // border
                    borderSoft: "#E3D5C5",
                    borderStrong: "#A67C39",

                    // text
                    text: "#201911",
                    textSoft: "#6C5A46",

                    // utilitas
                    white: "#FFFFFF",
                    black: "#0C0C0C",
                    gunmetal: "#2C2C2C",
                    steel: "#4B4B4B",
                    silver: "#C8C8C8",
                },

                /* --- Accent Red (CTA, status penting) --- */
                accent: {
                    50: "#FFF5F5",
                    100: "#FEE4E4",
                    200: "#FECDCD",
                    300: "#FDA4A4",
                    400: "#F97373",
                    500: "#C73527", // Main accent red
                    600: "#A92A20",
                    700: "#8E261D",
                    800: "#6E1C16",
                    900: "#4C1510",
                    DEFAULT: "#C73527",
                },

                /* --- Semantik & utilitas --- */
                primary: {
                    DEFAULT: "#D4A757", // gold
                    dark: "#A67C39",
                    soft: "#F8F2E7",
                },
                secondary: {
                    DEFAULT: "#2C2C2C", // gunmetal
                },
                success: {
                    DEFAULT: "#22C55E",
                    soft: "#DCFCE7",
                },
                warning: {
                    DEFAULT: "#EAB308",
                    soft: "#FEF9C3",
                },
                danger: {
                    DEFAULT: "#C73527",
                    soft: "#FEE2E2",
                },
                info: {
                    DEFAULT: "#0EA5E9",
                    soft: "#E0F2FE",
                },

                /* --- Netral umum (kalau butuh) --- */
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

                /* --- Text helper alias --- */
                "text-main": "#201911",
                "text-muted": "#6C5A46",
            },

            /* =======================================
             *  SHADOW, RADIUS, BORDERS
             * ======================================= */
            boxShadow: {
                // untuk navbar / header
                header: "0 4px 18px rgba(0,0,0,0.06)",

                // card biasa
                card: "0 10px 30px rgba(0,0,0,0.06)",

                // card penting / highlight (contoh paket membership utama)
                "card-strong": "0 18px 45px rgba(0,0,0,0.12)",

                // sidebar / flyout
                sidebar: "0 0 40px rgba(0,0,0,0.35)",

                // tombol utama
                "btn-primary": "0 10px 25px rgba(199,53,39,0.35)",
                "btn-primary-hover": "0 16px 40px rgba(199,53,39,0.55)",

                // tombol sekunder
                "btn-soft": "0 8px 20px rgba(0,0,0,0.12)",

                // efek glow gold (untuk fokus atau highlight)
                "gold-glow": "0 0 25px rgba(212,167,87,0.45)",
            },

            borderRadius: {
                lg: "0.75rem",
                xl: "1rem",
                "2xl": "1.3rem",
                "3xl": "1.8rem",
                pill: "999px",
            },

            borderWidth: {
                3: "3px",
            },

            ringColor: {
                primary: "#D4A757",
                accent: "#C73527",
                danger: "#C73527",
            },

            /* =======================================
             *  BACKGROUND & GRADIENT VARIANTS
             * ======================================= */
            backgroundImage: {
                // gradasi emas utama (untuk badge / hero text)
                "brand-gold":
                    "linear-gradient(135deg, #A67C39 0%, #D4A757 40%, #F3D79C 100%)",

                // gradasi emas soft (untuk card premium / strip hero)
                "brand-gold-soft":
                    "linear-gradient(145deg, #FBF7EE 0%, #F3D79C 45%, #FBEECF 100%)",

                // gradasi gelap ber-emas (cocok untuk footer / header hero)
                "brand-dark":
                    "linear-gradient(145deg, #000000 0%, #0C0C0C 45%, #2C2C2C 100%)",

                // radial shell di atas background (bisa dipakai di <section>)
                "brand-shell":
                    "radial-gradient(circle at top, rgba(212,167,87,0.18), transparent 60%)",

                // gradasi “ember” merah+gold (untuk CTA besar / strip promo)
                "brand-ember":
                    "linear-gradient(135deg, #8E261D 0%, #C73527 35%, #D4A757 100%)",

                // gradasi clean untuk hero visitor (beige lembut → putih)
                "brand-sand":
                    "linear-gradient(135deg, #F8F2E7 0%, #FCFCFA 40%, #FFFFFF 100%)",

                // gradasi diagonal halus (bisa untuk card / background kecil)
                "brand-diagonal-light":
                    "linear-gradient(120deg, #FBF7EE 0%, #F4E6D4 45%, #FBF7EE 100%)",

                // gradasi overlay gelap untuk banner foto / gambar
                "brand-overlay-dark":
                    "linear-gradient(180deg, rgba(0,0,0,0.65) 0%, rgba(0,0,0,0.2) 60%, transparent 100%)",

                // radial spotlight (bisa untuk animasi hover card)
                "brand-radial-spot":
                    "radial-gradient(circle at 20% 0%, rgba(212,167,87,0.32), transparent 55%)",

                // gradasi accent merah (untuk tombol / badge)
                "accent-gradient":
                    "linear-gradient(135deg, #C73527 0%, #8E261D 100%)",

                // gradasi accent “fire” (untuk warn animasi background)
                "accent-fire":
                    "linear-gradient(120deg, #6E1C16 0%, #C73527 40%, #F97373 100%)",
            },

            /* =======================================
             *  TRANSITIONS & HOVER FEEL
             * ======================================= */
            transitionDuration: {
                fast: "150ms",
                normal: "220ms",
                slow: "350ms",
            },
            transitionTimingFunction: {
                smooth: "cubic-bezier(0.22, 0.61, 0.36, 1)",
            },

            scale: {
                98: "0.98",
                101: "1.01",
                102: "1.02",
            },

            opacity: {
                15: "0.15",
                35: "0.35",
            },

            /* =======================================
             *  ANIMATIONS (plus animate plugin)
             * ======================================= */
            keyframes: {
                "pulse-gold": {
                    "0%, 100%": { opacity: "1", transform: "translateY(0)" },
                    "50%": { opacity: "0.85", transform: "translateY(-1px)" },
                },
                "soft-pop": {
                    "0%": {
                        transform: "scale(0.96)",
                        opacity: "0",
                    },
                    "100%": {
                        transform: "scale(1)",
                        opacity: "1",
                    },
                },
                // bisa dipakai untuk card yang “hidup” pelan
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

    /* =======================================
     *  PLUGINS
     * ======================================= */
    plugins: [
        forms, // styling form
        typography, // prose / konten teks panjang
        aspectRatio, // rasio gambar/video
        containerQueries, // responsive by container
        animate, // util animasi tambahan
    ],
};
