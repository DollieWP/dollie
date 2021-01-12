module.exports = {
    prefix: 'dol-',
    theme: {
        extend: {
            colors: {
                'brand': {
                    50: '#F5FBFD',
                    100: '#EBF7FA',
                    200: '#CDECF3',
                    300: '#AFE1EB',
                    400: '#72CADC',
                    500: '#36B3CD',
                    600: '#31A1B9',
                    700: '#206B7B',
                    800: '#18515C',
                    900: '#10363E',
                },
                'cobalt': {
                    50: '#F3F5F6',
                    100: '#E7EBEE',
                    200: '#C2CED4',
                    300: '#9DB0BB',
                    400: '#547487',
                    500: '#0B3954',
                    600: '#0A334C',
                    700: '#072232',
                    800: '#051A26',
                    900: '#031119',
                },
                'almond': {
                    50: '#FFFEFD',
                    100: '#FFFDFA',
                    200: '#FFFAF4',
                    300: '#FFF7ED',
                    400: '#FFF2DF',
                    500: '#FFECD1',
                    600: '#E6D4BC',
                    700: '#998E7D',
                    800: '#736A5E',
                    900: '#4D473F',
                },
                'flame': {
                    50: '#FEF7F4',
                    100: '#FDEEE9',
                    200: '#FBD5C8',
                    300: '#F8BBA7',
                    400: '#F38964',
                    500: '#EE5622',
                    600: '#D64D1F',
                    700: '#8F3414',
                    800: '#6B270F',
                    900: '#471A0A',
                },
                'green': {
                    50: '#F5FDF9',
                    100: '#EBFCF3',
                    200: '#CCF7E2',
                    300: '#ADF2D0',
                    400: '#70E8AD',
                    500: '#32DE8A',
                    600: '#2DC87C',
                    700: '#1E8553',
                    800: '#17643E',
                    900: '#0F4329',
                },
                'ash': {
                    100: '#FCFCFC',
                    200: '#F8F8F8',
                    300: '#F4F4F4',
                    400: '#EBEBEB',
                    500: '#E3E3E3',
                    600: '#CCCCCC',
                    700: '#888888',
                    800: '#666666',
                    900: '#444444',
                },
            },
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
    future: {
        removeDeprecatedGapUtilities: true,
        purgeLayersByDefault: true,
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
        ]
    },
    corePlugins: {
        preflight: false,
    }
}
