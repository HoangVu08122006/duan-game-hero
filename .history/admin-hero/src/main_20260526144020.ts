import { createApp } from 'vue'
import { createPinia } from 'pinia'

// Import Element Plus và file CSS của nó
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'

import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(createPinia())
app.use(router)

// Kích hoạt Element Plus cho toàn bộ dự án
app.use(ElementPlus)

app.mount('#app')

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'YOUR_PUSHER_KEY', // Lấy từ file .env Laravel
    cluster: 'mt1',
    forceTLS: true
});