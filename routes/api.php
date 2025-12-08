<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\CallScreenApiController;
use App\Http\Controllers\Api\StaffController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/webhook', [WebhookController::class, 'handle']);

Route::post('/login', [ApiController::class, 'login']);
// Route::post('create/tenant', [ApiController::class, 'tenantCreate'])->name('tenant.save');
Route::post('create/tenant', [ApiController::class, 'tenantCreateWithLocation'])->name('tenant.save');
Route::post('autologin', [ApiController::class, 'autoLogin'])->name('autologin');
   Route::post('/send-whatsapp', [ApiController::class, 'sendMessage']);
   Route::post('/send-ticket-template', [ApiController::class, 'sendTicketTemplate']);
// Route::middleware(['strict.rate'])->group(function () {
    Route::post('/selected-location', [ApiController::class, 'setLocation']);
    Route::get('/test', [ApiController::class, 'testapi']);
    Route::get('/test-second', [ApiController::class, 'testapi2']);
    Route::get('/test-third', [ApiController::class, 'testapi3']);


Route::middleware('auth:sanctum')->group(function () {
    /**queue screen api */
    Route::post('/all-category', [ApiController::class, 'allCategory']);
    Route::post('/get-form-fields', [ApiController::class, 'getFormFields']);
    Route::post('/check-ticket-business-hours', [ApiController::class, 'checkTicketBusinessHoursApi']);
    Route::post('/check-availability', [ApiController::class, 'check']);
    Route::post('/queue/store', [ApiController::class, 'storeQueue']);
    Route::post('/convert-booking-to-queue', [ApiController::class, 'convertToQueue']);

     Route::post('/ticket-generate', [ApiController::class, 'ticketgenerate']);

    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    });

     /**call screen api */
    Route::post('/getStaffCounters', [CallScreenApiController::class, 'allstaffCounter']);
    Route::post('/getallCounters', [CallScreenApiController::class, 'allCounter']);
    Route::post('/current-call', [CallScreenApiController::class, 'getCurrentServeDetails']);
    Route::post('/getVisitorsList', [CallScreenApiController::class, 'getPendingQueues']);
    Route::post('/getMissedCalls', [CallScreenApiController::class, 'getMissedCalls']);
    Route::post('/nextcall', [CallScreenApiController::class, 'nextCall']);
    Route::post('/startcall', [CallScreenApiController::class, 'startCall']);
    Route::post('/closecall', [CallScreenApiController::class, 'closeCall']);
    Route::post('/recall', [CallScreenApiController::class, 'reCall']);
    Route::post('/forward-counter', [CallScreenApiController::class, 'forwardCounter']);
    Route::post('/transferCall', [CallScreenApiController::class, 'transferCall']);

     Route::post('/cancel-call', [CallScreenApiController::class, 'cancelCall']);
    Route::post('/skip-call', [CallScreenApiController::class, 'skipCall']);
    Route::post('hold-call', [CallScreenApiController::class, 'holdCall']);
    Route::post('/unhold-call', [CallScreenApiController::class, 'unholdCall']);
    Route::post('/revert-served-queue', [CallScreenApiController::class, 'revertServedCall']);
    Route::post('/move-back', [CallScreenApiController::class, 'moveBack']);
    // Route::post('/edit-visitor', [CallScreenApiController::class, 'editVisitor']);

    Route::post('/send-sms', [CallScreenApiController::class, 'sendSMS']);
    Route::post('/history', [CallScreenApiController::class, 'historyQueue']);
    Route::post('/break-reason', [CallScreenApiController::class, 'break']);
    Route::post('/suspension', [CallScreenApiController::class, 'suspendAppointmentsAndQueue']);

   /**display screen api */
    Route::post('/screen-list', [ApiController::class, 'displayscreen']);
    Route::post('/display-screen', [ApiController::class, 'getDisplayScreen']);
    Route::post('appointment-page', [ApiController::class, 'appointmentPage']);


    /**List of staff api */
    Route::get('staff', [StaffController::class, 'index']);
    Route::get('getcountries', [ApiController::class, 'getCountries']);






});
Route::post('/generate-ticket-with-qr', [ApiController::class, 'generateTicketWithQR']);
 Route::get('/test-api', function (Request $request) {

        return response()->json(['message' => 'Test successfully']);
    });
// });
