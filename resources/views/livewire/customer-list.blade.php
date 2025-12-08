<div class="p-6 md:p-8 bg-gray-50 dark:bg-gray-900 min-h-screen">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-2 mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ __('report.Customer List') }}</h2>
    </div>

    <!-- Filters -->
   <!-- Filters -->
<div class="flex flex-wrap items-center gap-2 mb-6">

    <!-- Search -->
    <input 
        type="text" 
        wire:model.live.debounce.500ms="search" 
        placeholder="{{ __('report.Search by name, phone') }}"
        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-48"
    />

    <!-- Start Date -->
    <input 
        type="date" 
        wire:model.live="startDate" 
        onclick="this.showPicker()" 
        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-40"
    />

    <!-- End Date -->
    <input 
        type="date" 
        wire:model.live="endDate" 
        onclick="this.showPicker()" 
        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-40"
    />

    <!-- Export CSV -->
    <button 
        wire:click="exportCSV" 
        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition"
    >
        {{ __('report.CSV') }}
    </button>

    <!-- Export PDF -->
    <button 
        wire:click="exportToPDF" 
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
    >
        {{ __('report.PDF') }}
    </button>

    <!-- Bulk Delete -->
    <button 
        id="bulkDeleteBtn"
        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition disabled:opacity-50"
    >
        {{ __('report.Bulk Delete') }}
    </button>

    <!-- Import -->
    <button 
        wire:click.prevent="showImportModel"
        type="button"
        class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition"
    >
        {{ __('report.Import') }}
    </button>

    <!-- Download Sample -->
    <a 
        href="{{ url('/storage/sample/customers.csv') }}" 
        download 
        class="px-4 py-2 bg-orange-700 text-white rounded hover:bg-orange-800 transition"
    >
         {{ __('report.Sample CSV') }}
    </a>

    <button wire:click="createCustomer" class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-800 transition">+ {{ __('report.Add Customer') }}</button>


</div>

@if($showimportDiv)
    <div 
    id="importFormContainer"
    class="scale-y-100 max-w-xl mb-8 p-6 bg-white dark:bg-gray-800 rounded-lg shadow transform transition-all duration-300 ease-in-out overflow-hidden scale-y-0 origin-top"
>
    @if (session()->has('message'))
        <div class="mb-4 p-3 text-green-800 bg-green-100 border border-green-300 rounded">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="importCustomers" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">{{ __('report.Import File') }}</label>
            <input 
                type="file" 
                wire:model="file" 
                accept=".csv, .xlsx, .xls"
                class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-blue-200 focus:border-blue-300"
            />
            @error('file')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button 
            type="submit"
            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition disabled:opacity-50"
            @if(!$file) disabled @endif
        >
            {{ __('report.Import') }}
        </button>
    </form>

    <div wire:loading wire:target="file" class="mt-4 text-sm text-gray-600"> {{ __('report.Uploading') }}...</div>
    <div wire:loading wire:target="importCustomers" class="mt-4 text-sm text-gray-600">{{ __('report.Importing') }}...</div>
