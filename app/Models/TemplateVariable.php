<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateVariable extends Model
{
    use HasFactory;

    protected $table ="templates_variables";

    protected $fillable = [
        'variable_name', 
        'description', 
        'example_value'
    ];

    public static function getVaribles(){

        return self::get()->pluck('variable_name', 'description')->toArray();
    }


    public static function viewVariable($value){
        return self::where(['variable_name'=>$value])->first()?->variable_name;
    }
}
