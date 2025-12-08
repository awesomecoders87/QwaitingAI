<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{

    use HasFactory;
    protected $fillable =['name','team_id','location_id','level','tag_line','acronyms_show_level','created_at','updated_at'];


    public function team(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function levels(): HasMany{
        return $this->hasMany(Category::class);
    }


    public static function getFirstRecord(){
      return self::first();
    }
    public static function getSecondRecord(){
        return self::skip(1)->take(1)->first();
      }
      public static function getThirdRecord(){
        return self::skip(2)->take(1)->first();
      }
}
