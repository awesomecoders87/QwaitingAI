<div class="max-w-7xl mx-auto p-6 space-y-8">

  <h1 class="text-3xl font-bold text-red-600">Manage System Error Logs</h1>

  @if (session()->has('success'))
    <div class="px-4 py-2 bg-green-100 text-green-700 rounded">
      {{ session('success') }}
    </div>
  @endif

  {{-- Add New Error Form --}}
  <div class="bg-white p-6 rounded-xl shadow space-y-4">
    <h2 class="text-xl font-semibold">Add New Error</h2>

    <form wire:submit.prevent="save" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium">Error Code</label>
        <input type="text" wire:model.defer="code"
          class="w-full border rounded p-2" />
        @error('code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Message</label>
        <input type="text" wire:model.defer="message"
          class="w-full border rounded p-2" />
        @error('message') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-medium">Description</label>
        <textarea wire:model.defer="description" rows="3"
          class="w-full border rounded p-2"></textarea>
        @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-medium">Resolution</label>
        <textarea wire:model.defer="resolution" rows="3"
          class="w-full border rounded p-2"></textarea>
        @error('resolution') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-medium">Resolution</label>
         <select wire:model.defer="type">
            <option value="">Select Error type</option>
            <option value="system">System Error Type</option>
            <option value="booking">Booking Error type</option>
         </select>
        @error('resolution') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
      </div>

      <div class="md:col-span-2">
        <button type="submit"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
          Save Error
        </button>
      </div>
    </form>
  </div>

  {{-- Error List Table --}}
  <div class="overflow-x-auto bg-white rounded-xl shadow">
    <table class="min-w-full text-sm divide-y divide-gray-200">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2 text-left">Code</th>
          <th class="px-4 py-2 text-left">Error Type</th>
          <th class="px-4 py-2 text-left">Message</th>
          <th class="px-4 py-2 text-left">Description</th>
          <th class="px-4 py-2 text-left">Resolution</th>
           <th class="px-4 py-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        @forelse($items as $item)
          <tr>
              <td class="px-4 py-2">{{ $item->code }}</td>
              <td class="px-4 py-2">{{ $item->type }}</td>
            <td class="px-4 py-2">{{ $item->message }}</td>
            <td class="px-4 py-2">{{ $item->description }}</td>
            <td class="px-4 py-2">{{ $item->resolution }}</td>
            <td class="px-4 py-2 space-x-2">
            <!-- Edit Button -->
            <button 
              wire:click="edit({{ $item->id }})"
              class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600"
            >Edit</button>

            <!-- Delete Button -->
            <button 
              wire:click="confirmDelete({{ $item->id }})"
              class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600"
            >Delete</button>
          </td>
          </tr>
        @empty
           <tr>
                        <td colspan="7" class="text-center py-6">
                            <img src="{{ url('images/no-record.jpg') }}" alt="No Records Found"
                                class="mx-auto" style="max-width:300px">
                            <p class="text-gray-500 dark:text-gray-400 mt-2">No records found.</p>
                        </td>
                    </tr>
        @endforelse
      </tbody>
    </table>
    <div class="p-4">
      {{ $items->links() }}
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
                 confirmButtonColor: '#d33',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                   window.reload(true);
                }
            });

        });
    });
    </script>

</div>
