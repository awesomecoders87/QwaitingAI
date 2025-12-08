<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Log;
use App\Events\QueueCreated;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreferBooking extends Model
{
     use HasFactory,SoftDeletes;

    protected $fillable = [ 'team_id',
    'work_permit',
    'reference_number',
    'pax',
    'booking_type',
    'booking_date',
    'refID',
    'name',
    'phone',
    'email',
    'category_id',
    'sub_category_id',
    'child_category_id',
    'level_id',
    'location_id',
    'start_time',
    'created_by',
    'cancel_reason',
    'cancel_remark',
    'interview_mode',
    'json',
    'staff_id',
    'status',
    'is_convert',
    'is_notification_sent',
    'is_rescheduled',
    'suspension_logs_id',
    'created_at',
    'updated_at' ];


    public function team(): BelongsTo
 {
        return $this->belongsTo(Tenant::class );
    }

    public function location(): BelongsTo
 {
    return $this->belongsTo(Location::class);

    }

    public function categories(): BelongsTo
 {
        return $this->belongsTo(Category::class, 'category_id', 'id')->where( [ 'level_id'=>Level::getFirstRecord()?->id ] );
}

    public function sub_category(): BelongsTo
 {
        return $this->belongsTo(Category::class, 'sub_category_id', 'id' )->where( [ 'level_id'=>Level::getSecondRecord()?->id ] );
    }
    public function book_sub_category(): BelongsTo
 {
        return $this->belongsTo(Category::class, 'sub_category_id', 'id' );
    }
    public function book_child_category(): BelongsTo
 {
        return $this->belongsTo( Category::class, 'child_category_id', 'id' );
    }

    public function child_category(): BelongsTo
 {
        return $this->belongsTo( Category::class, 'child_category_id', 'id' )->where( [ 'level_id'=>Level::getThirdRecord()?->id ] );
    }

    public function createdBy(): BelongsTo
 {
        return $this->belongsTo( User::class, 'created_by', 'id' );
    }
    public function staff(): BelongsTo
 {
        return $this->belongsTo( User::class, 'staff_id','id');
    }

}
