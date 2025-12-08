<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CronEmailData extends Model
{
    use HasFactory;
    protected $table = 'cron_email_data';

    // Define the fillable fields to protect against mass-assignment vulnerabilities
    protected $fillable = [
        'to_mail',
        'subject',
        'body',
        'smtp_details',
        'failed_detail',
        'status',
    ];

    // Cast json columns as arrays
    protected $casts = [
        'smtp_details' => 'array', // Convert the smtp_details column to an array
    ];
}
