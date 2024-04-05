/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;
window.env = import.meta.env;

window.Echo = new Echo({
    broadcaster: "pusher",
    authEndpoint : env.VITE_PUSHER_AUTH_ENDPOINT,
    key: env.VITE_PUSHER_APP_KEY,
    cluster: env.VITE_PUSHER_APP_CLUSTER,
    wsHost:
        env.VITE_PUSHER_HOST ??
        `ws-${env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: env.VITE_PUSHER_PORT ?? 80,
    wssPort: env.VITE_PUSHER_PORT ?? 443,
    forceTLS: false,
    encrypted: env.VITE_PUSHER_ENCRYPTED ?? true,
    disableStats: true,
    enabledTransports: ["ws"]
});

