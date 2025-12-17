@extends('superadmin.components.layout')

@section('title', 'Packages')
@section('page-title', 'Packages')

@section('content')
<div class="px-6 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6" style="justify-content: space-between">
        <h2 class="text-3xl font-bold text-gray-900">Create Packages</h2>
        <a href="{{ route('superadmin.packages.create') }}" class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
            + Create Package
        </a>
    </div>

    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input name="name" value="{{ request('name') }}" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Search by name...">
            <input name="price" value="{{ request('price') }}" type="number" step="0.01" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Price">
            <select name="status" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Status (All)</option>
                <option value="Active" @selected(request('status') == 'Active')>Active</option>
                <option value="Inactive" @selected(request('status') == 'Inactive')>Inactive</option>
            </select>
            <button class="bg-black text-white rounded-lg px-4 py-2 hover:bg-gray-900 transition">Apply</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">#</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Name</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Price</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Yearly</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($packages as $i => $package)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $package->id }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $package->name }}</td>
                            <td class="px-4 py-3">{{ $package->price }}</td>
                            <td class="px-4 py-3">{{ $package->price_yearly }}</td>
                            <td class="px-4 py-3">
                                @if($package->status === 'Active')
                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-full font-semibold">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-gray-200 text-gray-600 rounded-full">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('superadmin.packages.edit', $package->id) }}" class="px-3 py-1 bg-yellow-400 text-black rounded-md hover:bg-yellow-500 transition">Edit</a>
                                    <form id="delete-form-{{ $package->id }}" action="{{ route('superadmin.packages.destroy', $package->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmDelete({{ $package->id }})" class="px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 transition">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-center text-gray-500">No packages found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">
        {{ $packages->links() }}
    </div>
</div>
@push('scripts')
<script>
    // Make the function globally available
    window.confirmDelete = function(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    };
</script>
@endpush
@endsection
