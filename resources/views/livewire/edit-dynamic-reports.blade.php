<div class="p-4"> 
    <h2 class="text-xl font-semibold dark:text-white/90 mb-4">{{ __('text.Edit Dynamic Report') }}</h2>

    <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-md p-4">
        <form wire:submit.prevent="saveReport">
            
            {{-- Report Name --}}
            <div class="mb-4">
                <label class="block mb-1">{{ __('text.Report Name') }}</label>
                <input type="text" wire:model="report_name" class="w-full px-4 py-2 border rounded-lg">
                @error('report_name')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror
            </div>

            {{-- Status Checkbox --}}
            <div class="mt-4 flex justify-end space-x-2">
                <label class="block mt-4 flex items-center">
                    <input type="checkbox" wire:model="status" class="mr-2">
                    {{ __('text.Active/Inactive') }}
                </label>
            </div>

            {{-- Columns --}}
            <div class="mb-4">
                <label class="block mb-1">{{ __('text.Columns') }}</label>
                 <div class="grid grid-cols-1 md:grid-cols-3 gap-2  p-3">

                    @foreach ($availableFields as $key => $field)
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" value="{{ $key }}" wire:model="report_fields" {{ in_array($key,$report_fields) ? 'checked' : ''}}
                                class="form-checkbox rounded text-brand-500">
                            <span>{{ ucfirst($field) }}</span>
                        </label>
                    @endforeach

                </div>
                @error('report_fields')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex space-x-2">
                <button type="submit"
                    class="px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                    {{ __('text.Update') }}
                </button>

                <a href="{{ url('dynamic-reports') }}"
                    class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-error-500 shadow-theme-xs hover:bg-error-600">
                    {{ __('text.Cancel') }}
                </a>
            </div>
        </form>

        {{-- SweetAlert --}}
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Livewire.on('updated', () => {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Report updated successfully.',
                        icon: 'success',
                    }).then(() => {
                        window.location.href = '/dynamic-reports-list'; 
                    });
                });
            });
        </script>
    </div>
</div>
