<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\Office365Controller;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\DisplayScreenController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Livewire\TicketScreenSettings;
use App\Livewire\ColorSettings;
use App\Http\Middleware\TenantAuthMiddleware;
use App\Http\Middleware\TenantGuestMiddleware;
use App\Livewire\AddCounter;
use App\Livewire\AddLocation;
use App\Livewire\BookingSettingsNewComponent;
use App\Livewire\CallScreenSettings;
use App\Livewire\CategoriesReport;
use App\Livewire\ConvertBookToQueue;
use App\Livewire\CounterComponent;
use App\Livewire\Dashboard;
use App\Livewire\DisplaySetting;
use App\Livewire\EditCounter;
use App\Livewire\Main;
use App\Livewire\MainBooking;
use App\Livewire\MessageTemplate;
use App\Livewire\WhatsappTemplate;
use App\Livewire\PusherSettings;
use App\Livewire\Queue;
use App\Livewire\QueueStaff;
use App\Livewire\QueueCalls;
use App\Livewire\QueueDisplay;
use App\Livewire\SubCategoriesReport;
use App\Livewire\MonthlyReport;
use App\Livewire\TicketGeneratorComponent;
use App\Livewire\ViewWaitlist;
use App\Livewire\TicketView;
use App\Livewire\RatingSurvey;
use App\Livewire\RatingThankYou;
use App\Livewire\EditStaffComponent;
use App\Livewire\NotificationTemplates;
use App\Livewire\StaffSettingComponent;
use App\Livewire\CategoryManagement;
use App\Livewire\CategoryCreateComponent;
use App\Livewire\CategorySettingComponent;
use App\Livewire\EmailSettings;
use App\Livewire\FeedbackForm;
use App\Livewire\FeedbackSettings;
use App\Livewire\FormFieldCreate;
use App\Livewire\FormFieldEdit;
use App\Livewire\FormFieldManager;
use App\Livewire\LogoUpdate;
use App\Livewire\BookingConfirmed;
use App\Livewire\BookingCancelled;
use App\Livewire\EditBooking;
use App\Livewire\BookingRescheduled;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Livewire\StaffListComponent;
use App\Livewire\StaffManagementComponent;
use App\Livewire\ProfileForm;
use App\Livewire\QrCode;
use App\Livewire\SmsApi;
use App\Livewire\WhatsappIntegration;
use App\Livewire\EditLocation;
use App\Livewire\Locations;
use App\Livewire\LocationMap;
use App\Livewire\OverviewPerDay;
use App\Livewire\RolesManager;
use App\Livewire\CreateRole;
use App\Livewire\CreateScreenTemplate;
use App\Livewire\CreateTermsAndConditions;
use App\Livewire\EditRole;
use App\Livewire\EditScreenTemplate;
use App\Livewire\StaffPerformanceReports;
use App\Livewire\OverviewPerTimePeriodReports;
use App\Livewire\FeedbackReports;
use App\Livewire\ScreenTemplates;
use App\Livewire\TermsAndConditions;
use App\Livewire\EditTermsAndConditions;
use App\Livewire\EditScreenTemplateSettings;
use App\Livewire\StaticsReport;
use App\Livewire\FeedbackStaticsReport;
use App\Livewire\ActivityLogReport;
use App\Livewire\SmsTransactionsReport;
use App\Livewire\ChangePassword;
use App\Livewire\MobileQueue;
use App\Livewire\TicketVisit;
use App\Livewire\OpeningHoursSetting;
use App\Livewire\DisplayScreenComponent;
use App\Livewire\DisplayScreen;
use App\Livewire\MainBookingAppointment;
use App\Livewire\BookingList;
use App\Livewire\PreferBookingList;
use App\Livewire\AppointmentCalendar;
use App\Livewire\CategoryLevel;
use App\Livewire\Branches\QueueOverview;
use App\Livewire\Branches\OverallOverview;
use App\Livewire\Branches\MonthlyReport as BranchesMonthlyReport;
use App\Livewire\MobileAppSetting;
use App\Livewire\NotificationSetting;
use App\Livewire\PaymentSettings;
use App\Livewire\PaymentReport;
use App\Livewire\Integrations;
use App\Livewire\CustomerList;
use App\Livewire\TicketPrint;
use App\Livewire\CustomerActivityLogs;
use App\Livewire\AllReports;
use App\Livewire\ErrorLogs;
use App\Livewire\ErrorLogsList;
use App\Livewire\BreakReason;
use App\Livewire\BreakReasonForm;
use App\Livewire\BreakRequest;
use App\Livewire\Addons;
use App\Livewire\LanguageSettings;
use App\Livewire\HoldBookingComponent;
use App\Livewire\CheckinComponent;
use App\Livewire\DashboardSummary;
// use App\Livewire\DummyTest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use App\Livewire\Package\PackageManagement;
use App\Livewire\Package\BuySubcription;
use App\Livewire\Branches\DashboardBranchSummary;
use App\Livewire\Analytics;
use App\Livewire\MetaAdsUtmLinkGenerator;
use App\Livewire\RatingAlreadySubmitted;
use App\Livewire\AddTenant;
use App\Livewire\SetupProgress;
use App\Livewire\Auth\OtpVerification;
use App\Livewire\VirtualMeeting;
use App\Livewire\SalesForceSetting;
use App\Http\Controllers\SalesforceController;
use App\Livewire\DynamicReport;
use App\Livewire\DynamicReportsList;
use App\Livewire\CreateDynamicReports;
use App\Livewire\EditDynamicReports;
use App\Livewire\EmailLogs;
use Illuminate\Support\Facades\Http;
use App\Livewire\CategoryGrouping;
use App\Livewire\TwillioVideoSetting;
use App\Livewire\AutomationComponent;
use App\Livewire\LocationGrouping;
use App\Livewire\SlackSetting;
use App\Livewire\SlackTemplates;
use App\Livewire\CountryManager;
use App\Livewire\LocationSelectorPage;
use App\Livewire\ServiceDisplay;
use App\Livewire\SingleDisplayScreen;
use App\Livewire\DisplayScreenTest;
use App\Livewire\DisplayScreenFirst;
use App\Livewire\DisplayScreenSecond;
use App\Livewire\QRCodeScanner;
use App\Livewire\PublicAIAgentCall;
use App\Livewire\PublicVirtualQueueTypeSelection;
use App\Livewire\PublicLocationSelection;
use App\Livewire\BookingChatbot;

