import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],

    server: {
        host: "0.0.0.0",
        port: 5173,
        strictPort: true,

        allowedHosts: ["permanganic-yolande-orthostichous.ngrok-free.dev"],

        hmr: {
            protocol: "wss",
            host: "permanganic-yolande-orthostichous.ngrok-free.dev",
            clientPort: 443,
        },
    },
});
