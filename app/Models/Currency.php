<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
     protected $table="currency";

    protected $fillable = [
        'name',
        'code',
        'dial_code',
        'currency_name',
        'currency_symbol',
        'currency_code',
        'hex_code',
    ];
}