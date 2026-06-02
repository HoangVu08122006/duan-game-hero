import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Khai báo kiểu cho window để tránh lỗi TypeScript
declare global {
  interface Window {
    Pusher: any;
    Echo: any;
  }
}

window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: 'pusher',
  key:' import.meta.env.VITE_PUSHER_APP_KEY, // Lấy từ file .env phía Frontend'
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
  forceTLS: true
});