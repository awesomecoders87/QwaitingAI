<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueEmailTemplate extends Model
{
    use HasFactory;

    protected $table = 'queue_email_template';

    protected $fillable = [
        'team_id',
        'feedback_email',
        'ticket_email',
        'ticket_email_subject',
        'feedback_email_subject',
        'reminder_email_subject',
        'reminder_email',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class,'team_id','id');
    }
}
