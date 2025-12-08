<div class="relative items-center hidden w-full h-full bg-brand-950 lg:grid lg:w-1/2">
       
    @php 
      $url = request()->url();
      $headerPage = App\Models\SiteDetail::FIELD_BUSINESS_LOGO;

      if ( strpos( $url, 'mobile/queue' ) !== false ) {
        $headerPage = App\Models\SiteDetail::FIELD_MOBILE_LOGO;
      }

  $logo =  App\Models\SiteDetail::viewImage($headerPage);
  @endphp
    <div class="flex flex-col items-center justify-center w-full h-full text-center z-1">
        <!-- ===== Common Grid Shape Start ===== -->
        <div class="absolute right-0 top-0 -z-1 w-full max-w-[250px] xl:max-w-[450px]">
            <img src="{{ url('images/shape/grid-01.svg') }}" alt="grid">
        </div>
        <div class="absolute bottom-0 left-0 -z-1 w-full max-w-[250px] rotate-180 xl:max-w-[450px]">
            <img src="{{ url('images/shape/grid-01.svg') }}" alt="grid">
        </div>

        <!-- Logo -->
        <!-- <a href="" class="block mb-4">
            <img src="{{url($logo)}}" alt="Logo" width="100" height="100" />
        </a> -->

        <!-- Text Content -->
        <div class="text-center text-white space-y-3">
            <h1 class="text-2xl font-bold">Welcome to {{ tenant('brand') ?? tenant('name') }}</h1>
            <p class="text-gray-400 dark:text-white/60">Efficient queue management for seamless customer experiences.</p>

            <div class="text-gray-300 dark:text-white/60 space-y-2">
                <p>✓ Easy appointment scheduling</p>
                <p>✓ Real-time queue tracking</p>
                <p>✓ Seamless customer engagement</p>
            </div>
        </div>
    </div>
</div>
