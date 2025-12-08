<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-6">
    <h2 class="text-xl font-semibold mb-4">Logo Update</h2>

    @if (session()->has('message'))
        <div class="p-2 mb-4 text-green-600 bg-green-100 rounded">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-2 mb-4 text-red-600 bg-red-100 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @php
            $logos = [
                'business_logo' => 'Business Logo',
                'mobile_logo' => 'Mobile Logo',
                'logo_print_ticket' => 'Logo on Print Ticket',
                'logo_footer_ticket_screen' => 'Footer Ticket Screen Logo',
            ];
        @endphp

        @foreach ($logos as $key => $label)
            <div class="mt-6">
                <label class="block text-sm font-medium mb-2">{{ $label }}</label>

                @php 
                    $existingLogo = "existing" . str_replace('_', '', ucwords($key, '_'));
                @endphp

                @if ($$existingLogo)
                    <p class="text-gray-600 text-sm">Current:</p>
                    <img src="{{ url('images/' . $$existingLogo) }}" height="200" width="200" class="rounded shadow-md border">
                    
                    <button wire:click="deleteLogo('{{ $key }}')" 
                        class="px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-error-500 shadow-theme-xs hover:bg-error-600 mt-2">
                        Delete
                    </button>
                @endif


                @if ($$key instanceof \Livewire\TemporaryUploadedFile)
    <p class="text-gray-600 text-sm mt-2">New Selection:</p>
    <img src="{{ $$key->temporaryUrl() }}" class="w-32 h-32 mb-2 rounded shadow-md border">
@endif

                <label class="flex items-center justify-center w-full h-10 px-4 py-2 mt-2 bg-white border border-gray-300 rounded-md shadow-sm cursor-pointer hover:bg-gray-50">
                    <span class="text-gray-600 text-sm">Choose File</span>
                    <input type="file" wire:model="{{ $key }}" class="hidden">
                </label>

                <button wire:click="uploadLogo('{{ $key }}')"
                    class="mt-2 px-4 py-2 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 disabled:opacity-50"
                   >
                    <span >Upload</span>
                    <!-- <span wire:loading>Uploading...</span> -->
                </button>
            </div>
        @endforeach
    </div>
</div>
