<!DOCTYPE html>
{{-- <html lang="en" data-theme="light"> --}}

<html lang="{{ session('app_locale', app()->getLocale()) }}"
      dir="{{ session('app_locale') === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ url('images/favicon.ico') }}" />

    <title>{{ $title ?? 'Call Screen'}}</title>

    {{-- <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script> --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="{{ asset('js/cdn/tailwind.js') }}"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    @if(session('app_locale') === 'ar')
        <link rel="stylesheet" href="{{ asset('css/style-rtl.css?v='.time()) }}">
    @endif

    <link rel="stylesheet" href="{{ asset('css/queue.css') }}">

    @stack('styles')
    @livewireStyles
    <style>
        .header_lang {
            position: absolute;
            top: 0;
            left: 100%;
            transform: translate(-100%, 0);
            width: 200px;
            background-color: #f0f0f0;
        }

        .header_lang select {
            padding: 5px;
        }

        .header_sec {
            display: flex;
            position: absolute;
            align-items: baseline;
            right: 10px;
            top: 10px
        }
        .hidden {
            display: none !important;
        }
        </style>

{{-- Light theme color style --}}
        <style>
   html.light {
        --primary-color: {{ $theme->theme_color ?? '#4CAF50' }};
        --button-color: {{ $theme->button_color ?? '#007bff' }};
        --font-color: {{ $theme->font_color ?? '#000' }};
    }

    html.light body,
    html.light a,
    html.light h1,
    html.light h2,
    html.light h3,
    html.light h4,
    html.light h5,
    html.light p,
    html.light select option,
    html.light label {
        color: var(--font-color);
    }

    html.light .btn,
    html.light button {
        background-color: var(--button-color);
        border-color: var(--button-color);
        color: #fff;
    }

    html.light .btn:hover,
    html.light .button:hover {
        background-color: color-mix(in srgb, var(--button-color) 90%, black 10%);
        border-color: color-mix(in srgb, var(--button-color) 90%, black 10%);
    }

    html.dark .bg-white:not(button){background:transparent }
    html.dark .bg-gray-50,html.dark .bg-gray-50{background-color: rgb(17 24 39 / var(--tw-bg-opacity, 1));color: rgb(255 255 255 / 0.9);}
    html.dark button {
        background-color: #4f46e5;
        border-color: #4f46e5;
        color: #fff;
    }
    html.dark .border-gray-200,html.dark .border-gray-300{border-color: rgb(55 65 81 / var(--tw-border-opacity, 1));}
    html.dark .bg-blue-100{background-color: rgb(17 24 39 / var(--tw-bg-opacity, 1));}
    html.dark .bg-brand-100{background-color: rgb(44  55  77 / var(--tw-bg-opacity, 1));}

</style>

</head>
<body class="dark:bg-gray-900 dark:text-gray-200">
<!-- <body class="dark:bg-gray-900 dark:text-gray-200" 
 x-data="{
    page: 'ecommerce',
    loaded: true,
    darkMode: false,
    stickyMenu: false,
    sidebarToggle: false,
    scrollTop: false,
    monthly: true,

    init() {
      try {
        const stored = localStorage.getItem('darkMode');
        this.darkMode = stored ? JSON.parse(stored) : false;
        this.$watch('darkMode', value => {
          try {
            localStorage.setItem('darkMode', JSON.stringify(value));
          } catch (e) {
            // ignore if private mode blocks storage
          }
        });
      } catch (e) {
        // private mode or storage blocked — fallback to default
        this.darkMode = false;
      }
    }
  }"
  :class="{ 'dark:bg-gray-900 dark:text-gray-200': darkMode }"
> -->


    {{ $slot }}

    @livewireScripts
    <script src="{{ asset('js/cdn/pusher.min.js') }}"></script>
    <script src="{{ asset('js/cdn/jquery.min.js') }}"></script>
    <script src="{{ asset('js/cdn/sweetalert2.js') }}"></script>
     <script src="{{asset('/js/fullscreen.min.js?v=3.1.0.0')}}"></script>


    @stack('scripts')

    @php
        $reverbDetails = App\Models\ReverbDetail::viewReverbDetails();
    @endphp

    <script type="module">
        // import Echo from 'https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/+esm'
        // window.Pusher = Pusher;
        // window.Echo = new Echo({
        //     broadcaster: 'pusher',
        //     key: "{{ !empty($pusherDetails) ? $pusherDetails->key : '' }}",
        //     cluster: "{{ !empty($pusherDetails) ? $pusherDetails->options_cluster : ''}}" ?? 'mt1',
        //     forceTLS: true
        // });
        // window.Echo.channel(`queue-progress.{team_id}`)
        //     .listen('QueueProgress', (e) => {
        //         console.log('Queue event progress received:');
        //         console.log(e);
        //     });
        Livewire.on('languageChanged', () => {
            window.location.reload();
        });


    </script>

    <script type="module" src="{{ asset('/js/app/echo-unauth.js?v='.time()) }}" type="module"></script>
    <script src="{{ asset('/js/livewire.js?v='.time()) }}" type="module"></script>
    <script src="{{asset('/js/app/desktop-notification.js?v='.time())}}"></script>
    <script src="{{asset('/js/app/waiting-notification.js?v='.time())}}"></script>
    {{-- <script type="module" src="{{ Vite::asset('resources/js/app.js') }}"></script> --}}

     <script>
    const htmlEl = document.documentElement;
    const themeToggleBtn = document.getElementById('theme-toggle');

    // Function to apply theme
    function applyTheme(theme) {
        if (theme === 'dark') {
            htmlEl.classList.add('dark');
            htmlEl.classList.remove('light');
        } else {
            htmlEl.classList.add('light');
            htmlEl.classList.remove('dark');
        }
    }

    // Load theme from localStorage or default to light
    let savedTheme = localStorage.getItem('theme') || 'light';
    applyTheme(savedTheme);

    // Toggle theme on button click
    // themeToggleBtn.addEventListener('click', () => {
    //     savedTheme = (savedTheme === 'light') ? 'dark' : 'light';
    //     localStorage.setItem('theme', savedTheme);
    //     applyTheme(savedTheme);
    // });
</script>

<script>
    window.addEventListener('waiting-alert', event => {
        console.log(event);
        let alertBox = document.createElement('div');
        alertBox.innerText = event.detail[0].message;
        alertBox.className = "fixed bottom-5 right-5 bg-red-600 text-white px-4 py-2 rounded shadow-lg z-50 animate-bounce";

        document.body.appendChild(alertBox);

        setTimeout(() => {
            alertBox.remove();
        }, 30000); // alert disappears after 4s
    });
</script>
<script>

    if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark')
    }
</script>
</body>

</html>
