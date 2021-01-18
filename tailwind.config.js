const colors = require('tailwindcss/colors')
module.exports = {
    darkMode: 'class',
    prefix: 'dol-',
    theme: {
        extend: {
            zIndex: {
                '-1': '-1',
                '500': '500'
            },
            colors: {
                'primary': 'var(--primary)',
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
        enabled: false,
        content: [
            './templates/*.php',
            './templates/**/*.php',
            './core/Widgets/**/*.php',
            './core/Widgets/**/templates/*.php',
            './core/Widgets/**/templates/**/*.php',
            './core/Widgets/**/templates/**/**/*.php',
            './core/Shortcodes/Blueprints.php',
            './core/Admin/NavMenu/Component.php',
            './templates/link-domain.php'
        ],
        options: {
            safelist: ["dark"],
            safelist: ["dol-custom"]
        }
    },
    corePlugins: {
        preflight: false,
    }
}
