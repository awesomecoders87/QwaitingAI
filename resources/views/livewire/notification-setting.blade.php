<div class="p-4">
    <h2 class="text-xl font-semibold mb-4">{{ __('setting.Notifications Settings') }}</h2>
    <div class="py-0 h-full">
        <div>
            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-3 gap-6">

                <!-- WhatsApp Card -->
                <div class="bg-white rounded-xl shadow-xl p-5 flex flex-col items-center text-center relative hover:bg-gray-200 transition-all duration-300 ease-in-out dark:bg-white/[0.03] dark:border-gray-600 dark:text-white  dark:hover:bg-gray-800">
                    <a href="{{ route('tenant.whatsapp-integration') }}" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700" title="{{ __('setting.Configure WhatsApp notification settings') }}">
                        <i class="fas fa-cogs text-lg"></i>
                    </a>
                    <img src="{{ url('images/whatsapp.png') }}" alt="WhatsApp" class="mb-4">
                    <h2 class="text-md font-semibold text-gray-900 mb-1 dark:text-white">{{ __('setting.WhatsApp Notifications') }}</h2>
                    <p class="text-sm text-gray-500 mb-4">{{ __('setting.Manage WhatsApp alerts for your customers.') }}</p>
                    <a href="{{ route('tenant.whatsapp-templates') }}" class="flex items-center justify-center gap-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs py-1 px-3 rounded-md transition-all">
                        <i class="fas fa-pencil-alt text-xs"></i> {{ __('setting.Template Settings') }}
                    </a>
                </div>

                <!-- Email Card -->
                <div class="bg-white rounded-xl shadow-xl p-5 flex flex-col items-center text-center relative hover:bg-gray-200 transition-all duration-300 ease-in-out dark:bg-white/[0.03] dark:border-gray-600 dark:text-white dark:hover:bg-gray-800">
                    <a href="{{ route('tenant.email-settings') }}" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700" title="{{ __('setting.Configure Email notification settings') }}">
                        <i class="fas fa-cogs text-lg"></i>
                    </a>
                    <img src="{{ url('images/new-post.png') }}" alt="Email" class="mb-4">
                    <h2 class="text-md font-semibold text-gray-900 mb-1 dark:text-white">{{ __('setting.Email Notifications') }}</h2>
                    <p class="text-sm text-gray-500 mb-4">{{ __('setting.Configure email alerts and templates.') }}</p>
                    <a href="{{ route('tenant.notification-templates') }}" class="flex items-center justify-center gap-2 bg-purple-100 hover:bg-purple-200 text-purple-700 text-xs py-1 px-3 rounded-md transition-all">
                        <i class="fas fa-pencil-alt text-xs"></i> {{ __('setting.Template Settings') }}
                    </a>
                </div>

                <!-- SMS Card -->
                <div class="bg-white rounded-xl shadow-xl p-5 flex flex-col items-center text-center relative hover:bg-gray-200 transition-all duration-300 ease-in-out dark:bg-white/[0.03] dark:border-gray-600 dark:text-white dark:hover:bg-gray-800">
                    <a href="{{ route('tenant.sms-api') }}" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700" title="{{ __('setting.Configure SMS notification settings') }}">
                        <i class="fas fa-cogs text-lg"></i>
                    </a>
                    <img src="{{ url('images/sms.png') }}" alt="SMS" class="mb-4">
                    <h2 class="text-md font-semibold text-gray-900 mb-1 dark:text-white">{{ __('setting.SMS Notifications') }}</h2>
                    <p class="text-sm text-gray-500 mb-4">{{ __('setting.Set up SMS templates and integration.') }}</p>
                    <a href="{{ route('tenant.message-templates') }}" class="flex items-center justify-center gap-2 bg-pink-100 hover:bg-pink-200 text-pink-700 text-xs py-1 px-3 rounded-md transition-all">
                        <i class="fas fa-pencil-alt text-xs"></i> {{ __('setting.Template Settings') }}
                    </a>
                </div>

                <!-- SMS Card -->
                <div class="bg-white rounded-xl shadow-xl p-5 flex flex-col items-center text-center relative hover:bg-gray-200 transition-all duration-300 ease-in-out dark:bg-white/[0.03] dark:border-gray-600 dark:text-white dark:hover:bg-gray-800">
                    <a href="{{ route('tenant.slack-setting') }}" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700" title="{{ __('setting.Slack settings') }}">
                        <i class="fas fa-cogs text-lg"></i>
                    </a>
                    <img src="{{ url('images/sms.png') }}" alt="SMS" class="mb-4">
                    <h2 class="text-md font-semibold text-gray-900 mb-1 dark:text-white">{{ __('setting.slack template') }}</h2>
                    <p class="text-sm text-gray-500 mb-4">{{ __('setting.Set up slack templates and integration.') }}</p>
                    <a href="{{ route('tenant.slack-templates') }}" class="flex items-center justify-center gap-2 bg-pink-100 hover:bg-pink-200 text-pink-700 text-xs py-1 px-3 rounded-md transition-all">
                        <i class="fas fa-pencil-alt text-xs"></i> {{ __('setting.Template Settings') }}
                    </a>
                </div>

            </div>
        </div>
        <style>
            h2.text-md.font-semibold.text-gray-800.mb-1 {
                color: #000;
            }
        </style>
    </div>
</div>
