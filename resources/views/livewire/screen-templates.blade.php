<div class="p-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">{{ __('setting.Screen Templates') }}</h2>
        <a href="{{ route('tenant.create-screen-template') }}" class="px-3 py-2 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600"><i class="ri-add-circle-line"></i> {{ __('setting.Add New') }}</a>
    </div>
    <div class="overflow-auto rounded-lg shadow border p-4 border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <table class="w-full border-collapse table-auto">
            <thead>
                <tr>
                    <th class="p-2">{{ __('setting.Name') }}</th>
                    <th class="p-2">{{ __('setting.Type') }}</th>
                    <th class="p-2">{{ __('setting.Template') }}</th>
                    <th class="p-2">{{ __('setting.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @if(count($screenTemplates) > 0)
                    @foreach ($screenTemplates as $template)
                        <tr>
                            <td class="p-2">{{ $template->name }}</td>
                            <td class="p-2">{{ $template->type == 'Category' ? __('setting.Service')  : __('setting.Counter')}}</td>
                            <td class="p-2">  {{ is_array($template->template_name) ? implode(', ', $template->template_name) : $template->template_name }}</td>
                            <td class="p-2">
                                <div>
                                    <div x-data="{openDropDown: false}" class="relative">
                                        <button @click="openDropDown = !openDropDown" class="text-gray-500 dark:text-gray-400 action-btn">
                                            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.749 11.0285 19.749 11.995V12.005C19.749 12.9715 18.9655 13.755 17.999 13.755C17.0325 13.755 16.249 12.9715 16.249 12.005V11.995C16.249 11.0285 17.0325 10.245 17.999 10.245ZM13.749 11.995C13.749 11.0285 12.9655 10.245 11.999 10.245C11.0325 10.245 10.249 11.0285 10.249 11.995V12.005C10.249 12.9715 11.0325 13.755 11.999 13.755C12.9655 13.755 13.749 12.9715 13.749 12.005V11.995Z" fill=""></path>
                                            </svg>
                                        </button>
                                        <div x-show="openDropDown" @click.outside="openDropDown = false" class="dropdown-menu shadow-theme-lg dark:bg-gray-dark absolute top-full right-0 z-40 w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800">
                                            <a href="{{ route('tenant.edit-screen-template', $template->id) }}" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                                {{ __('setting.Edit') }}
                                            </a>
                                            <a href="{{ route('tenant.edit-screen-templates-settings', $template->id) }}" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                                {{ __('setting.Settings') }}
                                            </a>
                                            <button class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300" wire:click="deletetemplate({{ $template->id}})">
                                                {{ __('setting.Delete') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="text-center py-6">
                            <img src="{{ url('images/no-record.jpg') }}" alt="No Records Found" class="mx-auto h-30 w-30">
                            <p class="text-gray-500 dark:text-gray-400 mt-2">{{ __('setting.No records found.') }}</p>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="mt-4">
            {{ $screenTemplates->links() }}
        </div>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {

        Livewire.on('confirm-delete', () => {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
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
                    location.reload(); // Refresh the page when OK is clicked
                }
            });
        });
    });
</script>
</div>
