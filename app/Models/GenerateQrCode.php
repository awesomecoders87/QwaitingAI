<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Session;

class GenerateQrCode extends Model
{

    use HasFactory;
    protected $table ='generate_qrcodes';
    protected $fillable =['qrcode_url_status','level_ecc','url_validity_str','url','created_by','team_id','margin','gradient_to','size','color','back_color','gradient_form','style','eye_style','location_id','scan_valid_distance','gradient_type','eye_color_inner','eye_color_outer','created_at','updated_at','qr_update_time','status','qrupdated_at'];
    const STATUC_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
    
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
 
    public static function getLevelEcc(){
        return [
              'L' =>'L - '.__('text.smallest'),
              'M' => 'M',
              'Q' => 'Q',
              'H' => 'H',
        ];
    } 

    public static function getSize(){
      return [
        '1'=>'1',
        '2'=>'2',
        '3'=>'3',
        '4'=>'4',
        '5'=>'5',
        '6'=>'6',
        '7'=>'7',
        '8'=>'8',
        '9'=>'9',
        '10'=>'10',
    ];
    }
    public static function getupdatetime(){
        return [
          '5' => '5 minutes',
          '10' => '10 minute',
          '15' => '15 minutes',
          '20' => '20 minutes',
          '25' => '25 minutes',
          '30' => '30 minutes',
      ];
      }

    public static function viewGeneratorCode($teamId)
    {
        $locationId = Session::get('selectedLocation') ?? null;

        $query = self::where('team_id', $teamId);
    
        if ($locationId !== null) {
            $query->where('location_id', $locationId);
        }
    
        return $query->first();
    }
    public static function getRadius($teamId,$locationId = null)
    {
        
        $query = self::where('team_id', $teamId);
    
        if ($locationId !== null) {
            $query->where('location_id', $locationId);
        }
    
        return $query->value('scan_valid_distance');
    }
}
