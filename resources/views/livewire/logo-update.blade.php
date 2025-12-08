<div class="p-4">


<style>
    .tool-description{
        position:absolute;
        left:50%;
        bottom:100%;
        transform:translateX(-50%);
        margin-bottom:0.5rem;
        color:#fff !important;
        background-color:#374151;
        font-size:0.875rem;
        padding:0.25rem 0.5rem;
        border-radius:0.25rem;
        width:14rem;
        text-align:left;
        z-index:50;
    }
</style>
    <h2 class="text-xl font-semibold mb-4">{{ __('setting.Logo Update') }}</h2>

    <div class="rounded-lg border shadow p-4 border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-6">

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

        <div class="-mx-2.5 flex flex-wrap w-full border-gray-200 dark:border-gray-800 dark:bg-white/[0.03]">
            @php
                $logos = [
                    'business_logo' => __('setting.Business Logo'),
                    'mobile_logo' => __('setting.Mobile Logo'),
                    'logo_print_ticket' => __('setting.Logo on Print Ticket'),
                    'logo_footer_ticket_screen' => __('setting.Footer Ticket Screen Logo'),
                ];

                $notes = [
                    'business_logo' => __('setting.Displayed in the browser interface and web header.'),
                    'mobile_logo' => __('setting.Displayed on mobile devices for a responsive brand appearance.'),
                    'logo_print_ticket' => __('setting.Used on printed tickets to maintain brand consistency.'),
                    'logo_footer_ticket_screen' => __('setting.Shown in the footer area of the ticket display screen.'),
                ];
            @endphp

            @foreach ($logos as $key => $label)
                <div class="w-full px-2.5 xl:w-1/3 flex flex-col py-4 sm:py-2">
                    <section class="px-4 py-3 mt-1 rounded-2xl border border-gray-200 bg-gray-100 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex items-center mb-2 gap-1">
                            <label class="block text-sm text-xl font-medium">{{ $label }}</label>
                            <div class="relative inline-block">
                                <button type="button" data-tooltip-id="tooltip-{{ $key }}" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="9" />
                                        <line x1="12" y1="8" x2="12" y2="8" />
                                        <line x1="12" y1="12" x2="12" y2="16" />
                                    </svg>
                                </button>
                                <div id="tooltip-{{ $key }}" class="hidden tool-description">
                                    {{ $notes[$key] }}
                                </div>
                            </div>
                        </div>

                        @php $existingLogo = "existing" . str_replace('_', '', ucwords($key, '_')); @endphp

                        <p class="text-gray-600 text-sm mb-3 font-bold">{{ __('setting.Current') }}</p>
                        @if (!empty($$existingLogo))
                            <div class="flex items-center py-4">
                                <img src="{{ url('storage/'.$$existingLogo) }}" height="200" width="200" alt="Current">
                                @if($$existingLogo != 'logo/qwaiting.png')
                                    <div class="px-4 py-3" wire:click="deleteconfirmation('{{ $key }}')">
                                        <svg class="cursor-pointer hover:fill-error-500 dark:hover:fill-error-500 fill-gray-700 dark:fill-gray-400"
                                            width="20" height="20" viewBox="0 0 20 20" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M6.54142 3.7915C6.54142 2.54886 7.54878 1.5415 8.79142 1.5415H11.2081C12.4507 1.5415 13.4581 2.54886 13.4581 3.7915V4.0415H15.6252H16.666C17.0802 4.0415 17.416 4.37729 17.416 4.7915C17.416 5.20572 17.0802 5.5415 16.666 5.5415H16.3752V8.24638V13.2464V16.2082C16.3752 17.4508 15.3678 18.4582 14.1252 18.4582H5.87516C4.63252 18.4582 3.62516 17.4508 3.62516 16.2082V13.2464V8.24638V5.5415H3.3335C2.91928 5.5415 2.5835 5.20572 2.5835 4.7915C2.5835 4.37729 2.91928 4.0415 3.3335 4.0415H4.37516H6.54142V3.7915ZM14.8752 13.2464V8.24638V5.5415H13.4581H12.7081H7.29142H6.54142H5.12516V8.24638V13.2464V16.2082C5.12516 16.6224 5.46095 16.9582 5.87516 16.9582H14.1252C14.5394 16.9582 14.8752 16.6224 14.8752 16.2082V13.2464ZM8.04142 4.0415H11.9581V3.7915C11.9581 3.37729 11.6223 3.0415 11.2081 3.0415H8.79142C8.37721 3.0415 8.04142 3.37729 8.04142 3.7915V4.0415ZM8.3335 7.99984C8.74771 7.99984 9.0835 8.33562 9.0835 8.74984V13.7498C9.0835 14.1641 8.74771 14.4998 8.3335 14.4998C7.91928 14.4998 7.5835 14.1641 7.5835 13.7498V8.74984C7.5835 8.33562 7.91928 7.99984 8.3335 7.99984ZM12.4168 8.74984C12.4168 8.33562 12.081 7.99984 11.6668 7.99984C11.2526 7.99984 10.9168 8.33562 10.9168 8.74984V13.7498C10.9168 14.1641 11.2526 14.4998 11.6668 14.4998C12.081 14.4998 12.4168 14.1641 12.4168 13.7498V8.74984Z"
                                                fill=""></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        @else
                            <img src="{{ url('images/no-image.jpg') }}" height="200" width="200" alt="No image">
                        @endif

                        {{-- New selection preview (images only) --}}
                        @if ($$key)
                            @php
                                $mime = $$key->getMimeType();
                                $isImage = str_starts_with($mime, 'image/');
                            @endphp
                            @if ($isImage)
                                <p class="text-gray-600 text-sm mt-2">{{ __('setting.New Selection') }}:</p>
                                <img src="{{ $$key->temporaryUrl() }}" class="w-32 h-32 mb-2 rounded shadow-md border" alt="Preview">
                            @endif
                        @endif

                        <div class="flex gap-10">
                            <label class="flex items-center justify-center w-full h-10 px-4 py-2 mt-2 bg-white border border-gray-300 rounded-md shadow-sm cursor-pointer hover:bg-gray-50">
                                <span class="text-gray-600 text-sm">{{ __('setting.Choose File') }}</span>
                                <input
                                   type="file"
                                   wire:model="{{ $key }}"
                                   class="hidden"
                                   accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                >
                            </label>


                            <button
                                wire:click="uploadLogo('{{ $key }}')"
                                class="mt-2 px-4 py-2 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 disabled:opacity-50"
                                wire:loading.attr="disabled"
                                wire:target="{{ $key }}"
                            >
                                <span wire:loading.remove wire:target="{{ $key }}">{{ __('setting.Upload') }}</span>
                                <span wire:loading wire:target="{{ $key }}">{{ __('setting.Uploading') }}...</span>
                            </button>

                        </div>
                           @error($key)
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                    </section>
                </div>
            @endforeach
        </div>
    </div>
    {{-- SweetAlert hooks (unchanged) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    Livewire.on('confirmation-delete', () => {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('confirmed-delete');
            }
        });
    });

    Livewire.on('deleted', () => {
        Swal.fire({
            title: 'Success!',
            text: 'Deleted successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                location.reload();
            }
        });
    });

    Livewire.on('updated', () => {
        Swal.fire({
            title: 'Success!',
            text: 'Updated successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                location.reload();
            }
        });
    });
});
</script>

{{-- Tooltip behavior (unchanged) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-tooltip-id]').forEach(button => {
        const tooltipId = button.getAttribute('data-tooltip-id');
        const tooltip = document.getElementById(tooltipId);

        button.addEventListener('mouseenter', () => tooltip.classList.remove('hidden'));
        button.addEventListener('mouseleave', () => tooltip.classList.add('hidden'));
        tooltip.addEventListener('mouseenter', () => tooltip.classList.remove('hidden'));
        tooltip.addEventListener('mouseleave', () => tooltip.classList.add('hidden'));
    });
});
</script>
</div>


