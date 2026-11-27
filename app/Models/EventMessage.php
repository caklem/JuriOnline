<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventMessage extends Model
{
    protected $fillable = [
        'event_id',
        'sender_id',
        'body',
        'is_hidden',
        'hidden_by',
        'hidden_at',
        'reported_at',
        'reported_by',
        'report_reason',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
        'hidden_at' => 'datetime',
        'reported_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
