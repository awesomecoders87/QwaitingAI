<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Models\{
    Queue,
    Counter,
    Category,
    Level,
    SiteDetail,
    StaffBreak,
    User,
    ActivityLog,
    FeedbackSetting,
    Location,
    FormField,
    PusherDetail,
    QueueStorage,
    SmtpDetails,
    BreakReason,
    AccountSetting,
    SmsAPI,
    SuspensionLog,
    MessageDetail
};

use App\Events\{QueueProgress, QueuePending, QueueCreated, BreakEvent, QueueDisplay};
use Carbon\Carbon;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Facades\Mail;
use App\Mail\SuspensionNotification;

class CallScreenApiController extends Controller
{

    /**
     * Get all counter assign to staff
     */
    public function allstaffCounter(Request $request)
    {

    $validator = Validator::make($request->all(), [
        'teamId' => 'required',
        'locationId' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => $validator->errors(),
        ], 422);
    }

        $teamId = $request->teamId;
        $locationId = $request->locationId;
        $siteDetails = SiteDetail::where('team_id', $teamId)->where('location_id',$locationId)->select('counter_option')->first();
        $userAuth = Auth::user();

        if (!$userAuth) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated'
            ], 401);
        }

        $counter = Counter::getCounter( $teamId, $siteDetails?->counter_option, $userAuth, $locationId);

        if (!$counter) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'No Counter found',
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'counter' => $counter,
        ],200);
    }

    /**
     * fetch all counters list
     *
     */
    public function allCounter(Request $request)
    {

    $validator = Validator::make($request->all(), [
        'teamId' => 'required',
        'locationId' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => $validator->errors(),
        ], 422);
    }

        $teamId = $request->teamId;
        $locationId = $request->locationId;

        $counters = Counter::where(['team_id' => $teamId, 'show_checkbox' => Counter::STATUS_ACTIVE])
        ->whereJsonContains('counter_locations', "$locationId")
        ->get();

        if (!$counters) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'No Counter found',
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'counters' => $counters,
        ],200);
    }


 /**
  * Current visitor call detail
  *
  */

    public function getCurrentServeDetails(Request $request)
{
    $validator = Validator::make($request->all(), [
        'teamId' => 'required|integer',
        'locationId' => 'required|integer',
        'queue_id' => 'nullable|integer',
        'storage_id' => 'nullable|integer',
        'counterId' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => $validator->errors(),
        ], 422);
    }

    $teamId = $request->teamId;
    $locationId = $request->locationId;
    $queueId = $request->queue_id;
    $storageId = $request->storage_id;
    $selectedCounter = $request->counterId;

    $user = Auth::user(); // Fetch the authenticated user

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User not authenticated'
        ], 401);
    }

    $conditionTeam = ['team_id' => $teamId, 'counter_id' => $selectedCounter];

    $currentServeDetails = Queue::currentVisitorRecord(
        $conditionTeam,
        $user->id,
        $queueId,
        $locationId,
        $storageId
    );

    if (!$currentServeDetails) {
        return response()->json([
            'status' => 'not_found',
            'message' => 'No current serve details found',
        ], 404);
    }

    return response()->json([
        'status' => 'success',
        'data' => $currentServeDetails,
    ],200);
}

public function getPendingQueues(Request $request)
{
    $validator = Validator::make($request->all(), [
        'teamId' => 'required|integer',
        'locationId' => 'required|integer',
        'counterId'=>'required|integer',
        'page' => 'nullable|integer',
        'name' => 'nullable|string',
        'queue_type' => 'nullable|string',
        'is_fixed' => 'nullable|boolean',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => $validator->errors(),
        ], 422);
    }

    $isFixed = $request->is_fixed ?? false;
    $teamId = $request->teamId;
    $locationId = $request->locationId;
    $page = $request->page ?? Queue::INITIAL_VISITOR_SHOW_COUNT;
    $name = $request->name;
    $queueType = $request->queue_type;
    $counterId = $request->counterId;


    $conditionTeam = ['team_id' => $teamId];

    $pendingQueues = Queue::getPendingQueues(
        $conditionTeam,
        $isFixed,
        $locationId,
        $page,
        $name,
        $teamId,
        $queueType,
        $counterId
    );

    if ($pendingQueues->isEmpty()) {
        return response()->json([
            'status' => 'not_found',
            'message' => 'No pending queues found',
        ], 404);
    }

    return response()->json([
        'status' => 'success',
        'data' => $pendingQueues,
        'count' => count($pendingQueues),
    ],200);
}

public function getMissedCalls(Request $request)
{
    $validator = Validator::make($request->all(), [
        'teamId' => 'required|integer',
        'locationId' => 'required|integer',
        'only_department_queue' => 'nullable|boolean',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => $validator->errors(),
        ], 422);
    }

    $teamId = $request->teamId;
    $locationId = $request->locationId;

    $onlyDepartmentQueue = $request->only_department_queue ?? 0;

    // Define team condition
    $conditionTeam = ['team_id' => $teamId];
    $userAuth = Auth::user();
    // Fetch missed calls
    $missedCalls = Queue::getMissedCallId($conditionTeam, $onlyDepartmentQueue, $locationId);
    $holdCalls = QueueStorage::getHoldCall($conditionTeam, $onlyDepartmentQueue,$locationId);
    $tokenServed = Queue::totalTokenServed($conditionTeam, $userAuth->id, $locationId);

    if (empty($missedCalls)) {
        return response()->json([
            'status' => 'not_found',
            'message' => 'No missed calls found',
        ], 404);
    }

    return response()->json([
        'status' => 'success',
        'tokenServed' => $tokenServed,
        'missedQueue' => $missedCalls,
        'holdQueue' => $holdCalls,
    ]);
}

