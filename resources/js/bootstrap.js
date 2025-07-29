import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Setup axios
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set CSRF token (needed for auth to private channels)
let token = document.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Setup Echo with Pusher
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.PUSHER_APP_KEY || '41a30e61ffd80a89fc68',
    cluster: import.meta.env.PUSHER_APP_CLUSTER || 'eu',
    forceTLS: true,
    encrypted: true,
    authEndpoint: '/broadcasting/auth', // For private channels
    auth: {
        headers: {
            'X-CSRF-TOKEN': token ? token.content : '',
        }
    }
});
