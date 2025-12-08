<div class="max-w-xl w-full bg-white p-6 rounded shadow">
    @if (session()->has('success'))
        <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-4">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Tenant Name</label>
            <input
                wire:model.defer="name"
                type="text"
                id="name"
                class="mt-1 block w-full border border-gray-300 rounded p-2 focus:ring focus:ring-blue-200"
                placeholder="Enter Tenant Name"
            />
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
        >
            Create Tenant
        </button>
    </form>
</div>
