import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { visualizer } from 'rollup-plugin-visualizer';
import viteCompression from 'vite-plugin-compression';

export default defineConfig(({ mode }) => ({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        // Gzip compression for smaller file sizes
        viteCompression({
            algorithm: 'gzip',
            ext: '.gz',
        }),
        // Brotli compression for even better compression
        viteCompression({
            algorithm: 'brotliCompress',
            ext: '.br',
        }),
        // Bundle analyzer (only in analyze mode)
        mode === 'analyze' && visualizer({
            filename: 'dist/stats.html',
            open: true,
            gzipSize: true,
            brotliSize: true,
        }),
    ].filter(Boolean),
    build: {
        // Enable code splitting
        rollupOptions: {
            output: {
                manualChunks: {
                    // Separate Chart.js into its own chunk
                    charts: ['chart.js'],
                    // Separate Alpine.js into its own chunk
                    alpine: ['alpinejs'],
                    // Separate axios into its own chunk
                    http: ['axios']
                }
            }
        },
        // Enable minification
        minify: 'esbuild',
        // Target modern browsers for better optimization
        target: 'es2015',
        // Generate source maps for debugging (can be disabled in production)
        sourcemap: false,
        // Chunk size warning limit
        chunkSizeWarningLimit: 1000,
        // Enable CSS code splitting
        cssCodeSplit: true,
        // Report compressed size
        reportCompressedSize: true
    },
    // Optimize dependencies
    optimizeDeps: {
        include: ['chart.js', 'alpinejs', 'axios']
    },
    // Enable compression plugins for better performance
    esbuild: {
        drop: mode === 'production' ? ['console', 'debugger'] : [], // Remove console.logs only in production
    }
}));
