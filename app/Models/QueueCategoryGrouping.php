<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueCategoryGrouping extends Model
{
    protected $table = 'queue_category_grouping';

    protected $fillable = [
        'team_id',
        'location_id',
        'category_id',
        'grouping_data',
        'created_by',
        'status',
    ];

    protected $casts = [
        'grouping_data' => 'array', // auto JSON decode/encode
        'status'        => 'boolean',
    ];
}