use App\Livewire\TicketGenerationSelection;
use App\Livewire\VirtualQueueTypeSelection;
use App\Livewire\AIAgentCall;
use App\Livewire\HumanAgentWaiting;
use App\Livewire\VirtualQueueSettings;
use App\Http\Controllers\ImportQueueController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Livewire\StaffRatingSummary;
use App\Livewire\LocationMapSettings;
//use App\Livewire\LocationMap;
use App\Livewire\QueueResetSetting;


/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',

    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {

        if (Auth::check()) {
            return to_route('tenant.dashboard');
        } else {
            return to_route('tenant.login');
        }
    });

    Route::get('/autologin/{id}', [AuthController::class, 'webAutoLogin'])->name('autologin');
    Route::get('/403-page', function () {

        return view('errors.403');
    });

    Route::get('/no-service', function () {

        return view('errors.no-available');
    });

    Route::get('send-sms', [TestController::class, 'testSendSMS']);
    Route::get('test-sms', [TestController::class, 'sendUnifonicSMS']);
    Route::get('test-sms-get', [TestController::class, 'sendUnifonicgetSMS']);

    Route::middleware([TenantGuestMiddleware::class])->name('tenant.')->group(function () {
        Route::get('login', [AuthController::class, 'login'])->name('login');
        Route::post('login', [AuthController::class, 'loginstore'])->name('loginstore');

        Route::get('office365/login', [Office365Controller::class, 'redirect']);
        Route::get('office365/callback', [Office365Controller::class, 'callback']);

        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [PasswordResetLinkController::class, 'tenantstore'])->name('password.email');
        Route::get('/password/reset/{token}', [PasswordResetLinkController::class, 'showResetForm'])->name('password.reset');
        Route::post('/password/update', [PasswordResetLinkController::class, 'updatePassword'])->name('password.update');
        // Route::get('register',[AuthController::class, 'register'])->name('register');
        // Route::post('register',[AuthController::class, 'registerstore'])->name('registerstore');
        Route::get('/authenticate', [AuthController::class, 'authenticate'])->name('tenant.authenticate');
        Route::get('send-reminder-email/{id}', [TestController::class, 'sendReminderEmail']);
     
    });
    Route::middleware([TenantAuthMiddleware::class])->name('tenant.')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/locations', Locations::class)->name('locations');
        Route::get('/add-location', AddLocation::class)->name('add-location');
        Route::get('/locations/{location}/edit', EditLocation::class)->name('edit-location');
        Route::get('/location-map', LocationMap::class)->name('location-map');
    });

    Route::middleware([TenantAuthMiddleware::class, 'location.exists'])->name('tenant.')->group(function () {
        Route::get('dashboard', Dashboard::class)->name('dashboard');
        Route::get('change-password', ChangePassword::class)->name('change-password');

        // reports routes
        Route::get('monthly-report', MonthlyReport::class)->name('monthly-report');
        Route::get('categories-report', CategoriesReport::class)->name('categories-report');
        Route::get('sub-categories-report', SubCategoriesReport::class)->name('sub-categories-report');
        Route::get('overview-per-day-report', OverviewPerDay::class)->name('overview-per-day-report');
        Route::get('statistics-report', StaticsReport::class)->name('statistics-report');
        Route::get('feedback-statistics-report', FeedbackStaticsReport::class)->name('feedback-statistics-report');
        Route::get('/activity-logs', ActivityLogReport::class)->name('activity.logs');
        Route::get('/sms-transactions-report', SmsTransactionsReport::class)->name('sms-transactions-report');
        Route::get('/revenue-report', PaymentReport::class)->name('payment-report');
        Route::get('/reports', AllReports::class)->name('all-report');

        //customers routes
        Route::get('/customers', CustomerList::class)->name('customer-list');
        Route::get('/customers/{customerId}/logs', CustomerActivityLogs::class)->name('customer.logs');

        // Branches reports routes
        Route::get('queue-overview', QueueOverview::class)->name('queue-overview-report');
        Route::get('branches/overall-overview-report', OverallOverview::class)->name('overall-overview-report');
        Route::get('branches/monthly-report', BranchesMonthlyReport::class)->name('branches-monthly-report');

        // settings routes
        Route::get('booking-settings', BookingSettingsNewComponent::class)->name('booking-settings');
        // Route::get('message-template', MessageTemplate::class)->name('message-template');
        Route::get('display-tune-settings', DisplaySetting::class)->name('display-tune-settings');
        Route::get('ticket-generate-settings', TicketGeneratorComponent::class)->name('ticket-generate-settings');
        Route::get('calls', QueueCalls::class)->name('calls');
        Route::get('counters', CounterComponent::class)->name('counters');
        Route::get('add-counter', AddCounter::class)->name('add-counter');
        Route::get('edit-counter/{counterId}', EditCounter::class)->name('edit-counter');
        Route::get('call-screen-settings', CallScreenSettings::class)->name('call-screen-settings');
        Route::get('pusher-settings', PusherSettings::class)->name('pusher-settings');

        Route::get('/staff', StaffListComponent::class)->name('staff.list');
        Route::get('/staff/create', StaffManagementComponent::class)->name('staff.create');
        Route::get('/staff/view/{id}', StaffManagementComponent::class)->name('staff.view');
        Route::get('/staff/edit/{staffId}', EditStaffComponent::class)->name('staff.edit');
        Route::get('/staff/{staffId}/setting', StaffSettingComponent::class)->name('staff.setting');
        Route::get('/profile', ProfileForm::class)->name('profile');
        Route::get('/color-settings', ColorSettings::class)->name('color-settings');
        Route::get('/message-templates', MessageTemplate::class)->name('message-templates');
        Route::get('/whatsapp-templates', WhatsappTemplate::class)->name('whatsapp-templates');
        Route::get('/notification-templates', NotificationTemplates::class)->name('notification-templates');
        Route::get('/category-management', CategoryManagement::class)->name('category-management');
        Route::get('/category/{level}/setting', CategorySettingComponent::class)->name('category.setting');
        Route::get('/category/{level}/create', CategoryCreateComponent::class)->name('category.create');
        Route::get('/category/{level}/edit/{categoryId}', CategoryCreateComponent::class)->name('category.edit');
        Route::get('/category/{level}/setting/{categoryId}', CategorySettingComponent::class)->name('category.setting');
        Route::get('/qr-code', QrCode::class)->name('qr-code');
        Route::get('/email-settings', EmailSettings::class)->name('email-settings');
        Route::get('/sms-api', SmsApi::class)->name('sms-api');
        Route::get('/whatsapp-integration', WhatsappIntegration::class)->name('whatsapp-integration');
        Route::get('/ticket-screen-settings', TicketScreenSettings::class)->name('ticket-screen-settings');
        Route::get('/feedback-form', FeedbackForm::class)->name('feedback-form');
        Route::get('/feedback-settings', FeedbackSettings::class)->name('feedback-settings');
        Route::get('/logo-update', LogoUpdate::class)->name('logo-update');
        Route::get('/form-fields', FormFieldManager::class)->name('form-fields');
        Route::get('/create-form-field', FormFieldCreate::class)->name('create-form-field');
        Route::get('/edit-form-field/{id}', FormFieldEdit::class)->name('edit-form-field');

        Route::get('/roles', RolesManager::class)->name('roles');
        Route::get('/create-roles', CreateRole::class)->name('create-role');
        Route::get('/edit-roles/{id}', EditRole::class)->name('edit-role');
        Route::get('/staff-performance-reports', StaffPerformanceReports::class)->name('staff-performance-reports');
        Route::get('/overview-per-time-period-reports', OverviewPerTimePeriodReports::class)->name('overview-per-time-period-reports');
        Route::get('/feedback-reports', FeedbackReports::class)->name('feedback-reports');
        Route::get('/staff-rating-summary', StaffRatingSummary::class)->name('staff.rating.summary');
        Route::get('/screen-templates', ScreenTemplates::class)->name('screen-templates');
        Route::get('/create-screen-template', CreateScreenTemplate::class)->name('create-screen-template');
        Route::get('/edit-screen-template/{id}', EditScreenTemplate::class)->name('edit-screen-template');
        Route::get('/terms-conditions', TermsAndConditions::class)->name('terms-conditions');
        Route::get('/create-terms-conditions', CreateTermsAndConditions::class)->name('create-terms-conditions');
        Route::get('/edit-terms-conditions/{id}', EditTermsAndConditions::class)->name('edit-terms-conditions');
        Route::get('/screen-templates/{record}/edit', EditScreenTemplateSettings::class)->name('edit-screen-templates-settings');
        Route::get('/locations/{locationId}/setting-location', OpeningHoursSetting::class)->name('location.setting');
        Route::get('screens', DisplayScreenComponent::class)->name('screens');
        Route::get('/mobile-app-setting', MobileAppSetting::class)->name('mobile.app.setting');
        Route::get('/payment-settings', PaymentSettings::class)->name('payment-settings');
        Route::get('/notification-settings', NotificationSetting::class)->name('notification-settings');
        Route::get('/integrations', Integrations::class)->name('integrations');

        Route::get('/break-reason', BreakReason::class)->name('break-reason');
        Route::get('/break-reasons/create', BreakReasonForm::class)->name('break-reasons.create');

        Route::get('/break-reasons/{breakReasonId}/edit', BreakReasonForm::class)->name('break-reasons.edit');
        Route::get('/break-request', BreakRequest::class)->name('break-request');

        Route::get('/language-settings', LanguageSettings::class)->name('language-settings');
        Route::get('/location-map-settings', LocationMapSettings::class)->name('location-map-settings');
        Route::get('addons', Addons::class)->name('addons');
        Route::get('dashboard-summary', DashboardSummary::class)->name('dashboard-summary');
        Route::get('branches/dashboard-branch-summary', DashboardBranchSummary::class)->name('dashboard-branch-summary');
        Route::get('/packages', PackageManagement::class)->name('package');

        Route::get('automation', AutomationComponent::class)->name('automation');

        Route::get('meta-ads-utm-link-generator', MetaAdsUtmLinkGenerator::class)->name('meta-ads-utm-link-generator');

        Route::get('/analytics', Analytics::class)->name('analytics');
        Route::get('/sales-force-setting', SalesForceSetting::class)->name('sales-force-setting');
        Route::get('/dynamic-report/{id}', DynamicReport::class)->name('dynamic-report');
        Route::get('/dynamic-reports-list', DynamicReportsList::class)->name('dynamic-report-list');
        Route::get('/create-dynamic-report', CreateDynamicReports::class)->name('create-dynamic-report');
        Route::get('/edit-dynamic-report/{id}', EditDynamicReports::class)->name('edit-dynamic-report');
        Route::get('/category-grouping', CategoryGrouping::class)->name('category.grouping');
        Route::get('twillio-video-setting', TwillioVideoSetting::class)->name('twillio-video-setting');
        Route::get('/email-logs', EmailLogs::class)->name('email-logs');
        Route::get('location-grouping', LocationGrouping::class)->name('location-grouping');

        Route::get('slack-setting', SlackSetting::class)->name('slack-setting');
        Route::get('slack-templates', SlackTemplates::class)->name('slack-templates');
        Route::get('country-manager', CountryManager::class)->name('country-manager');
        Route::get('select-location', LocationSelectorPage::class)->name('select-location');

          // Virtual Queue Settings
        Route::get('virtual-queue-settings', VirtualQueueSettings::class)->name('virtual-queue-settings');

        // CSV Import for old records
        Route::get('/import/queues', [ImportQueueController::class, 'showForm'])->name('import-queues.form');
        Route::post('/import/queues', [ImportQueueController::class, 'upload'])->name('import-queues.upload');
        Route::get('queue-reset-setting', QueueResetSetting::class)->name('queue-reset-setting');
    });

    //2FA Verification Routes
    Route::middleware('auth')->group(function () {
        Route::get('/2fa/verify', [TwoFactorController::class, 'index'])->name('2fa.verify');
        Route::post('/2fa/verify', [TwoFactorController::class, 'store'])->name('2fa.store');

        // Route::get('/packages', PackageManagement::class)->name('package');
        Route::get('/buy-subscription', BuySubcription::class)->name('buy-subcription');
    });

    Route::get('/verify-otp', OtpVerification::class)->name('verify.otp');

    Route::get('ds/{id}', DisplayScreen::class);
    Route::get('queue/{location_id?}', Queue::class)->name('queue');
    Route::get('queue-staff/{location_id?}', QueueStaff::class)->name('queue-staff');
    Route::get('display', QueueDisplay::class)->name('display');
    Route::get('main/{location_id?}', Main::class);
    Route::get('service-display', ServiceDisplay::class)->name('service-display');
    Route::get('single-display/{location_id?}', SingleDisplayScreen::class)->name('single-display');
    Route::get('ds-test/{id}', DisplayScreenTest::class);
    Route::get('ds-first/{id}', DisplayScreenFirst::class);
    Route::get('ds-second/{id}', DisplayScreenSecond::class);

    //Booking module
    Route::get('booking-list', BookingList::class)->name('booking-list');
    Route::get('prefer-booking-list', PreferBookingList::class)->name('prefer-booking-list');
    // Route::get('main/booking', MainBooking::class);
    Route::get('/book-appointment/{location_id?}', MainBookingAppointment::class)->name('book-appointment');
    Route::get('convert-to-queue', ConvertBookToQueue::class)->name('convert-to-queue');
    Route::get('/booking-confirmed/{id}', BookingConfirmed::class)->name('booking-confirmed');
    Route::get('/booking-cancelled/{id}', BookingCancelled::class);
    Route::get('/edit-booking/{id}', EditBooking::class)->name('edit-booking');
    Route::get('/main/booking-rescheduled/{id}', BookingRescheduled::class);
    Route::get('/add-calendar/{id}', [MainController::class, 'createEvent']);
    Route::get('/add-calendar-outlook/{id}', [MainController::class, 'createOutlookEvent']);
    Route::get('/calendar/appointments', AppointmentCalendar::class)->name('appointment-calendar');

    Route::get('/checkin/{id}/{location}', CheckinComponent::class);

    // Route::get('/dummy-test', DummyTest::class);

    Route::get('/view-waitlist/{location?}/{id?}', ViewWaitlist::class);
    Route::get('/ticket-view/{id}', TicketView::class);

    Route::get('/rating/survey', RatingSurvey::class);
    Route::get('/rating/thank-you', RatingThankYou::class);
    Route::get('/rating/already_submitted', RatingAlreadySubmitted::class);
    Route::get('/level', CategoryLevel::class)->name('category-level');
    Route::get('/ticket-print/{id}', TicketPrint::class)->name('ticket.print');

    //error manage and list route
    Route::get('/error-logs-manage', ErrorLogs::class)->name('error-logs');
    Route::get('/error-logs', ErrorLogsList::class)->name('error-logs');


    Route::middleware(['check.qr.url'])->group(function () {
        Route::get('/mobile/{url_string}/{getlocation}/{getseconds}', MobileQueue::class);
    });

    Route::get('visits/{id}', TicketVisit::class);

   Route::get('/ds-bbj/{id}', [DisplayScreenController::class, 'show'])->name('bbj.show');

