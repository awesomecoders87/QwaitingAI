<div class="flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8 mx-auto">
        <h2 class="text-2xl font-semibold text-center text-slate-800 mb-6">QR Code Scanner</h2>


            <!-- QR Code Input -->
            <div class="flex flex-col">
                <label for="code" class="text-sm font-medium text-slate-700 mb-1">QR Code</label>
                <input
                    type="text"
                    id="code"
                    wire:model.live.debounce.1000ms="code"
                    placeholder="Enter your code"
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                />
                @error('code')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

         @if($showData && $getData)
    <div class="mt-4 p-4 border rounded bg-gray-50">
        <h3 class="font-bold mb-2">QR Data:</h3>

        <ul>
            <li><strong>Message:</strong> <span class="{{ $getData['status']== 'error' ? 'text-red-500' : ''}}">{{ $getData['message'] ?? '' }}</span></li>

            @if(isset($getData['data']) && is_array($getData['data']))
                <li><strong>Data:</strong>
                    <ul class="ml-4 mt-1">
                        @foreach($getData['data'] as $key => $value)
                            <li><strong>{{ $key }}:</strong> {{ $value }}</li>
                        @endforeach
                    </ul>
                </li>
            @endif
        </ul>
    </div>
@endif


    </div>
</div>
