<?php
namespace App\Models;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permission extends SpatiePermission
{

    protected $table ="permissions";
    protected $fillable = ["id","name","parent_type","guard_name","team_id","created_at","updated_at"];
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
    
}