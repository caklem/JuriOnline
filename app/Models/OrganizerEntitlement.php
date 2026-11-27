<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizerEntitlement extends Model
{
    public const DEFAULT_EVENT_LIMIT = 3;

    protected $fillable = [
        'user_id',
        'event_limit',
        'features',
        'source',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'event_limit' => 'integer',
        'features' => 'array',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
