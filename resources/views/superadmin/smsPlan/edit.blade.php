@extends('superadmin.components.layout')

@section('title', 'Edit SMS Plan')
@section('page-title', 'Edit SMS Plan')

@section('content')
<div class="mt-5">
    <a href="{{ route('superadmin.packages.index') }}" class="inline-flex items-center text-gray-700 hover:text-blue-600 font-semibold">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Back
    </a>
</div>
<div class="w-full flex justify-center px-4 py-10">
    <div class="w-full max-w-5xl bg-white shadow-lg rounded-2xl p-10 border border-gray-100">

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Edit SMS Plan</h2>
            <p class="text-gray-500 text-sm mt-1">Update the details below to modify the SMS credit plan.</p>
        </div>

        <form action="{{ route('superadmin.sms-plans.update', $plan->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Row 1: Name + Credits --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Plan Name</label>
                    <input type="text" name="name"
                        value="{{ old('name', $plan->name) }}"
                        class="w-full border-gray-300 rounded-lg px-4 py-3 shadow-sm 
                        focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Enter plan name" required>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Credit Balance</label>
                    <input type="number" name="credit_amount"
                        value="{{ old('credit_amount', $plan->credit_amount) }}"
                        class="w-full border-gray-300 rounded-lg px-4 py-3 shadow-sm 
                        focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Credit Balance" min="1" required>
                </div>
            </div>

            {{-- Row 2: Price + Currency --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Price</label>
                    <input type="number" step="0.01" name="price"
                        value="{{ old('price', $plan->price) }}"
                        class="w-full border-gray-300 rounded-lg px-4 py-3 shadow-sm 
                        focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Plan price" min="0" required>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Currency</label>
                    <select name="currency_code"
                        class="w-full border-gray-300 rounded-lg px-4 py-3 shadow-sm bg-white 
                        focus:ring-indigo-500 focus:border-indigo-500"
                        required>
                        <option value="">Choose currency</option>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->currency_code }}"
                                {{ old('currency_code', $plan->currency_code) == $currency->currency_code ? 'selected' : '' }}>
                                {{ $currency->currency_name }} ({{ $currency->currency_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Description --}}
            <div class="mb-8">
                <label class="block text-gray-700 font-semibold mb-2">Description</label>
                <textarea name="description"
                    class="w-full border-gray-300 rounded-lg px-4 py-3 shadow-sm 
                    focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Optional short description"
                    rows="2"
                    maxlength="255">{{ old('description', $plan->description) }}</textarea>
            </div>

            {{-- Checkboxes --}}
            <div class="flex items-center gap-8 mb-10">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_popular" value="1"
                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded"
                        {{ old('is_popular', $plan->is_popular) ? 'checked' : '' }}>
                    <span class="text-gray-700">Popular</span>
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1"
                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded"
                        {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                    <span class="text-gray-700">Active</span>
                </label>
            </div>

            {{-- Buttons --}}
            <div class="flex justify-end gap-4">
                <a href="{{ route('superadmin.sms-plans.index') }}"
                    class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg shadow 
                    hover:bg-gray-300 transition">
                    Cancel
                </a>

                <button type="submit"
                    class="px-7 py-2.5 bg-indigo-600 text-white rounded-lg shadow 
                    hover:bg-indigo-700 transition">
                    Update Plan
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
