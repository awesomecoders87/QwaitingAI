import Echo from 'https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/+esm';
import Pusher from 'pusher-js';  // Import Pusher correctly

// Assign Pusher to the window object
window.Pusher = Pusher;

// Initialize Laravel Echo with Pusher
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'eba5649505c6a2153688', // Your Pusher app key
    cluster: 'ap2',  // Your Pusher cluster
    forceTLS: true
});

// Listen for the global event on the 'global-notifications' channel

window.Echo.channel('global-notifications')
    .listen('desktop-notification', (e) => {
        showNotification('Global Queue Notification', e.message); // Call the showNotification function
    });
