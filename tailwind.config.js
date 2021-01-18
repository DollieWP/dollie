const colors = require('tailwindcss/colors')
module.exports = {
    darkMode: 'class',
    prefix: 'dol-',
    theme: {
        extend: {
            zIndex: {
                '-1': '-1',
                '500': '500'
            }
        }
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