// Optional AJAX refresh endpoint
Route::post('/ds-bbj/refresh', [DisplayScreenController::class, 'refreshQueues'])->name('bbj.refresh');

    Route::get('qr-code-scanner', QRCodeScanner::class)->name('qr-code-scanner');

    // Virtual Queue Routes (Public Access)
    Route::get('ticket-selection/{location_id?}', TicketGenerationSelection::class)->name('ticket-selection');
    Route::get('virtual-queue-type-staff/{location_id?}', VirtualQueueTypeSelection::class)->name('virtual-queue-type-selection-staff');
    Route::get('ai-agent-call-staff/{virtualQueueId}', AIAgentCall::class)->name('ai-agent-call-staff');
    Route::get('human-agent-waiting-staff/{virtualQueueId}', HumanAgentWaiting::class)->name('human-agent-waiting-staff');
    
    Route::get('/clear-cache', function () {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        return "Cache cleared successfully!";
    });
    Route::get('/crons-start', function () {
        Artisan::call('users:logout-forgotten');
        return "logout users";
    });
    Route::get('/crons-run', function () {
        Artisan::call('bookings:auto-cancel');
        return "crons run";
    });

    Route::get('/test-send-mail', function () {
        Mail::raw('This is a test email.', function ($message) {
            $message->to('aksh@stelleninfotech.in')->subject('Test Email');
        });
        return "mail sent";
    });

    Route::get('/success', function () {
        return "Payment Successful!";
    })->name('payment.success');
    Route::get('/cancel', function () {
        return "Payment Canceled!";
    })->name('payment.cancel');

    // Route::get('/saleforces/callback', function () {
    //     return "saleforsce url";
    // });

    Route::get('meta-ads-utm-link-generator', MetaAdsUtmLinkGenerator::class)->name('meta-ads-utm-link-generator');

    // Route::get('/tenant/create', AddTenant::class)->name('tenant.create');
    Route::get('/progress-bar', SetupProgress::class);

    Route::get('/meeting/{room?}/{queueId?}', VirtualMeeting::class)->name('virtual-meeting');

    Route::get('/salesforce/authorize', [SalesforceController::class, 'authorizeUser'])->name('salesforce.authorize');
    Route::get('/salesforce/callback', [SalesforceController::class, 'callback'])->name('salesforce.callback');
    Route::get('/salesforce/userlist', [SalesforceController::class, 'getUserList'])->name('salesforce.getUserList');

    Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

    Route::get('/check-team', function () {
        return tenant('id');
    });


    // Public AI Agent Call Routes (Accessible without tenant domain)
