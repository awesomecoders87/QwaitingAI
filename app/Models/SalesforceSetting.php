<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesforceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'location_id',
        'client_id',
        'client_secret',
        'redirect_uri',
        'created_by',
    ];

    public function team()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
