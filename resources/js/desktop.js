import Echo from 'https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/+esm';
import Pusher from 'pusher-js';

// Assign Pusher to the window object
window.Pusher = Pusher;

// Initialize Laravel Echo with Reverb
// Note: Reverb uses the Pusher protocol, so we configure it similarly
// The actual values should come from your environment or be passed from the backend
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: window.reverbKey || import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: window.reverbHost || import.meta.env.VITE_REVERB_HOST || '127.0.0.1',
    wsPort: window.reverbPort || import.meta.env.VITE_REVERB_PORT || 8080,
    wssPort: window.reverbPort || import.meta.env.VITE_REVERB_PORT || 8080,
    forceTLS: (window.reverbScheme || import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
});

// Listen for the global event on the 'global-notifications' channel

window.Echo.channel('global-notifications')
    .listen('desktop-notification', (e) => {
        showNotification('Global Queue Notification', e.message); // Call the showNotification function
    });
