const plugin = require('tailwindcss/plugin')

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],
    darkMode: 'class',
    theme: {
        asideScrollbars: {
            light: 'light',
            gray: 'gray'
        },
        extend: {
            colors: {
                'custom-teal': {
                    50: '#f7faf9',
                    100: '#EBF5F5',
                    200: '#e1ebe8',
                    300: '#D3E0DD',
                    400: '#b3c9c4',
                    500: '#93b2ab',
                    600: '#748e89',
                    700: '#5a6f6b',
                    800: '#41504d',
                    900: '#28302f',
                },
            },
            zIndex: {
                '-1': '-1'
            },
            flexGrow: {
                5: '5'
            },
            maxHeight: {
                'screen-menu': 'calc(100vh - 3.5rem)',
                modal: 'calc(100vh - 160px)'
            },
            transitionProperty: {
                position: 'right, left, top, bottom, margin, padding',
                textColor: 'color'
            },
            keyframes: {
                fadeOut: {
                    from: { opacity: 1 },
                    to: { opacity: 0 }
                },
                fadeIn: {
                    from: { opacity: 0 },
                    to: { opacity: 1 }
                }
            },
            animation: {
                fadeOut: 'fadeOut 250ms ease-in-out',
                fadeIn: 'fadeIn 250ms ease-in-out'
            }
        }
    },
    plugins: [
        require('@tailwindcss/forms'),
        plugin(function ({ matchUtilities, theme }) {
            matchUtilities(
                {
                    'aside-scrollbars': (value) => {
                        const track = value === 'light' ? '100' : '900'
                        const thumb = value === 'light' ? '300' : '600'
                        const color = value === 'light' ? 'gray' : value

                        return {
                            scrollbarWidth: 'thin',
                            scrollbarColor: `${theme(`colors.${color}.${thumb}`)} ${theme(`colors.${color}.${track}`)}`,
                            '&::-webkit-scrollbar': {
                                width: '8px',
                                height: '8px'
                            },
                            '&::-webkit-scrollbar-track': {
                                backgroundColor: theme(`colors.${color}.${track}`)
                            },
                            '&::-webkit-scrollbar-thumb': {
                                borderRadius: '0.25rem',
                                backgroundColor: theme(`colors.${color}.${thumb}`)
                            }
                        }
                    }
                },
                { values: theme('asideScrollbars') }
            )
        })
    ]
}
