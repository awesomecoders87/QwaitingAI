<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'location_id',
        'name',
        'phone',
        'email',
        'image',
        'json_data',
    ];

    // protected $casts = [
    //     'json_data' => 'array',
    // ];

    /**
     * Get the activity logs for the customer.
     */
    public function activityLogs()
    {
        return $this->hasMany(CustomerActivityLog::class);
    }
}
