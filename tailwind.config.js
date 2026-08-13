/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.js",
    ],
    darkMode: 'class',
    theme: {
        extend: {},
    },
    plugins: [],
    safelist: [
        // Botões de ação - cores e variações
        {
            pattern: /text-(cyan|blue|red|green|yellow|gray)-(500|700)/,
        },
        {
            pattern: /(dark:)?text-(cyan|blue|red|green|yellow|gray)-(300|400|500)/,
        },
        {
            pattern: /hover:(text|bg)-(cyan|blue|red|green|yellow|gray)-(100|700|950)/,
        },
        {
            pattern: /(dark:)?hover:(text|bg)-(cyan|blue|red|green|yellow|gray)-(300|700|950)/,
        },
        {
            pattern: /dark:(text|bg)-(cyan|blue|red|green|yellow|gray)-(400|500|950)/,
        },
        // Badges
        {
            pattern: /bg-(green|yellow|red|blue|cyan|gray)-(50|100|900)/,
        },
        {
            pattern: /dark:bg-(green|yellow|red|blue|cyan|gray)-(700|900)/,
        },
        {
            pattern: /text-(green|yellow|red|blue|cyan|gray)-(300|400|700)/,
        },
        {
            pattern: /dark:text-(green|yellow|red|blue|cyan|gray)-(300|400)/,
        },
        {
            pattern: /bg-(green|yellow|red|blue|cyan)-500/,
        },
    ],
}
