<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
   protected $table = 'payment_settings';

    protected $fillable = [
        'team_id',
        'location_id',
        'currency',
        'api_key',
        'api_secret',
        'payment_service',
        'enable_payment',
        'category_level',
        'payment_applicable_to',
        'stripe_enable',
        'third_party_enable',
        'third_api_key',
        'third_webhook_url',
    ];


    public $timestamps = true;
}
