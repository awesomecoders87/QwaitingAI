// Initialize Laravel Echo with Reverb
// This script should be loaded after pusher-js and laravel-echo

if (typeof window.Echo === 'undefined') {
    import('laravel-echo').then(({ default: Echo }) => {
        import('pusher-js').then(({ default: Pusher }) => {
            window.Pusher = Pusher;
            
            // Get Reverb configuration from window object (set by Blade templates)
            const reverbConfig = window.reverbConfig || {
                key: import.meta.env.VITE_REVERB_APP_KEY,
                host: import.meta.env.VITE_REVERB_HOST || '127.0.0.1',
                port: import.meta.env.VITE_REVERB_PORT || 8080,
                scheme: import.meta.env.VITE_REVERB_SCHEME || 'http',
            };

            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: reverbConfig.key,
                wsHost: reverbConfig.host,
                wsPort: reverbConfig.port,
                wssPort: reverbConfig.port,
                forceTLS: reverbConfig.scheme === 'https',
                enabledTransports: ['ws', 'wss'],
            });
        });
    });
}

