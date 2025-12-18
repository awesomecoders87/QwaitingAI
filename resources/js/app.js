import './bootstrap';

import 'flowbite';

import Alpine from "alpinejs";
import persist from '@alpinejs/persist';

// Alpine.plugin(persist);

// window.Alpine = Alpine;

// Alpine.start();


import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

window.Echo.join(`project.${projectId}`)
    .listen('TaskCreated', (event) => {
        console.log('New task created:', event.task);
        // Update the UI accordingly
    });



