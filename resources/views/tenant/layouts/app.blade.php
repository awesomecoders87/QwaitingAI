<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Qwaiting</title>

  <link rel="stylesheet" href="{{ asset('css/style.css') }}">


  @vite(['resources/css/app.css','resources/js/app.js'])

</head>

<body x-data="{ page: 'ecommerce', 'loaded': true, 'darkMode': false, 'stickyMenu': false, 'sidebarToggle': false, 'scrollTop': false }"
  x-init="
         darkMode = JSON.parse(localStorage.getItem('darkMode'));
         $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
  :class="{'dark bg-gray-900': darkMode === true}">
 
  @yield('content')

</body>

</html>