public function nextCall(Request $request)
{
    try {
        // Validate the request
        $validatedData = $request->validate([
            'teamId' => 'required|integer',
            'locationId' => 'required|integer',
            'queueId' => 'required|string',
            'selectedCounter' => 'required|integer',
        ]);

        // Extract validated data
        $encodedQueueId = $validatedData['queueId'];
        $teamId = $validatedData['teamId'];
        $locationId = $validatedData['locationId'];
        $selectedCounter = $validatedData['selectedCounter'];

        // Define team condition
        $conditionTeam = ['team_id' => $teamId];

        $nextStorageId = base64_decode($encodedQueueId);
        // Get the queue storage details
        $getQueue = QueueStorage::find($nextStorageId);
        if (!$getQueue) {
            return response()->json(['status'=>'not_found','message' => 'Queue storage not found'], 404);
        }

        $vistorId =  $getQueue->queue_id;
        $currentQueue = json_decode($getQueue->json, true);
        $normalizedJsonArray = array_change_key_case($currentQueue, CASE_LOWER);

         $ids = array_filter([
                    $getQueue->category_id,
                    $getQueue->sub_category_id,
                    $getQueue->child_category_id,
        ]);

        $categories = Category::whereIn('id', $ids)->pluck('name', 'id');

        $getCategory = $categories[$getQueue->category_id] ?? '';
        $getSubCategory = $categories[$getQueue->sub_category_id] ?? '';
        $getChildCategory = $categories[$getQueue->child_category_id] ?? '';

        $email = $normalizedJsonArray['email'] ?? '';
        $name = $normalizedJsonArray['name'] ?? '';

        $siteDetails = SiteDetail::where('team_id', $teamId)->where('location_id',$locationId)->first();

        $showStartBtn = $siteDetails?->hide_button;
        $isCheckSameCounter = $siteDetails?->counter_assigned_queue == 1;
        // Check for existing progress call
        $progressCall = Queue::getProgressRecordExist($conditionTeam, Auth::id(), $locationId, $selectedCounter);

        // Check if the call is on hold
        $holdCall = Queue::getHoldRecord($conditionTeam, $vistorId, $nextStorageId, $locationId);
        if ($holdCall) {
            return response()->json(['error' => 'This call is on hold temporarily'], 400);
        }

        if ( $showStartBtn == 'HIDE_START_CLOSE'   &&  !empty( $progressCall ) ) {
            $this->closeCall($nextStorageId);
            $progressCall = [];

        }


        if ($progressCall) {
            return response()->json(['error' => 'Close current serving call first!'], 400);
        }


        // Update the queue status if it's a missed call
        $servedQueueCall = QueueStorage::whereNotNull(['closed_by', 'closed_datetime'])
            ->where([
                'is_missed' => Queue::STATUS_YES,
                'queue_id' => $vistorId,
                'id' => $nextStorageId,
            ])->first();

        if ($servedQueueCall) {
            $servedQueueCall->is_missed = Queue::STATUS_NO;
            $servedQueueCall->save();
        }


        if ( $showStartBtn  == 'SHOW_START_CLOSE' ) {
            $startCallRes = Queue::nextCalledField( $conditionTeam, $vistorId,$selectedCounter, auth()->user()->id, $showStartBtn, $locationId, $nextStorageId,$isCheckSameCounter);

        }else{
            $startCallRes = Queue::startCalledField( $conditionTeam, $vistorId,$selectedCounter, false, $locationId, $nextStorageId,$isCheckSameCounter );
        }

        if ( $startCallRes == 'hold on' ) {

            return response()->json([
                'success' => 'error',
                'message' => 'This call is on Hold temporarily (start)',

            ], 422);
        }

        $dateformat = AccountSetting::showDateTimeFormat();
        $locationName =  Location::locationName($locationId);
        $countername = '';
        if($selectedCounter){
        $countername = Counter::counterName($selectedCounter);
        }
        $getQueue['counter_name'] = $countername;
        // Process the queue
        $queueData = [
            'to_mail' => $email,
            'team_id' => $teamId,
            'name' => $name,
            'phone' => $getQueue->phone ?? '',
            'phone_code' => $getQueue->phone_code ?? '',
            'queue_no' => $getQueue->queue_id,
            'arrives_time' => Carbon::parse($getQueue->created_at)->format($dateformat),
            'category_name' => $getCategory->name ?? '',
            'secondC_name' => $getSubCategory->name ?? '',
            'thirdC_name' => $getChildCategory->name ?? '',
            'pending_count' => $getQueue->queue_count,
            'token' => $getQueue->start_acronym . $getQueue->token,
            'token_with_acronym' => $getQueue->start_acronym,
            'waiting_time' => $getQueue->waiting_time,
            'locations_id' =>  $locationId,
            'location_name' => $locationName,
        ];

    
        // Dispatch notifications or actions
        QueueCreated::dispatch($getQueue);
        QueueProgress::dispatch($getQueue);
        QueueDisplay::dispatch($getQueue);

        // Send notification
        // $this->sendNotification($queueData, 'call');

        // SMS Reminder
        // Queue::smsReminderNumber($vistorId, $teamId);

        SendEmailJob::dispatch($queueData, 'call');

        // Get updated queue counts
        // $tokenServed = Queue::totalTokenServed( $conditionTeam,auth()->user()->id, $locationId );
        // $queuesCount = Queue::getPendingQueuesC($conditionTeam, Auth::user(), $locationId);
        // $missedCalls = Queue::getMissedCallId($conditionTeam, $siteDetails->show_department_missed_queue, $locationId);

        return response()->json([
            'status' => 'success',
            'message' => 'Call successful!',
            // 'data' => $queueData,
            'servedToken' => $queueData,
            // 'queuesCount' => $queuesCount,
            // 'missedCalls' => $missedCalls,
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Handle validation exceptions
        return response()->json([
          'status' => 'error',
            'error' => 'Validation failed',
            'messages' => $e->errors(),
        ], 422);
    } catch (\Throwable $ex) {
        // Handle any other exceptions
        return response()->json([
           'status' => 'error',
            'message' => $ex->getMessage(),
        ], 500);
    }
}


private function sendNotification(array $data, $type)
{
    if ( isset( $data[ 'to_mail' ] ) && $data[ 'to_mail' ] != '' ){
        SmtpDetails::sendMail( $data, $type, 'ticket created', $data['team_id'] );
    }
    // $data[ 'location' ] = Location::find( $this->location )->value( 'location_name' );
    if(!empty($data['phone'])){
        SmsAPI::sendSms( $data['team_id'], $data,$type,$type);
    }
}

public function startCall(Request $request)
    {

        // $queueId = $request->input('queueStorageId');
        $encodedQueueId = $request->input('queueId');
        $queueId = base64_decode($encodedQueueId);

        if (empty($queueId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Queue id is required.',
            ], 422);
        }
        $queueStorage  = QueueStorage::where('id', $queueId)->whereNotNull('called_datetime')->whereNull('start_datetime')->first();

        if (!empty($queueStorage)) {

            $conditionTeam = ['team_id' => $queueStorage->team_id];

            $startCallRes = Queue::startCalledField($conditionTeam, $queueStorage->queue_id, $queueStorage->counter_id, false, $queueStorage->location_id, $queueStorage->id);


            if ($startCallRes == 'hold on') {

                return response()->json([
                    'status' => 'error',
                    'message' => 'This call is on Hold temporarily (start)',

                ], 422);
            }
            
            $countername = Counter::counterName($queueStorage->counter_id);
            $queueStorage['counter_name'] = $countername;


            // ActivityLog::storeLog( $queueStorage->team_id, auth()->user()->id, $queueStorage->queue_id,
            // $queueStorage->id, ActivityLog::QUEUE_STARTED, $queueStorage->locations_id );

            QueueCreated::dispatch($queueStorage);
            QueueProgress::dispatch($queueStorage);
            QueueDisplay::dispatch($queueStorage);

            return response()->json([
                'status' => 'success',
                'message' => 'Call Start Successfully',
                'closeShowbtn' => true,
            ], 200);
        } else {
            return response()->json([
                'status' => 'not_found',
                'message' => 'No Start Call Found',
            ], 404);
        }
    }

    public function closeCall(Request $request)
    {
        try {
            // $queueId = $request->input('queueStorageId');
            $encodedQueueId = $request->input('queueId');
            $queueId = base64_decode($encodedQueueId);

            if (empty($queueId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Call ID is required.',
                ], 422);
            }

            $queueStorage  = QueueStorage::where('id', $queueId)->whereNotNull('start_datetime')->first();
            if (!empty($queueStorage)) {

                $queueStorage?->update(['status' => Queue::STATUS_CLOSE, 'closed_datetime' => Carbon::now(), 'closed_by' => auth()->user()->id, 'datetime' => Carbon::now()]);

                $siteDetail = SiteDetail::where('team_id', $queueStorage->team_id)
                    ->where('location_id', $queueStorage->locations_id)
                    ->select('id', 'queue_priority', 'show_department_missed_queue')->first();

                $conditionTeam = ['team_id' => $queueStorage->team_id];

                ActivityLog::storeLog(
                    $queueStorage->team_id,
                    auth()->user()->id,
                    $queueStorage->queue_id,
                    $queueStorage->id,
                    ActivityLog::QUEUE_CLOSED,
                    $queueStorage->locations_id
                );

                // QueueProgress::dispatch($queueStorage );

                $feedbackSetting = FeedbackSetting::viewFeedbackSetting($queueStorage->team_id);

                if ($feedbackSetting?->form_after_closedcall == FeedbackSetting::STATUC_ACTIVE  && !empty($queueStorage?->closed_datetime) && $queueStorage?->ticket_mode  == Queue::TICKET_MODE_Walk_IN) {

                    $data = [];
                    $userDetails = json_decode($queueStorage?->json, true);

                    $data['to_mail'] = isset($userDetails['email']) ? $userDetails['email'] : (isset($userDetails['Email']) ? $userDetails['Email'] : null);
                    $data['name'] = $queueStorage?->name;
                    $data['phone'] = $queueStorage?->phone;
                    $data['phone_code'] = $queueStorage->phone_code ?? '91';
                    $data['locations_id'] = $queueStorage->locations_id;

                    if (!empty($data['to_mail'])) {
                        $data['feedback_link'] = request()->getSchemeAndHttpHost() . '/rating/survey?code=' . base64_encode($queueStorage?->queue_id);

                        SendEmailJob::dispatch($data, 'rating survey');
                    }

                    $queueType = $siteDetail?->queue_priority ?? Queue::DEFAULT_QUEUE;

                    // $queuesPending = Queue::getPendingQueues($conditionTeam, false, $queueStorage->location_id, Queue::INITIAL_VISITOR_SHOW_COUNT, null, $queueStorage->team_id, $queueType);
                    // $tokenServed = Queue::totalTokenServed($conditionTeam, auth()->user()->id, $queueStorage->location_id);
                    // $queuesCount = Queue::getPendingQueuesC($conditionTeam, Auth::user(), $queueStorage->location_id);
                    // $missedCalls = Queue::getMissedCallId($conditionTeam, $siteDetail->show_department_missed_queue, $queueStorage->location_id);

                    QueueCreated::dispatch($queueStorage);
                    QueueProgress::dispatch($queueStorage);

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Call Closed Successfully',
                        // 'pending' => $queuesPending,
                        // 'servedToken' => $tokenServed,
                        // 'queuesCount' => $queuesCount,
                        // 'missedCalls' => $missedCalls,
                    ], 200);
                } else {
                    QueuePending::dispatch(QueueStorage::viewQueue($queueStorage->id));

                    QueueStorage::where('queue_id', $queueStorage->queue_id)
                        ->where('id', '!=', $queueStorage->id)
                        ->update(['temp_hold' => Queue::STATUS_NO]);


                    $queueType = $siteDetail?->queue_priority ?? Queue::DEFAULT_QUEUE;

                    $queuesPending = Queue::getPendingQueues($conditionTeam, false, $queueStorage->location_id, Queue::INITIAL_VISITOR_SHOW_COUNT, null, $queueStorage->team_id, $queueType);
                    $tokenServed = Queue::totalTokenServed($conditionTeam, auth()->user()->id, $queueStorage->location_id);
                    $queuesCount = Queue::getPendingQueuesC($conditionTeam, Auth::user(), $queueStorage->location_id);
                    $missedCalls = Queue::getMissedCallId($conditionTeam, $siteDetail->show_department_missed_queue, $queueStorage->location_id);

                    QueueCreated::dispatch($queueStorage);
                    QueueProgress::dispatch($queueStorage);

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Call Closed Successfully',
                        'pending' => $queuesPending,
                        'servedToken' => $tokenServed,
                        'queuesCount' => $queuesCount,
                        'missedCalls' => $missedCalls,
                    ], 200);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No Close Call Found',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function reCall(Request $request)
    {

        // $queueId = $request->input('queueStorageId');
        $encodedQueueId = $request->input('queueId');
        $queueId = base64_decode($encodedQueueId);

        try {
            if (empty($queueId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Queue id is required.',
                ], 422);
            }

            $getQueue  = QueueStorage::where('id', $queueId)->first();


            if (!empty($getQueue)) {
                ActivityLog::storeLog($getQueue['team_id'], auth()->user()->id, $getQueue->queue_id,  $getQueue->id,  ActivityLog::QUEUE_RECALLED, $getQueue->locations_id);

                $getQueue->update([
                    'datetime' => date('Y-m-d H:i:s')
                ]);

                $currentQueue = json_decode($getQueue->json, true);
                $normalizedJsonArray = array_change_key_case($currentQueue, CASE_LOWER);

                $ids = array_filter([
                    $getQueue->category_id,
                    $getQueue->sub_category_id,
                    $getQueue->child_category_id,
                ]);

                $categories = Category::whereIn('id', $ids)->pluck('name', 'id');

                $getCategory = $categories[$getQueue->category_id] ?? '';
                $getSubCategory = $categories[$getQueue->sub_category_id] ?? '';
                $getChildCategory = $categories[$getQueue->child_category_id] ?? '';

                $email = isset($normalizedJsonArray['email']) ? $normalizedJsonArray['email'] : '';
                $name = isset($normalizedJsonArray['name']) ? $normalizedJsonArray['name'] : '';

                $data = [
                    'to_mail' => $email,
                    'name' => $name,
                    'team_id' => $getQueue->team_id ?? '',
                    'phone' => $getQueue->phone ?? '',
                    'phone_code' => $getQueue->phone_code ?? '',
                    'queue_no' => $getQueue->queue_id,
                    'token' => $getQueue->start_acronym . '' . $getQueue->token,
                    'token_with_acronym' => $getQueue->start_acronym,
                    'pending_count' => $getQueue->queue_count,
                    'waiting_time' => $getQueue->waiting_time,
                    'category_name' => isset($getCategory) ? $getCategory : '',
                    'secondC_name' => isset($getSubCategory) ? $getSubCategory : '',
                    'thirdC_name' => isset($getChildCategory) ? $getChildCategory : '',
                    'locations_id' => $getQueue->locations_id,
                ];

                // SendEmailJob::dispatch($data, 'call');

            //      $logData = [
            //     'team_id' => $getQueue->team_id,
            //     'location_id' => $getQueue->location,
            //     'user_id' => $getQueue->served_by,
            //     'customer_id' => $getQueue->created_by,
            //     'queue_id' => $getQueue->queue_id,
            //     'queue_storage_id' => $getQueue->id,
            //     'email' => $getQueue->email,
            //     'contact' => $getQueue->phone,
            //     'type' => MessageDetail::TRIGGERED_TYPE,
            //     'event_name' => 'Call Screen',
            // ];

            //     $this->sendNotification($data, 'recall', $logData);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Recall Successfully',
                ], 200);
            } else {
                return response()->json([
                    'success' => 'not_found',
                    'message' => 'No Call Found',
                ], 404);
            }
        } catch (\Throwable $ex) {
            // Handle any other exceptions
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
            ], 500);
        }
    }
    public function forwardCounter(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer',
                'counter_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                ], 422);
            }
            $id = $request->id;
            $counterId = $request->counter_id;
            $getQueue  = QueueStorage::where('id',$id)->first();

            $remark = "Forward counter is ".$counterId;
            $getQueue->update([
                    'forward_counter_id'=>$counterId,
                    'counter_id' =>null,
                    'transfer_id'=>null,
                    'status' => Queue::STATUS_PENDING,
                    'closed_by'=>null,
                    'called_datetime'=>null,
                    'start_datetime'=>null,
                    'datetime'=>Carbon::now(),
                    'served_by'=>null,
                    'reset_call'=>null,
                    'reset_by'=>null,
            ]);

        ActivityLog::storeLog($getQueue['team_id'], auth()->user()->id, $getQueue->queue_id,  $getQueue->id,  ActivityLog::VISITOR_TRANSFER, $getQueue->locations_id,ActivityLog::TYPE_CALL,$remark );

        QueueCreated::dispatch($getQueue);
        QueueProgress::dispatch($getQueue);

        return response()->json([
            'status' => 'success',
            'message' => 'Forward counter Successfully',
        ],200);


        }catch (\Throwable $ex) {
            // Handle any other exceptions
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }
    public function transferCall(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer',
                'category_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                ], 422);
            }
            $id = $request->id;
            $categoryId = $request->category_id;
            $getQueue = QueueStorage::findOrFail($id);

            $remark = "Forward Category is ".$categoryId;
            $getQueue->update([
                    'transfer_id'=>$categoryId,
                    'forward_counter_id'=>null,
                    'counter_id' =>null,
                    'status' => Queue::STATUS_PENDING,
                    'closed_by'=>null,
                    'called_datetime'=>null,
                    'start_datetime'=>null,
                    'datetime'=>Carbon::now(),
                    'served_by'=>null,
                    'reset_call'=>null,
                    'reset_by'=>null,
            ]);

            ActivityLog::storeLog($getQueue['team_id'], auth()->user()->id, $getQueue->queue_id,  $getQueue->id,  ActivityLog::VISITOR_TRANSFER, $getQueue->locations_id,ActivityLog::TYPE_CALL,$remark );

            QueueCreated::dispatch($getQueue);
            QueueProgress::dispatch($getQueue);


            return response()->json([
                'status' => 'success',
                'message' => 'Call transfer category Successfully',
            ], 200);


            }catch (\Throwable $ex) {
                // Handle any other exceptions
                return response()->json([
                    'status' => 'error',
                    'message' => $ex->getMessage(),
                ], 500);
            }
    }

    public function holdCall(Request $request)
    {

        $queueId = $request->input('queue_id');

        try {
            // Validate ID
            if (empty($queueId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Queue id is required.',
                ], 422);
            }

            // Find the call
            $record = QueueStorage::find($queueId);

            if (!$record) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Queue record not found.',
                ], 404);
            }

            // Update hold status
            $record->update([
                'is_hold' => Queue::STATUS_YES,
                'hold_start_datetime' => Carbon::now(),
            ]);

            // Store log
            ActivityLog::storeLog(
                $record->team_id,
                auth()->id(),
                (int) $record->queue_id,
                (int) $record->id,
                ActivityLog::HOLD_QUEUE,
                $record->locations_id
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Call held successfully.',
            ]);
        } catch (\Throwable $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function unholdCall(Request $request)
    {

        $queueId = $request->input('queue_id');

        try {

            if (empty($queueId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Queue id is required.',
                ], 422);
            }

            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access.',
                ], 401);
            }

            $teamId = $user->team_id;
            $locationId = $user->location_id; // Or wherever you get location from
            $timezone = auth()->user()->timezone ?? config('app.timezone');

            // Fetch record
            $record = QueueStorage::where([
                'team_id' => $teamId,
                'id' => $queueId,
                'locations_id' => $locationId
            ])->first();

            if (!$record) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Queue not found.',
                ], 404);
            }

            // Update unhold status
            $record->update([
                'is_hold' => Queue::STATUS_NO,
                'hold_end_datetime' => Carbon::now($timezone),
            ]);

            // Log activity
            ActivityLog::storeLog(
                $teamId,
                $user->id,
                (int) $record->queue_id,
                (int) $record->id,
                ActivityLog::UNHOLD_QUEUE,
                $locationId
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Queue unheld successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function skipCall(Request $request)
    {
        try {

            $queueId = $request->input('queue_id');

            if (empty($queueId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Queue id is required',
                ], 422);
            }

            $record = QueueStorage::where('queue_id', $queueId)->first();

            if (!$record) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Queue not found.',
                ], 404);
            }

            $record->update([
                'status'          => Queue::STATUS_PENDING,
                'start_datetime'  => null,
                'closed_datetime'  => null,
                'called_datetime' => null,
                'is_missed'       => Queue::STATUS_YES,
                'is_arrived'      => Queue::STATUS_NO,
                'late_duration'   => null,
                'served_by'       => null,
                'counter_id'      => null,
            ]);

            ActivityLog::storeLog(
                $record->team_id,
                auth()->id(),
                (int)$record->queue_id,
                (int)$queueId,
                ActivityLog::VISITOR_MOVE_BACK,
                $record->locations_id
            );

            $currentQueue = json_decode($record->json, true);
            $normalizedJsonArray = array_change_key_case($currentQueue, CASE_LOWER);

            $getCategory = Category::where('id', $record->category_id)->value('name');
            $getSubCategory = Category::where('id', $record->sub_category_id)->value('name');
            $getChildCategory = Category::where('id', $record->child_category_id)->value('name');

            $email = isset($normalizedJsonArray['email']) ? $normalizedJsonArray['email'] : '';
            $name = isset($normalizedJsonArray['name']) ? $normalizedJsonArray['name'] : '';

            $data = [
                'to_mail' => $email,
                'name' => $name,
                'phone' => $record->phone ?? '',
                'phone_code' => $record->phone_code ?? '',
                'queue_no' => $record->queue_id,
                'token' => $record->start_acronym . '' . $record->token,
                'token_with_acronym' => $record->start_acronym,
                'pending_count' => $record->queue_count,
                'waiting_time' => $record->waiting_time,
                'category_name' => isset($getCategory) ? $getCategory : '',
                'secondC_name' => isset($getSubCategory) ? $getSubCategory : '',
                'thirdC_name' => isset($getChildCategory) ? $getChildCategory : ''
            ];

            // $logData = [
            //     'team_id' => $record->team_id,
            //     'location_id' => $record->locations_id,
            //     'user_id' => $record->served_by,
            //     'customer_id' => $record->created_by,
            //     'queue_id' => $record->queue_id,
            //     'queue_storage_id' => $record->id,
            //     'email' => $record->email,
            //     'contact' => $record->phone,
            //     'type' => MessageDetail::TRIGGERED_TYPE,
            //     'event_name' => 'Call Screen',
            // ];

            // $this->sendNotification($data, 'call skip', $logData);

            return response()->json([
                'status' => 'success',
                'message' => 'Visitor marked as missed and moved back to queue.',
            ]);
        } catch (\Throwable $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }

    public function revertServedCall(Request $request)
    {

        $queueId = $request->input('queue_id');
        $storageId = $request->input('storage_id');

        try {

            if (empty($queueId) || empty($storageId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Queue ID and Storage ID are required.',
                ], 422);
            }

            $queue = Queue::findOrFail($queueId);
            $queue->status = Queue::STATUS_PENDING;
            $queue->save();

            $queueStorage = QueueStorage::where([
                'queue_id' => $queueId,
                'id' => $storageId
            ])->first();

            if (!$queueStorage) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Queue storage not found.'
                ], 404);
            }

            $queueStorage->update([
                'is_missed'       => Queue::STATUS_NO,
                'status'          => Queue::STATUS_PENDING,
                'closed_by'       => null,
                'called_datetime' => null,
                'start_datetime'  => null,
                'closed_datetime' => null,
                'served_by'       => null,
            ]);

            // Dispatch events
            QueueCreated::dispatch($queueStorage);
            QueueProgress::dispatch($queueStorage);
            QueueDisplay::dispatch($queueStorage);

            return response()->json([
                'status' => 'success',
                'message' => 'Queue successfully reverted to pending.',
            ]);
        } catch (\Throwable $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function cancelCall(Request $request)
    {
        $timezone = auth()->user()->timezone ?? config('app.timezone');

        $queueId = $request->input('queue_id');

        try {

            if (empty($queueId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Queue id is required',
                ], 422);
            }

            // Update queue status
            $updated = QueueStorage::where('id', $queueID)->update([
                'status' => Queue::STATUS_CANCELLED,
                'cancelled_datetime' => Carbon::now($timezone),
            ]);

            if (!$updated) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Queue not found or already cancelled.',
                ], 404);
            }

            // Fetch updated record
            $record = QueueStorage::find($queueId);

            // Store log
            ActivityLog::storeLog(
                $record->team_id,
                auth()->id(),
                (int) $record->queue_id,
                (int) $record->id,
                ActivityLog::CANCEL_CALL,
                $record->locations_id
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Call cancelled successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function sendSMS(Request $request)
    {
        try {
            $queueID = $request->input('queue_id');
            $smsText = $request->input('sms');

            if (empty($queueID) || empty($smsText)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Queue ID and SMS content are required.',
                ], 422);
            }

            $contact = QueueStorage::where('id', $queueID)
                ->select('id', 'queue_id', 'phone', 'phone_code', 'team_id', 'locations_id')
                ->first();

            if (!$contact || empty($contact->phone)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or missing phone number.',
                ], 404);
            }

            // Log the activity
            ActivityLog::storeLog(
                $contact->team_id,
                auth()->id(),
                (int)$contact->queue_id,
                (int)$contact->id,
                ActivityLog::SEND_SMS,
                $contact->locations_id
            );

            // Format phone number
            $phoneCode = isset($contact->phone_code) ? ltrim($contact->phone_code, '+') : '91';
            $fullPhone = $phoneCode . $contact->phone;

            // Send SMS
            $status = SmsAPI::currentQueueSms($fullPhone, $smsText, $contact->team_id, 'queue call');

            // Optional: Save SMS report
            // SmsReport::create([
            //     'team_id'     => $contact->team_id,
            //     'location_id' => $contact->locations_id,
            //     'user_id'     => auth()->id(),
            //     'message'     => $smsText,
            //     'contact'     => $fullPhone,
            //     'status'      => $status ? 'sent' : 'failed',
            //     'channel'     => 'sms',
            //     'type'        => 'queue',
            // ]);

            return response()->json([
                'status' => $status ? 'success' : 'error',
                'message' => $status ? 'SMS sent successfully.' : 'SMS sending failed.',
            ], $status ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function historyQueue(Request $request)
    {

        $queueId = $request->input('queue_id');
        $storageId = $request->input('storage_id');

        try {
            // Optional: validate IDs
            if (empty($queueId) || empty($storageId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Queue ID and Storage ID are required.',
                ], 422);
            }

            // Retrieve the queue details (replace with your actual logic)
            $queue = QueueStorage::where('id', $storageId)
                ->where('queue_id', $queueId)
                ->first();

            if (!$queue) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Queue record not found.',
                ], 404);
            }

            // Fetch site detail to check if activity log is enabled
            $siteDetail = SiteDetail::where('team_id', $queue->team_id)->first();

            $activityLogs = [];
            if ($siteDetail && $siteDetail->activity_log == SiteDetail::STATUS_YES) {
                $activityLogs = ActivityLog::viewLogs(
                    $queue->team_id,
                    $queueID,
                    $storageId,
                    $queue->locations_id
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Queue history retrieved successfully.',
                'data' => [
                    'queue' => $queue,
                    'activity_logs' => $activityLogs,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function break(Request $request)
    {
        try {
            $breakType = $request->input('break_type');     // Break reason ID
            $breakComment = $request->input('break_comment');
            $teamId = $request->input('team_id');
            $locationId = $request->input('location_id');

            if (empty($breakType)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Break reason is required.',
                ], 422);
            }

            if (empty($teamId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Team Id is required.',
                ], 422);
            }

            if (empty($locationId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Location Id is required.',
                ], 422);
            }

            $breakData = BreakReason::where('reason', $breakType)->first();


            if (!$breakData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Break reason not found.',
                ], 404);
            }


            $breakAttributes = [
                'team_id'       => $request->team_id,
                'location_id'   => $request->location_id,
                'user_id'       => auth()->id(),
                'reason'        => $breakData->reason,
                'comment'       => $breakComment,
                'breakreason_id' => $breakData->id,
            ];

            if ($breakData->is_approved == BreakReason::IS_APPROVED_YES) {
                $breakAttributes['time_start'] = now();
                $breakAttributes['status'] = StaffBreak::STATUS_AUTO_APPROVAL;
            } else {
                $breakAttributes['status'] = StaffBreak::STATUS_PENDING;
            }

            $break = StaffBreak::storeStaffBreak($breakAttributes);

            return response()->json([
                'status' => 'success',
                'message' => $breakData->is_approved == BreakReason::IS_APPROVED_YES
                    ? 'Break started with auto approval.'
                    : 'Request has been sent to the admin',
                'data' => [
                    'break_id' => $break->id,
                    'break_time' => $breakData->break_time ?? null,
                    'status' => $break->status,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function moveBack(Request $request)
    {
        try {

            $timezone = auth()->user()->timezone ?? config('app.timezone');
            $teamId = $request->input('team_id');
            $locationId = $request->input('location_id');
            $user = auth()->user();
            $visitorRecord = Queue::currentVisitorRecord(['team_id' =>  $teamId], null, null, (int)$locationId, null);

            $siteDetail = SiteDetail::where('team_id', $teamId)->where('location_id', $locationId)->first();

            if (!$visitorRecord) {
                return response()->json(['error' => 'Visitor not found.'], 404);
            }

            if ($siteDetail->served_queue_move == Queue::MOVE_BACK_TO_MQ) {
                // Your custom move back logic
                $this->moveBackMQVL($teamId, $locationId);
            } else {
                $visitorRecord->update([
                    'status' => Queue::STATUS_PENDING,
                    'start_datetime' => null,
                    'called_datetime' => null,
                    'datetime' => Carbon::now($timezone),
                    'is_arrived' => Queue::STATUS_NO,
                    'late_duration' => null,
                    'served_by' => null,
                    'is_missed' => Queue::STATUS_NO,
                    'is_hold' => Queue::STATUS_NO,
                    'hold_start_date' => null,
                    'hold_end_date' => null,
                ]);
            }

            QueueDisplay::dispatch($visitorRecord);
            QueueProgress::dispatch($visitorRecord);
            QueueCreated::dispatch($visitorRecord);

            ActivityLog::storeLog($teamId, $user->id, $visitorRecord->queue_id, $visitorRecord->id, ActivityLog::VISITOR_MOVE_BACK, $locationId);

            return response()->json([
                'status' => 'success',
                'message' => 'Call Move Back Successful',
            ], 200);
        } catch (\Throwable $ex) {
            return response()->json([
                'status' => 'error',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function suspendAppointmentsAndQueue(Request $request)
    {
        try {
            $validated = $request->validate([
                'suspension_reason'   => 'required|string',
                'action_type'         => 'required|in:queue,appointment,appointment_and_queue',
                'notification_type'   => 'required|in:sms,email,sms_and_email',
                'team_id'            => 'required|integer',
                'location_id'           => 'required|integer',
            ]);

            $teamId = $validated['team_id'];
            $locationId = $validated['location_id'];
            $actionType = $validated['action_type'];
            $notificationType = $validated['notification_type'];
            $reason = $validated['suspension_reason'] ?? 'Suspended all Appointments';

            // Create suspension log
            $suspensionLog = SuspensionLog::create([
                'team_id'          => $teamId,
                'location_id'      => $locationId,
                'action_type'      => $actionType,
                'notification_type' => $notificationType,
                'reason'           => $reason,
            ]);

            $queuestorages = collect();
            $bookings = collect();

            // Get QueueStorage records
            if (in_array($actionType, ['queue', 'appointment_and_queue'])) {
                $queuestorages = QueueStorage::where('team_id', $teamId)
                    ->where('locations_id', $locationId)
                    ->whereDate('arrives_time', Carbon::today())
                    ->whereNull('called_datetime')
                    ->where('status', '!=', 'Cancelled')
                    ->get();
            }

            // Get Booking records
            if (in_array($actionType, ['appointment', 'appointment_and_queue'])) {
                $bookings = Booking::where('team_id', $teamId)
                    ->where('location_id', $locationId)
                    ->when(auth()->user()->is_admin != 1, function ($query) {
                        $query->where('staff_id', auth()->id());
                    })
                    ->whereDate('booking_date', Carbon::today())
                    ->where('status', '!=', 'Cancelled')
                    ->get();
            }

            // Process Notifications
            if (in_array($notificationType, ['email', 'sms_and_email'])) {
                $this->processEmailNotifications($queuestorages, $bookings, $reason, $teamId, $locationId, $suspensionLog->id);
            }

            if (in_array($notificationType, ['sms', 'sms_and_email'])) {
                $this->processSmsNotifications($queuestorages, $bookings, $reason, $teamId, $suspensionLog->id);
            }

            // Update statuses
            $this->updateRecordsStatus($queuestorages, $bookings, $reason, $suspensionLog->id);

            return response()->json([
                'status'  => 'success',
                'message' => 'Suspension processed successfully with notifications sent',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function editVisitor(Request $request)
    {

        // $name = $request->input('name');
        // $phone = $request->input('email');
        // $teamId = $request->input('team_id');
        // $locationId = $request->input('location_id');

        // $siteDetail = SiteDetail::where('team_id', $teamId)->where('location_id', $locationId)->first();

        // if (empty($teamId)) {
        //         return response()->json([
        //             'status' => 'error',
        //             'message' => 'Team Id is required.',
        //         ], 422);
        //     }

        //     if (empty($locationId)) {
        //         return response()->json([
        //             'status' => 'error',
        //             'message' => 'Location Id is required.',
        //         ], 422);
        //     }

        //     if (empty($name)) {
        //         return response()->json([
        //             'status' => 'error',
        //             'message' => 'Name is required.',
        //         ], 422);
        //     }

        //     if (empty($phone)) {
        //         return response()->json([
        //             'status' => 'error',
        //             'message' => 'Phone is required.',
        //         ], 422);
        //     }

        //  if ($siteDetail->queue_form_display == SiteDetail::STATUS_YES)
        // $this->validate();

        // $formattedFields = [];

        // foreach ($this->dynamicProperties as $key => $value) {
        //         $trimmedKey = trim($key);
        //         $fieldName = preg_replace('/_\d+/', '', $trimmedKey);
        //         $fieldName = strtolower($fieldName); // normalize to lowercase
        //         $formattedFields[$fieldName] = $value;
        // }

        // $this->name = $formattedFields['name'] ?? null;
        // $this->phone = $formattedFields['phone'] ?? null;
        // $this->email = isset($formattedFields['email']) ? $formattedFields['email'] : (isset($formattedFields['Email']) ? $formattedFields['Email'] : null);

        // if (!empty($this->staticVisitorDetails))
        // $formattedFields = array_merge($formattedFields, $this->staticVisitorDetails);

        // $jsonDynamicData = json_encode($formattedFields);

        // $storeData = [
        //     'name' => $name,
        //     'phone' => $phone,
        //     // 'json' => $jsonDynamicData,
        //     'category_id' => $categoryId ?? null,
        //     'sub_category_id' => $subCategoryId ?? null,
        //     'child_category_id' => $childCategoryId ?? null,
        //     'location_id' => $locationId,

        // ];
        // $currentVisitorRecord = Queue::currentVisitorRecord(['team_id' =>  $teamId], null, null, (int)$locationId, null)->update($storeData);
        // $this->callPendingEvent($currentVisitorRecord->id);

        // return response()->json([
        //         'status'  => 'success',
        //         'message' => 'Visitor edited successfully',
        // ]);
    }

    public function callPendingEvent($currentStorageID)
    {
        if (!empty($currentStorageID))
            QueuePending::dispatch(QueueStorage::viewQueue($currentStorageID));
    }

    public function moveBackMQVL($teamId, $locationId)
    {
        Queue::currentVisitorRecord(['team_id' =>  $teamId], null, null, (int)$locationId, null)->update([
            'status' => Queue::STATUS_PENDING,
            'start_datetime' => null,
            'called_datetime' => null,
            'is_missed' => Queue::STATUS_YES,
            'is_arrived' => Queue::STATUS_NO,
            'late_duration' => null,
            // 'datetime' => Carbon::now($this->timezone),
            'served_by' => null,
            'counter_id' => null,
        ]);
    }

    protected function processEmailNotifications($queuestorages, $bookings, $message, $teamId, $locationId, $suspensionLogId)
    {

        $details = SmtpDetails::where('team_id', $teamId)->where('location_id', $locationId)->first();
        if (!empty($details->hostname) && !empty($details->port) &&  !empty($details->username) && !empty($details->password) && !empty($details->from_email) &&  !empty($details->from_name)) {
            Config::set('mail.mailers.smtp.transport', 'smtp');
            Config::set('mail.mailers.smtp.host', trim($details->hostname));
            Config::set('mail.mailers.smtp.port', trim($details->port));
            Config::set('mail.mailers.smtp.encryption', trim($details->encryption ?? 'ssl'));
            Config::set('mail.mailers.smtp.username', trim($details->username));
            Config::set('mail.mailers.smtp.password', trim($details->password));

            Config::set('mail.from.address', trim($details->from_email));
            Config::set('mail.from.name', trim($details->from_name));
        }
        // Process queue storage emails
        foreach ($queuestorages as $queuestorage) {
            $email = $this->extractEmailFromQueueStorage($queuestorage);

            if (!empty($email)) {
                try {
                    $recipientName = $this->extractNameFromQueueStorage($queuestorage);
                    $queueData = [
                        'arrives_time' => $queuestorage->arrives_time,
                        'token' => $queuestorage->token ?? null,
                        'team_id' => $queuestorage->team_id ?? null,
                        'location_id' => $queuestorage->locations_id ?? null,
                        // Add other minimal required fields
                    ];

                    if (!empty($details->hostname) && !empty($details->port) &&  !empty($details->username) && !empty($details->password) && !empty($details->from_email) &&  !empty($details->from_name)) {
                        // dd( $email,$recipientName,$queueData);
                        Mail::to($email)->send(new SuspensionNotification(
                            $message,
                            'Queue Cancellation',
                            $queueData
                        ));
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to send email to queue storage (ID: {$queuestorage->id}): " . $e->getMessage());
                }
            }
        }


        // Process booking emails
        foreach ($bookings as $booking) {
            if (!empty($booking->email)) {
                try {
                    $bookingData = [
                        'booking_date' => $booking->booking_date,
                        'booking_time' => $booking->booking_time,
                        'team_id' => $booking->team_id,
                        'location_id' => $booking->location_id,
                    ];
                    if (!empty($details->hostname) && !empty($details->port) &&  !empty($details->username) && !empty($details->password) && !empty($details->from_email) &&  !empty($details->from_name)) {
                        Mail::to($booking->email)->send(new SuspensionNotification(
                            $message,
                            'Appointment Cancellation',
                            $bookingData,
                        ));
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to send email to booking: " . $e->getMessage());
                }
            }
        }
    }

    protected function processSmsNotifications($queuestorages, $bookings, $message, $teamId, $suspensionLogId)
    {
        // Process queue storage SMS
        foreach ($queuestorages as $queuestorage) {
            if (!empty($queuestorage->phone)) {
                $phone_code = isset($queuestorage->phone_code) ? ltrim($queuestorage->phone_code, '+') : '91';
                $contactWithCode = $phone_code . $queuestorage->phone;
                SmsAPI::currentQueueSms($contactWithCode, $message, $teamId, 'suspensions queue');
            }
        }

        // Process booking SMS
        foreach ($bookings as $booking) {
            if (!empty($booking->phone)) {
                $phone_code = '91';
                $contactWithCode = $phone_code . $booking->phone;
                SmsAPI::currentQueueSms($contactWithCode, $message, $teamId, 'suspensions appointment');
            }
        }
    }

    protected function extractNameFromQueueStorage($queuestorage)
    {
        if ($queuestorage->json) {
            $jsonData = is_string($queuestorage->json) ? json_decode($queuestorage->json, true) : $queuestorage->json;

            return $jsonData['name'] ??
                $jsonData['Name'] ??
                $jsonData['Full Name'] ??
                $jsonData['full_name'] ?? null;
        }

        return null;
    }

    protected function extractEmailFromQueueStorage($queuestorage)
    {
        if ($queuestorage->json) {
            $jsonData = is_string($queuestorage->json) ? json_decode($queuestorage->json, true) : $queuestorage->json;

            return $jsonData['Email'] ??
                $jsonData['email'] ??
                $jsonData['Email Address'] ??
                $jsonData['email_address'] ??
                $queuestorage->email ?? null;
        }

        return $queuestorage->email ?? null;
    }

    protected function updateRecordsStatus($queuestorages, $bookings, $message, $suspensionLogId)
    {
        $timezone = auth()->user()->timezone ?? config('app.timezone');

        // Update queue storage records
        foreach ($queuestorages as $queuestorage) {
            $queuestorage->update([
                'suspension_logs_id' => $suspensionLogId,
                'status' => "Cancelled",
                'cancelled_datetime' => Carbon::now($timezone),
            ]);
        }

        // Update booking records
        foreach ($bookings as $booking) {
            $booking->update([
                'suspension_logs_id' => $suspensionLogId,
                'status' => "Cancelled",
                'cancel_remark' => $message,
                'cancel_reason' => $message,
            ]);
        }
    }

}
