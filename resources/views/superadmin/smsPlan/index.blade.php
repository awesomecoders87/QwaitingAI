@extends('superadmin.components.layout')

@section('title', 'SMS Plans')
@section('page-title', 'SMS Plans')

@section('content')
<div class="px-6 py-8">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6" style="justify-content: space-between">
        <h2 class="text-3xl font-bold text-gray-900">SMS Plans</h2>

        <a href="{{ route('superadmin.sms-plans.create') }}"
           class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
            + Create SMS Plan
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <!-- Search -->
            <input name="search" value="{{ request('search') }}"
                class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Search by plan name...">

            <!-- Status -->
            <select name="status"
                class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Status (All)</option>
                <option value="1" @selected(request('status') == '1')>Active</option>
                <option value="0" @selected(request('status') == '0')>Inactive</option>
            </select>

            <!-- Popular -->
            <select name="popular"
                class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Popular (All)</option>
                <option value="1" @selected(request('popular') == '1')>Yes</option>
                <option value="0" @selected(request('popular') == '0')>No</option>
            </select>

            <!-- Apply -->
            <button class="bg-black text-white rounded-lg px-4 py-2 hover:bg-gray-900 transition">
                Apply
            </button>
            <!-- <a href="{{ route('superadmin.sms-plans.index') }}" class="bg-gray-200 text-gray-700 rounded-lg px-4 py-2 hover:bg-gray-300 transition flex items-center justify-center">
                Refresh
            </a> -->

        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl shadow overflow-hidden">

        <div class="overflow-x-auto">
            <table class="min-w-full text-left">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">#</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Name</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Credits</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Price</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Currency</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Popular</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                    @forelse($smsPlans ?? [] as $i => $plan)
                    <tr class="hover:bg-gray-50">

                        <td class="px-4 py-3">{{ $i+1 }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $plan->name }}</td>
                        <td class="px-4 py-3">{{ $plan->credit_amount }}</td>
                        <td class="px-4 py-3">{{ $plan->price }}</td>
                        <td class="px-4 py-3">{{ $plan->currency_code }}</td>

                        <td class="px-4 py-3">
                            @if($plan->is_popular)
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full font-semibold">Yes</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-500 rounded-full">No</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            @if($plan->is_active)
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-full font-semibold">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-200 text-gray-600 rounded-full">Inactive</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex gap-2">

                                <a href="{{ route('superadmin.sms-plans.edit', $plan->id) }}"
                                    class="px-3 py-1 bg-yellow-400 text-black rounded-md hover:bg-yellow-500 transition">
                                    Edit
                                </a>

                                <form id="delete-sms-plan-{{ $plan->id }}" action="{{ route('superadmin.sms-plans.destroy', $plan->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            onclick="confirmSmsPlanDelete({{ $plan->id }})"
                                            class="px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                                        Delete
                                    </button>
                                </form>

                            </div>
                        </td>

                    </tr>
                    @empty

                    <tr>
                        <td colspan="8" class="px-4 py-4 text-center text-gray-500">
                            No SMS plans found.
                        </td>
                    </tr>

                    @endforelse

                </tbody>
            </table>
        </div>

    </div>

</div>
@push('scripts')
<script>
    // Make the function globally available
    window.confirmSmsPlanDelete = function(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This SMS plan will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-sms-plan-' + id).submit();
            }
        });
    };
</script>
@endpush
@endsection
