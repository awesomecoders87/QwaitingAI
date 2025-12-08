<div style="--col-span-default: 1 / -1;" class="col-[--col-span-default] fi-wi-widget fi-wi-stats-overview p-4">

<h1 class="text-3xl font-semibold mb-4">{{ __('text.Display Screen') }}</h1>
   
   <div class="fi-wi-stats-overview-stats-ctn grid gap-6 md:grid-cols-3">
  <div
           class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5">
           <div class="grid gap-y-2">
               <div class="flex items-center gap-x-2">

                       <div
                       class="fi-wi-stats-overview-stat-value">
                        <h3 class="text-xl font-semibold tracking-tight text-gray-950 mb-3">{{ __('text.Default Display') }}</h3>

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                       <path stroke-linecap="round" stroke-linejoin="round" d="M6 20.25h12m-7.5-3v3m3-3v3m-10.125-3h17.25c.621 0 1.125-.504 1.125-1.125V4.875c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125Z" />
                     </svg>
                     
                   </div>
               </div>

             <a href="{{url('display')}}" target="_blank"> 
                <span
               class="fi-wi-stats-overview-stat-label text-lg font-medium text-gray-500">
                   {{ __('text.View Screen') }}
               </span>
             </a>

           </div>

       </div>
       @foreach($screenNames as $screen)
    
       <div
           class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5">
           <div class="grid gap-y-2">
               <div class="flex items-center gap-x-2">

                       <div
                       class="fi-wi-stats-overview-stat-value">
                       <h3 class="text-xl font-semibold tracking-tight text-gray-950 mb-3"> {{$screen->name}}</h3>

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                       <path stroke-linecap="round" stroke-linejoin="round" d="M6 20.25h12m-7.5-3v3m3-3v3m-10.125-3h17.25c.621 0 1.125-.504 1.125-1.125V4.875c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125Z" />
                     </svg>
                     
                   </div>
               </div>
               @php
                $encodedId = base64_encode($screen->id);
            @endphp
<a href="{{ url('ds/' . $encodedId) }}" target="_blank">
                <span
               class="fi-wi-stats-overview-stat-label text-lg font-medium text-gray-500">
                   {{ __('text.View Screen') }}
               </span>
             </a>

           </div>

       </div>

    @endforeach 

   </div>
</div>