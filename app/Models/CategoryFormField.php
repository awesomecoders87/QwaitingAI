<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryFormField extends Model
{
    use HasFactory;
    protected $table ='category_form_field';

    protected $fillable = [
        'form_field_id',
        'category_id',
    ];

    public static function checkFieldCategory($fieldID,$data){
   

        if (!empty($data['thirdChildId']) && self::isFieldExist($fieldID, $data['thirdChildId'])) {
            return true;
        } elseif (!empty($data['secondChildId']) && self::isFieldExist($fieldID, $data['secondChildId'])) {
            return true;
        } elseif (!empty($data['selectedCategoryId']) && self::isFieldExist($fieldID, $data['selectedCategoryId'])) {
            return true;
        }

        return !self::where('form_field_id', $fieldID)->exists();
    }
  
    
     
    public static function isFieldExist($fieldID,$catID){
       return self::where(['form_field_id' =>$fieldID,'category_id'=>$catID])->exists();
    }

}