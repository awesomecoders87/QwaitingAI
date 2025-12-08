<!DOCTYPE html>
{{-- <html lang="en" data-theme="light"> --}}

<html lang="{{ session('app_locale', app()->getLocale()) }}"
      dir="{{ session('app_locale') === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Qwaiting'}}</title>

    <link rel="icon" href="{{ url('images/favicon.ico') }}" />
    {{-- <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script> --}}
      <script src="{{ asset('js/cdn/tailwind.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
      @if(session('app_locale') === 'ar')
        <link rel="stylesheet" href="{{ asset('css/style-rtl.css?v='.time()) }}">
    @endif
    <link rel="stylesheet" href="{{ asset('css/queue.css') }}">
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.3.2/main.min.css" rel="stylesheet"> --}}
      <link rel="stylesheet" href="{{ asset('css/cdn/select2.min.css') }}">


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

</head>

<body>

    {{ $slot }}

    @livewireScripts
    {{-- <script src="https://cdn.jsdelivr.net/npm/flowbite@2.4.1/dist/flowbite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>  --}}
        <script src="{{ asset('js/cdn/jquery.min.js') }}"></script>
   <script src="{{ asset('js/cdn/flowbite.min.js') }}"></script>
    <script src="{{ asset('js/cdn/flatpickr.js') }}"></script>
    <script src="{{ asset('js/cdn/pusher.min.js') }}"></script>
    <script src="{{ asset('js/cdn/sweetalert2.js') }}"></script>
    <script src="{{asset('/js/fullscreen.min.js?v=3.1.0.0')}}"></script>

    <script type="module" src="{{ asset('/js/app/echo-unauth.js?v='.time()) }}" type="module"></script>
    <script src="{{ asset('/js/livewire.js?v='.time()) }}" type="module"></script>
   <script src="{{asset('/js/app/desktop-notification.js?v='.time())}}"></script>
   @stack('scripts')

   <script>
    Livewire.on('locationUpdated', () => {
            window.location.reload();
        });
   </script>

</body>

</html>
