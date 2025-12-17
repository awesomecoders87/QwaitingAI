@extends('superadmin.components.layout')

@section('title', 'Create Package')
@section('page-title', 'Create Package')

@section('content')
<div class="w-full flex flex-col px-4 py-10">
    <div class="mb-6">
        <a href="{{ route('superadmin.packages.index') }}" class="inline-flex items-center text-gray-700 hover:text-blue-600 font-semibold">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Back
        </a>
    </div>
    <div class="w-full max-w-5xl bg-white shadow-lg rounded-2xl p-10 border border-gray-100">
        <form action="{{ route('superadmin.packages.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Package Name</label>
                    <input type="text" name="name" class="w-full border-gray-300 rounded-lg px-4 py-3 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter package name" required>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Monthly Price</label>
                    <input type="number" step="0.01" name="price" class="w-full border-gray-300 rounded-lg px-4 py-3 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Monthly price" min="0" required>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Yearly Price</label>
                    <input type="number" step="0.01" name="price_yearly" class="w-full border-gray-300 rounded-lg px-4 py-3 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Yearly price" min="0">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Type</label>
                    <select name="type" class="w-full border-gray-300 rounded-lg px-4 py-3 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        <option value="QUEUE">QUEUE</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Status</label>
                    <select name="status" class="w-full border-gray-300 rounded-lg px-4 py-3 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Currency</label>
                    <select name="currency" class="w-full border-gray-300 rounded-lg px-4 py-3 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        <option value="">Select Currency</option>
                        @foreach ($currencies as $currency)
                            <option value="{{ trim((string) $currency->currency_code) }}">{{ $currency->name }} ({{ $currency->currency_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Pricing Page</label>
                    <select name="show_page" class="w-full border-gray-300 rounded-lg px-4 py-3 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="Pricing Page">Pricing Page</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Monthly Price INR</label>
                    <input type="number" step="0.01" name="price_monthly_inr" class="w-full border-gray-300 rounded-lg px-4 py-3 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Monthly price INR" min="0">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Yearly Price INR</label>
                    <input type="number" step="0.01" name="price_yearly_inr" class="w-full border-gray-300 rounded-lg px-4 py-3 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Yearly price INR" min="0">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Sorting Order</label>
                    <input type="number" name="sorting" class="w-full border-gray-300 rounded-lg px-4 py-3 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Sorting order">
                </div>
            </div>
            <div class="mt-6">
                <h3 class="font-semibold mb-2">Select Features</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($featureList as $feature)
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" name="selectedFeatures[{{ $feature->id }}][enabled]" class="border rounded">
                            <label class="w-40">{{ $feature->feature }}</label>
                            <input type="text" name="selectedFeatures[{{ $feature->id }}][value]" placeholder="Feature value" class="border p-2 rounded flex-1">
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-4">
                <a href="{{ route('superadmin.packages.index') }}" class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg shadow hover:bg-gray-300 transition">Cancel</a>
                <button type="submit" class="px-7 py-2.5 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 transition">Create Package</button>
            </div>
        </form>
    </div>
</div>
@endsection
