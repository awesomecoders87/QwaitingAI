
<div>
      <!-- Main Content -->
    <main class="flex-1 p-4">
      <h1 class="text-xl font-semibold mb-6 dark:text-white">{{ __('report.Reports by Service') }}</h1>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Section Template -->
        <div class="bg-white p-6 rounded-md shadow-sm dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
          <h2 class="text-lg font-bold text-gray-800 mb-4 dark:text-white">{{ __('report.Department') }}</h2>
          <div class="flex items-start gap-3 mb-4">
            <i class="fas fa-graduation-cap text-indigo-600 text-lg mt-1"></i>
            <div>
              <div class="font-bold text-sm text-gray-900 cursor-pointer"><a href="{{ route('tenant.monthly-report') }}" class="dark:text-white dark:hover:underline">{{ __('report.Queue Reports') }}</a></div>
              <p class="text-sm text-gray-600 dark:text-gray-200">{{ __('report.Insights into queue status and waiting times across departments.') }}</p>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <i class="fas fa-undo text-indigo-600 text-lg mt-1"></i>
            <div>
              <div class="font-bold text-sm text-gray-900 cursor-pointer"><a href="{{ route('tenant.statistics-report') }}" class="dark:text-white dark:hover:underline">{{ __('report.Statistics Reports') }}</a></div>
              <p class="text-sm text-gray-600 dark:text-gray-200">{{ __('report.Statistical analysis and visualization of operational data.') }}.</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-md shadow-sm dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
          <h2 class="text-lg font-bold text-gray-800 mb-4 dark:text-white">{{ __('report.Staff') }}</h2>
          <div class="flex items-start gap-3 mb-4">
            <i class="fas fa-clipboard-list text-indigo-600 text-lg mt-1"></i>
            <div>
              <div class="font-bold text-sm text-gray-900 cursor-pointer"><a href="{{ route('tenant.activity.logs') }}" class="dark:text-white dark:hover:underline">{{ __('report.Activity Logs') }}</a></div>
              <p class="text-sm text-gray-600 dark:text-gray-200">{{ __('report.Chronological records of system and user activities for auditing.') }}.</p>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <i class="fas fa-plus-circle text-indigo-600 text-lg mt-1"></i>
            <div>
              <div class="font-bold text-sm text-gray-900 cursor-pointer"><a href="{{ route('tenant.payment-report') }}" class="dark:text-white dark:hover:underline">{{ __('report.Revenue Report') }}</a></div>
              <p class="text-sm text-gray-600 dark:text-gray-200">{{ __('report.Financial performance and revenue analytics.') }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-md shadow-sm dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
          <h2 class="text-lg font-bold text-gray-800 mb-4 dark:text-white">{{ __('report.Walk-in') }}</h2>
          <div class="flex items-start gap-3">
            <i class="fas fa-file-invoice text-indigo-600 text-lg mt-1"></i>
            <div>
              <div class="font-bold text-sm text-gray-900 cursor-pointer"><a href="{{ route('tenant.sms-transactions-report') }}" class="dark:text-white dark:hover:underline">{{ __('report.SMS Transactions') }}</a></div>
              <p class="text-sm text-gray-600 dark:text-gray-200">{{ __('report.Details of SMS communications sent and received through the system.') }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-md shadow-sm dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
          <h2 class="text-lg font-bold text-gray-800 mb-4 dark:text-white">{{ __('report.Appointment') }}</h2>
          <div class="flex items-start gap-3">
            <i class="fas fa-calendar-check text-indigo-600 text-lg mt-1"></i>
            <div>
              <div class="font-bold text-sm text-gray-900 cursor-pointer"><a href="{{ route('booking-list') }}" class="dark:text-white dark:hover:underline">{{ __('report.Appointment') }}</a></div>
              <p class="text-sm text-gray-600 dark:text-gray-200">{{ __('report.Overview of appointment bookings, statuses, and cancellations.') }}.</p>
            </div>
          </div>
        </div>

         <div class="bg-white p-6 rounded-md shadow-sm dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
          <div class="flex items-start gap-3">
            <i class="fas fa-file-invoice text-indigo-600 text-lg mt-1"></i>
            <div>
              <div class="font-bold text-sm text-gray-900 cursor-pointer"><a href="{{ route('tenant.email-logs') }}" class="dark:text-white dark:hover:underline">{{ __('report.Email Logs') }}</a></div>
              <p class="text-sm text-gray-600 dark:text-gray-200">{{ __('report.Details of Emails sent through the system.') }}</p>
            </div>
          </div>
        </div>

<!--
        <div class="bg-white p-6 rounded-md shadow-sm">
          <h2 class="text-lg font-bold text-gray-800 mb-4">{{ __('report.Traffic') }}</h2>
          <div class="flex items-start gap-3">
            <i class="fas fa-plus text-indigo-600 text-lg mt-1"></i>
            <div>
              <div class="font-bold text-sm text-gray-900 cursor-pointer"><a href="{{ route('tenant.customer-list') }}">{{ __('report.Visitor Traffic') }}</a></div>
              <p class="text-sm text-gray-600">{{ __('report.Analysis of walk-in and online visitor traffic trends.') }}.</p>
            </div>
          </div>
        </div> -->

        <!-- Custom Reports (Individual Cards Styled) -->
        <div class="bg-white p-6 rounded-md shadow-sm dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
          <div class="flex items-start gap-3">
            <i class="fas fa-user-clock text-indigo-600 text-lg mt-1"></i>
            <div>
              <div class="font-bold text-sm text-gray-900 cursor-pointer"><a href="{{ route('tenant.break-request') }}" class="dark:text-white dark:hover:underline"> {{ __('sidebar.Staff Break Request') }}</a></div>
              <p class="text-sm text-gray-600 dark:text-gray-200">{{ __('report.Track and manage staff break requests and approvals') }}.</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-md shadow-sm dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
          <div class="flex items-start gap-3">
            <i class="fas fa-layer-group text-indigo-600 text-lg mt-1"></i>
            <div>
              <div class="font-bold text-sm text-gray-900 cursor-pointer"><a href="{{ route('tenant.categories-report') }}" class="dark:text-white dark:hover:underline"> {{ __('sidebar.Services Report') }}</a></div>
              <p class="text-sm text-gray-600 dark:text-gray-200">{{ __('report.Detailed analysis of services and performance trends') }}.</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-md shadow-sm dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
          <div class="flex items-start gap-3">
            <i class="fas fa-sitemap text-indigo-600 text-lg mt-1"></i>
            <div>
              <div class="font-bold text-sm text-gray-900 cursor-pointer"><a href="{{ route('tenant.sub-categories-report') }}" class="dark:text-white dark:hover:underline"> {{ __('sidebar.Sub Services Report') }}</a></div>
              <p class="text-sm text-gray-600 dark:text-gray-200">{{ __('report.Breakdown of sub-services usage and effectiveness') }}.</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-md shadow-sm dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
          <div class="flex items-start gap-3">
            <i class="fas fa-calendar-day text-indigo-600 text-lg mt-1"></i>
            <div>
              <div class="font-bold text-sm text-gray-900 cursor-pointer"> <a href="{{ route('tenant.overview-per-day-report') }}" class="dark:text-white dark:hover:underline">{{ __('sidebar.Overview Per Day') }}</a></div>
              <p class="text-sm text-gray-600 dark:text-gray-200">{{ __('report.Daily summaries of queue and service activity') }}.</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-md shadow-sm dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
          <div class="flex items-start gap-3">
            <i class="fas fa-chart-line text-indigo-600 text-lg mt-1"></i>
            <div>
              <div class="font-bold text-sm text-gray-900 cursor-pointer"><a href="{{ route('tenant.staff-performance-reports') }}" class="dark:text-white dark:hover:underline">{{ __('sidebar.Staff Performance Report') }}</a></div>
              <p class="text-sm text-gray-600 dark:text-gray-200">{{ __('report.Monitor individual staff productivity and performance metrics') }}.</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-md shadow-sm dark:shadow dark:bg-white/[0.03] dark:border-gray-600">
          <div class="flex items-start gap-3">
            <i class="fas fa-clock text-indigo-600 text-lg mt-1"></i>
            <div>
              <div class="font-bold text-sm text-gray-900 cursor-pointer"> <a href="{{ route('tenant.overview-per-time-period-reports') }}" class="dark:text-white dark:hover:underline">{{ __('sidebar.Overview Per Time Period Reports') }}</a></div>
              <p class="text-sm text-gray-600 dark:text-gray-200">{{ __('report.View queue and service analytics over custom date ranges') }}.</p>
            </div>
          </div>
        </div>
        {{-- <div class="bg-white p-6 rounded-md shadow-sm dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
          <div class="flex items-start gap-3">
            <i class="fas fa-clock text-indigo-600 text-lg mt-1"></i>
            <div>
              <div class="font-bold text-sm text-gray-900 cursor-pointer"> <a href="{{ route('tenant.analytics') }}" class="dark:text-white dark:hover:underline">{{ __('sidebar.analytics') }}</a></div>
              <p class="text-sm text-gray-600 dark:text-gray-200">{{ __('report.Comprehensive analytics of walk-in services and user activity') }}.</p>
            </div>
          </div>
        </div> --}}
         <div class="bg-white p-6 rounded-md shadow-sm dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
          <div class="flex items-start gap-3">
            <i class="fas fa-clock text-indigo-600 text-lg mt-1"></i>
            <div>
              <div class="font-bold text-sm text-gray-900 cursor-pointer"> <a href="{{ route('tenant.feedback-reports') }}" class="dark:text-white dark:hover:underline">{{ __('sidebar.Feedback Report') }}</a></div>
              <p class="text-sm text-gray-600 dark:text-gray-200">{{ __('report.View feedback analytics over custom date ranges') }}.</p>
            </div>
          </div>
        </div> 
         <div class="bg-white p-6 rounded-md shadow-sm dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
          <div class="flex items-start gap-3">
            <i class="fas fa-clock text-indigo-600 text-lg mt-1"></i>
            <div>
              <div class="font-bold text-sm text-gray-900 cursor-pointer"> <a href="{{ route('tenant.staff.rating.summary') }}" class="dark:text-white dark:hover:underline">{{ __('sidebar.Staff Rating Summary') }}</a></div>
              <p class="text-sm text-gray-600 dark:text-gray-200">{{ __('report.View staff rating analytics over custom date ranges') }}.</p>
            </div>
          </div>
        </div> 
        <div class="bg-white p-6 rounded-md shadow-sm dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
          <div class="flex items-start gap-3">
            <i class="fas fa-clock text-indigo-600 text-lg mt-1"></i>
            <div>
              <div class="font-bold text-sm text-gray-900 cursor-pointer"> <a href="{{ route('tenant.dynamic-report-list') }}" class="dark:text-white dark:hover:underline">{{ __('text.Dynamic Reports') }}</a></div>
              <p class="text-sm text-gray-600 dark:text-gray-200">{{ __('text.Create your own custom reports easily') }}.</p>
            </div>
          </div>
        </div>
      </div>
    </main>
</div>
