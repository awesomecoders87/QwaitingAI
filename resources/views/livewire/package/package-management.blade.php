<div class="p-6 space-y-4">
    <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">
        <div class="grid grid-cols-2 gap-4">
            <input wire:model="name" type="text" placeholder="Package Name" class="border p-2 rounded">
            <input wire:model="price" type="number" step="0.01" placeholder="Monthly Price" class="border p-2 rounded">
            <input wire:model="price_yearly" type="number" step="0.01" placeholder="Yearly Price" class="border p-2 rounded">
           
            <select wire:model="type" class="border p-2 rounded">
                <option value="QUEUE">QUEUE</option>

            </select>
            <select wire:model="status" class="border p-2 rounded">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
           
           <select wire:model="currency" class="border p-2 rounded">
            <option value="">Select Currency</option>
            @foreach ($currencyList as $currency)
                <option value="{{ trim((string) $currency->currency_code) }}">
                    {{ $currency->name }} ({{ $currency->currency_code }})
                </option>
            @endforeach
            </select>
             <select wire:model="show_page" class="border p-2 rounded">
                <option value="Pricing Page">Pricing Page</option>
            </select>
            <input wire:model="price_monthly_inr" type="number" step="0.01" placeholder="Monthly Price INR" class="border p-2 rounded">
            <input wire:model="price_yearly_inr" type="number" step="0.01" placeholder="Yearly Price INR" class="border p-2 rounded">
            <input wire:model="sorting" type="number" placeholder="Sorting Order" class="border p-2 rounded">
        </div>
     <div class="mt-6">
    <h3 class="font-semibold mb-2">Select Features</h3>
    <div class="grid grid-cols-2 gap-4">
        @foreach ($featureList as $feature)
            <div class="flex items-center space-x-2">
                <!-- Checkbox -->
                <input 
                    type="checkbox"
                    wire:model="selectedFeatures.{{ $feature->id }}.enabled"
                    class="border rounded"
                >

                <label class="w-40">{{ $feature->feature }}</label>

                <!-- Text input -->
               
                    <input
                        type="text"
                        wire:model="selectedFeatures.{{ $feature->id }}.value"
                        placeholder="Feature value"
                        class="border p-2 rounded flex-1"
                    >
            
            </div>
        @endforeach
    </div>
</div>

        <div class="mt-4">
            <button class="px-4 py-2 bg-blue-600 text-white rounded">
                {{ $isEditMode ? 'Update Package' : 'Create Package' }}
            </button>
            @if($isEditMode)
                <button type="button" wire:click="resetForm" class="px-4 py-2 bg-gray-400 text-white rounded ml-2">Cancel</button>
            @endif
        </div>
    </form>

    <hr class="my-4">

    <table class="w-full table-auto border">
        <thead>
            <tr class="bg-gray-200">
                <th class="p-2 border">#</th>
                <th class="p-2 border">Name</th>
                <th class="p-2 border">Price</th>
                <th class="p-2 border">Yearly</th>
                <th class="p-2 border">Status</th>
                <th class="p-2 border">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($packages as $package)
                <tr>
                    <td class="p-2 border">{{ $package->id }}</td>
                    <td class="p-2 border">{{ $package->name }}</td>
                    <td class="p-2 border">{{ $package->price }}</td>
                    <td class="p-2 border">{{ $package->price_yearly }}</td>
                    <td class="p-2 border">{{ $package->status }}</td>
                    <td class="p-2 border space-x-2">
                        <button wire:click="edit({{ $package->id }})" class="px-2 py-1 bg-yellow-400 text-white rounded">Edit</button>
                        <button wire:click="delete({{ $package->id }})" class="px-2 py-1 bg-red-600 text-white rounded">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $packages->links() }}
    </div>
</div>
