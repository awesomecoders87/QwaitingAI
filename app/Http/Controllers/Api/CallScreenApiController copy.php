<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Models\ {
    Queue, Counter, Category, Level, SiteDetail, StaffBreak, User, ActivityLog, FeedbackSetting, Location, FormField, PusherDetail, QueueStorage,SmtpDetails,BreakReason,AccountSetting,SmsAPI};

    use App\Events\ { QueueProgress, QueuePending, QueueCreated,BreakEvent};
    use Carbon\Carbon;
    use App\Jobs\SendEmailJob;

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

    $user = Auth::user(); // Fetch the authenticated user

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User not authenticated'
        ], 401);
    }

    $conditionTeam = ['team_id' => $teamId];

    $currentServeDetails = QueueStorage::currentVisitorRecord(
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
            'vistorId' => 'required|integer',
            'nextStorageId' => 'required|integer',
            'selectedCounter' => 'required|integer',
        ]);

        // Extract validated data
        $vistorId = $validatedData['vistorId'];
        $nextStorageId = $validatedData['nextStorageId'];
        $teamId = $validatedData['teamId'];
        $locationId = $validatedData['locationId'];
        $selectedCounter = $validatedData['selectedCounter'];

        // Define team condition
        $conditionTeam = ['team_id' => $teamId];

        // Get the queue storage details
        $getQueue = QueueStorage::find($nextStorageId);
        if (!$getQueue) {
            return response()->json(['status'=>'not_found','message' => 'Queue storage not found'], 404);
        }

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
        $progressCall = Queue::getProgressRecord($conditionTeam, Auth::id(), $locationId);
        
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
       
        // Send notification
        // $this->sendNotification($queueData, 'call');

        // SMS Reminder
        // Queue::smsReminderNumber($vistorId, $teamId);

        SendEmailJob::dispatch($queueData, 'call');

        // Get updated queue counts
        $tokenServed = Queue::totalTokenServed( $conditionTeam,auth()->user()->id, $locationId );
        $queuesCount = Queue::getPendingQueuesC($conditionTeam, Auth::user(), $locationId);
        $missedCalls = Queue::getMissedCallId($conditionTeam, $siteDetails->show_department_missed_queue, $locationId);

        return response()->json([
            'status' => 'success',
            'message' => 'Call successful!',
            // 'data' => $queueData,
            'servedToken' => $queueData,
            'queuesCount' => $queuesCount,
            'missedCalls' => $missedCalls,
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

public function startCall($id){

     if (empty($id)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Call ID is required.',
        ], 422);
    }
            $queueStorage  = QueueStorage::where('id',$id)->whereNotNull('called_datetime')->whereNull('start_datetime')->first();

            if ( !empty( $queueStorage ) ){

                $conditionTeam = ['team_id' => $queueStorage->team_id];

                $startCallRes = Queue::startCalledField( $conditionTeam, $queueStorage->queue_id,$queueStorage->counter_id, false, $queueStorage->location_id, $queueStorage->id );

                if ( $startCallRes == 'hold on' ) {
               
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This call is on Hold temporarily (start)',
                        
                    ], 422);
                }


                // ActivityLog::storeLog( $queueStorage->team_id, auth()->user()->id, $queueStorage->queue_id, 
                // $queueStorage->id, ActivityLog::QUEUE_STARTED, $queueStorage->locations_id );



        QueueCreated::dispatch($queueStorage);
        QueueProgress::dispatch($queueStorage);

        return response()->json([
            'status' => 'success',
            'message' => 'Call Start Successfully',
            'closeShowbtn' => true,
        ], 200);
        
            }else{
                return response()->json([
                   'status' => 'not_found',
                    'message' => 'No Start Call Found',
                ], 404);
            }
            
        }

    
   public function closeCall($id){
    
         if (empty($id)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Call ID is required.',
        ], 422);
    }

        $queueStorage  = QueueStorage::where('id',$id)->whereNotNull('start_datetime')->first();

        if ( !empty( $queueStorage ) ){

            $queueStorage->update( [ 'status'=>Queue::STATUS_CLOSE, 'closed_datetime'=>Carbon::now(), 'closed_by'=>auth()->user()->id, 'datetime'=>Carbon::now() ] );

            $siteDetail = SiteDetail::where('team_id', $queueStorage->team_id)
            ->where('location_id', $queueStorage->locations_id)
            ->select('id','queue_priority','show_department_missed_queue')->first();

            $conditionTeam = ['team_id' => $queueStorage->team_id];

            ActivityLog::storeLog( $queueStorage->team_id, auth()->user()->id, $queueStorage->queue_id, 
            $queueStorage->id, ActivityLog::QUEUE_CLOSED, $queueStorage->locations_id );

            // QueueProgress::dispatch($queueStorage );

            $feedbackSetting = FeedbackSetting::viewFeedbackSetting( $queueStorage->team_id );

            if ( $feedbackSetting?->form_after_closedcall == FeedbackSetting::STATUC_ACTIVE  && !empty( $queueStorage?->closed_datetime ) && $queueStorage?->ticket_mode  == Queue::TICKET_MODE_Walk_IN ) {

                $data = [];
                $userDetails = json_decode( $queueStorage?->json, true );

                $data[ 'to_mail' ] = isset( $userDetails[ 'email' ] ) ? $userDetails[ 'email' ] : ( isset( $userDetails[ 'Email' ] ) ? $userDetails[ 'Email' ] : null );
                $data[ 'name' ] = $queueStorage?->name;
                $data[ 'phone' ] = $queueStorage?->phone;
                $data[ 'phone_code' ] = $queueStorage->phone_code ?? '91';
                $data['locations_id'] = $queueStorage->locations_id;

                if ( !empty( $data[ 'to_mail' ] ) ) {
                    $data[ 'feedback_link' ] = request()->getSchemeAndHttpHost().'/rating/survey?code='. base64_encode( $queueStorage?->queue_id );

                    SendEmailJob::dispatch($data, 'rating survey');

                }
               

            }else{
                
                QueuePending::dispatch(QueueStorage::viewQueue($queueStorage->id)); 

                QueueStorage::where( 'queue_id', $queueStorage->queue_id )
                ->where( 'id','!=', $queueStorage->id )
                ->update( [ 'temp_hold'=>Queue::STATUS_NO ] );
   
             
                $queueType = $siteDetail?->queue_priority ?? Queue::DEFAULT_QUEUE;

                $queuesPending = Queue::getPendingQueues( $conditionTeam,false,$queueStorage->location_id,Queue::INITIAL_VISITOR_SHOW_COUNT,null, $queueStorage->team_id, $queueType);
                $tokenServed = Queue::totalTokenServed( $conditionTeam,auth()->user()->id, $queueStorage->location_id );
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
        }else{
            return response()->json([
               'status' => 'not_found',
                'message' => 'No Close Call Found',
            ], 404);
        }
    }



    public function reCall($id) {
        try {
             if (empty($id)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Call ID is required.',
                ], 422);
            }
    
            $getQueue  = QueueStorage::where('id',$id)->first();


            if (!empty( $getQueue )){
                ActivityLog::storeLog($getQueue['team_id'], auth()->user()->id, $getQueue->queue_id,  $getQueue->id,  ActivityLog::QUEUE_RECALLED, $getQueue->locations_id );

                $getQueue->update([
                    'datetime'=> date('Y-m-d H:i:s')
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
                    'token' => $getQueue->start_acronym.''.$getQueue->token,
                    'token_with_acronym'=>$getQueue->start_acronym,
                    'pending_count' => $getQueue->queue_count,
                    'waiting_time' => $getQueue->waiting_time,
                    'category_name' => isset($getCategory) ? $getCategory : '',
                    'secondC_name' => isset($getSubCategory) ? $getSubCategory : '',
                    'thirdC_name' => isset($getChildCategory) ? $getChildCategory : '',
                    'locations_id' => $getQueue->locations_id,
                ];
    
                SendEmailJob::dispatch($data, 'call');

                return response()->json([
                    'status' => 'success',
                    'message' => 'Recall Successfully',
                ], 200);
                
               
            }else{
                return response()->json([
                    'success' => 'not_found',
                    'message' => 'No Call Found',
                ], 404);
            }


        } catch (\Throwable $ex) {
            // Handle any other exceptions
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
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

}