Route::name('public.')->group(function () {
    // Location Selection - First entry point for public access
    Route::get('/choose-locations', PublicLocationSelection::class)
        ->name('choose-locations');
    
    // Virtual Queue Type Selection - Entry point for public access
    Route::get('/virtual-queue', PublicVirtualQueueTypeSelection::class)
        ->name('virtual-queue-type-selection');
    
    // AI Agent Call - Public access
    Route::get('/ai-agent-call/{virtualQueueId}', PublicAIAgentCall::class)
        ->name('ai-agent-call');
    
    // Booking Chatbot - Public access for appointment booking
    Route::get('/booking-chatbot', BookingChatbot::class)
        ->name('booking-chatbot');
    
    // Placeholder routes for human agent waiting and virtual meeting
    // These should be implemented if needed
    Route::get('/human-agent-waiting/{virtualQueueId}', function($virtualQueueId) {
        return view('public.human-agent-waiting', compact('virtualQueueId'));
    })->name('human-agent-waiting');
    
    Route::get('/virtual-meeting/{room}/{queueId}', function($room, $queueId) {
        return view('public.virtual-meeting', compact('room', 'queueId'));
    })->name('virtual-meeting');
    
    Route::get('/queue-selection', function() {
        return redirect()->route('public.virtual-queue-type-selection');
    })->name('queue-selection');
});
});

