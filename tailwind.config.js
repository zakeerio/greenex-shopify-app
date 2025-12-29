/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./node_modules/flowbite/**/*.js",
    ],
    theme: {
        extend: {
            colors: {
                green: {
                    DEFAULT: "#4a9400",
                    50: "#f7fdf0",
                    100: "#ebfadd",
                    200: "#d5f5bc",
                    300: "#b1e98d",
                    400: "#86d75b",
                    500: "#5fbb31",
                    600: "#4a9400",
                    700: "#397206",
                    800: "#2f5b0b",
                    900: "#274b0f",
                    950: "#122903",
                },
            },
        },
    },
    plugins: [require("flowbite/plugin")],
};
