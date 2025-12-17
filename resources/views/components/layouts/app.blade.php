<!DOCTYPE html>
{{-- <html lang="en"> --}}
<html lang="{{ session('app_locale', app()->getLocale()) }}"
      dir="{{ session('app_locale') === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Qwaiting'}}</title>
    <link rel="icon" href="{{ url('images/favicon.ico') }}" />
     <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="{{ asset('css/custom.css?v='.time()) }}">
    <link rel="stylesheet" href="{{ asset('css/style-safari.css?v='.time()) }}">
    @if(session('app_locale') === 'ar')
            <link rel="stylesheet" href="{{ asset('css/style-rtl.css?v='.time()) }}">
        @endif


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- <script src="{{ url('js/desktop.js') }}"></script> --}}


    @stack('styles')
    @livewireStyles

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
    html.light label,
    html.light .text-gray-500,
    body.light div{
        color: var(--font-color);
    }

    html.light .btn,
    html.light .bg-brand-500 {
        background-color: var(--button-color);
        color: var(--font-color);
    }

    html.light .btn:hover,
    html.light .bg-brand-500:hover {
        background-color: color-mix(in srgb, var(--button-color) 90%, black 10%);
    }

</style>
<script>
    // Set theme immediately to avoid "flash" of wrong mode
    (function() {
      const stored = localStorage.getItem('theme');
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      if (stored === 'dark' || (!stored && prefersDark)) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    })();
  </script>
  <script>
    // Configure Tailwind to use 'class' mode only (no prefers-color-scheme)
    tailwind.config = {
      darkMode: 'class',
    }
  </script>
</head>

<body
    x-data="{ page: 'ecommerce', 'loaded': true, 'darkMode': false, 'stickyMenu': false, 'sidebarToggle': false, 'scrollTop': false,'monthly': true  }"
    x-init="
         darkMode = JSON.parse(localStorage.getItem('darkMode'));
         $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark:bg-gray-900 dark:text-gray-200': darkMode === false}" >
    <!-- ===== Preloader Start ===== -->
    <div x-show="loaded"
        x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 500)})"
        class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-black">
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent">
        </div>
    </div>


    <!-- ===== Preloader End ===== -->
    <div class="flex h-screen overflow-hidden dark:text-gray-200">
        <livewire:package.subscription-reminder />
        @include('components.layouts.sidebar')
        <livewire:public-links />
		<div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto bg-transparent dark:bg-gray-900">
            @include('components.layouts.header')
            <livewire:package.subscription-warning wire:poll.86400s />
            {{-- <livewire:setup-progress /> --}}

            @if(auth()->check() && auth()->user()->must_change_password === 1 && request()->is('dashboard') && auth()->user()->is_admin == 1)

            <div class="relative bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded-md flex items-center justify-between shadow-md w-full max-w-3xl mx-auto mt-4">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 9v2m0 4h.01M12 17h.01M12 7h.01M12 21.35c-4.97 0-9-4.03-9-9S7.03 3.35 12 3.35s9 4.03 9 9-4.03 9-9 9z" />
                    </svg>
                    <span class="text-sm font-medium">{{ __('text.For your security, we recommend updating your password.') }}</span>
                </div>
                <button
                    class="ml-4 px-4 py-2 text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600 rounded-md transition"
                    onclick="window.location.href='/change-password';">
                    {{ __('text.Change Password') }}
                </button>
            </div>

            @endif

            {{ $slot }}
        </div>

    </div>

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/gh/livewire/sortable@v1.x.x/dist/livewire-sortable.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('/js/livewire.js?v='.time()) }}" type="module"></script>
    <script src="{{asset('/js/app/desktop-notification.js?v='.time())}}"></script>
    <script src="{{asset('/js/app/waiting-notification.js?v='.time())}}"></script>
    <!-- <script>
        // Automatically apply dark mode if system prefers it
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
        document.body.classList.add('dark');
        }
    </script> -->
    <script>

    if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark')
    }
