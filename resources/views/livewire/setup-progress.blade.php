<div class="w-full max-w-4xl mx-auto mt-4 card">

    @if($showProgressBar)
    <div class="flex justify-between mb-8">
        @foreach (['Create Location', 'Create Category', 'Create Counter', 'Create Staff'] as $index => $label)
            @php $current = $index + 1; @endphp
            <div class="flex-1 flex flex-col items-center relative">
                <div class="rounded-full w-10 h-10 flex items-center justify-center text-white
                    {{ $step > $current ? 'bg-green-600' : 'bg-gray-300' }}">
                    {{ $current }}
                </div>

                <span class="mt-2 text-sm font-semibold text-gray-700">{{ $label }}</span>
                @if ($current <= 4)
                    <div class="absolute top-5 left-1/2 w-full h-1 z-[-1] -translate-x-1/2 bg-gray-300">
                        <div class="h-full bg-green-600 transition-all duration-300"
                             style="width: {{ $step > $current ? '100%' : '0%' }}"></div>
                    </div>
                @endif
            </div>
        @endforeach
        
    </div>

    <div class="p-6 bg-white shadow-md rounded-lg" style="display:none">
        @if ($step === 1)
            <h2 class="text-xl font-bold mb-4">Location already created ✅</h2>
        @elseif ($step === 2)
            <h2 class="text-xl font-bold mb-4">Create Category</h2>
        @elseif ($step === 3)
            <h2 class="text-xl font-bold mb-4">Create Counter</h2>
            {{-- Add your counter form here --}}
            <p class="text-gray-500">Counter creation form goes here...</p>
        @elseif ($step === 4)
            <h2 class="text-xl font-bold mb-4">Create Staff</h2>
            {{-- Add your staff form here --}}
            <p class="text-gray-500">Staff creation form goes here...</p>
        @endif

      <div class="mt-6 flex justify-between">
    <button
        wire:click="previousStep"
        class="px-4 py-2 rounded bg-gray-400 text-white disabled:opacity-50"
        @if ($step === 1) disabled @endif
    >
        Previous
    </button>

    <button
        wire:click="nextStep"
        class="px-4 py-2 rounded bg-blue-600 text-white disabled:opacity-50"
        @if ($step === 4) disabled @endif
    >
        Next
    </button>
</div>
    </div>
    @endif
</div>
