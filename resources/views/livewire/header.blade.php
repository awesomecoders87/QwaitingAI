<div>
    @if ($showHeader)
        <header
            class="text-gray-800 body-font main-header-display  {{ !empty($currentTemplate) && $currentTemplate->is_header_show == App\Models\ScreenTemplate::STATUS_INACTIVE ? 'hidden' : '' }}">
            <div class="container mx-auto  flex-wrap p-5 flex-col md:flex-row items-center justify-center mb-4">
                <div class="full text-center">
                    @php
                        $url = request()->url();
                        $headerPage = App\Models\SiteDetail::FIELD_BUSINESS_LOGO;

                        if (strpos($url, 'mobile/queue') !== false) {
                            $headerPage = App\Models\SiteDetail::FIELD_MOBILE_LOGO;
                        }

                        $logo = App\Models\SiteDetail::viewImage($headerPage, $teamId, $locationId);
                          $logo_size = 'w-32';
                        if($logoSize == 'small'){
                             $logo_size = 'w-32';
                        }elseif($logoSize == 'medium'){
                              $logo_size = 'w-60';
                        }elseif($logoSize == 'large'){
                             $logo_size = 'w-96';
                        }
                    @endphp

                    @if ($teamId != 4)
                        @if (!empty($currentTemplate))
                            @if ($currentTemplate->is_logo == App\Models\ScreenTemplate::STATUS_ACTIVE)
                                <img src="{{ url($logo) }}" class="mx-auto {{$logo_size}} mb-4" style="display:inline-block;"
                                    />
                            @endif
                        @else
                            <img src="{{ url($logo) }}" class="mx-auto {{$logo_size}} mb-4" style="display:inline-block;"
                                 />
                        @endif
                    @endif
                </div>
                <div class="header_sec flex  items-center gap-3">
                    @if ($showLocationSelector)
                        <livewire:location-selector wire:key="location-selector" />
                    @endif

                </div>
@if($enbaleHeaderTitle)
                <div class="grid">
                    <h1 class="text-3xl font-bold text-black-700 text-center  dark:text-white white_text">
                        @php
                            $locale = session('app_locale');
                            $heading1 = $siteData?->queue_heading_first ?? '';
                            if ($locale !== 'en' && isset($this->translations['Queue Heading 1'][$locale])) {
                                $heading1 = $this->translations['Queue Heading 1'][$locale];
                            }
                        @endphp
                        {{ $heading1 }}
                    </h1>

                    <p class="text-gray-600 text-lg mt-2 text-center dark:text-white white_text">
                        @php
                            $heading2 = $siteData?->queue_heading_second ?? '';
                            if ($locale !== 'en' && isset($this->translations['Queue Heading 2'][$locale])) {
                                $heading2 = $this->translations['Queue Heading 2'][$locale];
                            }
                        @endphp
                        {{ $heading2 }}
                    </p>
                </div>
@endif

            </div>

        </header>
    @endif


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Livewire.on('refresh', () => {

                location.reload(); // Refresh the page when OK is clicked
            });

        });
    </script>
</div>
