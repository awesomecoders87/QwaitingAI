<div>
  @if($showHeader)
    <header class="text-gray-800 body-font main-header-display  {{(!empty($currentTemplate) && $currentTemplate->is_header_show == App\Models\ScreenTemplate::STATUS_INACTIVE) ?'hidden':''}}">
        <div class="container mx-auto  flex-wrap p-5 flex-col md:flex-row items-center justify-center">
          <div class="full text-center">
         @php 
              $url = request()->url();
              $headerPage = App\Models\SiteDetail::FIELD_BUSINESS_LOGO;

              if ( strpos( $url, 'mobile/queue' ) !== false ) {
                $headerPage = App\Models\SiteDetail::FIELD_MOBILE_LOGO;
              }

              $logo =  App\Models\SiteDetail::viewImage($headerPage,$teamId,$locationId);
          @endphp


            @if(!empty($currentTemplate) )
                    @if( $currentTemplate->is_logo == App\Models\ScreenTemplate::STATUS_ACTIVE)
                    <img src="{{ url($logo)}}" class="w-100 h-100 max-w-44" style="display:inline-block" width="100"/>
                    @endif
             @else 
                 <img src="{{ url($logo)}}" class="w-100 h-100 max-w-44" style="display:inline-block" width="100"/>
              @endif
            </div>    
           <div class="header_sec flex  items-center gap-3">
              @if($showLanguageSelector)
                  {{-- <livewire:language-selector wire:key="language-selector" /> --}}
              @endif
              
              @if($showLocationSelector)
                  <livewire:location-selector wire:key="location-selector" />
              @endif

      
    </header>   
@endif    
  

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        Livewire.on('refresh', () => {
          alert('test');
                    location.reload(); // Refresh the page when OK is clicked
                });
        
    });
</script>  
</div>

