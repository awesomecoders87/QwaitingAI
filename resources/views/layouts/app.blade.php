<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Qwaiting'}}</title>
    <link rel="icon" href="{{ url('images/favicon.ico') }}" />
    <!-- <script src="https://cdn.tailwindcss.com"></script>  -->
    
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="{{ asset('css/custom.css?v='.time()) }}">
    <link rel="stylesheet" href="{{ asset('css/login.css?v='.time()) }}">
    <link rel="stylesheet" href="{{ asset('css/style-safari.css?v='.time()) }}">
    @yield('links')
    @vite(['resources/css/app.css','resources/js/app.js'])

</head>

<body x-data="{ page: 'ecommerce', 'loaded': true, 'darkMode': false, 'stickyMenu': false, 'sidebarToggle': false, 'scrollTop': false }"
    x-init="
         darkMode = JSON.parse(localStorage.getItem('darkMode'));
         $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark bg-gray-900': darkMode === true}">
    @yield('content')

    @include('n8n.chatbot')
</body>

</html>