/** @type {import('tailwindcss').Config} */
module.exports = {
    prefix: "dol-",
    content: [
        "./tailwind-whitelist.txt",
        "./templates/**/*",
        "./core/Admin/**/*",
        "./core/Modules/**/*",
        "./core/Shortcodes/**/*",
        "./core/Widgets/**/*",
        "./assets/js/**/*",
        "./core/Extras/**/*",
        "./core/Extras/dollie-setup/admin/templates/**/*"
    ],
    theme: {
        // screens: {
        //     'sm': '640px',
        //     'md': '768px',
        //     'lg': '1024px',
        //     'xl': '1280px',
        // },
        extend: {
            transitionDuration: {
                0: "0ms",
                300: "300ms",
                500: "500ms",
                1000: "1000ms",
            },
            zIndex: {
                "-1": "-1",
                500: "500",
                99999: "99999",
            },
            colors: {
                primary: "var(--d-primary)",
                "primary-100": "var(--d-primary-100)",
                "primary-200": "var(--d-primary-200)",
                "primary-300": "var(--d-primary-300)",
                "primary-400": "var(--d-primary-400)",
                "primary-500": "var(--d-primary-500)",
                "primary-600": "var(--d-primary-600)",
                "primary-700": "var(--d-primary-700)",
                "primary-800": "var(--d-primary-800)",
                "primary-900": "var(--d-primary-900)",
                secondary: "var(--d-secondary)",
                "secondary-100": "var(--d-secondary-100)",
                "secondary-200": "var(--d-secondary-200)",
                "secondary-300": "var(--d-secondary-300)",
                "secondary-400": "var(--d-secondary-400)",
                "secondary-500": "var(--d-secondary-500)",
                "secondary-600": "var(--d-secondary-600)",
                "secondary-700": "var(--d-secondary-700)",
                "secondary-800": "var(--d-secondary-800)",
                "secondary-900": "var(--d-secondary-900)",
            },
        },
    },
    daisyui: {
        themes: [
            {
                base: {
                    "primary": "#51AABF",
                    "secondary": "#f0a146",
                    "accent": "#37CDBE",
                    "neutral": "#343434",
                    "base-100": "#FFFFFF",
                    "base-200": "#EFEFEF",
                    "base-content": "#343434",
                    "info": "#51AABF",
                    "success": "#36D399",
                    "warning": "#FBBD23",
                    "error": "#F87272",
                },
            },
        ],
        darkTheme: "light",
    },
    corePlugins: {
        preflight: false,
    },
    plugins: [
        require('@tailwindcss/aspect-ratio'),
        require("@tailwindcss/forms"),
        require('@tailwindcss/line-clamp'),
        require("daisyui")
    ],
}
