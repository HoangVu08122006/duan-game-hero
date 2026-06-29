import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [vue()],
  optimizeDeps: {
    include: ['jquery', 'datatables.net-bs5'],
  },
  define: {
    // Cung cấp biến $ cho toàn bộ ứng dụng
    'window.$': 'jquery',
    'window.jQuery': 'jquery',
  }
});