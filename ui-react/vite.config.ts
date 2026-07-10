import { defineConfig } from 'vite'
import path from 'node:path'

/**
 * Build for the MelisCommerceOrderInvoice React brick.
 *
 * This brick has NO route/page of its own — it only exposes a small `OrderRowButton`
 * component (invoice download) on `window.MelisCommerceOrderInvoiceBrick`, consumed
 * by melis-commerce's Orders list IF this module is active (see melis-commerce's
 * `shared/externalBricks.ts`). React is EXTERNAL, mapped to the host globals exposed
 * in MelisCore's main.tsx, so this brick reuses the host React instance.
 */
export default defineConfig({
  esbuild: { jsx: 'automatic' },
  build: {
    outDir: path.resolve(import.meta.dirname, '..', 'public', 'ui-react'),
    emptyOutDir: false,
    lib: {
      entry: path.resolve(import.meta.dirname, 'src/brick.tsx'),
      formats: ['iife'],
      name: 'MelisCommerceOrderInvoiceBrickBundle',
      fileName: () => 'brick.js',
    },
    rollupOptions: {
      external: ['react', 'react-dom', 'react/jsx-runtime', 'react-router-dom'],
      output: {
        globals: {
          react: 'MelisReact',
          'react-dom': 'MelisReactDOM',
          'react/jsx-runtime': 'MelisReactJsxRuntime',
          'react-router-dom': 'MelisReactRouterDOM',
        },
      },
    },
  },
})
