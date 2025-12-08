<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
class Rating extends Model
{

    use HasFactory;
    protected $table ='ratings';
    protected $fillable =['queue_id','queue_storage_id','user_id','question','team_id','location_id','comment','created_at','updated_at'];
    const STATUC_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
  
    public function team(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
 
    public function queues(): BelongsTo
    {
        return $this->belongsTo(QueueStorage::class,'queue_id');
    }
    public function queuestorage(): BelongsTo
    {
        return $this->belongsTo(QueueStorage::class,'queue_storage_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
 
    public static function filterSettingExcel($selectedLocation,$filters){
        $data = [];
        $currentDate = Carbon::now()->format( 'd-m-Y' );
        $data[ 'Branch Name' ] = Location::locationName( $selectedLocation );
        $data[ 'Created From' ]  = ( !empty( $filters[ 'created_at' ][ 'created_from' ] ) ) ? Carbon::parse( $filters[ 'created_at' ][ 'created_from' ] )->format( 'd-m-Y' ) : $currentDate;
        $data[ 'Created Until' ]  = ( !empty( $filters[ 'created_at' ][ 'created_until' ] ) ) ? Carbon::parse( $filters[ 'created_at' ][ 'created_until' ] )->format( 'd-m-Y' ) : $currentDate;
        return $data;
      }
    
}
