<div class="p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">Location Groups</h2>
        <button wire:click="create" class="px-4 py-2 bg-blue-500 text-white rounded-lg">+ New Group</button>
    </div>

    <table class="min-w-full bg-white rounded-lg shadow">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-4 py-2 text-left">Group Name</th>
                <th class="px-4 py-2 text-left">Locations</th>
                <th class="px-4 py-2"> {{ __('setting.Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($groups as $group)
                <tr>
                    <td class="px-4 py-2">{{ $group->name }}</td>
                    <td class="px-4 py-2">
                        {{ $group->locations->pluck('location_name')->join(', ') }}
                    </td>


                      <td class="border-b border-gray-300 px-4 py-2 space-x-2">
                                <div x-data="{ openDropDown: false }" class="relative text-center">
                                    <button @click="openDropDown = !openDropDown" class="text-gray-500 dark:text-gray-400 action-btn">
                                        <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.749 11.0285 19.749 11.995V12.005C19.749 12.9715 18.9655 13.755 17.999 13.755C17.0325 13.755 16.249 12.9715 16.249 12.005V11.995C16.249 11.0285 17.0325 10.245 17.999 10.245ZM13.749 11.995C13.749 11.0285 12.9655 10.245 11.999 10.245C11.0325 10.245 10.249 11.0285 10.249 11.995V12.005C10.249 12.9715 11.0325 13.755 11.999 13.755C12.9655 13.755 13.749 12.9715 13.749 12.005V11.995Z"></path>
                                        </svg>
                                    </button>
                                    <div x-show="openDropDown" @click.outside="openDropDown = false" class="dropdown-menu shadow-theme-lg dark:bg-gray-dark fixed z-40 w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800">

                                        <button
                                           wire:click="edit({{ $group->id }})"
                                            class="w-full text-left px-3 py-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5">
                                            {{ __('setting.Edit') }}
                                        </button>

                                        <button wire:click="deleteconfirmation({{ $group->id }})" class="w-full text-left px-3 py-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5">
                                            {{ __('setting.Delete') }}
                                        </button>
                                    </div>
                                </div>
                            </td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-4 py-4 text-center text-gray-500">No groups found</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- Modal -->
    <div x-data="{ open: @entangle('showModal') }" x-show="open" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg p-6 w-1/2">
            <h3 class="text-lg font-bold mb-4">{{ $groupId ? 'Edit Group' : 'Create Group' }}</h3>

            <div class="space-y-4">
                <div>
                    <label class="block font-medium">Group Name</label>
                    <input type="text" wire:model="name" class="w-full border rounded p-2">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-medium">Description</label>
                    <textarea wire:model="description" class="w-full border rounded p-2"></textarea>
                </div>

                <div>
                    <label class="block font-medium mb-2">Select Locations</label>
                    <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto border p-2 rounded">
                        @foreach($locations as $loc)
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" wire:model="selectedLocations" value="{{ $loc->id }}">
                                <span>{{ $loc->location_name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-4 space-x-2">
                <button @click="open = false" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button wire:click="save" class="px-4 py-2 bg-blue-500 text-white rounded">Save</button>
            </div>
        </div>
    </div>

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
                location.reload(); // Refresh the page when OK is clicked
            }
        });
    });

      Livewire.on('create', () => {
            Swal.fire({
                title: 'Success!',
                text: 'Create successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload(); // Refresh the page when OK is clicked
                }
            });
        });
      Livewire.on('update', () => {
            Swal.fire({
                title: 'Success!',
                text: 'Update successfully.',
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
