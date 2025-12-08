<div class="py-6 px-4">

    <div style="padding-top: 20px; text-align: center" class="flex content-center gap-4">
        @if($showlogo)
            <img src="{{ url($logo) }}" class="w-100 h-100" style="margin: auto; max-width: 160px"/>
        @endif
    </div>

    <div class="flex flex-col gap-2 text-black-400 pt-5"
         style="line-height: 1.24; text-align: center; border: 1px solid #ddd; padding: 12px; border-radius: 14px; margin-top: 15px; font-family: 'Simplified Arabic Fixed';">

        @if(($showusername && !empty($data['name'])))
            <h3 style="font-size: 16px; margin: 0">{{ $nameLabel }}: {{ $data['name'] ?? '' }}</h3>
        @endif

        @if($showToken)
            <div>
                <h3 style="font-size: 16px; margin: 0"><strong>{{ $tokenLabel }}: {{ $data['acronym'] . $data['token'] }}</strong></h3>
            </div>
        @endif

        @if($showarrived)
            <div>
                <h5 style="font-size: 16px; margin: 0">{{ $arrivedLabel }}: {{ $data['arrives_time'] }}</h5>
            </div>
        @endif

        @if($showlocation)
            <div>
                <h3 style="font-size: 16px; margin: 0">{{ $data['location_name'] }}</h3>
            </div>
        @endif

        @if($showcategory)
            <div>
                <h3 style="font-size: 16px; margin: 0">{{ $data['category_name'] }}</h3>
                <h3 style="font-size: 16px; margin: 0">{{ $data['secondC_name'] }}</h3>
                <h3 style="font-size: 16px;">{{ $data['thirdC_name'] }}</h3>
            </div>
        @endif

        @if($showTextmessage)
            <div>
                <h4 style="font-size: 16px; margin: 0">{{ $showTicketText }}</h4>
                <h4 style="font-size: 16px; margin: 0">{{ $showTicketText_2 }}</h4>
            </div>
        @endif

        @if($showQrcode)
            <div style="display: flex; justify-content: center; align-items: center; margin-top: 10px;">
                <img src="data:image/svg+xml;base64,{{ base64_encode($qrcodeSvg) }}" style="width: 120px; height: 120px;"/>
            </div>
        @endif
        @if(!empty($ticket_image))
            <div style="display: flex; justify-content: center; align-items: center; margin-top: 10px;">
                <img src="{{url('/storage/' . $ticket_image)}}" style="max-width:70px;height:70px;"/>
            </div>
        @endif

    </div>

    {{-- Auto print --}}
    <script>

      window.addEventListener('DOMContentLoaded', function () {
    const goBack = () => {
      if (window.history.length > 1) {
        window.history.back();
      } else {
        window.location.href = '/queue'; // fallback route
      }
    };

    window.print();

    if ('onafterprint' in window) {
      window.onafterprint = () => {
        console.log('print');
         setTimeout(goBack, 3000);
      };
    }

    // Safety fallback if onafterprint doesn't fire (some kiosk/browser modes)
    // setTimeout(goBack, 10000);
  });
    </script>
</div>

