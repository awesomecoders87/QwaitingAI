<?php
namespace App\Models;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends SpatieRole
{

    protected $table ="roles";
    protected $fillable = ["id","name","guard_name","team_id",'location_id',"created_at","updated_at"];
    public function team(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    
}