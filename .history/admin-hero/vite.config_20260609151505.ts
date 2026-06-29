import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],

  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },

  optimizeDeps: {
    include: ['jquery', 'datatables.net-bs5']
  },

  // THÊM ĐOẠN CẤU HÌNH PROXY DƯỚI ĐÂY
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8000', // Đảm bảo khớp với URL backend của bạn
        changeOrigin: true,
        secure: false,
      },
    },
  },
})