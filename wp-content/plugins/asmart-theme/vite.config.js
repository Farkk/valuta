import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    outDir: 'assets/dist',
    emptyOutDir: true,
    manifest: true, // Генерация manifest.json

    rollupOptions: {
      input: {
        // Стили компонентов
        button: resolve(__dirname, 'assets/src/scss/button.scss'),
        modal: resolve(__dirname, 'assets/src/scss/modal.scss'),
        pagination: resolve(__dirname, 'assets/src/scss/pagination.scss'),
        card: resolve(__dirname, 'assets/src/scss/card.scss'),
        main: resolve(__dirname, 'assets/src/scss/main.scss'),

        // JavaScript модули
        'modal-js': resolve(__dirname, 'assets/src/js/modal.js'),
        'pagination-js': resolve(__dirname, 'assets/src/js/pagination.js'),
      },

      output: {
        assetFileNames: assetInfo => {
          // CSS файлы в папку css/
          if (assetInfo.name.endsWith('.css')) {
            return 'css/[name].[hash].css';
          }
          // Остальные ассеты
          return 'assets/[name].[hash][extname]';
        },

        entryFileNames: 'js/[name].[hash].js',

        // Минификация для production
        minify: 'esbuild',
      },
    },

    // Source maps для разработки
    sourcemap: false,

    // Минификация CSS
    cssMinify: true,
  },

  css: {
    preprocessorOptions: {
      scss: {
        // Используем современный @use вместо @import
        api: 'modern-compiler',
      },
    },
  },

  // Оптимизация сборки
  esbuild: {
    legalComments: 'none',
    minifyIdentifiers: true,
    minifySyntax: true,
    minifyWhitespace: true,
  },
});
