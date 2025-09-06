import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    host: true,         // слушать 0.0.0.0 в контейнере
    port: 5173,
    strictPort: true,
    hmr: {
      protocol: 'ws',
      host: 'localhost',
      port: 8080,       // <= Браузер на 8080, прокси докинет в 5173
    }
  }
})

