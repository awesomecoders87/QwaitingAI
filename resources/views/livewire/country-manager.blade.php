<div class="p-4">
    <div class="flex justify-between">
        <h2 class="text-xl font-semibold dark:text-white/90 mb-4">{{ __('text.Country Code') }}</h2>
    </div>

    <div class="flex justify-end mb-4">
        <button wire:click="openAddModal" class="primary-btn py-2 px-3 font-medium text-white transition-colors rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2"><i class="ri-add-circle-line"></i> {{ __('text.Add') }}
        </button>
    </div>

    <div class="p-4 md:p-4 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow">
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 table-auto">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border px-2 py-1">S.No</th>
                        <th class="border px-2 py-1">Country (Code)</th>
                        <th class="border px-2 py-1">Created</th>
                        <th class="border px-2 py-1">{{ __('text.Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @php $dateformat = Auth::user()->date_format ?? 'd M Y'; @endphp

                    @forelse($countries as $index => $country)
                        <tr>
                            {{-- Serial number with pagination --}}
                            <td class="border px-2 py-1">
                                {{ ($countries->currentPage() - 1) * $countries->perPage() + $index + 1 }}
                            </td>
                            <td class="border px-2 py-1">{{ $country->name }} (+{{ $country->phone_code }})</td>
                            <td class="border px-2 py-1">{{ $country->created_at->format($dateformat) }}</td>
                            <td class="border  px-5 py-4 sm:px-6">
                                <div x-data="{openDropDown: false}" class="relative">
                                    <button @click="openDropDown = !openDropDown" class="text-gray-500 dark:text-gray-400 action-btn">
                                        ⋮
                                    </button>
                                     <div x-show="openDropDown" @click.outside="openDropDown = false" class="dropdown-menu shadow-theme-lg dark:bg-gray-dark absolute top-full left-0 z-40 w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800" style="display: none;">
                                        <button class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                                                wire:click="openEditModal({{ $country->id }})">
                                            {{ __('text.Edit') }}
                                        </button>
                                        <button class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                                                wire:click="confirmDelete({{ $country->id }})">
                                            {{ __('text.Delete') }}
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="text-center py-6">
                                <p><strong>{{ __('report.No records found.') }}</strong></p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $countries->links() }}
        </div>
    </div>

    {{-- Modal --}}
    @if ($showcountryModel)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-6 rounded w-96">
                <h2 class="text-lg font-bold mb-4">{{ $countryId ? 'Edit Country Code' : 'Add Country Code' }}</h2>

                <select wire:model="select_countryId" class="w-full border rounded px-2 py-1 mb-4">
                    <option value="">-- Select Country --</option>
                    @foreach ($allcountries as $country)
                        <option value="{{ $country->id }}">{{ $country->name }} (+{{ $country->phonecode }})</option>
                    @endforeach
                </select>

                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showcountryModel', false)" class="bg-gray-300 px-3 py-2 rounded">Cancel</button>
                    <button wire:click="save" class="bg-blue-600 text-white px-3 py-2 rounded">Save</button>
                </div>
            </div>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Livewire.on('alert', ({
            type,
            message
        }) => {
            Swal.fire({
                icon: type,
                text: message,
                timer: 2000,
                showConfirmButton: false
            });
        });

        Livewire.on('confirmDelete', () => {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete the country code!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('delete');
                }
            });
        });
    });
</script>
