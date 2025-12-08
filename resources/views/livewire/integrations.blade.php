<div class="mx-auto p-4 w-full">

    <!-- Page Header -->
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-xl font-semibold">{{ __('text.Integrations') }}</h1>
    </div>

    <!-- Integrations Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

      <!-- Integration Card -->
      <div class="bg-white shadow rounded-lg p-6 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between mb-4">
          <div class="text-base font-semibold text-gray-500 dark:text-white">{{ __('text.Payments') }}</div>
          <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full">{{ __('text.Connected') }}</span>
        </div>
        <p class="text-sm text-gray-500 mb-4 dark:text-gray-400">
          {{ __('text.Add the payment gateways from payment settings.') }}
        </p>
        <div class="flex justify-end">
          <a href="{{ url('payment-settings') }}" class="text-blue-600 hover:underline text-sm"> {{ __('text.Manage') }}</a>
        </div>
      </div>

      <div class="bg-white shadow rounded-lg p-6 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between mb-4">
          <div class="text-base font-semibold text-gray-500 dark:text-white">{{ __('text.Meta') }}</div>
          <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full">{{ __('text.Connected') }}</span>
        </div>
        <p class="text-sm text-gray-500 mb-4  dark:text-gray-400">
          {{ __('text.Manage Meta Ads UTM Link Generator') }}.
        </p>
        <div class="flex justify-end">
          <a href="{{ url('meta-ads-utm-link-generator') }}" class="text-blue-600 hover:underline text-sm"> {{ __('text.Manage') }}</a>
        </div>
      </div>

      <div class="bg-white shadow rounded-lg p-6 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between mb-4">
          <div class="text-base font-semibold text-gray-500 dark:text-white">{{ __('text.Salesforce') }}</div>
          <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full">{{ __('text.Connected') }}</span>
        </div>
        <p class="text-sm text-gray-500 mb-4  dark:text-gray-400">
          {{ __('text.Manage Salesforce Integration') }}.
        </p>
        <div class="flex justify-end">
          <a href="{{ url('sales-force-setting') }}" class="text-blue-600 hover:underline text-sm"> {{ __('text.Manage') }}</a>
        </div>
      </div>

      <div class="bg-white shadow rounded-lg p-6 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between mb-4">
          <div class="text-base font-semibold text-gray-500 dark:text-white">{{ __('text.slack') }}</div>
          <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full">{{ __('text.Connected') }}</span>
        </div>
        <p class="text-sm text-gray-500 mb-4  dark:text-gray-400">
          {{ __('text.Manage slack Integration') }}.
        </p>
        <div class="flex justify-end">
          <a href="{{ route('tenant.slack-setting') }}" class="text-blue-600 hover:underline text-sm"> {{ __('text.Manage') }}</a>
        </div>
      </div>

      <div class="bg-white shadow rounded-lg p-6 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between mb-4">
          <div class="text-base font-semibold text-gray-500 dark:text-white">{{ __('setting.Twillio Video Setting') }}</div>
          <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full">{{ __('text.Connected') }}</span>
        </div>
        <p class="text-sm text-gray-500 mb-4  dark:text-gray-400">
          {{ __('text.Manage twillio video call Integration') }}.
        </p>
        <div class="flex justify-end">
          <a href="{{ route('tenant.twillio-video-setting') }}" class="text-blue-600 hover:underline text-sm"> {{ __('text.Manage') }}</a>
        </div>
      </div>

    </div>
  </div>
