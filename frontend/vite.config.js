import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    react(),
    tailwindcss()
  ],
  base: './',
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost/Project_PemWeb/backend',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, '/api.php')
      }
    }
  }
})
