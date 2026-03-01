import { defineConfig } from 'vite';
import { fileURLToPath, URL } from 'node:url'
import vue from '@vitejs/plugin-vue';
import Components from 'unplugin-vue-components/vite'
import AutoImport from 'unplugin-auto-import/vite'
import svgLoader from 'vite-svg-loader'
import { ElementPlusResolver } from 'unplugin-vue-components/resolvers'

export default defineConfig( () => ( {
  plugins: [
    vue(),
    svgLoader(),
    Components({
      dirs: ['src'],
      dts: 'src/components.d.ts',
      resolvers: [
        ElementPlusResolver()
      ],
    }),
    AutoImport({
      imports: ['vue', 'pinia'],
      dirs: ['src/utils', 'src/types', 'src/stores', 'src/composables', 'src/constants'],
      dts: 'src/auto-imports.d.ts',
      resolvers: [ElementPlusResolver()],
      vueTemplate: true,
      eslintrc: {
        enabled: true,
      },
    }),
  ],
  optimizeDeps: {
    include: ['vue', 'pinia', 'element-plus'],
  },
  server: {
    port: 5173, // Ensure this matches the port in the PHP file
    strictPort: true,
    cors: true,
  },
  build: {
    target: 'es2022',
    sourcemap: false,
    cssCodeSplit: true,
    rollupOptions: {
      input: {
        admin: fileURLToPath(new URL('./src/assets/js/admin/admin.ts', import.meta.url)),
        public: fileURLToPath(new URL('./src/assets/js/public/public.ts', import.meta.url)),
      },
      output: {
        chunkFileNames: 'chunks/[name]-[hash].js',
        entryFileNames: '[name].js',
        assetFileNames: '[name].[ext]',
      },
    },
  },
  resolve: {
    alias: {
      vue: 'vue/dist/vue.esm-bundler.js',
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  css: {
    devSourcemap: true,
    preprocessorOptions: {
      scss: {
        api: 'modern-compiler',
        additionalData: `
                @use '@/assets/scss/abstracts/all' as *;
                @use '@/assets/scss/colors/primitives/all' as *;
            `,
      },
    },
  },
}));
