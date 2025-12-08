<div class="p-4">
    <h2 class="text-xl font-semibold mb-4">{{ __('text.Edit Role') }}</h2>
    <div class="rounded-lg shadow border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-6">



        <form wire:submit.prevent="update">
            <div class="mb-4">
                <label class="block mb-1">{{ __('text.Role Name') }}</label>
                <input type="text" wire:model="name" class="w-full px-4 py-2 border rounded-lg">
                @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block mb-1">{{ __('text.permissions') }}</label>

                <!-- Select All Checkbox -->
                <div class="mb-2">
                    <input type="checkbox" id="selectAll" wire:model.live="selectAll" class="mr-2">
                    <label for="selectAll">{{ __('text.Select All / Unselect All') }}</label>
                </div>

                <!-- Permission Checkboxes -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 rounded-lg p-3">
                    @foreach ($allPermissions as $id => $permission)
                    <label class="flex items-center space-x-2">
                        <input
                            type="checkbox"
                            value="{{ $id }}"
                            wire:model="permissions"
                            class="form-checkbox rounded text-brand-500">
                        <span>{{ $permission }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">{{ __('text.Save') }}</button>
            <a href="{{ url('roles') }}"
                class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-error-500 shadow-theme-xs hover:bg-error-600">
                {{ __('text.Cancel') }}
            </a>
        </form>

        <script>
            document.addEventListener("DOMContentLoaded", function() {

                $(document).ready(function() {
                    $('#permissions').select2();
                    $('#permissions').on("change", function(e) {
                        let data = $(this).val();
                        @this.set('permissions', data);
                    });
                });
            });
        </script>
    </div>
</div>