const colors = require('tailwindcss/colors')
module.exports = {
    darkMode: 'class',
    prefix: 'dol-',
    theme: {
      screens: {
          'sm': '640px',
          // => @media (min-width: 640px) { ... }

          'md': '768px',
          // => @media (min-width: 768px) { ... }

          'lg': '1024px',
          // => @media (min-width: 1024px) { ... }
        },
        colors: {
            transparent: 'transparent',
            current: 'currentColor',
            black: colors.black,
            white: colors.white,
            gray: colors.trueGray,
            red: colors.red,
            green: colors.green,
        },
        extend: {
            transitionDuration: {
                '0': '0ms',
                '300': '300ms',
                '500': '500ms',
                '1000': '1000ms'
            },
            zIndex: {
                '-1': '-1',
                '500': '500'
            },
            colors: {
               'primary': 'var(--primary)',
               'primary-100': 'var(--primary-100)',
               'primary-200': 'var(--primary-200)',
               'primary-300': 'var(--primary-300)',
               'primary-400': 'var(--primary-400)',
               'primary-500': 'var(--primary-500)',
               'primary-600': 'var(--primary-600)',
               'primary-700': 'var(--primary-700)',
               'primary-800': 'var(--primary-800)',
               'primary-900': 'var(--primary-900)',
                'secondary': 'var(--secondary)',
                'secondary-100': 'var(--secondary-100)',
                'secondary-200': 'var(--secondary-200)',
                'secondary-300': 'var(--secondary-300)',
                'secondary-400': 'var(--secondary-400)',
                'secondary-500': 'var(--secondary-500)',
                'secondary-600': 'var(--secondary-600)',
                'secondary-700': 'var(--secondary-700)',
                'secondary-800': 'var(--secondary-800)',
                'secondary-900': 'var(--secondary-900)',
              }
        },
    },
    variants: {
        margin: ['responsive', 'last'],
        borderWidth: ['responsive', 'last'],
        display: ['responsive', 'group-hover']
    },
    purge: {
        enabled: true,
        content: [
            './templates/*.php',
            './templates/**/*.php',
            './core/Widgets/**/*.php',
            './core/Widgets/**/templates/*.php',
            './core/Widgets/**/templates/**/*.php',
            './core/Widgets/**/templates/**/**/*.php',
            './core/Shortcodes/Blueprints.php',
            './core/Admin/NavMenu/Component.php',
            './templates/link-domain.php',
            './core/tailwind-whitelist.txt'
        ],
        options: {
            safelist: {
               greedy: [/primary/, /secondary/]
            }
        }
    },
    corePlugins: {
        preflight: false,
    }
}
