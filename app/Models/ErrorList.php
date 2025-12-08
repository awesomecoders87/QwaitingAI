<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorList extends Model
{
    protected $fillable = [
        'code',
        'message',
        'description',
        'resolution',
        'type'
    ];
}
