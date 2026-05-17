import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  // Phân giải URL base cho assets khi chạy trên sub-directory (nếu dùng XAMPP sub-folder)
  base: '/kbuilder/apps/admin/dist/', 
  build: {
    outDir: 'dist',
    emptyOutDir: true,
  },
  server: {
    port: 3000,
    // Proxy gọi API từ localhost:3000 sang backend PHP ở cùng host
    proxy: {
      '/api': {
        target: 'http://localhost/kbuilder', // Thay đổi theo XAMPP setup của bạn
        changeOrigin: true,
      }
    }
  }
});
