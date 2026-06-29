import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [vue()],
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8000', // Đảm bảo đúng port backend của bạn
        changeOrigin: true,
        secure: false,
      },
      '/sanctum': { // Thêm cả phần này để xử lý CSRF
        target: 'http://localhost:8000',
        changeOrigin: true,
      }
    },
  },
  // ... phần còn lại
})
