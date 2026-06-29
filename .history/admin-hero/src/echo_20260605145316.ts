import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import jQuery from 'jquery'; // Import jquery trực tiếp

// Gán jQuery vào window sớm nhất có thể
window.$ = window.jQuery = jQuery;

declare global {
  interface Window {
    Pusher: any;
    Echo: any;
    $: any;
    jQuery: any;
  }
}

window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: 'Rco91IWCY521ZGsA3nqmffbTkqwopIWoIJ4vsTOZ6pQ',
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
  forceTLS: true
});