</script>
    <script>
        //password toggle method
        function togglePassword(inputId, eyeOpenId, eyeClosedId) {
            const input = document.getElementById(inputId);
            const eyeOpen = document.getElementById(eyeOpenId);
            const eyeClosed = document.getElementById(eyeClosedId);

            if (input.type == "password") {
                input.type = "text";

                eyeOpen.classList.remove("hidden");
                eyeClosed.classList.add("hidden");
            } else {
                input.type = "password";
                eyeOpen.classList.add("hidden");
                eyeClosed.classList.remove("hidden");
            }
        }

        function copyToClipboard(id) {
            var copyText = document.getElementById(id);
            navigator.clipboard.writeText(copyText.href).then(() => {
                showToast('✅ Link copied!');
            }).catch(() => {
                showToast('❌ Failed to copy!');
            });
        }

        function showToast(message) {
            let toast = document.createElement("div");
            toast.textContent = message;
            toast.style.cssText = `
    position: fixed; bottom: 20px; left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.8); color: white;
    padding: 10px 20px; border-radius: 6px;
    font-size: 14px; z-index: 10000; opacity: 1;
    transition: opacity 0.5s ease-in-out;
`;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = "0";
            }, 2000);
            setTimeout(() => {
                toast.remove();
            }, 2500);
        }

        function downloadQRCode(id) {
            var element = document.getElementById(id);

            if (!element) {
                alert("Element not found!");
                return;
            }

            var url = element.href || element.getAttribute("data-url"); // Get URL from href or data-url attribute
            if (!url) {
                alert("No valid URL found for QR code!");
                return;
            }

            var qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(url)}`;

            // Open QR code in a new tab
            window.open(qrCodeUrl, '_blank');
        }

        window.downloadQRCode = downloadQRCode;

        window.copyToClipboard = copyToClipboard;

        document.addEventListener('DOMContentLoaded', function() {

            Livewire.on('created', (redirectUrl) => {

                console.log(redirectUrl);

                Swal.fire({
                    title: "{{ __('message.Success!') }}",
                    text: "{{ __('message.SUCCESS0017.message') }}",
                    icon: 'success',
                    confirmButtonText: "{{ __('message.OK') }}"
                }).then((result) => {
                    if (result.isConfirmed) {
                        // location.reload(); // Refresh the page when OK is clicked
                        window.location.href = redirectUrl;
                    }
                });
            });

            Livewire.on('updated', (redirectUrl) => {

                Swal.fire({
                    title: "{{ __('message.Success!') }}",
                    text: "{{ __('message.SUCCESS0017.message') }}",
                    icon: 'success',
                    confirmButtonText: "{{ __('message.OK') }}"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = redirectUrl;
                    }
                });
            });

            Livewire.on('confirm-multiple-delete', () => {
                Swal.fire({
                    title: "{{ __('message.Are you sure') }}?",
                    text: "{{ __('message.You won\'t be able to revert this') }}!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    cancelButtonText: "{{ __('message.Cancel') }}!",
                    confirmButtonText: "{{ __('message.Yes, delete it') }}!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('confirmed-multiple-delete');
                    }
                });
            });

            Livewire.on('confirm-delete', () => {
                Swal.fire({
                    title: "{{ __('message.Are you sure')}}?",
                    text: "{{ __('message.You won\'t be able to revert this') }}!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    cancelButtonText: "{{ __('message.Cancel') }}!",
                    confirmButtonText: "{{ __('message.Yes, delete it') }}!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('confirmed-delete');
                    }
                });
            });

            Livewire.on('deleted', () => {
                Swal.fire({
                    title: "{{ __('message.Success!') }}",
                    text: "{{ __('message.Data Deleted Successfully') }}",
                    icon: 'success',
                    // confirmButtonText: 'OK'
                });
            });

            Livewire.on('no-record-selected', () => {
                Swal.fire({
                    title: "{{ __('message.warning') }}!",
                    text: "{{ __('message.No record selected') }}",
                    icon: 'warning',
                    confirmButtonText: "{{ __('message.OK') }}"
                });
            });

        });
    </script>

    <script>
       const htmlEl = document.documentElement;
    const bodyEl = document.body;
    const themeToggleBtn = document.getElementById('theme-toggle');

    // Function to apply theme on both html and body
    function applyTheme(theme) {
        if (theme === 'dark') {
            htmlEl.classList.add('dark');
            htmlEl.classList.remove('light');
            bodyEl.classList.add('dark');
            bodyEl.classList.remove('light');
        } else {
            htmlEl.classList.add('light');
            htmlEl.classList.remove('dark');
            bodyEl.classList.add('light');
            bodyEl.classList.remove('dark');
        }
    }

    // Load theme from localStorage or default to light
    let savedTheme = localStorage.getItem('theme') || 'light';
    applyTheme(savedTheme);

    // Toggle theme on button click
    themeToggleBtn.addEventListener('click', () => {
        savedTheme = (savedTheme === 'light') ? 'dark' : 'light';
        localStorage.setItem('theme', savedTheme);
        applyTheme(savedTheme);
    });
</script>

<script>
    const themeToggleBtns = document.getElementById('theme-toggle');
    const darkIcon = document.getElementById('theme-toggle-dark-icon');
    const lightIcon = document.getElementById('theme-toggle-light-icon');

    // Initialize icons based on localStorage
    if (localStorage.getItem('color-theme') === 'dark') {
      document.documentElement.classList.add('dark');
      lightIcon.classList.remove('hidden'); // show sun
    } else {
      document.documentElement.classList.remove('dark');
      darkIcon.classList.remove('hidden'); // show moon
      localStorage.setItem('color-theme', 'light'); // default
    }

    themeToggleBtns.addEventListener('click', () => {
      document.documentElement.classList.toggle('dark');
      darkIcon.classList.toggle('hidden');
      lightIcon.classList.toggle('hidden');

      if (document.documentElement.classList.contains('dark')) {
        localStorage.setItem('color-theme', 'dark');
      } else {
        localStorage.setItem('color-theme', 'light');
      }
    });
  </script>

    @stack('scripts')
</body>

</html>
