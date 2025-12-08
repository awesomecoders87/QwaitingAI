<!DOCTYPE html>

<html lang="{{ session('app_locale', app()->getLocale()) }}"
      dir="{{ session('app_locale') === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Qwaiting'}}</title>

    <link rel="icon" href="{{ url('images/favicon.ico') }}" />

     <script src="{{ asset('js/cdn/tailwind.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">


      @if(session('app_locale') === 'ar')
        <link rel="stylesheet" href="{{ asset('css/style-rtl.css?v='.time()) }}">
        @endif

        <link rel="stylesheet" href="{{ asset('css/queue.css') }}">
        <link rel="stylesheet" href="{{ asset('css/cdn/flatpickr.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/cdn/fullcalendar.min.css') }}">

        <link rel="stylesheet" href="{{ asset('css/display.css?v='.time()) }}">



    @stack('styles')


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




    @yield('content')

     <script src="{{ asset('js/cdn/flowbite.min.js') }}"></script>
     <script src="{{ asset('js/cdn/flatpickr.js') }}"></script>
     <script src="{{ asset('js/cdn/pusher.min.js') }}"></script>
     <script src="{{ asset('js/cdn/jquery.min.js') }}"></script>
    <script src="{{asset('/js/fullscreen.min.js?v=3.1.0.0')}}"></script>

    <script type="module" src="{{ asset('/js/app/echo-unauth.js?v='.time()) }}" type="module"></script>
    <script src="{{ asset('/js/livewire.js?v='.time()) }}" type="module"></script>
    <script src="{{ asset('js/cdn/sweetalert2.js') }}"></script>
   <script src="{{asset('/js/app/desktop-notification.js?v='.time())}}"></script>
   @stack('scripts')



</body>

</html>
