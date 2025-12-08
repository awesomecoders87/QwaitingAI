<div class="container mx-auto flex justify-center items-center md:min-h-screen">
    <div class="bg-zinc-100 rounded-lg shadow-lg md:p-6 p-2 w-full max-w-xl border rounded-lg mb-3"> 
  
        <div class="flex justify-center mb-4">
            <p class="text-xl font-semibold">{{__('text.your ticket number')}} </p>
        </div>
      
            <div class="flex justify-center mb-4">
                <div class="bg-blue-100 rounded-full px-4 py-2">
                    <span class="text-3xl font-bold text-blue-600">{{ $acronym . '' . $queueDB->token }}</span>
                </div>
            </div>
           

            <div class="flex justify-center mb-4">
                <p class="text-xl font-semibold">{{__('text.Issue Date')}}  {{$queueDB->created_at->format('Y-m-d H:i:s')}}</p>
            </div>
            
      

        <div class="mb-6">
            <div class="border-t border px-3 bg-white">
                @forelse($queueStorage as $key => $queueS)
                    <div class="flex justify-between py-2 flex-wrap gap-3">
                        <div class="text-gray-600" style="width:80%;">{{ App\Models\Category::viewCategory($queueS->category_id)?->name}} {!! !empty($queueS->sub_category_id) ? '<strong>-></strong>' : ''!!} {{App\Models\Category::viewCategory($queueS->sub_category_id)?->name }}</div>
                        <div class="font-semibold text-right" style="width:10%;">{{ $queueS->waiting_time}}</div>
                    </div>
                @empty
                {{__("text.Not Found")}}
                @endforelse
            </div>
        </div>

        <div class="mb-6">
            <div class="border-t border px-3 bg-white">
                @forelse($userDetails as $key => $userD)
                    <div class="flex justify-between py-2 flex-wrap gap-3">
                        <div class="text-gray-600">{{ App\Models\FormField::viewLabel($teamId, $key) }}</div>
                        <div class="font-semibold text-right">{{ $userD }}</div>
                    </div>
                @empty
                {{__("text.no user details")}}
                @endforelse
            </div>
        </div>
        <a href="{{url('servicecall/queue')}}" class="block text-center text-blue-500 hover:underline">
            {{__("text.home")}}
           </a>
    </div>
</div>