</div>

 @endif

    <!-- Table -->
    <div class="overflow-auto rounded-lg shadow bg-white dark:bg-gray-800">
        <table class="min-w-full table-auto">
            <thead class="dark:text-white text-sm">
                <tr>
                      <th class="px-4 py-3 border-b border-gray-200">
                        <input type="checkbox" id="selectAll" class="cursor-pointer">
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200">{{ __('report.name') }}</th>
                    <th class="px-4 py-3 border-b border-gray-200">{{ __('report.Phone') }}</th>
                    <th class="px-4 py-3 border-b border-gray-200">{{ __('report.Other Detail') }}</th>
                    <th class="px-4 py-3 border-b border-gray-200">{{ __('report.Queue Total') }}</th>
                    <th class="px-4 py-3 border-b border-gray-200">{{ __('report.Booking Total') }}</th>
                    <th class="px-4 py-3 border-b border-gray-200">{{ __('report.created at') }}</th>
                    <th class="px-4 py-3 border-b border-gray-200">{{ __('report.Actions') }}</th>
                </tr>
            </thead>
            <tbody class="text-sm text-gray-700 dark:text-gray-300">
                @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                         <td class="px-4 py-2 border-b border-gray-200">
                             <input type="checkbox" class="customer-checkbox cursor-pointer" value="{{ $customer->id }}">
                        </td>
                        <td class="px-4 py-2 border-b border-gray-200">{{ $customer->name }}</td>
                        <td class="px-4 py-2 border-b border-gray-200">{{ $customer->phone }}</td>
                        <td class="px-4 py-2 border-b border-gray-200">
                        @php
                                $json = [];

                                if (!empty($customer?->json_data)) {
                                    $decoded = json_decode($customer->json_data, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                        $json = $decoded;
                                    }
                                }
                            @endphp

                            @if (!empty($json))
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($json as $key => $value)
                                        <li><span class="font-medium">{{ ucfirst($key) }}:</span> {{ $value }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-gray-400 italic">No data</span>
                            @endif
                        </td>
                         <td class="px-4 py-2 border-b border-gray-200">{{ $customer->queue_count ?? '' }}</td>
                        <td class="px-4 py-2 border-b border-gray-200">{{ $customer->booking_count ?? '' }}</td>
                        <td class="px-4 py-2 border-b border-gray-200">{{ $customer->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-2 border-b border-gray-200">
                            <div class="flex gap-3">
                            <a 
                                href="{{ route('tenant.customer.logs', $customer->id) }}" 
                                target="_blank" 
                                class="inline-block px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700 transition"
                            >
                                {{ __('report.View Logs') }}
                            </a>

                            {{-- <button wire:click="editCustomer({{ $customer->id }})">Edit</button> --}}
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-6">
                           <p class="text-center"><strong>{{ __('report.No records found.') }}</strong></p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $customers->links() }}
    </div>

    @if($showCustomerModal)
<div class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999 p-4 modal">
    <div class="p-5 modal-close-btn inset-0  m-auto w-full max-w-lg bg-white-400/50 bg-white rounded-xl relative space-y-4">
        <h2 class="text-xl font-bold">{{ $editing ? __('report.Edit') : __('report.Add') }} Customer</h2>

        <div>
            <label>{{ __('report.name') }}</label>
            <input type="text" wire:model.defer="name" class="border rounded w-full">
            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label>{{ __('report.Phone') }}</label>
            <input type="text" wire:model.defer="phone" class="border rounded w-full">
            @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label>{{ __('report.Email') }}</label>
            <input type="email" wire:model.defer="email" class="border rounded w-full">
            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label>{{ __('report.Image') }}</label>
            <input type="file" wire:model="image" class="w-full border p-2 rounded border-gray-300">
            @error('image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end gap-2">
            <button wire:click="$set('showCustomerModal', false)" class="bg-gray-300 px-4 py-2 rounded">{{ __('report.Cancel') }}</button>
            <button wire:click="saveCustomer" class="bg-blue-600 text-white px-4 py-2 rounded">
                {{ __('report.Save') }}
            </button>
        </div> 
    </div>
</div>
@endif


 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener("livewire:init", function() {

    const toggleBtn = document.getElementById('toggleImportBtn');
    const importContainer = document.getElementById('importFormContainer');
  // Safely check if elements exist
    if (!toggleBtn || !importContainer) {
        console.warn('toggleImportBtn or importFormContainer not found in DOM.');
        return;
    }

    let isOpen = false;

    toggleBtn.addEventListener('click', () => {
        isOpen = !isOpen;
        if (isOpen) {
            importContainer.classList.remove('scale-y-0');
            importContainer.classList.add('scale-y-100');
            Livewire.dispatch('open-model');
        } else {
            importContainer.classList.add('scale-y-0');
            importContainer.classList.remove('scale-y-100');
            Livewire.dispatch('close-model');
        }
    });


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

        Livewire.on('confirm-multiple-delete', () => {
            alert('confirmed');
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
                    Livewire.dispatch('confirmed-multiple-delete');
                }
            });
        });

        Livewire.on('deleted', () => {
            Swal.fire({
                title: 'Success!',
                text: 'Deleted successfully.',
                icon: 'success',
                // confirmButtonText: 'OK'
            })

        });
        Livewire.on('updated', () => {
            Swal.fire({
                title: 'Success!',
                text: 'Updated successfully.',
                icon: 'success',
                // confirmButtonText: 'OK'
            })

        });
        Livewire.on('customer-saved', () => {
            Swal.fire({
                title: 'Success!',
                text: 'Created successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    // location.reload(); // Refresh the page when OK is clicked
                    window.location.href = '/staff';
                }
            });

        });
    });

  
    </script>

<script>
    document.getElementById('selectAll').addEventListener('click', function () {
        let checkboxes = document.querySelectorAll('.customer-checkbox');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });

    document.getElementById('bulkDeleteBtn').addEventListener('click', function () {
        let selectedIds = Array.from(document.querySelectorAll('.customer-checkbox:checked')).map(cb => cb.value);
      
        if (selectedIds.length > 0) {

            Livewire.dispatch('bulkDeleteCustomer', { 'ids' : selectedIds });
        } else {
            // alert('No staff selected');

            Swal.fire({
                title: 'Warning!',
                text: 'No Customer selected.',
                icon: 'warning',
                // confirmButtonText: 'OK'
            })
        }
    });
</script>

</div>
