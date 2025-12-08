<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserCategory extends Model
{
    use HasFactory;
    protected $table ='user_category';

    protected $fillable = [
        'user_id',
        'category_id',
        'created_at',
        'updated_at'
    ];

    
}
