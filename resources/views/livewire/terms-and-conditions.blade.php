@php
use App\Models\AccountSetting;
@endphp
<div class="p-4">
  <h2 class="text-xl font-semibold mb-4">{{ __('setting.Terms & Conditions') }}</h2>   
  <div class="p-6 bg-white shadow-md rounded-lg">
    <div class="flex justify-between items-center mb-4">
      @if (!$isExist)
        <a href="{{ route('tenant.create-terms-conditions') }}" class="px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
          + {{ __('setting.Add New') }}
        </a>
      @endif
    </div>

    <table class="w-full border-collapse table-auto">
      <thead>
        <tr>
          <th class="p-2">{{ __('setting.Title') }}</th>
          {{-- <th class="p-2">Team</th> --}}
          <th class="p-2">{{ __('setting.Created At') }}</th>
          <th class="p-2">{{ __('setting.Actions') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($termsConditions as $term)
        <tr>
          <td class="p-2">{{ $term->title }}</td>
          {{-- <td class="p-2">{{ tenant('name') }}</td> --}}
          <td class="p-2">
            {{ \Carbon\Carbon::parse($term->created_at)->format(AccountSetting::showDateTimeFormat($team_id, $selectedLocation)) }}
          </td>
          <td class="p-2">
            <a href="{{ route('tenant.edit-terms-conditions', $term->id) }}" class="text-blue-500">
              {{ __('setting.Edit') }}
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <div class="mt-4">
      {{ $termsConditions->links() }}
    </div>
  </div>
</div>
