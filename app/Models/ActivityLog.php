<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ActivityLog extends Model
 {
    use HasFactory;

    protected $table = 'activity_logs';
    protected $fillable = [
        'team_id',
        'type',
        'ip_address',
        'text',
        'call_book_id',
        'queues_storage_id',
        'location_id',
        'user_details',
        'remark',
        'created_by',
        'created_at',
        'updated_at'
    ];

    const TYPE_CALL = 'CALL';
    const LOGIN = 'LOGIN';
    const LOGOUT = 'LOGOUT';
    const TYPE_BOOKING = 'BOOKING';
    const QUEUE_REGISTERED = 'Queue Registered';
    const QUEUE_CALLED = 'Queue Called';
    const VISITOR_TRANSFER = 'Visitor Transfer';
    const NEXT_CALL = 'Next Call';
    const CALL_SKIPPED = 'Call Missed';
    const VISITOR_MOVE_BACK = 'Visitor Move Back';
    const QUEUE_RECALLED = 'Queue Recalled';
    const QUEUE_CLOSED = 'Queue Closed';
    const QUEUE_STARTED = 'Queue Started';
    const SYSTEM_LOGIN = 'System Login';
    const SEND_SMS = 'Send sms';
    const HOLD_QUEUE = 'Hold the Queue Call';
    const UNHOLD_QUEUE = 'Unhold the Queue Call';
    const CANCEL_CALL = 'Cancel the Queue Call';
    const CATEGORY = 'CATEGORY';
    const COUNTER = 'COUNTER';
    const STAFF = 'STAFF';
    const ROLE = 'ROLE';
    const SETTINGS = 'SETTINGS';
    const ADD = 'Add';
    const EDIT = 'Edit';
    const DELETE = 'Delete';
    const BULK_DELETE = 'Bulk Delete';
    const CHANGE = 'Change';
    const QUEUE = 'QUEUE';
    const WHATSAPP_MESSAGE = 'Whatsapp Message';
    const JUMPCLOUD_LOGIN = 'JUMPCLOUD LOGIN';
    const SERVICE_TRANSFER = 'Service Transfer';
    const COUNTER_TRANSFER = 'Counter Transfer';
    const RATING_SUBMITTED = 'Rating Submitted';


    public function team(): BelongsTo
 {
        return $this->belongsTo(Tenant::class );
    }

    public function createdBy(): BelongsTo
 {
        return $this->belongsTo( User::class, 'created_by', 'id' );
    }

    public function location(): BelongsTo
 {
        return $this->belongsTo( Location::class );
    }

    public static function storeLog( $teamID, $userAuthID, $callBookID = null,$queueStorageID = null, $text, $locationID, $type = self::TYPE_CALL, $remark = null, $userAuth = null ) {

        return self::create( [
            'team_id' => $teamID,
            'type' => $type,
            'text' => $text,
            'call_book_id' => (int)$callBookID,
            'queues_storage_id' => (int)$queueStorageID,
            'remark'=> $remark,
            'created_by' => $userAuthID,
            'ip_address' => request()->ip(),
            'location_id' => $locationID ?? '',
            'user_details' => !empty($userAuth) ? json_encode($userAuth, true) : null
        ] );
    }

    public static function viewLogs( $team_id, $call_book_id ,$storageID,$location=null) {
        return self::with('createdBy')->where(['team_id'=>$team_id,'location_id'=>$location,'queues_storage_id' => $storageID ])->get();
    }

    public static function filterSettingExcel( $selectedLocation, $filters ) {
        $data = [];
        $currentDate = Carbon::now()->format( 'd-m-Y' );
        $data[ 'Branch Name' ] = Location::locationName( $selectedLocation );
        $data[ 'Created From' ]  = ( !empty( $filters[ 'created_at' ][ 'created_from' ] ) ) ? Carbon::parse( $filters[ 'created_at' ][ 'created_from' ] )->format( 'd-m-Y' ) : $currentDate;
        $data[ 'Created Until' ]  = ( !empty( $filters[ 'created_at' ][ 'created_until' ] ) ) ? Carbon::parse( $filters[ 'created_at' ][ 'created_until' ] )->format( 'd-m-Y' ) : $currentDate;
        return $data;
    }

    public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}
}
