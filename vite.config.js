import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        outDir: 'public/build', // Ensure built files are in public/build
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,     // Remove console.logs
                drop_debugger: true,    // Remove debugger statements
                pure_funcs: ['console.log', 'console.info', 'console.debug'], // Remove specific console methods
            },
            mangle: {
                toplevel: true,         // Mangle top-level variable names
            },
            format: {
                comments: false,        // Remove all comments
            },
        },
        cssMinify: true, // Minify CSS as well
    },
});
