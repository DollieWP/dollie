const colors = require("tailwindcss/colors");
module.exports = {
  darkMode: "class",
  prefix: "dol-",
  theme: {
    colors: {
      transparent: "transparent",
      current: "currentColor",
      black: colors.black,
      white: colors.white,
      gray: colors.trueGray,
      red: colors.red,
      green: colors.green,
    },
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
        99999: "99999"
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
  variants: {
    margin: ["responsive", "last"],
    borderWidth: ["responsive", "last"],
    display: ["responsive", "group-hover"],
  },
  purge: {
    enabled: true,
    content: [
      "./templates/*.php",
      "./templates/**/*.php",
      "./core/Widgets/**/*.php",
      "./core/Widgets/**/templates/*.php",
      "./core/Widgets/**/templates/**/*.php",
      "./core/Widgets/**/templates/**/**/*.php",
      "./core/Shortcodes/Blueprints.php",
      "./core/Admin/NavMenu/Component.php",
      "./core/Modules/Blueprints.php",
      "./templates/link-domain.php",
      "./templates/widgets/site/site-content.php",
      "./core/tailwind-whitelist.txt",
    ],
    options: {
      safelist: {
        //standard: [/dark/]
      },
    },
  },
  corePlugins: {
    preflight: false,
  },
  plugins: [require("@tailwindcss/forms")],
};
