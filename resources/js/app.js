import './bootstrap';

import 'flowbite';

import Alpine from "alpinejs";
import persist from '@alpinejs/persist';

// Alpine.plugin(persist);

// window.Alpine = Alpine;

// Alpine.start();


import Echo from 'laravel-echo';
window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true
});

window.Echo.join(`project.${projectId}`)
    .listen('TaskCreated', (event) => {
        console.log('New task created:', event.task);
        // Update the UI accordingly
    });