Route::get('/tts', function () {
    $text = "هذا اختبار للوظيفة باللغة العربية"; // Arabic test text
    $lang = request('lang', 'ar-SA'); // Default Arabic (Saudi Arabia)

    $response = Http::get('https://texttospeech.responsivevoice.org/v1/text:synthesize', [
        'text'   => $text,
        'lang'   => $lang,
        'engine' => 'g1',
        'pitch'  => 0.5,
        'rate'   => 0.8,
        'volume' => 1,
        'key'    => 'Gc5DXRcK', // Your API Key
        'gender' => 'female',
    ]);

    if ($response->successful()) {
        return response($response->body(), 200)
            ->header('Content-Type', 'audio/mpeg');
    }

    return response()->json([
        'error' => 'TTS request failed',
        'status' => $response->status(),
        'body' => $response->body(),
    ], $response->status());
});


Route::get('/graph-test', function () {
    $graph = new \Microsoft\Graph\Graph();
    dd($graph);
});

Route::get('/phpinfo', function () {
    phpinfo();
});
Route::get('/bright-test', function () {
   return view('bright-sign');
});


Route::get('/heartbeat', fn() => response()->noContent());

// Route::get('/salesforce/users', [SalesforceController::class, 'users']);
// Route::get('/salesforce/authorize/{state?}', [SalesforceController::class, 'authorizeUser'])->name('salesforce.authorize');
// Route::get('/saleforce/callback', [SalesforceController::class, 'callback'])->name('saleforce.callback');



// Route::get('/download-adm', function () {
//     // $filePath = public_path('test-zip-file.zip'); // Adjust the path
//     $filePath = public_path('adminer.php'); // Adjust the path

//     return response()->download($filePath);
// });

Route::get('/run-queue-until-empty', function () {
    $count = DB::table('jobs')->count();
    if ($count === 0) {
        return "No jobs in queue.";
    }

    Artisan::call('queue:work', [
        '--queue' => 'default',
        '--tries' => 3,
        '--stop-when-empty' => true,
    ]);

    return "Queue processed until empty.";
});




