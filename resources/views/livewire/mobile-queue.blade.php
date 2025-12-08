<div class="container mx-auto selector-main-section">
<?php
    $background = '#fffff';
    $text_color = '#00000';
    $category_background = '#fffff';
    $buttons_background = '#fffff';
    $background = $colorSetting?->page_layout ?? '';
    $text_size = $this->siteData?->category_text_font_size ?? 'text-6xl';
    $text_color = $colorSetting?->text_layout ?? '#00000';
    $category_background = $colorSetting?->categories_background_layout ?? '#fffff';
    $text_color_hover = $colorSetting?->hover_text_layout ?? '#00000';
    $category_background_hover = $colorSetting?->hover_background_layout ?? '#fffff';
    $buttons_background = $colorSetting?->buttons_layout ?? '#00000';
    $buttons_background_hover = $colorSetting?->hover_buttons_layout ?? 'rgb(99 102 241 / var(--tw-bg-opacity))';
    
    ?>
<style>
        body,
        .queue-footer {
            background-color: <?=$background ?> !important
        }

        a{
        color:#569FF7;
        }

        .text-color {
            color: <?=$text_color ?> !important;
            background-color: <?=$category_background ?> !important;
            border-color: <?=$category_background ?>;

        }

        .text-color:hover {
            color: <?=$text_color_hover ?> !important;
            background-color: <?=$category_background_hover ?> !important;
            border-color: <?=$category_background ?>;

        }

        .Qr-text-color {
            color: <?=$text_color ?> !important;
        }

        .queue-footer-button {
            color: <?=$text_color ?> !important;
            background-color: <?=$buttons_background ?> !important;
            border-color: <?=$buttons_background ?>;
        }

        .queue-footer-button:hover {
            color: <?=$text_color_hover ?> !important;
            background-color: <?=$buttons_background_hover ?> !important;
            border-color: <?=$buttons_background ?>;
        }
       
    </style>
<link href="{{asset('/css/mobile-category.css?v=3.1.0.0')}}" rel="stylesheet" data-navigate-track />
  <!-- ===== Preloader Start ===== -->
  <div
  x-show="loaded"
  x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 500)})"
  class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-black"
>
  <div
    class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"
  ></div>
</div>

    <!-- ===== Preloader End ===== -->
@script
<script>
    document.addEventListener('livewire:initialized', () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    console.log('updateLocation: latitude ' + position.coords.latitude + ' longitude ' + position.coords.longitude);
                    let latitude = position.coords.latitude;
                    let longitude = position.coords.longitude;
                    Livewire.dispatch('locationCodChange', { latitude: latitude, longitude: longitude });
                },
                function (error) {
                    handleLocationError(error);
                }
            );
        } else {
            handleLocationError({ code: 'GEOPOSITION_UNSUPPORTED' });
        }

        function handleLocationError(error) {
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    console.error("User denied the request for Geolocation.");
                    Livewire.dispatch('locationError', {'error':'User denied the request for Geolocation.'});
                    break;
                case error.POSITION_UNAVAILABLE:
                    console.error("Location information is unavailable.");
                    Livewire.dispatch('locationError', {'error':'Location information is unavailable.'});
                    break;
                case error.TIMEOUT:
                    console.error("The request to get user location timed out.");
                    Livewire.dispatch('locationError', {'error':'The request to get user location timed out.'});
                    break;
                case error.UNKNOWN_ERROR:
                    console.error("An unknown error occurred.");
                    Livewire.dispatch('locationError', {'error':'An unknown error occurred.'});
                    break;
                case 'GEOPOSITION_UNSUPPORTED':
                    console.error("Geolocation is not supported by this browser.");
                    Livewire.dispatch('locationError',{'error':'Geolocation is not supported by this browser.'});
                    break;
            }
        }

        Livewire.on('deny-qr-scanning', () => {
            console.log("QR code scanning denied.");
            window.location.href = "{{ url('403-page') }}";
        });
    });
</script>
@endscript
