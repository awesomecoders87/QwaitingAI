<?php use Illuminate\Support\Facades\Session; ?>

@php
    $user = auth()->user();
    $isAdmin = $user && $user->hasRole('Admin');
    $accessibleLocations = [];

    if ($isAdmin || !$user) {
        $accessibleLocations = $locations;
    } elseif (!is_null($user->locations)) {
        foreach ($locations as $key => $location) {
            if (in_array($key, $user->locations)) {
                $accessibleLocations[$key] = $location;
            }
        }
    }
@endphp

<div wire:ignore>
    @if(count($accessibleLocations) > 1)
        <select wire:model.live="selectedLocationUser" id="selectLocation"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            <option value="" disabled selected>{{ __('text.select location') }}</option>
            @foreach($accessibleLocations as $key => $location)
                <option value="{{ $key }}">{{ $location }}</option>
            @endforeach
        </select>
    @elseif(count($accessibleLocations) == 1)
        @foreach($accessibleLocations as $location)
            <h2 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"><b>{{ $location }}</b></h2>
        @endforeach
    @endif
</div>


